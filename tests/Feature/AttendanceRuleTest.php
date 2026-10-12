<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\OvertimeRequest;
use App\Models\Payroll;
use App\Models\PayrollEntry;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleLocation;
use App\Models\ScheduleShift;
use App\Models\User;
use App\Services\AttendanceRuleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Menjalankan aturan absensi & lembur di atas salinan struktur database
 * (web_keuangan_mono_test), setiap test digulung kembali.
 */
class AttendanceRuleTest extends TestCase
{
    use DatabaseTransactions;

    private User $crew;
    private ScheduleLocation $location;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->crew = User::factory()->create(['role' => 'crew', 'is_active' => true]);

        $this->location = ScheduleLocation::create([
            'name' => 'Studio',
            'shift_rate' => 45000,
            'is_active' => true,
        ]);
    }

    private function makeShift(array $attributes = []): ScheduleShift
    {
        return ScheduleShift::create(array_merge([
            'schedule_location_id' => $this->location->id,
            'name' => 'Reguler',
            'start_time' => '10:00:00',
            'end_time' => '22:00:00',
            'color' => '#3b82f6',
            'max_crew' => 1,
        ], $attributes));
    }

    private function assign(ScheduleShift $shift, string $date): void
    {
        ScheduleAssignment::create([
            'schedule_shift_id' => $shift->id,
            'user_id' => $this->crew->id,
            'date' => $date,
        ]);
    }

    private function clockIn()
    {
        return $this->post(route('schedules.attendances.store'), [
            'schedule_location_id' => $this->location->id,
            'type' => 'in',
            'latitude' => -6.2,
            'longitude' => 107.0,
            'photo' => 'data:image/webp;base64,' . base64_encode('selfie'),
        ]);
    }

    private function clockOut(?string $note = null)
    {
        return $this->post(route('schedules.attendances.store'), [
            'schedule_location_id' => $this->location->id,
            'type' => 'out',
            'latitude' => -6.2,
            'longitude' => 107.0,
            'photo' => 'data:image/webp;base64,' . base64_encode('selfie'),
            'overtime_note' => $note,
        ]);
    }

    private function latestAttendance(): Attendance
    {
        return Attendance::latest('id')->first();
    }

    public function test_absen_masuk_lewat_jendela_dicatat_sebagai_terlambat(): void
    {
        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        \Illuminate\Support\Carbon::setTestNow('2026-10-12 09:55:00');

        $this->clockIn()->assertSessionHas('success');

        $attendance = $this->latestAttendance();
        $this->assertSame('2026-10-12', $attendance->date->toDateString());
        $this->assertSame(['late_check_in' => 15], $attendance->violations);
    }

    public function test_absen_keluar_terlalu_cepat_dicatat_sebagai_pelanggaran(): void
    {
        $shift = $this->makeShift([
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);

        \Illuminate\Support\Carbon::setTestNow('2026-10-12 10:00:00');
        $this->clockIn();

        \Illuminate\Support\Carbon::setTestNow('2026-10-12 21:35:00');
        $this->clockOut()->assertSessionHas('success');

        $this->assertSame(['early_check_out' => 25], $this->latestAttendance()->violations);
    }

    public function test_absen_keluar_lewat_jendela_menciptakan_pengajuan_lembur_proporsional(): void
    {
        \App\Models\Setting::set('overtime_rate_per_hour', '15000');

        $shift = $this->makeShift([
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);

        \Illuminate\Support\Carbon::setTestNow('2026-10-12 10:00:00');
        $this->clockIn();

        \Illuminate\Support\Carbon::setTestNow('2026-10-12 23:20:00');
        $this->clockOut('Lanjut edit foto customer')->assertSessionHas('success');

        $overtime = OvertimeRequest::sole();
        $this->assertSame(60, $overtime->minutes);
        $this->assertSame(15000, $overtime->amount);
        $this->assertSame(OvertimeRequest::STATUS_PENDING, $overtime->status);
        $this->assertSame('Lanjut edit foto customer', $overtime->note);
    }

    public function test_absen_keluar_setelah_tengah_malam_tetap_tercatat_di_tanggal_shift(): void
    {
        \App\Models\Setting::set('overtime_rate_per_hour', '15000');

        $shift = $this->makeShift([
            'start_time' => '10:00:00',
            'end_time' => '22:00:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-11');

        $this->actingAs($this->crew);

        \Illuminate\Support\Carbon::setTestNow('2026-10-11 10:00:00');
        $this->clockIn();

        // Jam dinding sudah lewat tengah malam, sesi kemarin belum ditutup.
        \Illuminate\Support\Carbon::setTestNow('2026-10-12 00:30:00');
        $this->clockOut('Operasional molor karena antrean')->assertSessionHas('success');

        $attendance = $this->latestAttendance();
        $this->assertSame('2026-10-11', $attendance->date->toDateString());

        // Batas 22:20 (11 Okt) ke 00:30 (12 Okt) = 2 jam 10 menit.
        $overtime = OvertimeRequest::sole();
        $this->assertSame(130, $overtime->minutes);
        $this->assertSame(32500, $overtime->amount);
        $this->assertSame('2026-10-11', $overtime->date->toDateString());
    }

    public function test_dua_shift_satu_lokasi_atau_absen_masuk_pagi_dan_keluar_malam(): void
    {
        $pagi = $this->makeShift([
            'name' => 'Pagi',
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
        ]);

        $malam = $this->makeShift([
            'name' => 'Malam',
            'start_time' => '18:00:00',
            'end_time' => '22:00:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);

        $this->assign($pagi, '2026-10-12');
        $this->assign($malam, '2026-10-12');

        $this->actingAs($this->crew);

        \Illuminate\Support\Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();
        $this->assertSame([], $this->latestAttendance()->violations, 'Absen pagi dalam jendela harus bersih.');

        \Illuminate\Support\Carbon::setTestNow('2026-10-12 22:10:00');
        $this->clockOut();

        $out = $this->latestAttendance();
        $this->assertSame([], $out->violations, 'Absen malam dalam jendela harus bersih.');
        $this->assertSame(0, OvertimeRequest::count());
    }

    public function test_shift_jamsit_yang_melewati_tengah_malam(): void
    {
        \App\Models\Setting::set('overtime_rate_per_hour', '15000');

        $shift = $this->makeShift([
            'name' => 'Jamsit',
            'start_time' => '18:00:00',
            'end_time' => '11:15:00',
            'check_in_start' => '18:00:00',
            'check_in_end' => '18:30:00',
            'check_out_start' => '10:59:00',
            'check_out_end' => '11:15:00',
        ]);
        $this->assign($shift, '2026-10-11');

        $this->assertTrue($shift->fresh()->isOvernight());

        $this->actingAs($this->crew);

        \Illuminate\Support\Carbon::setTestNow('2026-10-11 18:10:00');
        $this->clockIn();
        $this->assertSame([], $this->latestAttendance()->violations);

        // Pulang 11:45 keesokan hari: jendela keluar jatuh di hari berikutnya,
        // sehingga kelebihan 30 menit dihitung lembur pada tanggal shift asalnya.
        \Illuminate\Support\Carbon::setTestNow('2026-10-12 11:45:00');
        $this->clockOut('Lanjut jaga sampai customer terakhir');

        $attendance = $this->latestAttendance();
        $this->assertSame('2026-10-11', $attendance->date->toDateString());

        $overtime = OvertimeRequest::sole();
        $this->assertSame(30, $overtime->minutes);
        $this->assertSame(7500, $overtime->amount);
        $this->assertSame('2026-10-11', $overtime->date->toDateString());
    }

    public function test_absen_masuk_dua_kali_dalam_hari_sama_ditolak(): void
    {
        $shift = $this->makeShift();
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);

        \Illuminate\Support\Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();
        $this->clockIn()->assertSessionHas('error');

        $this->assertSame(1, Attendance::where('type', 'in')->count());
    }

    public function test_acc_lembur_masuk_sebagai_entri_komponen_gaji(): void
    {
        $overtime = OvertimeRequest::create([
            'user_id' => $this->crew->id,
            'schedule_location_id' => $this->location->id,
            'date' => '2026-10-12',
            'window_end_at' => '2026-10-12 22:20:00',
            'check_out_at' => '2026-10-12 23:20:00',
            'minutes' => 60,
            'rate_per_hour' => 15000,
            'amount' => 15000,
            'note' => 'Lembur edit foto',
            'status' => OvertimeRequest::STATUS_PENDING,
        ]);

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $this->post(route('schedules.overtimes.approve', $overtime))
            ->assertSessionHas('success');

        $entry = PayrollEntry::sole();
        $this->assertSame('overtime_fee', $entry->component);
        $this->assertSame('overtime_auto', $entry->source);
        $this->assertSame(15000, $entry->amount);
        $this->assertSame(OvertimeRequest::STATUS_APPROVED, $overtime->fresh()->status);
    }

    public function test_entri_komponen_gaji_tidak_saling_menimpa(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        foreach (['2026-10-10', '2026-10-12'] as $date) {
            $this->post(route('schedules.payrolls.entries.store'), [
                'user_id' => $this->crew->id,
                'period' => '2026-10',
                'component' => 'overtime_fee',
                'date' => $date,
                'amount' => 25000,
                'note' => 'Lembur ' . $date,
            ])->assertSessionHas('success');
        }

        $this->assertSame(2, PayrollEntry::count());
        $this->assertSame(50000, (int) PayrollEntry::totals($this->crew->id, '2026-10-01', '2026-10-31')['overtime_fee']);
        $this->assertNotNull(Payroll::where('user_id', $this->crew->id)->where('period', '2026-10')->first());
    }

    public function test_entri_di_luar_periode_ditolak(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $this->post(route('schedules.payrolls.entries.store'), [
            'user_id' => $this->crew->id,
            'period' => '2026-10',
            'component' => 'bonus',
            'date' => '2026-11-02',
            'amount' => 10000,
        ])->assertSessionHas('error');

        $this->assertSame(0, PayrollEntry::count());
    }

    public function test_absen_keluar_ditandai_lupa_setelah_melewati_batas_toleransi(): void
    {
        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();

        // Batas jendela keluar 22:20 ditambah toleransi 3 jam = 01:20.
        Carbon::setTestNow('2026-10-13 00:30:00');
        $this->get(route('schedules.attendances.index'));
        $this->assertSame([], $this->latestAttendance()->violations, 'Belum lewat batas toleransi, belum lupa.');

        Carbon::setTestNow('2026-10-13 02:00:00');
        $this->get(route('schedules.attendances.index'));

        $this->assertSame(['missing_check_out' => 220], Attendance::where('type', 'in')->sole()->violations);
    }

    public function test_owner_dapat_mengubah_lupa_absen_keluar_menjadi_lembur(): void
    {
        \App\Models\Setting::set('overtime_rate_per_hour', '15000');

        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();

        Carbon::setTestNow('2026-10-13 02:00:00');
        $this->get(route('schedules.attendances.index'));

        $in = Attendance::where('type', 'in')->sole();
        $this->assertArrayHasKey('missing_check_out', $in->violations);

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $this->post(route('schedules.attendances.close-session', $in), [
            'action' => 'overtime',
            'check_out_date' => '2026-10-12',
            'check_out_time' => '23:30',
            'minutes' => 70,
            'note' => 'Sesungguhnya lembur, lupa absen keluar',
        ])->assertSessionHas('success');

        $this->assertArrayNotHasKey('missing_check_out', $in->fresh()->violations);

        $out = Attendance::where('type', 'out')->sole();
        $this->assertSame('2026-10-12', $out->date->toDateString());
        $this->assertSame('23:30', $out->created_at->format('H:i'));
        $this->assertSame(Attendance::SOURCE_MANUAL, $out->source);
        $this->assertSame([], $out->violations);

        $overtime = OvertimeRequest::sole();
        $this->assertSame(70, $overtime->minutes);
        $this->assertSame(17500, $overtime->amount);
        $this->assertSame(OvertimeRequest::STATUS_PENDING, $overtime->status);
    }

    public function test_lupa_absen_keluar_tetap_tercatat_saat_sesi_ditutup_bukan_sebagai_lembur(): void
    {
        $shift = $this->makeShift([
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 10:00:00');
        $this->clockIn();

        Carbon::setTestNow('2026-10-13 02:00:00');
        $this->get(route('schedules.attendances.index'));
        $in = Attendance::where('type', 'in')->sole();

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $this->post(route('schedules.attendances.close-session', $in), [
            'action' => 'missing',
            'check_out_date' => '2026-10-12',
            'check_out_time' => '22:50',
        ])->assertSessionHas('success');

        $this->assertArrayHasKey('missing_check_out', Attendance::where('type', 'out')->sole()->violations);
        $this->assertSame(0, OvertimeRequest::count(), 'Lupa absen bukan lembur.');
    }

    public function test_absensi_diisi_otomatis_dari_jadwal_tanpa_menimpa_data_lama(): void
    {
        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);

        foreach (['2026-10-05', '2026-10-10', '2026-10-11', '2026-10-12', '2026-10-13'] as $date) {
            $this->assign($shift, $date);
        }

        // Satu sesi sudah absen sendiri, jadi tidak boleh dibuat ulang.
        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-10 09:30:00');
        $this->clockIn();

        // Sesi 2026-10-05 cuma punya absen keluar (crew lupa absen masuk).
        $lupa = new Attendance([
            'user_id' => $this->crew->id,
            'schedule_location_id' => $this->location->id,
            'schedule_shift_id' => $shift->id,
            'type' => 'out',
            'date' => '2026-10-05',
            'status' => 'in_radius',
            'violations' => [],
            'notes' => 'lupa absen masuk',
        ]);
        $lupa->created_at = Carbon::parse('2026-10-05 22:10:00');
        $lupa->save();

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);
        Carbon::setTestNow('2026-10-13 09:00:00');

        $this->post(route('schedules.attendances.auto-fill'), [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-12',
            'location_id' => $this->location->id,
        ])->assertSessionHas('success');

        $this->assertSame(2, Attendance::where('date', '2026-10-11')->count());
        $this->assertSame(2, Attendance::where('date', '2026-10-12')->count());
        // Absen masuk crew yang asli tetap dipertahankan, hanya absen keluarnya yang dilengkapi.
        $this->assertSame(1, Attendance::where('date', '2026-10-10')->where('type', 'in')->count());
        $this->assertSame('2026-10-10 09:30:00', Attendance::where('date', '2026-10-10')->where('type', 'in')->sole()->created_at->format('Y-m-d H:i:s'));
        $this->assertSame(Attendance::SOURCE_AUTO, Attendance::where('date', '2026-10-10')->where('type', 'out')->sole()->source);

        // Sisi yang hilang saja: absen masuk dilengkapi, absen keluar asli dibiarkan.
        $this->assertSame(1, Attendance::where('date', '2026-10-05')->where('type', 'in')->count());
        $this->assertSame(1, Attendance::where('date', '2026-10-05')->where('type', 'out')->count());
        $this->assertSame('2026-10-05 22:10:00', Attendance::where('date', '2026-10-05')->where('type', 'out')->sole()->created_at->format('Y-m-d H:i:s'));

        // Shift hari ini belum selesai operasinya, jadi tidak boleh dikarang.
        $this->assertSame(0, Attendance::where('date', '2026-10-13')->count());

        $auto = Attendance::where('source', Attendance::SOURCE_AUTO)->get();
        $this->assertSame([], $auto->first()->violations);
        $this->assertSame(
            ['2026-10-10 22:20', '2026-10-11 22:20', '2026-10-12 22:20'],
            $auto->where('type', 'out')->sortBy('created_at')->map(fn ($r) => $r->created_at->format('Y-m-d H:i'))->values()->all()
        );
        // Dipanggil ulang tidak menambah apa pun.
        $before = Attendance::count();
        $this->post(route('schedules.attendances.auto-fill'), [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-12',
        ]);
        $this->assertSame($before, Attendance::count());
    }

    public function test_owner_dapat_mengubah_jam_dan_keterangan_absensi(): void
    {
        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();

        $in = Attendance::sole();
        $this->assertSame([], $in->violations);

        $owner = User::factory()->create(['role' => 'owner', 'name' => 'Khairul Fikri']);
        $this->actingAs($owner);

        $this->put(route('schedules.attendances.update', $in), [
            'date' => '2026-10-12',
            'time' => '09:55',
            'status' => 'auto',
            'notes' => 'Kendaraan mogok, sudah izin leader',
        ])->assertSessionHas('success');

        $in->refresh();
        $this->assertSame(['late_check_in' => 15], $in->violations);
        $this->assertSame('09:55', $in->created_at->format('H:i'));
        $this->assertSame('Kendaraan mogok, sudah izin leader', $in->notes);
        $this->assertSame('Khairul Fikri', $in->corrected_by);
    }

    public function test_owner_dapat_isi_lembur_manual_dari_halaman_rekap(): void
    {
        \App\Models\Setting::set('overtime_rate_per_hour', '15000');

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $this->post(route('schedules.overtimes.store'), [
            'user_id' => $this->crew->id,
            'schedule_location_id' => $this->location->id,
            'date' => '2026-10-12',
            'minutes' => 45,
            'note' => 'Bongkar dekorasi setelah tutup',
        ])->assertSessionHas('success');

        $overtime = OvertimeRequest::sole();
        $this->assertSame(45, $overtime->minutes);
        $this->assertSame(11250, $overtime->amount);
        $this->assertSame(OvertimeRequest::STATUS_PENDING, $overtime->status);

        // Setelah di-acc baru masuk komponen gaji.
        $this->post(route('schedules.overtimes.approve', $overtime))->assertSessionHas('success');

        $entry = PayrollEntry::sole();
        $this->assertSame('overtime_fee', $entry->component);
        $this->assertSame(11250, $entry->amount);
        $this->assertSame('2026-10-12', $entry->date->toDateString());
    }

    public function test_crew_tidak_dapat_mengubah_atau_mengisi_absensi_otomatis(): void
    {
        $shift = $this->makeShift(['check_in_end' => '09:40:00']);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();

        $in = Attendance::sole();

        $this->post(route('schedules.attendances.auto-fill'), [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-12',
        ])->assertForbidden();

        $this->put(route('schedules.attendances.update', $in), [
            'date' => '2026-10-12',
            'time' => '09:30',
            'status' => 'clean',
        ])->assertForbidden();

        $this->post(route('schedules.attendances.close-session', $in), [
            'action' => 'missing',
            'check_out_date' => '2026-10-12',
            'check_out_time' => '22:10',
        ])->assertForbidden();

        $this->assertSame(1, Attendance::count(), 'Crew tidak boleh menambah baris absensi.');
    }

    public function test_alasan_crew_tersaat_bersama_absen_yang_melewati_jendela(): void
    {
        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 09:55:00');

        $this->post(route('schedules.attendances.store'), [
            'schedule_location_id' => $this->location->id,
            'type' => 'in',
            'latitude' => -6.2,
            'longitude' => 107.0,
            'photo' => 'data:image/webp;base64,' . base64_encode('selfie'),
            'note' => 'Ban motor bocor di jalan',
        ])->assertSessionHas('success');

        $this->assertSame(['late_check_in' => 15], $this->latestAttendance()->violations);
        $this->assertSame('Ban motor bocor di jalan', $this->latestAttendance()->notes);
    }

    public function test_sesi_lupa_absen_keluar_mengunci_absensi_berikutnya(): void
    {
        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');
        $this->assign($shift, '2026-10-13');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();

        // Sudah lewat batas 3 jam dan sudah lewat tengah malam, tapi belum absen keluar.
        Carbon::setTestNow('2026-10-13 09:30:00');
        $this->clockIn()->assertSessionHas('error');
        $this->get(route('schedules.attendances.create'))
            ->assertSee('Absensi Anda dikunci');

        $this->assertSame(1, Attendance::count(), 'Crew tidak boleh membuat absen baru sebelum owner menutup sesi.');
    }

    public function test_crew_dapat_mengirim_alasan_untuk_sesi_terkunci(): void
    {
        $shift = $this->makeShift([
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 10:00:00');
        $this->clockIn();

        Carbon::setTestNow('2026-10-13 09:00:00');
        $in = Attendance::where('type', 'in')->sole();

        $this->post(route('schedules.attendances.note', $in), [
            'notes' => 'HP mati, baru sempat lapor pagi ini',
        ])->assertSessionHas('success');

        $this->assertSame('HP mati, baru sempat lapor pagi ini', $in->fresh()->notes);

        // Crew lain tidak boleh menulis di absensi orang lain.
        $lain = User::factory()->create(['role' => 'crew', 'is_active' => true]);
        $this->actingAs($lain);
        $this->post(route('schedules.attendances.note', $in), ['notes' => 'asal'])
            ->assertSessionHas('error');
        $this->assertSame('HP mati, baru sempat lapor pagi ini', $in->fresh()->notes);
    }

    public function test_isi_otomatis_melewati_sesi_yang_masih_menunggu_keputusan_owner(): void
    {
        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-11');
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-11 09:30:00');
        $this->clockIn();

        Carbon::setTestNow('2026-10-13 09:00:00');
        $this->get(route('schedules.attendances.index'));
        $this->assertArrayHasKey('missing_check_out', Attendance::where('date', '2026-10-11')->sole()->violations);

        $this->actingAs(User::factory()->create(['role' => 'owner']));
        $this->post(route('schedules.attendances.auto-fill'), [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-13',
        ])->assertSessionHas('success');

        $this->assertSame(0, Attendance::where('date', '2026-10-11')->where('type', 'out')->count(),
            'Sesi yang ditandai lupa absen tidak boleh ditutup diam-diam oleh isi otomatis.');
        $this->assertSame(2, Attendance::where('date', '2026-10-12')->count(),
            'Sesi lain yang bersih tetap diisi.');
    }

    public function test_absen_keluar_setelah_tengah_malam_menutup_sesi_kemarin(): void
    {
        \App\Models\Setting::set('overtime_rate_per_hour', '15000');

        $shift = $this->makeShift([
            'check_in_start' => '18:40:00',
            'check_in_end' => '19:00:00',
            'check_out_start' => '23:00:00',
            'check_out_end' => '23:20:00',
        ]);
        $this->assign($shift, '2026-10-10');

        // Baris lama: tercatat sebelum jendela waktu ada, jadi statusnya belum dinilai.
        $in = new Attendance([
            'user_id' => $this->crew->id, 'schedule_location_id' => $this->location->id,
            'schedule_shift_id' => $shift->id, 'type' => 'in', 'date' => '2026-10-10',
            'status' => 'in_radius', 'notes' => 'hujan deras, telat datang',
        ]);
        $in->created_at = Carbon::parse('2026-10-10 20:55:00');
        $in->save();

        $out = new Attendance([
            'user_id' => $this->crew->id, 'schedule_location_id' => $this->location->id,
            'schedule_shift_id' => $shift->id, 'type' => 'out', 'date' => '2026-10-11',
            'status' => 'in_radius',
        ]);
        $out->created_at = Carbon::parse('2026-10-11 00:49:00');
        $out->save();

        $this->actingAs(User::factory()->create(['role' => 'owner']));
        $this->get(route('schedules.attendances.index'));

        $this->assertSame(['late_check_in' => 115], $in->fresh()->violations, 'Status dinilai ulang dari jam aslinya.');
        $this->assertSame('2026-10-10', $out->fresh()->date->toDateString(), 'Absen keluar ikut tanggal shift asalnya.');
        $this->assertSame([], $out->fresh()->violations);

        $overtime = OvertimeRequest::sole();
        $this->assertSame(89, $overtime->minutes);
        $this->assertSame(22250, $overtime->amount);
        $this->assertSame('2026-10-10', $overtime->date->toDateString());
    }

    public function test_sesi_lintas_tengah_malam_tidak_lagi_disebut_belum_absen_keluar(): void
    {
        $shift = $this->makeShift(['check_out_start' => '23:00:00', 'check_out_end' => '23:20:00']);
        $this->assign($shift, '2026-10-10');

        $in = Attendance::create([
            'user_id' => $this->crew->id, 'schedule_location_id' => $this->location->id,
            'schedule_shift_id' => $shift->id, 'type' => 'in', 'date' => '2026-10-10',
            'status' => 'in_radius', 'violations' => [],
        ]);
        $in->created_at = Carbon::parse('2026-10-10 19:00:00');
        $in->save();

        $out = Attendance::create([
            'user_id' => $this->crew->id, 'schedule_location_id' => $this->location->id,
            'schedule_shift_id' => $shift->id, 'type' => 'out', 'date' => '2026-10-11',
            'status' => 'in_radius', 'violations' => [],
        ]);
        $out->created_at = Carbon::parse('2026-10-11 00:49:00');
        $out->save();

        $this->assertNotNull(AttendanceRuleService::closingOut($in->fresh()));

        $this->actingAs(User::factory()->create(['role' => 'owner']));

        $response = $this->get(route('schedules.attendances.index', ['date' => '2026-10-10']));

        // Sesi dianggap tertutup, jadi tidak ada baris yang menunggu tindakan owner.
        $this->assertEmpty($response->viewData('openIds'));
        $this->assertSame([], $in->fresh()->violations);
        $this->assertSame([], $out->fresh()->violations);
    }

    public function test_status_lembur_dari_koreksi_owner_membuat_pengajuan_lembur_otomatis(): void
    {
        \App\Models\Setting::set('overtime_rate_per_hour', '15000');

        $shift = $this->makeShift([
            'check_in_start' => '09:20:00', 'check_in_end' => '09:40:00',
            'check_out_start' => '22:00:00', 'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();
        Carbon::setTestNow('2026-10-12 23:50:00');
        $this->clockOut();

        $out = Attendance::where('type', 'out')->sole();

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $this->put(route('schedules.attendances.update', $out), [
            'date' => '2026-10-12',
            'time' => '23:50',
            'status' => 'lembur',
            'notes' => 'Lembur packing produk',
        ])->assertSessionHas('success');

        $overtime = OvertimeRequest::sole();
        $this->assertSame(90, $overtime->minutes);
        $this->assertSame(22500, $overtime->amount);
        $this->assertSame(OvertimeRequest::STATUS_PENDING, $overtime->status);
        $this->assertSame([], $out->fresh()->violations);
    }

    public function test_log_absensi_memakai_sepuluh_baris_saat_default(): void
    {
        $shift = $this->makeShift();

        for ($day = 1; $day <= 12; $day++) {
            Attendance::create([
                'user_id' => $this->crew->id,
                'schedule_location_id' => $this->location->id,
                'schedule_shift_id' => $shift->id,
                'type' => 'in',
                'date' => sprintf('2026-10-%02d', $day),
                'status' => 'in_radius',
                'violations' => [],
            ]);
        }

        $this->actingAs(User::factory()->create(['role' => 'owner']));

        $this->get(route('schedules.attendances.index'))
            ->assertSee('10 baris')
            ->assertViewHas('perPage', 10);

        $this->get(route('schedules.attendances.index', ['per_page' => 25]))
            ->assertViewHas('perPage', 25);
    }

    public function test_laporan_pdf_absensi_harian_mingguan_dan_bulanan(): void
    {
        $shift = $this->makeShift([
            'check_in_start' => '09:20:00',
            'check_in_end' => '09:40:00',
            'check_out_start' => '22:00:00',
            'check_out_end' => '22:20:00',
        ]);
        $this->assign($shift, '2026-10-12');

        $this->actingAs($this->crew);
        Carbon::setTestNow('2026-10-12 09:30:00');
        $this->clockIn();

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        foreach (['daily', 'weekly', 'monthly'] as $period) {
            $this->get(route('schedules.attendances.report', [
                'period' => $period,
                'date' => '2026-10-12',
                'preview' => 1,
            ]))
                ->assertOk()
                ->assertSee('LAPORAN ABSENSI CREW')
                ->assertSee($this->crew->name);
        }

        // Unduhan menghasilkan berkas PDF sungguhan.
        $response = $this->get(route('schedules.attendances.report', ['period' => 'monthly', 'date' => '2026-10-12']));
        $this->assertStringContainsString('%PDF', $response->getContent());
    }
}
