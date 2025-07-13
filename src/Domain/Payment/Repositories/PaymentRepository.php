<?php

namespace Domain\Payment\Repositories;

use Core\Http\Requests\TableRequest;
use Domain\Payment\Models\Transaction;
use Domain\Payment\Repositories\Contracts\IPaymentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class PaymentRepository implements IPaymentRepository
{
    /**
     * Get the identityRecords pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Transaction::query()
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('bank_transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('reference', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the identityRecord.
     * @param IdentityRecord $identityRecord
     * @return array
     */
    public function show(string $bankTransactionId) : array
    {

        $transaction = Transaction::query()
            ->where('bank_transaction_id', $bankTransactionId)
            ->first();

        return [
            'bank_transaction_id' => $transaction->bank_transaction_id,
            'reference' => $transaction->reference,
            'status' => $transaction->status,
            'message' => $transaction->message,
            'date' => Jalalian::fromDateTime($transaction->created_at)->format('Y-m-d H:i:s'),
        ];
    }

}