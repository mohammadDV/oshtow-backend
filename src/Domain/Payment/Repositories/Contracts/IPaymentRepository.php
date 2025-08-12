<?php

namespace Domain\Payment\Repositories\Contracts;

use Core\Http\Requests\TableRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface IPaymentRepository
{
    /**
     * Get the transaction pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the transaction result.
     * @param string $bankTransactionId
     * @return array
     */
    public function show(string $bankTransactionId) : array;
}