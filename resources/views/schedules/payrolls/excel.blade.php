<table>
    <thead>
        <tr>
            <th colspan="9" style="font-weight: bold; font-size: 14px;">LAPORAN GAJI & PAYROLL CREW</th>
        </tr>
        <tr>
            <th colspan="9">Periode: {{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</th>
        </tr>
        <tr>
            <th colspan="9">Dicetak pada: {{ now()->translatedFormat('d F Y H:i:s') }}</th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold;">No</th>
            <th style="font-weight: bold;">Nama Crew</th>
            <th style="font-weight: bold;">Tipe Tunjangan</th>
            <th style="font-weight: bold;">Tunjangan / Gaji Pokok</th>
            <th style="font-weight: bold;">Total Shift</th>
            <th style="font-weight: bold;">Komisi Shift</th>
            <th style="font-weight: bold;">Motret / Project</th>
            <th style="font-weight: bold;">Lembur</th>
            <th style="font-weight: bold;">Bonus</th>
            <th style="font-weight: bold;">Potongan/Kasbon</th>
            <th style="font-weight: bold;">Total Bersih</th>
            <th style="font-weight: bold;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($salaryData as $index => $data)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $data['user']->name }}</td>
            <td>{{ $data['user']->allowance_type == 'daily' ? 'Harian' : ($data['user']->allowance_type == 'monthly' ? 'Bulanan' : 'Tidak Ada') }}</td>
            <td>{{ $data['allowance'] }}</td>
            <td>{{ $data['total_shifts'] }}</td>
            <td>{{ $data['komisi_shift'] }}</td>
            <td>{{ $data['motret'] }}</td>
            <td>{{ $data['lembur'] }}</td>
            <td>{{ $data['bonus'] }}</td>
            <td>{{ $data['kasbon'] }}</td>
            <td style="font-weight: bold;">{{ $data['total_bersih'] }}</td>
            <td>{{ $data['payroll'] && $data['payroll']->notes ? $data['payroll']->notes : '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="12">Belum ada data crew.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="10" style="text-align: right; font-weight: bold;">TOTAL KESELURUHAN PENGELUARAN GAJI</th>
            <th style="font-weight: bold;">{{ $totalSistem }}</th>
            <th></th>
        </tr>
    </tfoot>
</table>
