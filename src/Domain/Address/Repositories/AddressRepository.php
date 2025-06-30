<?php

namespace Domain\Address\Repositories;

use Core\Http\Requests\TableRequest;
use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
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
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the countrys.
     * @return Collection
     */
    public function activeCountries() :Collection
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
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the provinces.
     *
     * @param Country $country
     * @return Collection
     */
    public function activeProvinces(Country $country) :Collection
    {
        return Province::query()
            ->where('country_id', $country->id)
            ->where('status', 1)
            ->get();
    }


    /**
     * Get the provinces pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getCitiesPaginate(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return City::query()
            ->with('province.country')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the cities.
     * @param Province $province
     * @return Collection
     */
    public function activeCities(Province $province) :Collection
    {
        return City::query()
            ->where('province_id', $province->id)
            ->where('status', 1)
            ->get();
    }

    /**
     * Get address from city id
     * @param City $city
     * @return JsonResponse
     */
    public function getCityDetails(City $city) :Collection
    {
        return City::query()
            ->with('province.country')
            ->where('id', $city->id)
            ->get();
    }
}