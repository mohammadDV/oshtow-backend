<?php

namespace Domain\User\Repositories\Contracts;

use Domain\User\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Interface IUserRepository.
 */
interface IUserRepository
{
    /**
     * Get the users
     *
     * @return JsonResponse The seller object
     */
    public function index() :JsonResponse;

    /**
     * Get Activity Count about the user
     *
     * @return string The seller object
     */
    public function getActivityCount() :JsonResponse;

    /**
     * Get the user info.
     * @param User $user
     * @return array
     */
    public function show(User $user) :array;

    /**
     * Get verification of the user
     * @return array
     */
    public function checkVerification() :array;
}
