<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Warehouse;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Baca parameter delivery_status dari berbagai sumber
            $deliveryStatus = $request->input('delivery_status') 
                ?: $request->get('delivery_status') 
                ?: $request->query('delivery_status');
            
            $warehouseId = $request->input('warehouse_id') 
                ?: $request->get('warehouse_id') 
                ?: $request->query('warehouse_id');
            
            // Debug logging
            \Log::info('Location Filter Debug', [
                'delivery_status' => $deliveryStatus,
                'warehouse_id' => $warehouseId,
                'all_params' => $request->all(),
            ]);
            
            // Build query dengan filter
            $query = Location::with(['warehouse', 'parent']);

            // Filter by warehouse if provided
            if (!empty($warehouseId)) {
                $query->where('warehouse_id', $warehouseId);
            }

            // Filter by delivery status if provided
            if (!empty($deliveryStatus)) {
                if ($deliveryStatus === 'delivered') {
                    // Sudah Dikirim: lokasi yang memiliki stock dengan quantity > 0
                    $query->whereHas('stocks', function ($stockQuery) {
                        $stockQuery->where('quantity', '>', 0);
                    });
                } elseif ($deliveryStatus === 'pending') {
                    // Belum Dikirim: lokasi yang tidak memiliki stock dengan quantity > 0
                    $query->whereDoesntHave('stocks', function ($stockQuery) {
                        $stockQuery->where('quantity', '>', 0);
                    });
                }
            }

            // Add delivered_stock_quantity menggunakan subquery (karena withSum tidak bekerja dengan whereHas)
            $query->selectRaw('locations.*, 
                COALESCE((
                    SELECT SUM(quantity) 
                    FROM stocks 
                    WHERE stocks.location_id = locations.id 
                    AND stocks.quantity > 0
                ), 0) as delivered_stock_quantity');

            return DataTables::of($query)
                ->addColumn('warehouse_name', function ($location) {
                    return $location->warehouse->name ?? '-';
                })
                ->addColumn('parent_code', function ($location) {
                    return $location->parent->code ?? '-';
                })
                ->addColumn('delivery_status', function ($location) {
                    $hasDelivered = ($location->delivered_stock_quantity ?? 0) > 0;
                    $badgeClass = $hasDelivered ? 'bg-success' : 'bg-danger';
                    $label = $hasDelivered ? 'Sudah Dikirim' : 'Belum Dikirim';
                    return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
                })
                ->addColumn('full_path', function ($location) {
                    return $location->full_path;
                })
                ->addColumn('action', function ($location) {
                    return view('features.locations.partials.action-buttons', compact('location'))->render();
                })
                ->editColumn('created_at', function ($location) {
                    return $location->created_at->format('Y-m-d');
                })
                ->rawColumns(['delivery_status', 'action'])
                ->make(true);
        }

        // Get all warehouses for filter dropdown
        $warehouses = Warehouse::all();

        return view('features.locations.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::all();
        
        return response()->json([
            'html' => view('features.locations.partials.form', [
                'location' => null,
                'warehouses' => $warehouses,
                'formAction' => route('locations.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Tambah Lokasi Baru'
            ])->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Get base rules
            $rules = LocationService::getCreateRules();
            
            // Make parent_id required for RACK and SLOT
            $type = $request->input('type');
            if ($type === \App\Enums\LocationType::RACK->value || $type === \App\Enums\LocationType::SLOT->value) {
                $rules['parent_id'] = 'required|exists:locations,id';
            }
            
            $validated = $request->validate($rules);

            $this->locationService->create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lokasi berhasil dibuat.'
                ]);
            }

            return redirect()->route('locations.index')
                ->with('success', 'Lokasi berhasil dibuat.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        if (request()->ajax()) {
            if (request()->wantsJson()) {
                // Return JSON for API requests
                return response()->json([
                    'location' => [
                        'id' => $location->id,
                        'code' => $location->code,
                        'type' => $location->type->value,
                        'parent_id' => $location->parent_id,
                        'warehouse_id' => $location->warehouse_id,
                    ]
                ]);
            }
            return response()->json([
                'html' => view('features.locations.partials.show', compact('location'))->render()
            ]);
        }

        return redirect()->route('locations.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        $warehouses = Warehouse::all();
        
        return response()->json([
            'html' => view('features.locations.partials.form', [
                'location' => $location,
                'warehouses' => $warehouses,
                'formAction' => route('locations.update', $location),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit Lokasi'
            ])->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        try {
            $validated = $request->validate(LocationService::getUpdateRules($location));

            $this->locationService->update($location, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lokasi berhasil diperbarui.'
                ]);
            }

            return redirect()->route('locations.index')
                ->with('success', 'Lokasi berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        try {
            $this->locationService->delete($location);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lokasi berhasil dihapus.'
                ]);
            }

            return redirect()->route('locations.index')
                ->with('success', 'Lokasi berhasil dihapus.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get locations by warehouse and type (for AJAX dropdowns)
     */
    public function getByWarehouse(Request $request)
    {
        try {
            $warehouseId = $request->get('warehouse_id');
            $type = $request->get('type');
            $parentId = $request->get('parent_id');

            if (!$warehouseId) {
                return response()->json([]);
            }

            $query = Location::where('warehouse_id', $warehouseId);

            if ($type) {
                $query->where('type', $type);
            }

            // Filter by parent_id
            if ($parentId) {
                $query->where('parent_id', $parentId);
            } else {
                // If no parent_id provided, get root locations (parent_id is null)
                $query->whereNull('parent_id');
            }

            $locations = $query->orderBy('code')->get();

            return response()->json($locations);
        } catch (\Exception $e) {
            \Log::error('Error in getByWarehouse: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

