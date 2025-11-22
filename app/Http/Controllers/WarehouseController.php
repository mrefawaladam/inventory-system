<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
                ->editColumn('address', function ($warehouse) {
                    return $warehouse->address ?? '-';
                })
                ->editColumn('recipient', function ($warehouse) {
                    return $warehouse->recipient ?? '-';
                })
                ->editColumn('city', function ($warehouse) {
                    return $warehouse->city ?? '-';
                })
                ->editColumn('province', function ($warehouse) {
                    return $warehouse->province ?? '-';
                })
                ->addColumn('location_info', function ($warehouse) {
                    $info = [];
                    if ($warehouse->city) {
                        $info[] = $warehouse->city;
                    }
                    if ($warehouse->province) {
                        $info[] = $warehouse->province;
                    }
                    return !empty($info) ? implode(', ', $info) : '-';
                })
                ->addColumn('action', function ($warehouse) {
                    return view('features.warehouses.partials.action-buttons', compact('warehouse'))->render();
                })
                ->editColumn('created_at', function ($warehouse) {
                    return $warehouse->created_at->format('Y-m-d');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Get all warehouses for map
        $warehouses = Warehouse::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('features.warehouses.index', compact('warehouses'));
    }

    /**
     * Get provinces from API
     */
    private function getProvinces()
    {
        $apiUrls = [
            'https://api-regional-indonesia.vercel.app/api/provinces',
            'https://wilayah.id/api/provinces.json',
            'https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json'
        ];

        foreach ($apiUrls as $url) {
            try {
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Handle different response formats
                    $provinces = [];
                    
                    // Format 1: {status: true, data: [...]}
                    if (isset($data['data']) && is_array($data['data'])) {
                        $provinces = $data['data'];
                    }
                    // Format 2: Direct array [...]
                    elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
                        $provinces = $data;
                    }
                    // Format 3: Object with numeric keys
                    elseif (is_array($data) && !isset($data[0]) && !empty($data)) {
                        $provinces = array_values($data);
                    }
                    
                    if (!empty($provinces)) {
                        // Transform to consistent format
                        $formatted = [];
                        foreach ($provinces as $province) {
                            if (!is_array($province)) {
                                continue;
                            }
                            
                            $id = $province['id'] ?? $province['code'] ?? $province['kode'] ?? null;
                            $name = $province['name'] ?? $province['nama'] ?? '';
                            
                            // Skip if missing required fields
                            if (empty($id) || empty($name)) {
                                continue;
                            }
                            
                            $formatted[] = [
                                'id' => (string)trim($id), // Ensure string for consistency
                                'name' => trim(preg_replace('/\s+/', ' ', $name)) // Remove newlines and normalize spaces
                            ];
                        }
                        
                        // Sort by name
                        usort($formatted, function($a, $b) {
                            return strcmp($a['name'], $b['name']);
                        });
                        
                        if (count($formatted) > 0) {
                            \Log::info("Successfully fetched " . count($formatted) . " provinces from {$url}");
                            return $formatted;
                        }
                    }
                } else {
                    \Log::warning("API {$url} returned status: " . $response->status());
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to fetch provinces from {$url}: " . $e->getMessage());
                continue;
            }
        }
        
        \Log::error("All province APIs failed, returning empty array");
        // Return empty array if all APIs fail
        return [];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = $this->getProvinces();
        
        \Log::info("Create form - Provinces count: " . count($provinces));
        if (count($provinces) > 0) {
            \Log::info("Sample provinces: " . json_encode(array_slice($provinces, 0, 3)));
        } else {
            \Log::warning("No provinces loaded for create form!");
        }
        
        return response()->json([
            'html' => view('features.warehouses.partials.form', [
                'warehouse' => null,
                'formAction' => route('warehouses.store'),
                'formMethod' => 'POST',
                'modalTitle' => 'Tambah Sekolah',
                'provinces' => $provinces ?? []
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
                'message' => 'Sekolah berhasil ditambahkan.'
                ]);
            }

            return redirect()->route('warehouses.index')
                ->with('success', 'Sekolah berhasil ditambahkan.');
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
        $provinces = $this->getProvinces();
        
        \Log::info("Edit form - Provinces count: " . count($provinces));
        if (count($provinces) > 0) {
            \Log::info("Sample provinces: " . json_encode(array_slice($provinces, 0, 3)));
        } else {
            \Log::warning("No provinces loaded for edit form!");
        }
        
        return response()->json([
            'html' => view('features.warehouses.partials.form', [
                'warehouse' => $warehouse,
                'formAction' => route('warehouses.update', $warehouse),
                'formMethod' => 'PUT',
                'modalTitle' => 'Edit Sekolah',
                'provinces' => $provinces ?? []
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
                'message' => 'Sekolah berhasil diperbarui.'
                ]);
            }

            return redirect()->route('warehouses.index')
                ->with('success', 'Sekolah berhasil diperbarui.');
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
                'message' => 'Sekolah berhasil dihapus.'
            ]);
        }

        return redirect()->route('warehouses.index')
            ->with('success', 'Sekolah berhasil dihapus.');
    }
}

