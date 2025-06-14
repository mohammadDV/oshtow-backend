<?php

namespace Application\Api\Address\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Requests\TableRequest;
use Domain\Address\models\Country;
use Domain\Address\models\Province;
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
}
