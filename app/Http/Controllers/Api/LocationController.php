<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    /**
     * Base URLs for API Regional Indonesia (gratis dan reliable)
     * Fallback ke beberapa API jika yang pertama gagal
     */
    private const API_BASE_URLS = [
        'https://api-regional-indonesia.vercel.app/api',
        'https://wilayah.id/api',
        'https://www.emsifa.com/api-wilayah-indonesia/api'
    ];

    /**
     * Get all provinces with fallback
     */
    public function getProvinces()
    {
        foreach (self::API_BASE_URLS as $baseUrl) {
            try {
                $url = $baseUrl . (strpos($baseUrl, 'wilayah.id') !== false ? '/provinces.json' : '/provinces');
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Handle different response formats
                    $provinces = [];
                    if (isset($data['data']) && is_array($data['data'])) {
                        $provinces = $data['data'];
                    } elseif (isset($data['status']) && $data['status'] && isset($data['data'])) {
                        $provinces = $data['data'];
                    } elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
                        $provinces = $data;
                    } elseif (is_array($data) && !isset($data['status'])) {
                        $provinces = $data;
                    } else {
                        continue; // Try next API
                    }
                    
                    if (empty($provinces)) {
                        continue; // Try next API
                    }
                    
                    // Transform to match expected format
                    $formatted = [];
                    foreach ($provinces as $province) {
                        $formatted[] = [
                            'id' => $province['id'] ?? $province['code'] ?? null,
                            'name' => $province['name'] ?? $province['nama'] ?? ''
                        ];
                    }
                    
                    // Sort by name
                    usort($formatted, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    
                    return response()->json($formatted);
                }
            } catch (\Exception $e) {
                \Log::warning("API {$baseUrl} failed: " . $e->getMessage());
                continue; // Try next API
            }
        }
        
        return response()->json(['error' => 'All API endpoints failed'], 500);
    }

    /**
     * Get cities/regencies by province with fallback
     */
    public function getCitiesByProvince(Request $request)
    {
        $request->validate([
            'province_id' => 'required|string'
        ]);

        foreach (self::API_BASE_URLS as $baseUrl) {
            try {
                $url = $baseUrl . (strpos($baseUrl, 'wilayah.id') !== false ? "/regencies/{$request->province_id}.json" : "/cities/{$request->province_id}");
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Handle different response formats
                    $cities = [];
                    if (isset($data['data']) && is_array($data['data'])) {
                        $cities = $data['data'];
                    } elseif (isset($data['status']) && $data['status'] && isset($data['data'])) {
                        $cities = $data['data'];
                    } elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
                        $cities = $data;
                    } elseif (is_array($data) && !isset($data['status'])) {
                        $cities = $data;
                    } else {
                        continue; // Try next API
                    }
                    
                    if (empty($cities)) {
                        continue; // Try next API
                    }
                    
                    // Transform to match expected format
                    $formatted = [];
                    foreach ($cities as $city) {
                        $formatted[] = [
                            'id' => $city['id'] ?? $city['code'] ?? null,
                            'name' => $city['name'] ?? $city['nama'] ?? ''
                        ];
                    }
                    
                    // Sort by name
                    usort($formatted, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    
                    return response()->json($formatted);
                }
            } catch (\Exception $e) {
                \Log::warning("API {$baseUrl} failed: " . $e->getMessage());
                continue; // Try next API
            }
        }
        
        return response()->json(['error' => 'All API endpoints failed'], 500);
    }

    /**
     * Get districts by city/regency with fallback
     */
    public function getDistrictsByCity(Request $request)
    {
        $request->validate([
            'city_id' => 'required|string'
        ]);

        foreach (self::API_BASE_URLS as $baseUrl) {
            try {
                $url = $baseUrl . (strpos($baseUrl, 'wilayah.id') !== false ? "/districts/{$request->city_id}.json" : "/districts/{$request->city_id}");
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Handle different response formats
                    $districts = [];
                    if (isset($data['data']) && is_array($data['data'])) {
                        $districts = $data['data'];
                    } elseif (isset($data['status']) && $data['status'] && isset($data['data'])) {
                        $districts = $data['data'];
                    } elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
                        $districts = $data;
                    } elseif (is_array($data) && !isset($data['status'])) {
                        $districts = $data;
                    } else {
                        continue; // Try next API
                    }
                    
                    if (empty($districts)) {
                        continue; // Try next API
                    }
                    
                    // Transform to match expected format
                    $formatted = [];
                    foreach ($districts as $district) {
                        $formatted[] = [
                            'id' => $district['id'] ?? $district['code'] ?? null,
                            'name' => $district['name'] ?? $district['nama'] ?? ''
                        ];
                    }
                    
                    // Sort by name
                    usort($formatted, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    
                    return response()->json($formatted);
                }
            } catch (\Exception $e) {
                \Log::warning("API {$baseUrl} failed: " . $e->getMessage());
                continue; // Try next API
            }
        }
        
        return response()->json(['error' => 'All API endpoints failed'], 500);
    }

    /**
     * Get villages by district with fallback
     */
    public function getVillagesByDistrict(Request $request)
    {
        $request->validate([
            'district_id' => 'required|string'
        ]);

        foreach (self::API_BASE_URLS as $baseUrl) {
            try {
                $url = $baseUrl . (strpos($baseUrl, 'wilayah.id') !== false ? "/villages/{$request->district_id}.json" : "/villages/{$request->district_id}");
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Handle different response formats
                    $villages = [];
                    if (isset($data['data']) && is_array($data['data'])) {
                        $villages = $data['data'];
                    } elseif (isset($data['status']) && $data['status'] && isset($data['data'])) {
                        $villages = $data['data'];
                    } elseif (is_array($data) && isset($data[0]) && is_array($data[0])) {
                        $villages = $data;
                    } elseif (is_array($data) && !isset($data['status'])) {
                        $villages = $data;
                    } else {
                        continue; // Try next API
                    }
                    
                    if (empty($villages)) {
                        continue; // Try next API
                    }
                    
                    // Transform to match expected format
                    $formatted = [];
                    foreach ($villages as $village) {
                        $formatted[] = [
                            'id' => $village['id'] ?? $village['code'] ?? null,
                            'name' => $village['name'] ?? $village['nama'] ?? ''
                        ];
                    }
                    
                    // Sort by name
                    usort($formatted, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    
                    return response()->json($formatted);
                }
            } catch (\Exception $e) {
                \Log::warning("API {$baseUrl} failed: " . $e->getMessage());
                continue; // Try next API
            }
        }
        
        return response()->json(['error' => 'All API endpoints failed'], 500);
    }
}
