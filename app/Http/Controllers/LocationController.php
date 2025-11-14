<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Warehouse;
use App\Services\LocationService;
use Illuminate\Http\Request;
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
            $query = Location::with(['warehouse', 'parent'])
                ->select('locations.*');

            // Filter by warehouse if provided
            if ($request->has('warehouse_id') && $request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            // Filter by type if provided
            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            return DataTables::of($query)
                ->addColumn('warehouse_name', function ($location) {
                    return $location->warehouse->name ?? '-';
                })
                ->addColumn('parent_code', function ($location) {
                    return $location->parent->code ?? '-';
                })
                ->addColumn('type_label', function ($location) {
                    return $location->type->label();
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
                ->rawColumns(['action'])
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
            $validated = $request->validate(LocationService::getCreateRules());

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
        $warehouseId = $request->get('warehouse_id');
        $type = $request->get('type');
        $parentId = $request->get('parent_id');

        $query = Location::where('warehouse_id', $warehouseId);

        if ($type) {
            $query->where('type', $type);
        }

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        $locations = $query->get();

        return response()->json($locations);
    }
}

