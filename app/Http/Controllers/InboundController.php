<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Location;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InboundController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of inbound transactions.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\Transaction::with(['item', 'toLocation.warehouse', 'user', 'supplier'])
                ->where('type', \App\Enums\TransactionType::INBOUND)
                ->select('transactions.*');

            return DataTables::of($query)
                ->addColumn('item_name', function ($transaction) {
                    return $transaction->item->name ?? '-';
                })
                ->addColumn('item_sku', function ($transaction) {
                    return $transaction->item->sku ?? '-';
                })
                ->addColumn('location_name', function ($transaction) {
                    return $transaction->toLocation ? $transaction->toLocation->code . ' - ' . ($transaction->toLocation->warehouse->name ?? '') : '-';
                })
                ->addColumn('supplier_name', function ($transaction) {
                    return $transaction->supplier->name ?? '-';
                })
                ->addColumn('user_name', function ($transaction) {
                    return $transaction->user->name ?? '-';
                })
                ->editColumn('created_at', function ($transaction) {
                    return $transaction->created_at->format('Y-m-d H:i:s');
                })
                ->make(true);
        }

        return view('features.inbound.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return redirect()->route('inbound.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return redirect()->route('inbound.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('inbound.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return redirect()->route('inbound.index');
    }

    /**
     * Show the form for creating a new inbound transaction.
     */
    public function create()
    {
        $warehouses = \App\Models\Warehouse::all();
        $suppliers = \App\Models\Supplier::all();
        return view('features.inbound.create', compact('warehouses', 'suppliers'));
    }

    /**
     * Store a newly created inbound transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'to_location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'batch' => 'nullable|string|max:255',
            'expired_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $transaction = $this->transactionService->createInbound(
                $validated['item_id'],
                $validated['to_location_id'],
                $validated['quantity'],
                $validated['batch'] ?? null,
                $validated['expired_at'] ?? null,
                $validated['notes'] ?? null,
                $validated['supplier_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi inbound berhasil dibuat.',
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get item by barcode
     */
    public function getItemByBarcode(Request $request)
    {
        $barcode = $request->input('barcode');

        $item = Item::where('barcode', $barcode)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan dengan barcode: ' . $barcode,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'barcode' => $item->barcode,
                'unit' => $item->unit,
            ],
        ]);
    }

    /**
     * Get locations by warehouse
     */
    public function getLocationsByWarehouse(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');

        $locations = Location::where('warehouse_id', $warehouseId)
            ->where('type', 'SLOT')
            ->with('parent')
            ->get()
            ->map(function ($location) {
                $path = $location->code;
                if ($location->parent) {
                    $path = $location->parent->code . ' > ' . $path;
                }
                return [
                    'id' => $location->id,
                    'code' => $location->code,
                    'path' => $path,
                ];
            });

        return response()->json([
            'success' => true,
            'locations' => $locations,
        ]);
    }

    /**
     * Search items by name, SKU, or barcode
     */
    public function searchItems(Request $request)
    {
        $query = $request->input('query');

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'items' => [],
            ]);
        }

        // Search items by name, SKU, or barcode
        $items = Item::where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('sku', 'like', '%' . $query . '%')
                  ->orWhere('barcode', 'like', '%' . $query . '%');
            })
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'unit' => $item->unit,
                ];
            });

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    /**
     * Search locations by code, path, or warehouse name
     */
    public function searchLocations(Request $request)
    {
        $query = $request->input('query');

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'locations' => [],
            ]);
        }

        // Search locations by code, path, or warehouse name
        $locations = Location::where('type', 'SLOT')
            ->with(['warehouse', 'parent'])
            ->where(function ($q) use ($query) {
                $q->where('code', 'like', '%' . $query . '%')
                  ->orWhereHas('warehouse', function ($wq) use ($query) {
                      $wq->where('name', 'like', '%' . $query . '%');
                  });
            })
            ->limit(20)
            ->get()
            ->map(function ($location) {
                $path = $location->code;
                if ($location->parent) {
                    $path = $location->parent->code . ' > ' . $path;
                }
                return [
                    'id' => $location->id,
                    'code' => $location->code,
                    'path' => $path,
                    'warehouse_name' => $location->warehouse->name ?? '',
                    'full_path' => ($location->warehouse->name ?? '') . ' - ' . $path,
                ];
            });

        return response()->json([
            'success' => true,
            'locations' => $locations,
        ]);
    }
}

