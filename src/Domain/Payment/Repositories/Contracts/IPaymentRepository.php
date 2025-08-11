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
}
