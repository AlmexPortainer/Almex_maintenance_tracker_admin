<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCatalogItemRequest;
use App\Http\Requests\UpdateCatalogItemRequest;
use App\Http\Resources\CatalogItemResource;
use App\Models\CatalogItem;

/**
 * @deprecated This class is deprecated.
 */
class CatalogItemController extends Controller
{
    public function index()
    {
        $items = CatalogItem::query()
            ->when(request('tipo_item'), fn ($q, $v) => $q->where('tipo_item', $v))
            ->orderBy('codigo')
            ->paginate(20);

        return CatalogItemResource::collection($items);
    }

    public function store(StoreCatalogItemRequest $request)
    {
        $item = CatalogItem::create($request->validated());

        return (new CatalogItemResource($item))->response()->setStatusCode(201);
    }

    public function show(CatalogItem $catalogItem)
    {
        $catalogItem->load('calibrations');

        return new CatalogItemResource($catalogItem);
    }

    public function update(UpdateCatalogItemRequest $request, CatalogItem $catalogItem)
    {
        $catalogItem->update($request->validated());

        return new CatalogItemResource($catalogItem->fresh());
    }

    public function destroy(CatalogItem $catalogItem)
    {
        $catalogItem->delete();

        return response()->noContent();
    }
}
