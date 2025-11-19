<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Basic Statistics
        $totalItems = Item::count();
        $totalWarehouses = Warehouse::count();
        $totalLocations = Location::count();
        $totalStocks = Stock::sum('quantity');
        $totalTransactions = Transaction::count();

        // Transaction Statistics
        $inboundCount = Transaction::where('type', TransactionType::INBOUND)->count();
        $outboundCount = Transaction::where('type', TransactionType::OUTBOUND)->count();
        $transferCount = Transaction::where('type', TransactionType::TRANSFER)->count();

        // Today's Transactions
        $todayInbound = Transaction::where('type', TransactionType::INBOUND)
            ->whereDate('created_at', today())
            ->count();
        $todayOutbound = Transaction::where('type', TransactionType::OUTBOUND)
            ->whereDate('created_at', today())
            ->count();
        $todayTransfer = Transaction::where('type', TransactionType::TRANSFER)
            ->whereDate('created_at', today())
            ->count();

        // This Week's Transactions
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $weekInbound = Transaction::where('type', TransactionType::INBOUND)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
        $weekOutbound = Transaction::where('type', TransactionType::OUTBOUND)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
        $weekTransfer = Transaction::where('type', TransactionType::TRANSFER)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        // Low Stock Items
        $lowStockItems = Item::with('stocks')
            ->get()
            ->filter(function ($item) {
                return $item->isLowStock();
            })
            ->take(5);

        // Recent Transactions
        $recentTransactions = Transaction::with(['item', 'fromLocation.warehouse', 'toLocation.warehouse', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Transaction Chart Data (Last 7 days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d M'),
                'inbound' => Transaction::where('type', TransactionType::INBOUND)
                    ->whereDate('created_at', $date)
                    ->count(),
                'outbound' => Transaction::where('type', TransactionType::OUTBOUND)
                    ->whereDate('created_at', $date)
                    ->count(),
                'transfer' => Transaction::where('type', TransactionType::TRANSFER)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        // Top Items by Transaction
        $topItems = Transaction::select('item_id', DB::raw('count(*) as transaction_count'))
            ->with('item')
            ->groupBy('item_id')
            ->orderBy('transaction_count', 'desc')
            ->take(5)
            ->get();

        return view('pages.dashboard', compact(
            'totalItems',
            'totalWarehouses',
            'totalLocations',
            'totalStocks',
            'totalTransactions',
            'inboundCount',
            'outboundCount',
            'transferCount',
            'todayInbound',
            'todayOutbound',
            'todayTransfer',
            'weekInbound',
            'weekOutbound',
            'weekTransfer',
            'lowStockItems',
            'recentTransactions',
            'chartData',
            'topItems'
        ));
    }
}

