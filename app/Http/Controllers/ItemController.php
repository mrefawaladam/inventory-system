<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Http\Request;
use Milon\Barcode\Facades\DNS1DFacade;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Item::select('items.*');

            return DataTables::of($query)
                ->addColumn('total_stock', function ($item) {
                    return number_format($item->total_stock, 0, ',', '.');
                })
                ->addColumn('stock_status', function ($item) {
                    if ($item->isLowStock()) {
                        return '<span class="badge bg-danger">Low Stock</span>';
                    }
                    return '<span class="badge bg-success">OK</span>';
                })
                ->addColumn('image_preview', function ($item) {
                    if ($item->image) {
                        return '<img src="' . asset('storage/' . $item->image) . '" alt="' . $item->name . '" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">';
                    }
                    return '<span class="text-muted">No Image</span>';
                })
                ->addColumn('barcode_image', function ($item) {
                    if ($item->barcode) {
                        return '<img src="data:image/png;base64,' . base64_encode($this->generateBarcodeImage($item->barcode)) . '" alt="Barcode" style="height: 40px;">';
                    }
                    return '-';
                })
                ->addColumn('action', function ($item) {
                    return view('features.items.partials.action-buttons', compact('item'))->render();
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at->format('Y-m-d');
                })
                ->rawColumns(['image_preview', 'barcode_image', 'stock_status', 'action'])
                ->make(true);
        }

        return view('features.items.index');
    }

    /**
     * Generate barcode image
     */
    private function generateBarcodeImage(string $barcode): string
    {
        try {
            // Use milon/barcode DNS1D for EAN-13 barcode
            return DNS1DFacade::getBarcodePNG($barcode, 'EAN13', 2, 40, [0, 0, 0], true);
        } catch (\Exception $e) {
            // Fallback to simple text if barcode generation fails
            return '';
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'html' => view('features.items.partials.form', [
                'item' => null,
                'formAction' => route('items.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Tambah Barang Baru'
            ])->render()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(ItemService::getCreateRules());

            $this->itemService->create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Barang berhasil dibuat.'
                ]);
            }

            return redirect()->route('items.index')
                ->with('success', 'Barang berhasil dibuat.');
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
    public function show(Item $item)
    {
        if (request()->ajax()) {
            // Get stock details by warehouse
            $stockByWarehouse = $item->getStockByWarehouse();
            $stockByLocation = $item->getStockByLocation();
            
            return response()->json([
                'html' => view('features.items.partials.show', [
                    'item' => $item,
                    'stockByWarehouse' => $stockByWarehouse,
                    'stockByLocation' => $stockByLocation
                ])->render()
            ]);
        }

        return redirect()->route('items.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        return response()->json([
            'html' => view('features.items.partials.form', [
                'item' => $item,
                'formAction' => route('items.update', $item),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit Barang'
            ])->render()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        try {
            $validated = $request->validate(ItemService::getUpdateRules($item));

            $this->itemService->update($item, $validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Barang berhasil diperbarui.'
                ]);
            }

            return redirect()->route('items.index')
                ->with('success', 'Barang berhasil diperbarui.');
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
    public function destroy(Item $item)
    {
        try {
            $this->itemService->delete($item);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Barang berhasil dihapus.'
                ]);
            }

            return redirect()->route('items.index')
                ->with('success', 'Barang berhasil dihapus.');
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
     * Generate barcode image
     */
    public function barcode(Item $item)
    {
        if (!$item->barcode) {
            abort(404);
        }

        try {
            $barcodeImage = DNS1DFacade::getBarcodePNG($item->barcode, 'EAN13', 2, 60, [0, 0, 0], true);
            return response($barcodeImage)->header('Content-Type', 'image/png');
        } catch (\Exception $e) {
            abort(404);
        }
    }
}

