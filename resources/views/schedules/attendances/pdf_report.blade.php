<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi Crew</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 4px 0 0; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; vertical-align: top; }
        th { background-color: #f1f3f5; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        td.left { text-align: left; }
        .crew-head { background-color: #e9ecef; text-align: left; font-weight: bold; font-size: 11px; }
        .violation { color: #c62828; font-weight: bold; }
        .sub { font-size: 8px; color: #666; }
        .total-row { background-color: #f8f9fa; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 9px; color: #666; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        @php $logoDataUri = \App\Models\Setting::logoDataUri(); @endphp
        @if($logoDataUri)
            <img src="{{ $logoDataUri }}" style="width: 90px; margin-bottom: 8px;">
        @endif
        <h1>{{ strtoupper(\App\Models\Setting::get('store_name', 'MONOFRAME')) }}</h1>
        <p>LAPORAN ABSENSI CREW</p>
        <p>Periode: {{ $periodLabel }}@if($locationId) &middot; Lokasi: {{ $locations->firstWhere('id', $locationId)?->name }}@endif</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="9%">Tanggal</th>
                <th width="13%">Lokasi</th>
                <th width="9%">Masuk</th>
                <th width="9%">Keluar</th>
                <th width="20%">Status Absensi</th>
                <th width="12%">Lembur</th>
                <th width="12%">Sumber</th>
                <th width="14%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summary as $crew)
            <tr>
                <td class="crew-head" colspan="9">
                    {{ $crew['name'] }}
                    <span class="sub" style="text-transform: uppercase">({{ $crew['role'] }})</span>
                    &mdash; hadir {{ $crew['hadir'] }} hari &middot; {{ $crew['pelanggaran'] }} pelanggaran &middot;
                    lembur {{ intdiv($crew['lembur_menit'], 60) }}j {{ $crew['lembur_menit'] % 60 }}m
                    @if($crew['lembur_acc'] > 0)&middot; nominal di-acc Rp {{ number_format($crew['lembur_acc'], 0, ',', '.') }}@endif
                </td>
            </tr>
            @foreach($crew['rows'] as $index => $session)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($session['date'])->translatedFormat('d/m/Y') }}</td>
                <td class="left">{{ $session['location']?->name ?? '-' }}</td>
                <td>{{ $session['in'] ? $session['in']->created_at->format('H:i') : '-' }}</td>
                <td>{{ $session['out'] ? $session['out']->created_at->format('H:i') : '-' }}</td>
                <td class="left">
                    @if(!$session['in'])
                        <span class="violation">Tidak absen masuk</span>
                    @elseif(!$session['out'])
                        <span class="violation">Belum absen keluar</span>
                    @endif

                    @if(empty($session['violations']))
                        @if($session['dinilai']) Tepat Waktu @else <span class="sub">Belum dinilai</span> @endif
                    @else
                        @foreach($session['violations'] as $code => $value)
                            <span class="violation">{{ \App\Services\AttendanceRuleService::labels()[$code] ?? $code }}</span>
                            <span class="sub">{{ $code === 'out_of_radius' ? $value . ' m' : $value . ' menit' }}</span><br>
                        @endforeach
                    @endif
                </td>
                <td>
                    @if($session['overtime'])
                        {{ intdiv($session['overtime']->minutes, 60) }}j {{ $session['overtime']->minutes % 60 }}m<br>
                        <span class="sub">Rp {{ number_format($session['overtime']->amount, 0, ',', '.') }} &middot; {{ \App\Models\OvertimeRequest::statusLabel($session['overtime']->status) }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="left sub">{{ \App\Models\Attendance::sourceLabel($session['source']) }}</td>
                <td class="left sub">{{ $session['notes'] ?: '-' }}</td>
            </tr>
            @endforeach
            @empty
            <tr>
                <td colspan="9">Belum ada data absensi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($summary) > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="left">TOTAL KESELURUHAN</td>
                <td>{{ array_sum(array_column($summary, 'pelanggaran')) }} pelanggaran</td>
                <td>{{ intdiv(array_sum(array_column($summary, 'lembur_menit')), 60) }}j {{ array_sum(array_column($summary, 'lembur_menit')) % 60 }}m</td>
                <td colspan="2">Rp {{ number_format(array_sum(array_column($summary, 'lembur_acc')), 0, ',', '.') }} lembur di-acc</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &middot;
        {{ count($summary) }} crew &middot; periode {{ $from->translatedFormat('d M') }} - {{ $to->translatedFormat('d M Y') }}
    </div>
</body>
</html>
