<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\OvertimeRequest;
use App\Models\Payroll;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleLocation;
use App\Models\ScheduleShift;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Memastikan halaman-halaman modul jadwal tetap ter-render setelah fitur
 * absensi, lembur, dan entri gaji ditambahkan.
 */
class SchedulePagesTest extends TestCase
{
    use DatabaseTransactions;

    private User $crew;
    private User $owner;
    private ScheduleLocation $location;
    private ScheduleShift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->crew = User::factory()->create(['role' => 'crew', 'is_active' => true]);
        $this->owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        $this->location = ScheduleLocation::create([
            'name' => 'Studio', 'shift_rate' => 45000, 'is_active' => true,
        ]);

        $this->shift = ScheduleShift::create([
            'schedule_location_id' => $this->location->id,
            'name' => 'Pagi',
            'start_time' => '10:00:00',
            'end_time' => '22:00:00',
            'color' => '#3b82f6',
            'max_crew' => 2,
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);

        ScheduleAssignment::create([
            'schedule_shift_id' => $this->shift->id,
            'user_id' => $this->crew->id,
            'date' => now()->toDateString(),
        ]);
    }

    public function test_halaman_dashboard_jadwal_terinclude_peringkat(): void
    {
        Attendance::create([
            'user_id' => $this->crew->id,
            'schedule_location_id' => $this->location->id,
            'schedule_shift_id' => $this->shift->id,
            'type' => 'in',
            'date' => now()->toDateString(),
            'status' => 'in_radius',
            'violations' => ['late_check_in' => 12],
        ]);

        $this->actingAs($this->owner)
            ->get('/schedules?tab=dashboard&stats_filter=monthly&stats_month=' . now()->format('Y-m'))
            ->assertOk()
            ->assertSee('Performa Crew per Lokasi')
            ->assertSee($this->crew->name);
    }

    public function test_halaman_absensi_tampilkan_jendela_dan_pelanggaran(): void
    {
        $this->actingAs($this->crew)->get('/schedules/attendances/create')
            ->assertOk()
            ->assertSee('Jendela Absen');

        $this->actingAs($this->owner)->get('/schedules/attendances')
            ->assertOk()
            ->assertSee('Terlambat absen masuk');
    }

    public function test_halaman_rekap_lembur(): void
    {
        OvertimeRequest::create([
            'user_id' => $this->crew->id,
            'schedule_location_id' => $this->location->id,
            'date' => now()->toDateString(),
            'window_end_at' => now()->setTime(22, 20),
            'check_out_at' => now()->setTime(23, 30),
            'minutes' => 70,
            'rate_per_hour' => 15000,
            'amount' => 17500,
            'note' => 'Lanjut edit foto',
            'status' => OvertimeRequest::STATUS_PENDING,
        ]);

        $this->actingAs($this->owner)->get('/schedules/overtimes')
            ->assertOk()
            ->assertSee('Menunggu Acc')
            ->assertSee($this->crew->name)
            ->assertSee('Lanjut edit foto');
    }

    public function test_halaman_payroll_dengan_modal_entri(): void
    {
        Payroll::create(['user_id' => $this->crew->id, 'period' => now()->format('Y-m'), 'notes' => 'Catatan uji']);

        $this->actingAs($this->owner)->get('/schedules/payrolls?month=' . now()->format('Y-m'))
            ->assertOk()
            ->assertSee('Edit Komponen Gaji: ' . $this->crew->name)
            ->assertSee('Tambah');
    }

    public function test_pdf_gaji_termuat_dengan_logo(): void
    {
        $preview = $this->actingAs($this->owner)
            ->get('/schedules/payrolls/export-pdf?month=' . now()->format('Y-m') . '&preview');

        $preview->assertOk()->assertSee('data:image/', false);

        $download = $this->actingAs($this->owner)
            ->get('/schedules/payrolls/export-pdf?month=' . now()->format('Y-m'));

        $download->assertOk();
        $this->assertStringStartsWith('%PDF', $download->getContent());
    }

    public function test_filter_absensi_berdasarkan_pelanggaran(): void
    {
        $lain = User::factory()->create(['role' => 'crew', 'is_active' => true]);

        $telat = new Attendance([
            'user_id' => $this->crew->id,
            'schedule_location_id' => $this->location->id,
            'schedule_shift_id' => $this->shift->id,
            'type' => 'in',
            'date' => now()->toDateString(),
            'status' => 'in_radius',
            'violations' => ['late_check_in' => 15],
        ]);
        // 09:55, 15 menit sesudah jendela absen masuk 09:20 - 09:40.
        $telat->created_at = now()->setTime(9, 55);
        $telat->save();

        Attendance::create([
            'user_id' => $lain->id,
            'schedule_location_id' => $this->location->id,
            'schedule_shift_id' => $this->shift->id,
            'type' => 'in',
            'date' => now()->toDateString(),
            'status' => 'out_of_radius',
            'distance' => 800,
            'violations' => ['out_of_radius' => 800],
        ]);

        // Periksa data yang dikirim ke view, bukan HTML-nya, karena nama crew juga
        // tampil di dropdown filter dan modal "Isi Otomatis".
        $late = $this->actingAs($this->owner)->get('/schedules/attendances?violation=late_check_in')
            ->assertOk()->viewData('attendances');
        $this->assertSame([$this->crew->name], $late->pluck('user.name')->all());

        $radius = $this->actingAs($this->owner)->get('/schedules/attendances?violation=out_of_radius')
            ->assertOk()->viewData('attendances');
        $this->assertSame([$lain->name], $radius->pluck('user.name')->all());
    }

    public function test_halaman_jadwal_dan_absensiversi_crew(): void
    {
        Payroll::create([
            'user_id' => $this->crew->id,
            'period' => now()->format('Y-m'),
            'notes' => 'Catatan evaluasi crew',
        ]);

        \App\Models\PayrollEntry::create([
            'user_id' => $this->crew->id,
            'date' => now()->toDateString(),
            'component' => 'overtime_fee',
            'amount' => 30000,
            'note' => 'Lembur semalam',
            'source' => 'manual',
        ]);

        $this->actingAs($this->crew)->get('/schedules?tab=schedule')
            ->assertOk();

        // Halaman absen crew harus membawa jendela waktu ke sisi klien.
        $this->actingAs($this->crew)->get('/schedules/attendances/create')
            ->assertOk()
            ->assertSee('out_deadline')
            ->assertSee('09:20 - 09:40');

        $this->actingAs($this->crew)->get('/schedules/overtimes')
            ->assertOk();
    }

    public function test_simpan_jendela_absen_shift(): void
    {
        $this->actingAs($this->owner)->post('/schedules/shifts', [
            'schedule_location_id' => $this->location->id,
            'name' => 'Sore',
            'start_time' => '15:00',
            'end_time' => '22:00',
            'color' => '#f59e0b',
            'max_crew' => 1,
            'check_in_start' => '14:20',
        ])->assertSessionHasErrors('check_in_end');

        $this->actingAs($this->owner)->post('/schedules/shifts', [
            'schedule_location_id' => $this->location->id,
            'name' => 'Sore',
            'start_time' => '15:00',
            'end_time' => '22:00',
            'color' => '#f59e0b',
            'max_crew' => 1,
            'check_in_start' => '14:20',
            'check_in_end' => '14:40',
            'check_out_start' => '22:00',
            'check_out_end' => '22:20',
        ])->assertSessionHas('success');

        $shift = ScheduleShift::where('name', 'Sore')->sole();
        $this->assertSame('14:20', substr($shift->check_in_start, 0, 5));
        $this->assertTrue($shift->hasWindow('out'));
        $this->assertFalse($shift->isOvernight());
    }

    public function test_logo_pdf_laporan_mengembalikan_data_uri(): void
    {
        // store_logo kosong seharusnya tetap jatuh ke public/logo.png, bukan tanpa logo.
        $this->assertStringStartsWith('data:image/', \App\Models\Setting::logoDataUri());
    }

    public function test_pdf_laporan_master_terrender_dengan_logo(): void
    {
        $this->actingAs($this->owner)->post('/reports/export-pdf', [
            'sections' => ['ringkasan'],
            'period' => 'bulan_ini',
            'orientation' => 'portrait',
            'theme' => 'white',
            'preview' => 1,
        ])->assertOk()->assertSee('data:image/', false);
    }

    public function test_halaman_pengaturan_bagian_absensi(): void
    {
        $this->actingAs($this->owner)->get('/settings')
            ->assertOk()
            ->assertSee('Absensi &amp; Lembur', false)
            ->assertSee('overtime_rate_per_hour');
    }
}
