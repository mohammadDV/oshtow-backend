<?php

namespace Domain\Address\Repositories\Contracts;

use Core\Http\Requests\TableRequest;
use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface IAddressRepository.
 */
interface IAddressRepository
{
    /**
     * Get the countrys pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCountriesPaginate(TableRequest $request) :LengthAwarePaginator;
    /**
     * Get the countrys.
     * @return Collection
     */
    public function activeCountries() :Collection;

    /**
     * Get the provinces pagination.
     * @param Country $country
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getProvincesPaginate(Country $country, TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the provinces.
     *
     * @param Country $country
     * @return Collection
     */
    public function activeProvinces(Country $country) :Collection;


    /**
     * Get the provinces pagination.
     * @param Province $province
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCitiesPaginate(Province $province, TableRequest $request) :LengthAwarePaginator;

    /**
     * Get the cities.
     * @param Province $province
     * @return Collection
     */
    public function activeCities(Province $province) :Collection;
}
