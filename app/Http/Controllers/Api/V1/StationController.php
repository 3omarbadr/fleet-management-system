<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\V1\StationResource;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StationController extends BaseApiController
{
    /**
     * Get all stations.
     */
    public function index(): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () {
            $stations = Station::orderBy('name')->get();

            return $this->successResponse(
                StationResource::collection($stations),
                'Stations retrieved successfully.'
            );
        });
    }

    /**
     * Get a specific station.
     */
    public function show(Station $station): JsonResponse
    {
        return $this->executeWithExceptionHandling(function () use ($station) {
            return $this->successResponse(
                new StationResource($station),
                'Station retrieved successfully.'
            );
        });
    }
}
