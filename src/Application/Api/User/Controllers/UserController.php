<?php

namespace Application\Api\User\Controllers;

use Core\Http\Controllers\Controller;
use Domain\User\Repositories\Contracts\IUserRepository;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Constructor of UserController.
     */
    public function __construct(protected  IUserRepository $repository)
    {
        //
    }

    /**
     * Handle an incoming authentication request.
     */
    public function index(): JsonResponse
    {
        return response()->json($this->repository->index());
    }
}
