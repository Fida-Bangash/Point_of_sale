<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;        
use App\Models\SaleReturnItem;
use App\Models\ShopSetting;
use App\Models\Exchange;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SyncService
{
    // Order important: products ko category/brand/unit pehle chahiye
    public const MODELS = [
        'units'      => Unit::class,
        'categories' => Category::class,
        'brands'     => Brand::class,
        'products'   => Product::class,
        'users'      => User::class,
        'shop_settings' => ShopSetting::class,
        'sales'         => Sale::class,
        'sale_items'    => SaleItem::class,
        'sale_returns'      => SaleReturn::class,
        'sale_return_items' => SaleReturnItem::class,
        'exchanges'         => Exchange::class,
    ];

    public const IMAGE_COLUMNS = ['products' => 'image', 'shop_settings' => 'logo'];

    public static function queue(string $table, string $uuid, string $action): void
    {
        DB::table('sync_queue')->upsert(
            [['table_name' => $table, 'uuid' => $uuid, 'action' => $action, 'tries' => 0,
              'last_error' => null, 'created_at' => now(), 'updated_at' => now()]],
            ['table_name', 'uuid'],
            ['action', 'tries', 'last_error', 'updated_at']
        );
    }

    public static function get(string $key): ?string
    {
        return DB::table('settings')->where('key', $key)->value('value');
    }

    public static function put(string $key, ?string $value): void
    {
        DB::table('settings')->upsert([['key' => $key, 'value' => $value]], ['key'], ['value']);
    }

    public function saveSettings(string $url, string $token): void
    {
        self::put('sync_url', rtrim($url, '/'));
        self::put('sync_token', Crypt::encryptString($token));
    }

    /** Shop code from SYNC_SHOP_CODE in .env; null when not set. */
    private function shopCode(): ?string
    {
        $code = trim((string) (self::get('shop_code') ?: config('sync.shop_code')));

        return $code !== '' ? strtoupper($code) : null;
    }

    private function client(): ?\Closure
    {
        $url = self::get('sync_url') ?: config('sync.url');
        $enc = self::get('sync_token');
        if (! $url || ! $enc) {
            return null;
        }
        $token = Crypt::decryptString($enc);

        return fn () => Http::withToken($token)->acceptJson()->timeout(20)->baseUrl($url . '/api/sync');
    }

        // First run: register this PC with the server and keep the token it returns
    private function enroll(): bool
    {
        $url = rtrim((string) (self::get('sync_url') ?: config('sync.url')), '/');
        $key = config('sync.enroll_key');
        if (! $url || ! $key) {
            return false;
        }

        $id = self::get('install_id');
        if (! $id) {
            $id = (string) Str::uuid();
            self::put('install_id', $id);
        }

        try {
            $res = Http::withHeaders(['X-Enroll-Key' => $key])
                ->acceptJson()->timeout(10)
                ->post($url . '/api/sync/enroll', array_filter([
                    'install_id' => $id,
                    'name'       => gethostname() ?: 'POS',
                    // Groups the PCs of one shop on the server (optional)
                    'shop_code'  => $this->shopCode(),
                ], fn ($v) => $v !== null && $v !== ''));
        } catch (\Throwable) {
            return false;
        }

        if (! $res->successful()) {
            return false;
        }

        if ($token = $res->json('token')) {
            self::put('sync_token', Crypt::encryptString($token));

            return true;
        }

        // Server knows this id but we never got the token: start over with a new id
        self::put('install_id', null);

        return false;
    }

    public function run(int $maxBatches = 5): array
{
    $client = $this->client();
    if (! $client) {
        if (! $this->enroll() || ! ($client = $this->client())) {
            return ['status' => 'not_configured'];
        }
    }

    $lock = Cache::lock('sync-run', 120);
    if (! $lock->get()) {
        return ['status' => 'busy'];
    }

    try {
        try {
            $client()->timeout(5)->get('ping')->throw();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // 401 = this device is not approved yet (or was disabled)
            return ['status' => $e->response->status() === 401 ? 'not_approved' : 'offline'];
        } catch (\Throwable) {
            return ['status' => 'offline']; // no net/server: quietly go back
        }

        $sent = 0;
        foreach (self::MODELS as $table => $class) {
            for ($i = 0; $i < $maxBatches; $i++) {
                $items = DB::table('sync_queue')->where('table_name', $table)
                    ->where('tries', '<', 10)->orderBy('id')->limit(100)->get();
                if ($items->isEmpty()) {
                    break;
                }

                $upserts = [];
                $deletes = [];
                foreach ($items as $q) {
                    $m = $q->action === 'upsert' ? $class::where('uuid', $q->uuid)->first() : null;
                    if ($m) {
                        $upserts[] = $m->toSync();
                    } else {
                        $deletes[] = $q->uuid;
                    }
                }

                $rejected = [];
                try {
                    $imageColumn = self::IMAGE_COLUMNS[$table] ?? null;
                    if ($imageColumn) {
                        foreach ($upserts as $row) {
                            $this->pushImage($client, $row[$imageColumn] ?? null);
                            }
                        }
                    $res = $client()->post('push', compact('table', 'upserts', 'deletes'))->throw();
                    $rejected = collect($res->json('rejected', []))->pluck('uuid')->filter()->all();
                } catch (\Throwable $e) {
                    DB::table('sync_queue')->whereIn('id', $items->pluck('id'))->update([
                        'tries'      => DB::raw('tries + 1'),
                        'last_error' => substr($e->getMessage(), 0, 250),
                    ]);
                    return ['status' => 'error', 'message' => $e->getMessage(), 'sent' => $sent];
                }

                foreach ($items as $q) {
                    if (in_array($q->uuid, $rejected, true)) {
                        // Server ne reject kiya: dobara try nahi hoga, "failed" ginti mein aayega
                        DB::table('sync_queue')->where('id', $q->id)
                            ->update(['tries' => 10, 'last_error' => 'Server ne reject kiya']);
                        continue;
                    }
                    DB::table('sync_queue')->where('id', $q->id)
                        ->where('updated_at', $q->updated_at)->delete();
                }
                $sent += $items->count();
            }
        }

        self::put('last_sync_at', now()->toDateTimeString());

        return ['status' => 'ok', 'sent' => $sent];
    } finally {
        $lock->release();
    }
}

    private function pushImage(\Closure $client, ?string $image): void
    {
        $disk = Storage::disk('public');
        if (! $image || ! $disk->exists($image)) {
            return;
        }
        $client()->attach('file', $disk->get($image), basename($image))
            ->post('image', ['path' => $image])->throw();
    }

        public function restoreWithLogin(string $email, string $password, ?int $deviceId = null): array
{
    $url = rtrim((string) (self::get('sync_url') ?: config('sync.url')), '/');
    $key = config('sync.enroll_key');
    if (! $url || ! $key) {
        throw new \RuntimeException('Server URL is not configured in this app.');
    }

    // Pehle se request bhej chuke hain? Ho sakta hai ab approve ho gayi ho.
    if (self::get('sync_token') && self::get('install_id') && ($client = $this->client())) {
        $approved = false;
        try {
            $client()->timeout(5)->get('ping')->throw();
            $approved = true;
        } catch (\Throwable) {
            // abhi approve nahi hui, neeche naya request jayega
        }
        if ($approved) {
            $this->restore();
            return ['status' => 'ok'];
        }
    }

    $installId = self::get('install_id') ?: (string) Str::uuid();
    self::put('install_id', $installId);

    try {
        $res = Http::withHeaders(['X-Enroll-Key' => $key])
            ->acceptJson()->timeout(20)
            ->post($url . '/api/sync/restore-request', array_filter([
                'email'      => $email,
                'password'   => $password,
                'install_id' => $installId,
                'device_id'  => $deviceId,
            ], fn ($v) => $v !== null));
    } catch (\Throwable) {
        throw new \RuntimeException('Cannot reach the server. Check your internet connection and try again.');
    }

    if (! $res->successful()) {
        throw new \RuntimeException($res->json('message') ?: 'Restore failed (HTTP ' . $res->status() . ').');
    }

    if ($res->json('status') === 'choose') {
        return ['status' => 'choose', 'devices' => $res->json('devices', [])];
    }

    $token = $res->json('token');
    if (! $token) {
        throw new \RuntimeException('The server did not return a token.');
    }

    self::put('sync_url', $url);
    self::put('sync_token', Crypt::encryptString($token));

    // Server ne request pending rakhi hai: administrator approve karega
    if ($res->json('status') === 'pending') {
        throw new \RuntimeException('Request bhej di gayi hai. Administrator ke approve karne ke baad dobara Restore dabayein.');
    }

    $this->restore();

    return ['status' => 'ok'];
}

    public function restore(): void
    {
        $client = $this->client() ?? throw new \RuntimeException('Server URL or token is missing.');
        $images = [];

        // All-or-nothing: agar beech mein toota to kuch save nahi hota, dobara try kar sakte hain
        DB::transaction(function () use ($client, &$images) {
            foreach (self::MODELS as $table => $class) {
                $after = 0;
                do {
                    $res = $client()->get('pull', ['table' => $table, 'after' => $after])->throw()->json();
                    foreach ($res['rows'] as $row) {
                        $class::applyFromServer($row);
                        $imageColumn = self::IMAGE_COLUMNS[$table] ?? null;
                            if ($imageColumn && ! empty($row[$imageColumn])) {
                                $images[] = $row[$imageColumn];
                                    }
                                }
                    $after = $res['last_id'] ?? $after;
                } while (count($res['rows']) >= 200);
            }
        });

        DB::table('sync_queue')->delete();
        
        foreach ($images as $image) {
            $this->pullImage($client, $image);
        }

        self::put('last_sync_at', now()->toDateTimeString());
    }

    private function pullImage(\Closure $client, string $image): void
    {
        // Network se aaya hua path kabhi trust nahi karna
        if (! preg_match('#^(products|shop)/[A-Za-z0-9][A-Za-z0-9._-]*$#', $image)) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($image)) {
            return;
        }

        try {
            $res = $client()->get('image', ['path' => $image]);
            if ($res->successful()) {
                $disk->put($image, $res->body());
            }
        } catch (\Throwable) {
            // ek photo na aaye to restore na ruke
        }
    }
}