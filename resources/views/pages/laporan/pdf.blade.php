<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000000;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #000000;
        }
        .header h1 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            margin: 3px 0 0 0;
            font-size: 11pt;
            font-weight: normal;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 8.5pt;
            color: #555555;
        }

        /* Meta Information */
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        /* Main Data Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8.5pt;
        }
        table.data-table th, 
        table.data-table td {
            border: 1px solid #cccccc;
            padding: 5px 6px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
            border-bottom: 1px solid #000000;
        }
        table.data-table tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
            border-top: 1px solid #000000;
        }

        /* Summary Box */
        .summary-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .summary-table td {
            padding: 3px 0;
        }
    </style>
</head>
<body>
    {{-- Header Sederhana --}}
    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <h2>Market Kuy</h2>
        <p>Jl. Raya Penjualan No. 123 | Telp: (022) 87654321</p>
    </div>

    {{-- Informasi Periode & Cetak --}}
    <table class="info-table">
        <tr>
            <td style="width: 15%;"><strong>Periode</strong></td>
            <td style="width: 35%;">: 
                @if(filled($start) && filled($end))
                    {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
                @elseif(filled($start))
                    Mulai {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }}
                @elseif(filled($end))
                    Sampai {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
                @else
                    Semua Waktu
                @endif
            </td>
            <td style="width: 15%;"><strong>Tanggal Cetak</strong></td>
            <td style="width: 35%;">: {{ date('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>Dicetak Oleh</strong></td>
            <td>: {{ $userCetak->nama ?? '-' }} ({{ ucfirst($userCetak->peran ?? 'Kasir') }})</td>
            @if(isset($kasir) && $kasir)
                <td><strong>Kasir</strong></td>
                <td>: {{ $kasir->nama }}</td>
            @else
                <td></td>
                <td></td>
            @endif
        </tr>
    </table>

    {{-- Tabel Data Penjualan --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">No</th>
                <th style="width: 18%;">No. Faktur</th>
                <th style="width: 14%;">Tanggal</th>
                <th style="width: 16%;">Kasir</th>
                <th style="width: 16%;">Pelanggan</th>
                <th style="width: 10%;" class="text-right">Subtotal</th>
                <th style="width: 9%;" class="text-right">Diskon</th>
                <th style="width: 13%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penjualans as $index => $row)
                @php
                    $diskonNominal = $row->subtotal * ($row->diskon ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->no_kw }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->tgl_penjualan)->format('d/m/Y H:i') }}</td>
                    <td>{{ $row->kasir->nama ?? '-' }}</td>
                    <td>{{ $row->pelanggan->nama ?? 'Umum' }}</td>
                    <td class="text-right">Rp {{ number_format($row->subtotal, 0, ',', '.') }}</td>
                    <td class="text-right">
                        @if($diskonNominal > 0)
                            Rp {{ number_format($diskonNominal, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px; color: #777777;">
                        Tidak ada data transaksi pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right font-bold">Total</td>
                <td class="text-right font-bold">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Ringkasan Sederhana --}}
    <table class="summary-table">
        <tr>
            <td style="width: 70%;">
                <p style="margin: 0;">Total Transaksi: <strong>{{ number_format($totalTransaksi, 0, ',', '.') }}</strong> transaksi (<strong>{{ number_format($totalItem, 0, ',', '.') }}</strong> item terjual)</p>
            </td>
            <td style="width: 30%; text-align: right;">
                <p style="margin: 0; font-size: 10pt;"><strong>Total Pendapatan: Rp {{ number_format($totalOmset, 0, ',', '.') }}</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
