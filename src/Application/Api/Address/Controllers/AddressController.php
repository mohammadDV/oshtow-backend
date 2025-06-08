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
     * @param TelegramNotificationService $service
     */
    public function __construct(protected IAddressRepository $repository)
    {

    }

    /**
     * Get all countries.
     * @param TableRequest $request
     */
    public function getCountries(TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->getCountries($request), Response::HTTP_OK);
    }

    /**
     * Get provinces of the country.
     * @param Country $country
     * @param TableRequest $request
     *
     * @return JsonResponse
     */
    public function getProvinces(Country $country, TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->getProvinces($country, $request), Response::HTTP_OK);
    }

    /**
     * Get all cities of the province.
     * @param Province $province
     * @param TableRequest $request
     *
     * @return JsonResponse
     */
    public function getCities(Province $province, TableRequest $request): JsonResponse
    {
        return response()->json($this->repository->getCities($province, $request), Response::HTTP_OK);
    }
}
