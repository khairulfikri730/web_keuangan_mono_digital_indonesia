<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleLocation;
use App\Models\ScheduleCrew;
use App\Models\ScheduleShift;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $viewMode = $request->input('view', 'daily');
        $date = $request->input('date', now()->format('Y-m-d'));
        $month = $request->input('month', now()->format('Y-m'));
        $tab = $request->input('tab', 'dashboard');

        $locations = ScheduleLocation::with(['shifts' => function ($q) {
            $q->orderBy('start_time');
        }])->get();

        $crews = ScheduleCrew::orderBy('name')->get();
        $activeCrews = ScheduleCrew::active()->orderBy('name')->get();

        // Determine date range based on view mode
        if ($viewMode === 'weekly') {
            $startDate = Carbon::parse($date)->startOfWeek(Carbon::MONDAY);
            $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
        } elseif ($viewMode === 'monthly') {
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } else {
            $startDate = Carbon::parse($date);
            $endDate = $startDate->copy();
        }

        $assignments = ScheduleAssignment::with(['shift.location', 'crew', 'originalCrew'])
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // Build dates array for weekly/monthly grid
        $dates = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }

        // Stats
        $todayAssignments = ScheduleAssignment::where('date', now()->format('Y-m-d'))->count();
        $todayOpen = ScheduleAssignment::where('date', now()->format('Y-m-d'))->where('status', 'open')->count();
        $todayClosed = ScheduleAssignment::where('date', now()->format('Y-m-d'))->where('status', 'close')->count();
        $totalShifts = ScheduleShift::count();
        $activeLocations = ScheduleLocation::where('is_active', true)->count();

        // ── Crew Statistics ───────────────────────────────────
        $statsFilter = $request->input('stats_filter', 'weekly');
        $statsDate = $request->input('stats_date', now()->format('Y-m-d'));

        if ($statsFilter === 'daily') {
            $statsStart = Carbon::parse($statsDate);
            $statsEnd = $statsStart->copy();
        } elseif ($statsFilter === 'monthly') {
            $statsMonth = $request->input('stats_month', now()->format('Y-m'));
            $statsStart = Carbon::parse($statsMonth . '-01')->startOfMonth();
            $statsEnd = $statsStart->copy()->endOfMonth();
        } else {
            $statsStart = Carbon::parse($statsDate)->startOfWeek(Carbon::MONDAY);
            $statsEnd = $statsStart->copy()->endOfWeek(Carbon::SUNDAY);
        }

        $statsAssignments = ScheduleAssignment::with(['shift.location', 'crew'])
            ->whereBetween('date', [$statsStart->format('Y-m-d'), $statsEnd->format('Y-m-d')])
            ->get();

        $crewStats = [];
        foreach ($activeCrews as $crew) {
            $crewAsgn = $statsAssignments->where('schedule_crew_id', $crew->id);
            $total = $crewAsgn->count();
            $open = $crewAsgn->where('status', 'open')->count();
            $closed = $crewAsgn->where('status', 'close')->count();
            $replaced = $crewAsgn->whereNotNull('original_crew_id')->count();
            $crewStats[] = [
                'crew' => $crew,
                'total_shifts' => $total,
                'open' => $open,
                'closed' => $closed,
                'replaced' => $replaced,
                'pct_active' => $total > 0 ? round(($open / $total) * 100) : 0,
            ];
        }

        return view('schedules.index', compact(
            'locations', 'crews', 'activeCrews', 'assignments',
            'viewMode', 'date', 'month', 'startDate', 'endDate',
            'todayAssignments', 'todayOpen', 'todayClosed', 'totalShifts',
            'activeLocations', 'dates', 'tab',
            'crewStats', 'statsFilter', 'statsDate', 'statsStart', 'statsEnd'
        ));
    }

    // ── LOCATIONS ──────────────────────────────────────────────

    public function storeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        ScheduleLocation::create($request->only('name', 'description'));
        return back()->with('success', 'Lokasi berhasil ditambahkan!');
    }

    public function updateLocation(Request $request, ScheduleLocation $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $location->update($request->only('name', 'description'));
        return back()->with('success', 'Lokasi berhasil diperbarui!');
    }

    public function destroyLocation(ScheduleLocation $location)
    {
        $location->delete();
        return back()->with('success', 'Lokasi berhasil dihapus!');
    }

    // ── CREWS ──────────────────────────────────────────────────

    public function storeCrew(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
        ]);

        ScheduleCrew::create($request->only('name', 'phone', 'position'));
        return back()->with('success', 'Crew berhasil ditambahkan!');
    }

    public function updateCrew(Request $request, ScheduleCrew $crew)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
        ]);

        $crew->update($request->only('name', 'phone', 'position'));
        return back()->with('success', 'Crew berhasil diperbarui!');
    }

    public function toggleCrew(ScheduleCrew $crew)
    {
        $crew->update(['is_active' => !$crew->is_active]);
        return back()->with('success', 'Status crew diperbarui!');
    }

    public function destroyCrew(ScheduleCrew $crew)
    {
        $crew->delete();
        return back()->with('success', 'Crew berhasil dihapus!');
    }

    // ── SHIFTS ─────────────────────────────────────────────────

    public function storeShift(Request $request)
    {
        $request->validate([
            'schedule_location_id' => 'required|exists:schedule_locations,id',
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'color' => 'required|string',
            'max_crew' => 'required|integer|min:1',
        ]);

        ScheduleShift::create($request->only('schedule_location_id', 'name', 'start_time', 'end_time', 'color', 'max_crew'));
        return back()->with('success', 'Shift berhasil ditambahkan!');
    }

    public function updateShift(Request $request, ScheduleShift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'color' => 'required|string',
            'max_crew' => 'required|integer|min:1',
        ]);

        $shift->update($request->only('name', 'start_time', 'end_time', 'color', 'max_crew'));
        return back()->with('success', 'Shift berhasil diperbarui!');
    }

    public function destroyShift(ScheduleShift $shift)
    {
        $shift->delete();
        return back()->with('success', 'Shift berhasil dihapus!');
    }

    // ── ASSIGNMENTS ────────────────────────────────────────────

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'schedule_shift_id' => 'required|exists:schedule_shifts,id',
            'schedule_crew_id' => 'required|exists:schedule_crews,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $shift = ScheduleShift::find($request->schedule_shift_id);

        // Check max_crew limit
        $currentCount = ScheduleAssignment::where('schedule_shift_id', $request->schedule_shift_id)
            ->where('date', $request->date)
            ->count();

        if ($currentCount >= $shift->max_crew) {
            return back()->with('error', "Shift {$shift->name} sudah penuh ({$shift->max_crew} orang max)!");
        }

        // Check duplicate
        $existing = ScheduleAssignment::where('schedule_shift_id', $request->schedule_shift_id)
            ->where('schedule_crew_id', $request->schedule_crew_id)
            ->where('date', $request->date)
            ->first();

        if ($existing) {
            return back()->with('error', 'Crew sudah terjadwal di shift ini pada tanggal tersebut!');
        }

        // Auto-close if the shift is already closed by another crew member on this date
        $existingClosed = ScheduleAssignment::where('schedule_shift_id', $request->schedule_shift_id)
            ->where('date', $request->date)
            ->where('status', 'close')
            ->first();

        $newAsgn = ScheduleAssignment::create($request->only('schedule_shift_id', 'schedule_crew_id', 'date', 'notes'));

        if ($existingClosed) {
            $newAsgn->update([
                'status' => 'close',
                'closed_by' => $existingClosed->closed_by,
                'closed_reason' => $existingClosed->closed_reason,
                'closed_at_time' => $existingClosed->closed_at_time,
            ]);
        }

        return back()->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function destroyAssignment(ScheduleAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Jadwal berhasil dihapus!');
    }

    // ── CLOSE / REOPEN ASSIGNMENT ──────────────────────────────

    public function closeAssignment(Request $request, ScheduleAssignment $assignment)
    {
        $request->validate([
            'closed_reason' => 'nullable|string|max:255',
            'closed_at_time' => 'nullable|date_format:H:i',
        ]);

        $assignment->update([
            'status' => 'close',
            'closed_by' => auth()->user()->name,
            'closed_reason' => $request->closed_reason ?? 'Tidak ada alasan',
            'closed_at_time' => $request->closed_at_time,
        ]);

        // Propagasi penutupan ke kru lain di shift dan tanggal yang sama
        ScheduleAssignment::where('schedule_shift_id', $assignment->schedule_shift_id)
            ->where('date', $assignment->date->format('Y-m-d'))
            ->where('id', '!=', $assignment->id)
            ->update([
                'status' => 'close',
                'closed_by' => auth()->user()->name,
                'closed_reason' => $request->closed_reason ?? 'Tidak ada alasan',
                'closed_at_time' => $request->closed_at_time,
            ]);

        return back()->with('success', "Shift ditutup oleh " . auth()->user()->name . "!");
    }

    public function reopenAssignment(ScheduleAssignment $assignment)
    {
        $assignment->update([
            'status' => 'open',
            'closed_by' => null,
            'closed_reason' => null,
        ]);

        return back()->with('success', 'Shift dibuka kembali!');
    }

    // ── CHANGE CREW ────────────────────────────────────────────

    public function changeAssignment(Request $request, ScheduleAssignment $assignment)
    {
        $request->validate([
            'new_crew_id' => 'required|exists:schedule_crews,id',
            'change_notes' => 'nullable|string|max:255',
        ]);

        // Check if new crew already assigned to this shift on this date
        $existing = ScheduleAssignment::where('schedule_shift_id', $assignment->schedule_shift_id)
            ->where('schedule_crew_id', $request->new_crew_id)
            ->where('date', $assignment->date->format('Y-m-d'))
            ->first();

        if ($existing) {
            return back()->with('error', 'Crew pengganti sudah terjadwal di shift ini pada tanggal tersebut!');
        }

        $oldCrewId = $assignment->schedule_crew_id;

        $assignment->update([
            'original_crew_id' => $assignment->original_crew_id ?? $oldCrewId,
            'schedule_crew_id' => $request->new_crew_id,
            'changed_by' => auth()->user()->name,
            'notes' => $request->change_notes ?? $assignment->notes,
        ]);

        return back()->with('success', 'Crew berhasil diganti!');
    }

    // ── BULK ASSIGN ────────────────────────────────────────────

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'schedule_shift_id' => 'required|exists:schedule_shifts,id',
            'schedule_crew_id' => 'required|exists:schedule_crews,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $shift = ScheduleShift::find($request->schedule_shift_id);
        $start = Carbon::parse($request->date_from);
        $end = Carbon::parse($request->date_to);
        $created = 0;
        $skipped = 0;

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr = $d->format('Y-m-d');

            // Check max_crew limit
            $currentCount = ScheduleAssignment::where('schedule_shift_id', $request->schedule_shift_id)
                ->where('date', $dateStr)
                ->count();

            if ($currentCount >= $shift->max_crew) {
                $skipped++;
                continue;
            }

            // Check duplicate
            $existing = ScheduleAssignment::where('schedule_shift_id', $request->schedule_shift_id)
                ->where('schedule_crew_id', $request->schedule_crew_id)
                ->where('date', $dateStr)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            ScheduleAssignment::create([
                'schedule_shift_id' => $request->schedule_shift_id,
                'schedule_crew_id' => $request->schedule_crew_id,
                'date' => $dateStr,
            ]);
            $created++;
        }

        return back()->with('success', "{$created} jadwal berhasil ditambahkan!" . ($skipped > 0 ? " ({$skipped} dilewati karena duplikat/penuh)" : ''));
    }

    // ── WEEKLY BULK ASSIGN (CHECKBOX-BASED) ────────────────────

    public function weeklyBulkAssign(Request $request)
    {
        $request->validate([
            'schedule_shift_id' => 'required|exists:schedule_shifts,id',
            'schedule_crew_id' => 'required|exists:schedule_crews,id',
            'week_start' => 'required|date',
            'days' => 'required|array|min:1',
            'days.*' => 'integer|between:0,6',
        ]);

        $shift = ScheduleShift::find($request->schedule_shift_id);
        $weekStart = Carbon::parse($request->week_start)->startOfWeek(Carbon::MONDAY);
        $created = 0;
        $skipped = 0;

        foreach ($request->days as $dayOffset) {
            $targetDate = $weekStart->copy()->addDays((int) $dayOffset);
            $dateStr = $targetDate->format('Y-m-d');

            // Check max_crew limit
            $currentCount = ScheduleAssignment::where('schedule_shift_id', $request->schedule_shift_id)
                ->where('date', $dateStr)
                ->count();

            if ($currentCount >= $shift->max_crew) {
                $skipped++;
                continue;
            }

            // Check duplicate
            $existing = ScheduleAssignment::where('schedule_shift_id', $request->schedule_shift_id)
                ->where('schedule_crew_id', $request->schedule_crew_id)
                ->where('date', $dateStr)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            ScheduleAssignment::create([
                'schedule_shift_id' => $request->schedule_shift_id,
                'schedule_crew_id' => $request->schedule_crew_id,
                'date' => $dateStr,
            ]);
            $created++;
        }

        return back()->with('success', "{$created} jadwal berhasil ditambahkan!" . ($skipped > 0 ? " ({$skipped} dilewati)" : ''));
    }

    // ── POSTER (WEEKLY) ────────────────────────────────────────

    public function poster(Request $request)
    {
        $type = $request->input('type', 'weekly');
        $locationId = $request->input('location_id');

        if ($type === 'monthly') {
            $month = $request->input('month', now()->format('Y-m'));
            $startDate = Carbon::parse($month . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } elseif ($type === 'daily') {
            $date = $request->input('date', now()->format('Y-m-d'));
            $startDate = Carbon::parse($date);
            $endDate = $startDate->copy();
        } else {
            $date = $request->input('date', now()->format('Y-m-d'));
            $startDate = Carbon::parse($date)->startOfWeek(Carbon::MONDAY);
            $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
        }

        $reportDates = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $reportDates[] = $d->copy();
        }

        $locationsQuery = ScheduleLocation::active()->with(['shifts' => function ($q) use ($startDate, $endDate) {
            $q->orderBy('start_time')
              ->with(['assignments' => function ($q2) use ($startDate, $endDate) {
                $q2->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                   ->with('crew');
            }]);
        }]);

        if ($locationId) {
            $locationsQuery->where('id', $locationId);
        }

        $locations = $locationsQuery->get();

        return view('schedules.poster', compact('locations', 'startDate', 'endDate', 'reportDates', 'type'));
    }
}
