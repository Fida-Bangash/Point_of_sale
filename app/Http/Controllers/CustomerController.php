<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\ShopSetting;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->withSum('ledger as balance', 'amount')
            ->orderByDesc('balance')   // biggest debtor first
            ->orderBy('name')
            ->get();

        return view('pages.customers', [
            'customers'   => $customers,
            'shop'        => ShopSetting::current(),
            'canSetLimit' => $this->canSetLimit($request),
        ]);
    }

    public function store(Request $request, LedgerService $ledger)
    {
        $this->prepare($request);

        $data = $request->validate($this->rules(), $this->messages());

        $opening = (float) ($data['opening_balance'] ?? 0);
        $data    = $this->customerData($data, $request);

        DB::transaction(function () use ($data, $opening, $ledger, $request) {
            $customer = Customer::create($data);

            // The opening balance is the first ledger entry, so the ledger stays the single source of truth.
            $ledger->openingBalance($customer, $opening, $request->user()->id);
        });

        return back()->with('status', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer)
    {
        $this->prepare($request);

        $data = $request->validate($this->rules($customer), $this->messages());

        // The balance is never edited here. It only changes through the ledger.
        $customer->update($this->customerData($data, $request));

        return back()->with('status', 'Customer updated successfully.');
    }

    /** Ledger page: every entry with a running balance, newest first. */
    public function ledger(Request $request, Customer $customer)
    {
        $running = 0.0;

        $rows = $customer->ledger()
            ->with('user:id,name')
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get()
            ->map(function ($entry) use (&$running) {
                $running = round($running + (float) $entry->amount, 2);

                return ['entry' => $entry, 'balance' => $running];
            });

        return view('pages.customer-ledger', [
            'customer' => $customer,
            'rows'     => $rows->reverse()->values(),
            'balance'  => $running,
            'shop'     => ShopSetting::current(),
            // Step 4: payment form, and which entries were already cancelled
            'paymentMethods' => CustomerLedger::PAYMENT_METHODS,
            'reversedIds'    => $customer->ledger()->whereNotNull('reversal_of_id')->pluck('reversal_of_id')->all(),
            'canCancel'      => $request->user()->hasRole('admin'),
            'canAdjust'      => $request->user()->hasRole('admin', 'manager'),
        ]);
    }


    /**
     * Redirect to WhatsApp with the customer's pending balance reminder.
     */
    public function reminder(Customer $customer)
    {
        $balance = $customer->currentBalance();

        abort_if(
            $balance <= 0,
            404,
            'Nothing is pending for this customer.'
        );

        $number = $customer->whatsappNumber();

        abort_if(
            $number === null,
            422,
            'This customer has no valid mobile number.'
        );

        $text = $customer->reminderText(
            $balance,
            ShopSetting::current()
        );

        return redirect()->away(
            'https://wa.me/' . $number . '?text=' . rawurlencode($text)
        );
    }


    private function prepare(Request $request): void
    {
        $request->merge([
            'name'  => trim((string) $request->name),
            'phone' => Customer::normalizePhone((string) $request->phone),
        ]);
    }

    private function rules(?Customer $customer = null): array
    {
        $rules = [
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => [
                'required', 'regex:' . Customer::PHONE_REGEX,
                Rule::unique('customers', 'phone')->ignore($customer?->id),
            ],
            'address'      => ['nullable', 'string', 'max:500'],
            'city'         => ['nullable', 'string', 'max:100'],
            'credit_limit' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'is_active'    => ['required', 'boolean'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];

        if ($customer === null) {
            $rules['opening_balance'] = ['nullable', 'numeric', 'min:0', 'max:9999999999.99'];
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'phone.regex'  => 'Phone number must be 11 digits and start with 03 (e.g. 03001234567).',
            'phone.unique' => 'A customer with this phone number already exists.',
        ];
    }

    /** Cashiers can add customers, but only admin/manager may set a credit limit. */
    private function customerData(array $data, Request $request): array
    {
        $data = Arr::except($data, ['opening_balance']);

        if (! $this->canSetLimit($request)) {
            unset($data['credit_limit']);
        }

        return $data;
    }

    private function canSetLimit(Request $request): bool
    {
        return in_array($request->user()->role, ['admin', 'manager'], true);
    }
}