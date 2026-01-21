<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalibrationRequest;
use App\Http\Requests\UpdateCalibrationRequest;
use App\Http\Resources\CalibrationResource;
use App\Models\Calibration;
use App\Models\CatalogItem;

/**
 * @deprecated This class is deprecated.
 */
class CalibrationController extends Controller
{
    public function store(StoreCalibrationRequest $request, CatalogItem $catalogItem)
    {
        $cal = $catalogItem->calibrations()->create($request->validated());

        return (new CalibrationResource($cal->fresh()))->response()->setStatusCode(201);
    }

    public function update(UpdateCalibrationRequest $request, CatalogItem $catalogItem, Calibration $calibration)
    {
        abort_unless($calibration->catalog_item_id === $catalogItem->id, 404);
        $calibration->update($request->validated());

        return new CalibrationResource($calibration->fresh());
    }

    public function destroy(CatalogItem $catalogItem, Calibration $calibration)
    {
        abort_unless($calibration->catalog_item_id === $catalogItem->id, 404);
        $calibration->delete();

        return response()->noContent();
    }
}
