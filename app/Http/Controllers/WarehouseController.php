<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Warehouse::select('warehouses.*');

            return DataTables::of($query)
                ->addColumn('coordinates', function ($warehouse) {
                    if ($warehouse->latitude && $warehouse->longitude) {
                        return number_format($warehouse->latitude, 6) . ', ' . number_format($warehouse->longitude, 6);
                    }
                    return '<span class="text-muted">Not set</span>';
                })
                ->editColumn('address', function ($warehouse) {
                    return $warehouse->address ?? '-';
                })
                ->addColumn('action', function ($warehouse) {
                    return view('features.warehouses.partials.action-buttons', compact('warehouse'))->render();
                })
                ->editColumn('created_at', function ($warehouse) {
                    return $warehouse->created_at->format('Y-m-d');
                })
                ->rawColumns(['coordinates', 'action'])
                ->make(true);
        }

        // Get all warehouses for map
        $warehouses = Warehouse::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('features.warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'html' => view('features.warehouses.partials.form', [
                'warehouse' => null,
                'formAction' => route('warehouses.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Create New Warehouse'
            ])->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(WarehouseService::getCreateRules());

            $this->warehouseService->create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gudang berhasil dibuat.'
                ]);
            }

            return redirect()->route('warehouses.index')
                ->with('success', 'Gudang berhasil dibuat.');
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
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Warehouse $warehouse)
    {
        if (request()->ajax()) {
            return response()->json([
                'html' => view('features.warehouses.partials.show', compact('warehouse'))->render()
            ]);
        }

        return redirect()->route('warehouses.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warehouse $warehouse)
    {
        return response()->json([
            'html' => view('features.warehouses.partials.form', [
                'warehouse' => $warehouse,
                'formAction' => route('warehouses.update', $warehouse),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit Warehouse'
            ])->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        try {
            $validated = $request->validate(WarehouseService::getUpdateRules($warehouse));

            $this->warehouseService->update($warehouse, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gudang berhasil diperbarui.'
                ]);
            }

            return redirect()->route('warehouses.index')
                ->with('success', 'Gudang berhasil diperbarui.');
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
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        $this->warehouseService->delete($warehouse);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gudang berhasil dihapus.'
            ]);
        }

        return redirect()->route('warehouses.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }
}

