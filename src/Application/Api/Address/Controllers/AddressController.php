<?php

namespace Application\Api\Address\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Address\Models\City;
use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Repositories\Contracts\IAddressRepository;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;


class AddressController extends Controller
{

    /**
     * @param IPlanRepository $repository
     */
    public function __construct(protected IAddressRepository $repository)
    {

    }

    /**
     * Get all countries.
     * @param TableRequest $request
     */
    public function activeCountries(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->activeCountries($request), Response::HTTP_OK);
    }

    /**
     * Get provinces of the country.
     * @param Country $country
     * @param TableRequest $request
     *
     * @return JsonResponse
     */
    public function activeProvinces(Country $country, TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->activeProvinces($country, $request), Response::HTTP_OK);
    }

    /**
     * Get all cities of the province.
     * @param Province $province
     * @param TableRequest $request
     *
     * @return JsonResponse
     */
    public function activeCities(Province $province, TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->activeCities($province, $request), Response::HTTP_OK);
    }

    /**
     * Get the address from city id
     * @param City $city
     *
     * @return JsonResponse
     */
    public function getCityDetails(City $city): JsonResponse
    {
        return response()->json($this->repository->getCityDetails($city), Response::HTTP_OK);
    }

    /**
     * Get cities with search title
     * @param TableRequest $request
     *
     * @return JsonResponse
     */
    public function getCitiesPaginate(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->getCitiesPaginate($request), Response::HTTP_OK);
    }
}
