<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Supplier::withCount([
                'transactions as inbound_count' => function ($query) {
                    $query->where('type', \App\Enums\TransactionType::INBOUND);
                }
            ])->select('suppliers.*');

            return DataTables::of($query)
                ->editColumn('phone', function ($supplier) {
                    return $supplier->phone ?? '-';
                })
                ->editColumn('address', function ($supplier) {
                    return $supplier->address ?? '-';
                })
                ->addColumn('inbound_count', function ($supplier) {
                    return $supplier->inbound_count ?? 0;
                })
                ->addColumn('action', function ($supplier) {
                    return view('features.suppliers.partials.action-buttons', compact('supplier'))->render();
                })
                ->editColumn('created_at', function ($supplier) {
                    return $supplier->created_at->format('Y-m-d');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('features.suppliers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('features.suppliers.partials.form', [
            'supplier' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(SupplierService::getCreateRules());

        try {
            $supplier = $this->supplierService->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil ditambahkan.',
                'data' => $supplier,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load('inbounds');
        return view('features.suppliers.partials.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('features.suppliers.partials.form', [
            'supplier' => $supplier,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate(SupplierService::getUpdateRules($supplier));

        try {
            $supplier = $this->supplierService->update($supplier, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil diperbarui.',
                'data' => $supplier,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $this->supplierService->delete($supplier);

            return response()->json([
                'success' => true,
                'message' => 'Supplier berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

