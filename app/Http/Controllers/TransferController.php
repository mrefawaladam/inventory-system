<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Location;
use App\Models\Stock;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of transfer transactions.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\Transaction::with(['item', 'fromLocation.warehouse', 'toLocation.warehouse', 'user'])
                ->where('type', \App\Enums\TransactionType::TRANSFER)
                ->select('transactions.*');

            return DataTables::of($query)
                ->addColumn('item_name', function ($transaction) {
                    return $transaction->item->name ?? '-';
                })
                ->addColumn('item_sku', function ($transaction) {
                    return $transaction->item->sku ?? '-';
                })
                ->addColumn('from_location_name', function ($transaction) {
                    return $transaction->fromLocation ? $transaction->fromLocation->code . ' - ' . ($transaction->fromLocation->warehouse->name ?? '') : '-';
                })
                ->addColumn('to_location_name', function ($transaction) {
                    return $transaction->toLocation ? $transaction->toLocation->code . ' - ' . ($transaction->toLocation->warehouse->name ?? '') : '-';
                })
                ->addColumn('user_name', function ($transaction) {
                    return $transaction->user->name ?? '-';
                })
                ->editColumn('created_at', function ($transaction) {
                    return $transaction->created_at->format('Y-m-d H:i:s');
                })
                ->make(true);
        }

        return view('features.transfer.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return redirect()->route('transfer.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return redirect()->route('transfer.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('transfer.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return redirect()->route('transfer.index');
    }

    /**
     * Show the form for creating a new transfer transaction.
     */
    public function create()
    {
        $warehouses = \App\Models\Warehouse::all();
        return view('features.transfer.create', compact('warehouses'));
    }

    /**
     * Store a newly created transfer transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
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
                    'message' => "Stok tidak mencukupi di lokasi sumber. Tersedia: {$totalStock}, Diminta: {$validated['quantity']}",
                ], 422);
            }

            $transaction = $this->transactionService->createTransfer(
                $validated['item_id'],
                $validated['from_location_id'],
                $validated['to_location_id'],
                $validated['quantity'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi transfer berhasil dibuat.',
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
     * Get location by code
     */
    public function getLocationByCode(Request $request)
    {
        $code = $request->input('code');
        
        $location = Location::where('code', $code)
            ->where('type', 'SLOT')
            ->with(['warehouse', 'parent'])
            ->first();
        
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi tidak ditemukan dengan kode: ' . $code,
            ], 404);
        }

        $path = $location->code;
        if ($location->parent) {
            $path = $location->parent->code . ' > ' . $path;
        }

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'code' => $location->code,
                'path' => $path,
                'warehouse_name' => $location->warehouse->name ?? '',
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
}

