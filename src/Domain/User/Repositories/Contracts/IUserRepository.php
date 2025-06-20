<?php

namespace Domain\User\Repositories\Contracts;

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
}