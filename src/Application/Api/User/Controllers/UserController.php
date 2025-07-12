<?php

namespace Application\Api\User\Controllers;

use Application\Api\User\Requests\UpdateUserRequest;
use Core\Http\Controllers\Controller;
use Domain\User\Models\User;
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

    /**
     * Handle an incoming authentication request.
     */
    public function getActivityCount(): JsonResponse
    {
        return response()->json($this->repository->getActivityCount());
    }

    /**
     * Get the user info
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($this->repository->show($user));
    }

    /**
     * Get the user info
     * @return JsonResponse
     */
    public function checkVerification(): JsonResponse
    {
        return response()->json($this->repository->checkVerification());
    }

     /**
     * Update the user.
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user) :JsonResponse
    {
        return response()->json($this->repository->update($request, $user));
    }
}