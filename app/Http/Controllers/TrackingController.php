<?php

namespace App\Http\Controllers;

use App\Services\TrackingService;
use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Display the tracking map page
     */
    public function index(Request $request)
    {
        // Get filter data
        $items = Item::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        // Get initial routes (last 50 transactions)
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'type' => $request->get('type'),
            'item_id' => $request->get('item_id'),
            'warehouse_id' => $request->get('warehouse_id'),
        ];

        $routes = $this->trackingService->getTransactionsWithRoutes($filters);
        $warehousesMap = $this->trackingService->getWarehousesForMap();

        return view('features.tracking.index', compact(
            'routes',
            'warehousesMap',
            'items',
            'warehouses',
            'filters'
        ));
    }

    /**
     * Get route data via API (for AJAX requests)
     */
    public function getRoutes(Request $request)
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'type' => $request->get('type'),
            'item_id' => $request->get('item_id'),
            'warehouse_id' => $request->get('warehouse_id'),
        ];

        $routes = $this->trackingService->getTransactionsWithRoutes($filters);
        $warehousesMap = $this->trackingService->getWarehousesForMap();

        return response()->json([
            'success' => true,
            'routes' => $routes,
            'warehouses' => $warehousesMap,
        ]);
    }

    /**
     * Display item movement history page
     */
    public function itemHistory(Request $request, ?int $itemId = null)
    {
        $items = Item::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        $item = null;
        $itemDetails = null;
        $history = [];
        $warehousesMap = $this->trackingService->getWarehousesForMap();

        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'type' => $request->get('type'),
            'warehouse_id' => $request->get('warehouse_id'),
        ];

        // If item ID is provided, get history
        if ($itemId) {
            $item = Item::find($itemId);
            if ($item) {
                $itemDetails = $this->trackingService->getItemDetails($itemId);
                $history = $this->trackingService->getItemMovementHistory($itemId, $filters);
            }
        }

        return view('features.tracking.item-history', compact(
            'items',
            'warehouses',
            'item',
            'itemDetails',
            'history',
            'warehousesMap',
            'filters',
            'itemId'
        ));
    }

    /**
     * Get item history via API
     */
    public function getItemHistory(Request $request, int $itemId)
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'type' => $request->get('type'),
            'warehouse_id' => $request->get('warehouse_id'),
        ];

        $history = $this->trackingService->getItemMovementHistory($itemId, $filters);
        $itemDetails = $this->trackingService->getItemDetails($itemId);
        $warehousesMap = $this->trackingService->getWarehousesForMap();

        return response()->json([
            'success' => true,
            'history' => $history,
            'itemDetails' => $itemDetails,
            'warehouses' => $warehousesMap,
        ]);
    }
}

