<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Item;
use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display a listing of all transactions.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->buildQuery($request);

            return DataTables::of($query)
                ->addColumn('type_label', function ($transaction) {
                    return $transaction->type->label();
                })
                ->addColumn('item_name', function ($transaction) {
                    return $transaction->item->name ?? '-';
                })
                ->addColumn('item_sku', function ($transaction) {
                    return $transaction->item->sku ?? '-';
                })
                ->addColumn('from_location', function ($transaction) {
                    if ($transaction->fromLocation) {
                        return $transaction->fromLocation->code . ' - ' . ($transaction->fromLocation->warehouse->name ?? '');
                    }
                    return '-';
                })
                ->addColumn('to_location', function ($transaction) {
                    if ($transaction->toLocation) {
                        return $transaction->toLocation->code . ' - ' . ($transaction->toLocation->warehouse->name ?? '');
                    }
                    return '-';
                })
                ->addColumn('user_name', function ($transaction) {
                    return $transaction->user->name ?? '-';
                })
                ->editColumn('created_at', function ($transaction) {
                    return $transaction->created_at->format('Y-m-d H:i:s');
                })
                ->make(true);
        }

        $items = Item::orderBy('name')->get();
        return view('features.reports.index', compact('items'));
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        $transactions = $this->buildQuery($request)->get();
        
        $filename = 'laporan-transaksi-' . date('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Kode Transaksi',
                'Jenis',
                'Item',
                'SKU',
                'Lokasi Sumber',
                'Lokasi Tujuan',
                'Jumlah',
                'Batch',
                'User',
                'Tanggal',
                'Catatan'
            ]);

            // Data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_code,
                    $transaction->type->label(),
                    $transaction->item->name ?? '-',
                    $transaction->item->sku ?? '-',
                    $transaction->fromLocation ? $transaction->fromLocation->code . ' - ' . ($transaction->fromLocation->warehouse->name ?? '') : '-',
                    $transaction->toLocation ? $transaction->toLocation->code . ' - ' . ($transaction->toLocation->warehouse->name ?? '') : '-',
                    $transaction->quantity,
                    $transaction->batch ?? '-',
                    $transaction->user->name ?? '-',
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->notes ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $transactions = $this->buildQuery($request)->get();
        
        $data = [
            'transactions' => $transactions,
            'filters' => [
                'type' => $request->input('type', ''),
                'start_date' => $request->input('start_date', ''),
                'end_date' => $request->input('end_date', ''),
                'item_id' => $request->input('item_id', ''),
            ]
        ];

        $html = view('features.reports.pdf', $data)->render();
        
        // Simple PDF using browser print or use DomPDF
        return response()->make($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * Build query with filters
     */
    private function buildQuery(Request $request)
    {
        $query = Transaction::with(['item', 'fromLocation.warehouse', 'toLocation.warehouse', 'user'])
            ->select('transactions.*')
            ->orderBy('created_at', 'desc');

        // Filter by transaction type
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by item
        if ($request->has('item_id') && $request->item_id != '') {
            $query->where('item_id', $request->item_id);
        }

        return $query;
    }
}

