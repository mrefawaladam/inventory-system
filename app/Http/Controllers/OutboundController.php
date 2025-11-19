<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Location;
use App\Models\Stock;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OutboundController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of outbound transactions.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\Transaction::with(['item', 'fromLocation.warehouse', 'user'])
                ->where('type', \App\Enums\TransactionType::OUTBOUND)
                ->select('transactions.*');

            return DataTables::of($query)
                ->addColumn('item_name', function ($transaction) {
                    return $transaction->item->name ?? '-';
                })
                ->addColumn('item_sku', function ($transaction) {
                    return $transaction->item->sku ?? '-';
                })
                ->addColumn('location_name', function ($transaction) {
                    return $transaction->fromLocation ? $transaction->fromLocation->code . ' - ' . ($transaction->fromLocation->warehouse->name ?? '') : '-';
                })
                ->addColumn('user_name', function ($transaction) {
                    return $transaction->user->name ?? '-';
                })
                ->editColumn('created_at', function ($transaction) {
                    return $transaction->created_at->format('Y-m-d H:i:s');
                })
                ->make(true);
        }

        return view('features.outbound.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return redirect()->route('outbound.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return redirect()->route('outbound.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('outbound.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return redirect()->route('outbound.index');
    }

    /**
     * Show the form for creating a new outbound transaction.
     */
    public function create()
    {
        $warehouses = \App\Models\Warehouse::all();
        return view('features.outbound.create', compact('warehouses'));
    }

    /**
     * Store a newly created outbound transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'from_location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            // Check stock availability
            $totalStock = Stock::where('item_id', $validated['item_id'])
                ->where('location_id', $validated['from_location_id'])
                ->sum('quantity');

            if ($totalStock < $validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak mencukupi. Tersedia: {$totalStock}, Diminta: {$validated['quantity']}",
                ], 422);
            }

            $transaction = $this->transactionService->createOutbound(
                $validated['item_id'],
                $validated['from_location_id'],
                $validated['quantity'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi outbound berhasil dibuat.',
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
        $locationId = $request->input('location_id');

        if (!$locationId) {
            return response()->json([
                'success' => false,
                'message' => 'Location ID harus diisi',
            ], 422);
        }

        $item = Item::where('barcode', $barcode)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan dengan barcode: ' . $barcode,
            ], 404);
        }

        // Validate location exists
        $location = Location::find($locationId);
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi tidak ditemukan',
            ], 404);
        }

        // Get stock at location - ensure we get a numeric value (0 if no stock)
        $stock = Stock::where('item_id', (int) $item->id)
            ->where('location_id', (int) $locationId)
            ->sum('quantity') ?? 0;

        // Ensure stock is always an integer
        $stock = (int) $stock;

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'barcode' => $item->barcode,
                'unit' => $item->unit,
                'available_stock' => $stock,
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
            ->with(['parent', 'stocks'])
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
     * Search items by name, SKU, or barcode with stock information
     */
    public function searchItems(Request $request)
    {
        $query = $request->input('query');
        $locationId = $request->input('location_id');

        if (!$locationId) {
            return response()->json([
                'success' => false,
                'message' => 'Location ID harus diisi',
            ], 422);
        }

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'items' => [],
            ]);
        }

        // Validate location exists
        $location = Location::find($locationId);
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi tidak ditemukan',
            ], 404);
        }

        // Search items by name, SKU, or barcode
        $items = Item::where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('sku', 'like', '%' . $query . '%')
                  ->orWhere('barcode', 'like', '%' . $query . '%');
            })
            ->limit(20)
            ->get()
            ->map(function ($item) use ($locationId) {
                // Get stock at location
                $stock = Stock::where('item_id', (int) $item->id)
                    ->where('location_id', (int) $locationId)
                    ->sum('quantity') ?? 0;
                $stock = (int) $stock;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'unit' => $item->unit,
                    'available_stock' => $stock,
                ];
            });

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }
}

