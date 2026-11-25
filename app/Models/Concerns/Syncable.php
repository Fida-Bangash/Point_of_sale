<?php

namespace App\Models\Concerns;

use App\Services\SyncService;
use Illuminate\Support\Str;

trait Syncable
{
    public static function bootSyncable(): void
    {
        static::creating(fn ($m) => $m->uuid ??= (string) Str::uuid());
        static::saved(fn ($m) => SyncService::queue($m->getTable(), $m->uuid, 'upsert'));
        static::deleted(fn ($m) => SyncService::queue($m->getTable(), $m->uuid, 'delete'));
    }

    public function syncRelations(): array
    {
        return [];
    }

    public function toSync(): array
    {
        $raw  = $this->getAttributes();
        $data = [
            'uuid'       => $this->uuid,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];

        foreach (static::SYNC_COLUMNS as $c) {
            $data[$c] = $raw[$c] ?? null;
        }

        foreach ($this->syncRelations() as $fk => [$class, $key]) {
            $data[$key] = $this->{$fk} ? $class::whereKey($this->{$fk})->value('uuid') : null;
        }

        return $data;
    }

    public static function applyFromServer(array $row): void
{
    $m = static::where('uuid', $row['uuid'])->first();

    if ($m && defined(static::class . '::APPEND_ONLY') && static::APPEND_ONLY) {
    return;
    }

    // Migration ki default rows (jaise Unit "Box") ka uuid server se alag hota hai:
    // unhe naam se dhoondo aur server ka uuid de do
    if (! $m && defined(static::class . '::NATURAL_KEY')) {
        $m = static::query()->where(function ($q) use ($row) {
            foreach (static::NATURAL_KEY as $c) {
                $q->orWhere($c, $row[$c] ?? null);
            }
        })->first();
    }

    $m ??= new static;
    $m->uuid = $row['uuid'];

    foreach (static::SYNC_COLUMNS as $c) {
        $m->{$c} = $row[$c] ?? null;
    }

    foreach ($m->syncRelations() as $fk => [$class, $key]) {
        $m->{$fk} = ! empty($row[$key]) ? $class::where('uuid', $row[$key])->value('id') : null;
    }

    $m->timestamps = false;
    $m->created_at = $row['created_at'];
    $m->updated_at = $row['updated_at'];
    $m->saveQuietly();
}
}