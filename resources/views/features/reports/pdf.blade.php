<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            color: #666;
        }
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        .filters p {
            margin: 3px 0;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        table th {
            background-color: #333;
            color: white;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        @media print {
            body {
                padding: 10px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TRANSAKSI</h1>
        <p>Dicetak pada: {{ date('d F Y H:i:s') }}</p>
    </div>

    <div class="filters">
        <p><strong>Filter yang diterapkan:</strong></p>
        <p>Jenis Transaksi: {{ $filters['type'] ? \App\Enums\TransactionType::from($filters['type'])->label() : 'Semua' }}</p>
        <p>Tanggal Mulai: {{ $filters['start_date'] ? date('d F Y', strtotime($filters['start_date'])) : 'Semua' }}</p>
        <p>Tanggal Akhir: {{ $filters['end_date'] ? date('d F Y', strtotime($filters['end_date'])) : 'Semua' }}</p>
        <p>Total Data: {{ $transactions->count() }} transaksi</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Jenis</th>
                <th>Item</th>
                <th>SKU</th>
                <th>Lokasi Sumber</th>
                <th>Lokasi Tujuan</th>
                <th>Jumlah</th>
                <th>Batch</th>
                <th>User</th>
                <th>Tanggal Pengiriman</th>
                <th>Tanggal Terima</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction->transaction_code }}</td>
                <td>{{ $transaction->type->label() }}</td>
                <td>{{ $transaction->item->name ?? '-' }}</td>
                <td>{{ $transaction->item->sku ?? '-' }}</td>
                <td>
                    @if($transaction->fromLocation)
                        {{ $transaction->fromLocation->code }} - {{ $transaction->fromLocation->warehouse->name ?? '' }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($transaction->toLocation)
                        {{ $transaction->toLocation->code }} - {{ $transaction->toLocation->warehouse->name ?? '' }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ number_format($transaction->quantity, 0, ',', '.') }}</td>
                <td>{{ $transaction->batch ?? '-' }}</td>
                <td>{{ $transaction->user->name ?? '-' }}</td>
                <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $transaction->updated_at ? $transaction->updated_at->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align: center;">Tidak ada data transaksi</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Manajemen Sekolah</p>
        <p>Halaman 1</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

