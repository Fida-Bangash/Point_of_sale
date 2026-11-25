<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Manual correction of a customer's balance (e.g. a mistake in the old paper khata).
 * Admin and manager only. The route is protected by role middleware and the check below repeats it,
 * so the rule still holds if the route is ever moved. The ledger entry is written by
 * LedgerService::adjust() inside a database transaction and can only be undone by a reversal.
 */
class CustomerAdjustmentController extends Controller
{
    public function store(Request $request, Customer $customer, LedgerService $ledger)
    {
        abort_unless($request->user()->hasRole('admin', 'manager'), 403);

        $data = $request->validate([
            'direction' => ['required', Rule::in(['increase', 'decrease'])],
            'amount'    => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
            'reason'    => ['required', 'string', 'min:3', 'max:400'],
        ], [
            'amount.regex' => 'The amount can have at most 2 decimal places.',
        ]);

        $ledger->adjust(
            $customer,
            ($data['direction'] === 'increase' ? '' : '-') . $data['amount'],
            trim($data['reason']),
            $request->user()->id,
        );

        return back()->with('status', 'Balance adjusted. The change is recorded in the ledger.');
    }
}
