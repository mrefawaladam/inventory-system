<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Location;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Stock::with(['item', 'location.warehouse', 'location.parent'])
                ->select('stocks.*');

            // Filter by item if provided
            if ($request->has('item_id') && $request->item_id) {
                $query->where('item_id', $request->item_id);
            }

            // Filter by location if provided
            if ($request->has('location_id') && $request->location_id) {
                $query->where('location_id', $request->location_id);
            }

            return DataTables::of($query)
                ->addColumn('item_name', function ($stock) {
                    return $stock->item->name ?? '-';
                })
                ->addColumn('item_sku', function ($stock) {
                    return $stock->item->sku ?? '-';
                })
                ->addColumn('warehouse_name', function ($stock) {
                    return $stock->location->warehouse->name ?? '-';
                })
                ->addColumn('location_code', function ($stock) {
                    return $stock->location->code ?? '-';
                })
                ->addColumn('location_path', function ($stock) {
                    return $stock->location->full_path ?? '-';
                })
                ->addColumn('expired_status', function ($stock) {
                    if ($stock->expired_at && $stock->expired_at->isPast()) {
                        return '<span class="badge bg-success">Sudah Dikirim</span>';
                    }

                    if ($stock->expired_at && $stock->expired_at->isFuture()) {
                        return '<span class="badge bg-danger">Belum Dikirim</span>';
                    }

                    return '<span class="badge bg-danger">Belum Dikirim</span>';
                })
                ->addColumn('action', function ($stock) {
                    return view('features.stocks.partials.action-buttons', compact('stock'))->render();
                })
                ->editColumn('expired_at', function ($stock) {
                    return $stock->expired_at ? $stock->expired_at->format('Y-m-d') : '-';
                })
                ->editColumn('created_at', function ($stock) {
                    return $stock->created_at->format('Y-m-d');
                })
                ->rawColumns(['expired_status', 'action'])
                ->make(true);
        }

        // Get items and locations for filters
        $items = Item::all();
        $locations = Location::with('warehouse')->get();

        return view('features.stocks.index', compact('items', 'locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::all();
        $locations = Location::with('warehouse')->get();

        return response()->json([
            'html' => view('features.stocks.partials.form', [
                'stock' => null,
                'items' => $items,
                'locations' => $locations,
                'formAction' => route('stocks.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Tambah Stok'
            ])->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(StockService::getValidationRules());

            $stock = $this->stockService->increaseStock(
                $validated['item_id'],
                $validated['location_id'],
                $validated['quantity'],
                $validated['batch'] ?? null,
                $validated['expired_at'] ?? null
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stok berhasil ditambahkan.'
                ]);
            }

            return redirect()->route('stocks.index')
                ->with('success', 'Stok berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
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
    public function show(Stock $stock)
    {
        if (request()->ajax()) {
            $summary = $this->stockService->getStockSummary($stock->item_id, $stock->location_id);

            return response()->json([
                'html' => view('features.stocks.partials.show', [
                    'stock' => $stock,
                    'summary' => $summary
                ])->render()
            ]);
        }

        return redirect()->route('stocks.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Stock $stock)
    {
        $items = Item::all();
        $locations = Location::with('warehouse')->get();

        return response()->json([
            'html' => view('features.stocks.partials.form', [
                'stock' => $stock,
                'items' => $items,
                'locations' => $locations,
                'formAction' => route('stocks.update', $stock),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit Stok'
            ])->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Stock $stock)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:0',
                'batch' => 'nullable|string|max:255',
                'expired_at' => 'nullable|date',
            ]);

            // Update stock
            $stock->update([
                'quantity' => $validated['quantity'],
                'batch' => $validated['batch'] ?? $stock->batch,
                'expired_at' => $validated['expired_at'] ? \Carbon\Carbon::parse($validated['expired_at']) : $stock->expired_at,
            ]);

            // Delete if quantity is 0
            if ($stock->quantity <= 0) {
                $stock->delete();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stok berhasil diperbarui.'
                ]);
            }

            return redirect()->route('stocks.index')
                ->with('success', 'Stok berhasil diperbarui.');
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
    public function destroy(Stock $stock)
    {
        try {
            $stock->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stok berhasil dihapus.'
                ]);
            }

            return redirect()->route('stocks.index')
                ->with('success', 'Stok berhasil dihapus.');
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
     * Increase stock
     */
    public function increase(Request $request)
    {
        try {
            $validated = $request->validate(StockService::getValidationRules());

            $stock = $this->stockService->increaseStock(
                $validated['item_id'],
                $validated['location_id'],
                $validated['quantity'],
                $validated['batch'] ?? null,
                $validated['expired_at'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil ditambahkan.',
                'stock' => $stock
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Decrease stock
     */
    public function decrease(Request $request)
    {
        try {
            $validated = $request->validate([
                'item_id' => 'required|exists:items,id',
                'location_id' => 'required|exists:locations,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $affectedStocks = $this->stockService->decreaseStock(
                $validated['item_id'],
                $validated['location_id'],
                $validated['quantity']
            );

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil dikurangi.',
                'affected_stocks' => $affectedStocks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Transfer stock
     */
    public function transfer(Request $request)
    {
        try {
            $validated = $request->validate(StockService::getTransferValidationRules());

            $result = $this->stockService->transferStock(
                $validated['item_id'],
                $validated['from_location_id'],
                $validated['to_location_id'],
                $validated['quantity']
            );

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil ditransfer.',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}

