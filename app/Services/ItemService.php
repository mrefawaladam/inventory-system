<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\Facades\DNS1DFacade;
use Milon\Barcode\Facades\DNS2DFacade;

class ItemService
{
    /**
     * Generate barcode automatically
     */
    public function generateBarcode(): string
    {
        // Generate EAN-13 barcode (13 digits)
        // Format: 8 digits random + check digit
        $prefix = '200'; // Company prefix (3 digits)
        $random = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
        $barcode = $prefix . $random;

        // Calculate check digit (EAN-13 algorithm)
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$barcode[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        $barcode .= $checkDigit;

        // Check if barcode already exists
        while (Item::where('barcode', $barcode)->exists()) {
            $random = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $barcode = $prefix . $random;
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $digit = (int)$barcode[$i];
                $sum += ($i % 2 === 0) ? $digit : $digit * 3;
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            $barcode .= $checkDigit;
        }

        return $barcode;
    }

    /**
     * Generate SKU automatically
     */
    public function generateSKU(): string
    {
        $lastItem = Item::orderBy('id', 'desc')->first();

        if ($lastItem) {
            // Extract number from SKU (e.g., "PRD-001" -> 1)
            preg_match('/PRD-(\d+)/', $lastItem->sku, $matches);
            $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            $nextNumber = 1;
        }

        return 'PRD-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Upload item image
     */
    public function uploadImage(UploadedFile $file): string
    {
        // Ensure items directory exists
        $itemsDir = storage_path('app/public/items');
        if (!file_exists($itemsDir)) {
            mkdir($itemsDir, 0755, true);
        }

        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Store file using Storage facade on public disk
        // This will save to storage/app/public/items/filename.ext
        $path = Storage::disk('public')->putFileAs('items', $file, $filename);

        // Verify file was saved
        if (!Storage::disk('public')->exists($path)) {
            throw new \Exception('Failed to save image file.');
        }

        return $path;
    }

    /**
     * Delete item image
     */
    public function deleteImage(string $imagePath): bool
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            return Storage::disk('public')->delete($imagePath);
        }

        return false;
    }

    /**
     * Create a new item
     */
    public function create(array $data): Item
    {
        // Generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSKU();
        }

        // Generate barcode if not provided
        if (empty($data['barcode'])) {
            $data['barcode'] = $this->generateBarcode();
        }

        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile && $data['image']->isValid()) {
            $data['image'] = $this->uploadImage($data['image']);
        } else {
            $data['image'] = null;
        }

        return Item::create([
            'sku' => $data['sku'],
            'name' => $data['name'],
            'barcode' => $data['barcode'],
            'unit' => $data['unit'] ?? null,
            'image' => $data['image'],
            'minimum_stock' => $data['minimum_stock'] ?? 0,
        ]);
    }

    /**
     * Update item
     */
    public function update(Item $item, array $data): Item
    {
        // Handle image upload if new image is provided
        if (isset($data['image']) && $data['image'] instanceof UploadedFile && $data['image']->isValid()) {
            // Delete old image if exists
            if ($item->image) {
                $this->deleteImage($item->image);
            }
            $data['image'] = $this->uploadImage($data['image']);
        } else {
            // Keep existing image if not provided or invalid
            unset($data['image']);
        }

        // Don't allow changing SKU and barcode on update (for data integrity)
        // If you want to allow, uncomment these:
        // if (isset($data['sku'])) {
        //     $item->sku = $data['sku'];
        // }
        // if (isset($data['barcode'])) {
        //     $item->barcode = $data['barcode'];
        // }

        $updateData = [
            'name' => $data['name'],
            'unit' => $data['unit'] ?? $item->unit,
            'minimum_stock' => $data['minimum_stock'] ?? $item->minimum_stock,
        ];

        // Only update image if a new one was uploaded
        if (isset($data['image'])) {
            $updateData['image'] = $data['image'];
        }

        $item->update($updateData);

        return $item->fresh();
    }

    /**
     * Delete item
     */
    public function delete(Item $item): bool
    {
        // Delete image if exists
        if ($item->image) {
            $this->deleteImage($item->image);
        }

        return $item->delete();
    }

    /**
     * Get validation rules for create
     */
    public static function getCreateRules(): array
    {
        return [
            'sku' => 'nullable|string|max:255|unique:items,sku',
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:items,barcode',
            'unit' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'minimum_stock' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get validation rules for update
     */
    public static function getUpdateRules(Item $item): array
    {
        return [
            'sku' => 'nullable|string|max:255|unique:items,sku,' . $item->id,
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:items,barcode,' . $item->id,
            'unit' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'minimum_stock' => 'nullable|integer|min:0',
        ];
    }
}

