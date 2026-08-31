<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Gaji & Payroll</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f8f9fa; font-weight: bold; text-align: center; text-transform: uppercase; font-size: 9px; }
        td.text-left { text-align: left; }
        td.text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .summary-box { float: right; width: 300px; border: 1px solid #333; padding: 10px; background: #f8f9fa; }
        .summary-box p { margin: 5px 0; font-size: 12px; }
        .summary-box h3 { margin: 10px 0 0; font-size: 16px; border-top: 1px dashed #ccc; padding-top: 10px; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN GAJI & PAYROLL CREW</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="15%">Nama Crew</th>
                <th width="10%">Tunjangan / Gaji Pokok</th>
                <th width="12%">Komisi Shift</th>
                <th width="12%">Motret / Project</th>
                <th width="10%">Lembur</th>
                <th width="10%">Bonus</th>
                <th width="10%">Potongan/Kasbon</th>
                <th width="12%">Total Bersih</th>
                <th width="15%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($salaryData as $index => $data)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-left font-bold">{{ $data['user']->name }}
                    <br><span style="font-size: 8px; color: #666;">
                        {{ $data['user']->allowance_type == 'daily' ? '(Harian)' : ($data['user']->allowance_type == 'monthly' ? '(Bulanan)' : '') }}
                    </span>
                </td>
                <td>{{ number_format($data['allowance'], 0, ',', '.') }}</td>
                <td>
                    {{ number_format($data['komisi_shift'], 0, ',', '.') }}<br>
                    <span style="font-size: 8px; color: #666;">{{ $data['total_shifts'] }} shift</span>
                </td>
                <td>
                    {{ number_format($data['motret'], 0, ',', '.') }}
                    @if($data['payroll'] && $data['payroll']->photographer_fee_note)
                        <br><span style="font-size: 7px; color: #666; font-style: italic;">{{ $data['payroll']->photographer_fee_note }}</span>
                    @endif
                </td>
                <td>
                    {{ number_format($data['lembur'], 0, ',', '.') }}
                    @if($data['payroll'] && $data['payroll']->overtime_fee_note)
                        <br><span style="font-size: 7px; color: #666; font-style: italic;">{{ $data['payroll']->overtime_fee_note }}</span>
                    @endif
                </td>
                <td>
                    {{ number_format($data['bonus'], 0, ',', '.') }}
                    @if($data['payroll'] && $data['payroll']->bonus_note)
                        <br><span style="font-size: 7px; color: #666; font-style: italic;">{{ $data['payroll']->bonus_note }}</span>
                    @endif
                </td>
                <td style="color: #d32f2f;">
                    - {{ number_format($data['kasbon'], 0, ',', '.') }}
                    @if($data['payroll'] && $data['payroll']->deduction_note)
                        <br><span style="font-size: 7px; color: #666; font-style: italic;">{{ $data['payroll']->deduction_note }}</span>
                    @endif
                </td>
                <td class="font-bold" style="background-color: #e8f5e9;">{{ number_format($data['total_bersih'], 0, ',', '.') }}</td>
                <td class="text-left" style="font-size: 8px;">{{ $data['payroll'] && $data['payroll']->notes ? $data['payroll']->notes : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Belum ada data crew.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" style="text-align: right; font-size: 11px;">
                    {{ in_array(auth()->user()->role, ['crew', 'kasir']) ? 'TOTAL TAKE-HOME PAY' : 'TOTAL KESELURUHAN PENGELUARAN GAJI' }}
                </th>
                <th style="font-size: 12px; background-color: #c8e6c9;">Rp {{ number_format($totalSistem, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i:s') }}</p>
    </div>
</body>
</html>
