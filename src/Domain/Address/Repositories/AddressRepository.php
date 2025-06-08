<?php

namespace Domain\Address\Repositories;

use Core\Http\Requests\TableRequest;
use Domain\Address\models\City;
use Domain\Address\models\Country;
use Domain\Address\models\Province;
use Domain\Address\Repositories\Contracts\IAddressRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class AddressRepository.
 */
class AddressRepository implements IAddressRepository
{
    /**
     * Get the countrys pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCountriesPaginate(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Country::query()
            // ->when(Auth::user()->level != 3, function ($query) {
            //     return $query->where('user_id', Auth::user()->id);
            // })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
                    // ->orWhere('alias_title','like','%' . $search . '%');
            })
            ->orderBy($request->get('sortBy', 'id'), $request->get('sortType', 'desc'))
            ->paginate($request->get('rowsPerPage', 25));
    }

    /**
     * Get the countrys.
     * @return Collection
     */
    public function getCountries() :Collection
    {
        return Country::query()
            ->where('status', 1)
            ->get();
    }


    /**
     * Get the provinces pagination.
     * @param Country $country
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getProvincesPaginate(Country $country, TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Province::query()
            ->where('country_id', $country->id)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('sortBy', 'id'), $request->get('sortType', 'desc'))
            ->paginate($request->get('rowsPerPage', 25));
    }

    /**
     * Get the provinces.
     *
     * @param Country $country
     * @return Collection
     */
    public function getProvinces(Country $country) :Collection
    {
        return Province::query()
            ->where('country_id', $country->id)
            ->where('status', 1)
            ->get();
    }


    /**
     * Get the provinces pagination.
     * @param Province $province
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCitiesPaginate(Province $province, TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return City::query()
            ->where('province_id', $province->id)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('sortBy', 'id'), $request->get('sortType', 'desc'))
            ->paginate($request->get('rowsPerPage', 25));
    }

    /**
     * Get the cities.
     * @param Province $province
     * @return Collection
     */
    public function getCities(Province $province) :Collection
    {
        return City::query()
            ->where('province_id', $province->id)
            ->where('status', 1)
            ->get();
    }
}
