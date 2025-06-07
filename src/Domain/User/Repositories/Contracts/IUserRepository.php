<?php

namespace Domain\User\Repositories\Contracts;

/**
 * Interface IUserRepository.
 */
interface IUserRepository
{
    /**
     * Get the seller based on the given identifier.
     *
     * @return string The seller object
     */
    public function index() :string;
}
