<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\Location;
use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HeatmapController extends Controller
{
    /**
     * Display the heatmap analytics page.
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        $items = Item::orderBy('name')->get();

        return view('features.heatmap.index', compact('warehouses', 'items'));
    }

    /**
     * Get item movement heatmap data
     * Returns items sorted by frequency of movement (transfer transactions)
     */
    public function getItemMovementHeatmap(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $limit = $request->input('limit', 20);

        $query = Transaction::select(
                'item_id',
                DB::raw('COUNT(*) as movement_count'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->where('type', TransactionType::TRANSFER)
            ->where('user_id', Auth::id())
            ->groupBy('item_id')
            ->orderBy('movement_count', 'desc')
            ->limit($limit);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $movements = $query->get();

        $items = Item::whereIn('id', $movements->pluck('item_id'))->get()->keyBy('id');

        $data = $movements->map(function ($movement) use ($items) {
            $item = $items->get($movement->item_id);
            return [
                'item_id' => $movement->item_id,
                'item_name' => $item ? $item->name : 'Unknown',
                'item_sku' => $item ? $item->sku : '-',
                'movement_count' => (int) $movement->movement_count,
                'total_quantity' => (int) $movement->total_quantity,
            ];
        });

        // Calculate max for intensity scaling
        $maxCount = $data->max('movement_count') ?? 1;

        $data = $data->map(function ($item) use ($maxCount) {
            $item['intensity'] = $maxCount > 0 ? ($item['movement_count'] / $maxCount) : 0;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'max_count' => $maxCount,
        ]);
    }

    /**
     * Get warehouse activity heatmap data
     * Returns warehouses sorted by transaction activity
     */
    public function getWarehouseActivityHeatmap(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get transactions from locations grouped by warehouse
        $query = DB::table('transactions')
            ->join('locations as from_locs', 'transactions.from_location_id', '=', 'from_locs.id')
            ->leftJoin('locations as to_locs', 'transactions.to_location_id', '=', 'to_locs.id')
            ->select(
                DB::raw('COALESCE(from_locs.warehouse_id, to_locs.warehouse_id) as warehouse_id'),
                DB::raw('COUNT(DISTINCT transactions.id) as transaction_count'),
                DB::raw('SUM(transactions.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT transactions.item_id) as unique_items')
            )
            ->where('transactions.user_id', Auth::id())
            ->groupBy('warehouse_id');

        if ($startDate) {
            $query->whereDate('transactions.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transactions.created_at', '<=', $endDate);
        }

        $activities = $query->get();

        $warehouses = Warehouse::whereIn('id', $activities->pluck('warehouse_id'))->get()->keyBy('id');

        $data = $activities->map(function ($activity) use ($warehouses) {
            $warehouse = $warehouses->get($activity->warehouse_id);
            return [
                'warehouse_id' => $activity->warehouse_id,
                'warehouse_name' => $warehouse ? $warehouse->name : 'Unknown',
                'transaction_count' => (int) $activity->transaction_count,
                'total_quantity' => (int) $activity->total_quantity,
                'unique_items' => (int) $activity->unique_items,
            ];
        })->sortByDesc('transaction_count')->values();

        // Calculate max for intensity scaling
        $maxCount = $data->max('transaction_count') ?? 1;

        $data = $data->map(function ($warehouse) use ($maxCount) {
            $warehouse['intensity'] = $maxCount > 0 ? ($warehouse['transaction_count'] / $maxCount) : 0;
            return $warehouse;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'max_count' => $maxCount,
        ]);
    }

    /**
     * Get traffic visualization data
     * Returns movement patterns between locations/warehouses
     */
    public function getTrafficVisualization(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $type = $request->input('type', 'warehouse'); // 'warehouse' or 'location'

        $query = Transaction::where('type', TransactionType::TRANSFER)
            ->where('user_id', Auth::id())
            ->whereNotNull('from_location_id')
            ->whereNotNull('to_location_id');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->with(['fromLocation.warehouse', 'toLocation.warehouse'])->get();

        if ($type === 'warehouse') {
            // Group by warehouse pairs
            $traffic = [];
            foreach ($transactions as $transaction) {
                $fromWarehouse = $transaction->fromLocation->warehouse ?? null;
                $toWarehouse = $transaction->toLocation->warehouse ?? null;

                if ($fromWarehouse && $toWarehouse && $fromWarehouse->id !== $toWarehouse->id) {
                    $key = min($fromWarehouse->id, $toWarehouse->id) . '-' . max($fromWarehouse->id, $toWarehouse->id);

                    if (!isset($traffic[$key])) {
                        $traffic[$key] = [
                            'from_warehouse_id' => $fromWarehouse->id,
                            'from_warehouse_name' => $fromWarehouse->name,
                            'to_warehouse_id' => $toWarehouse->id,
                            'to_warehouse_name' => $toWarehouse->name,
                            'count' => 0,
                            'total_quantity' => 0,
                        ];
                    }

                    $traffic[$key]['count']++;
                    $traffic[$key]['total_quantity'] += $transaction->quantity;
                }
            }

            $data = collect($traffic)->values()->sortByDesc('count');
        } else {
            // Group by location pairs
            $traffic = [];
            foreach ($transactions as $transaction) {
                $fromLocation = $transaction->fromLocation;
                $toLocation = $transaction->toLocation;

                if ($fromLocation && $toLocation && $fromLocation->id !== $toLocation->id) {
                    $key = min($fromLocation->id, $toLocation->id) . '-' . max($fromLocation->id, $toLocation->id);

                    if (!isset($traffic[$key])) {
                        $traffic[$key] = [
                            'from_location_id' => $fromLocation->id,
                            'from_location_code' => $fromLocation->code,
                            'from_location_path' => $fromLocation->full_path,
                            'to_location_id' => $toLocation->id,
                            'to_location_code' => $toLocation->code,
                            'to_location_path' => $toLocation->full_path,
                            'count' => 0,
                            'total_quantity' => 0,
                        ];
                    }

                    $traffic[$key]['count']++;
                    $traffic[$key]['total_quantity'] += $transaction->quantity;
                }
            }

            $data = collect($traffic)->values()->sortByDesc('count')->take(50);
        }

        // Calculate max for intensity scaling
        $maxCount = $data->max('count') ?? 1;

        $data = $data->map(function ($item) use ($maxCount) {
            $item['intensity'] = $maxCount > 0 ? ($item['count'] / $maxCount) : 0;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'max_count' => $maxCount,
            'type' => $type,
        ]);
    }

    /**
     * Get time-based activity data for charts
     */
    public function getTimeBasedActivity(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $groupBy = $request->input('group_by', 'day'); // 'day', 'week', 'month'

        $query = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->where('user_id', Auth::id())
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($groupBy === 'week') {
            $query->select(
                DB::raw('YEARWEEK(created_at) as period'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(quantity) as total_quantity')
            )->groupBy('period');
        } elseif ($groupBy === 'month') {
            $query->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(quantity) as total_quantity')
            )->groupBy('period');
        } else {
            $query->groupBy('date');
        }

        $data = $query->orderBy('date', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'group_by' => $groupBy,
        ]);
    }
}

