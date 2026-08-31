<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleLocation;
use App\Models\User;
use App\Models\ScheduleShift;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $viewMode = $request->input('view', 'weekly');
        $date = $request->input('date');
        $month = $request->input('month');

        // Smart sync between date and month when transitioning views
        if ($date && !$month) {
            $month = Carbon::parse($date)->format('Y-m');
        } elseif ($month && !$date) {
            $date = Carbon::parse($month . '-01')->format('Y-m-d');
        }

        // Fallbacks
        $date = $date ?? now()->format('Y-m-d');
        $month = $month ?? now()->format('Y-m');
        $tab = $request->input('tab', 'dashboard');

        $locationsQuery = ScheduleLocation::with(['shifts' => function ($q) {
            $q->orderBy('start_time');
        }]);

        if (auth()->user()->role === 'crew') {
            $userLocIds = \App\Models\ScheduleAssignment::where('user_id', auth()->id())
                ->whereHas('shift')
                ->join('schedule_shifts', 'schedule_assignments.schedule_shift_id', '=', 'schedule_shifts.id')
                ->pluck('schedule_shifts.schedule_location_id')
                ->unique();
            $locationsQuery->whereIn('id', $userLocIds);
        }

        $locations = $locationsQuery->get();

        $users = User::orderBy('name')->get();
        $activeUsers = User::where('is_active', true)->orderBy('name')->get();

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

        $assignments = ScheduleAssignment::with(['shift.location', 'user', 'originalUser'])
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // Build dates array for weekly/monthly grid
        $dates = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }

        // Pending Swaps (for super admin or the targeted user)
        $pendingSwapsQuery = ScheduleAssignment::with(['shift.location', 'user', 'swapTargetUser'])
            ->where('swap_status', 'pending');
        if (!in_array(auth()->user()->role, ['superadmin', 'owner'])) {
            $pendingSwapsQuery->where('swap_requested_to', auth()->id());
        }
        $pendingSwaps = $pendingSwapsQuery->get();

        // Stats
        $todayAssignments = ScheduleAssignment::where('date', now()->format('Y-m-d'))->count();
        $todayOpen = ScheduleAssignment::where('date', now()->format('Y-m-d'))->where('status', 'open')->count();
        $todayClosedDb = ScheduleAssignment::where('date', now()->format('Y-m-d'))->where('status', 'close')->count();
        
        $totalCapacityToday = 0;
        foreach($locations as $loc) {
            foreach($loc->shifts as $s) {
                $totalCapacityToday += $s->max_crew;
            }
        }
        $todayEmptySlots = max(0, $totalCapacityToday - $todayAssignments);
        $todayClosed = $todayClosedDb + $todayEmptySlots;
        $totalShifts = ScheduleShift::count();
        $activeLocations = ScheduleLocation::where('is_active', true)->count();

        // ── Crew Statistics ───────────────────────────────────
        $statsFilter = $request->input('stats_filter', 'weekly');
        $statsDate = $request->input('stats_date');
        $statsMonth = $request->input('stats_month');

        // Smart sync for stats dates
        if ($statsDate && !$statsMonth) {
            $statsMonth = Carbon::parse($statsDate)->format('Y-m');
        } elseif ($statsMonth && !$statsDate) {
            $statsDate = Carbon::parse($statsMonth . '-01')->format('Y-m-d');
        }

        // Fallbacks
        $statsDate = $statsDate ?? now()->format('Y-m-d');
        $statsMonth = $statsMonth ?? now()->format('Y-m');

        if ($statsFilter === 'daily') {
            $statsStart = Carbon::parse($statsDate);
            $statsEnd = $statsStart->copy();
        } elseif ($statsFilter === 'monthly') {
            $statsStart = Carbon::parse($statsMonth . '-01')->startOfMonth();
            $statsEnd = $statsStart->copy()->endOfMonth();
        } else {
            $statsStart = Carbon::parse($statsDate)->startOfWeek(Carbon::MONDAY);
            $statsEnd = $statsStart->copy()->endOfWeek(Carbon::SUNDAY);
        }

        $statsAssignments = ScheduleAssignment::with(['shift.location', 'user'])
            ->whereBetween('date', [$statsStart->format('Y-m-d'), $statsEnd->format('Y-m-d')])
            ->get();

        $locationStats = [];
        foreach ($locations->where('is_active', true) as $loc) {
            $locUsers = [];
            foreach ($activeUsers as $user) {
                // Get all assignments for this user in this location
                $userAsgn = $statsAssignments->filter(function($a) use ($user, $loc) {
                    return $a->user_id == $user->id && $a->shift && $a->shift->schedule_location_id == $loc->id;
                });
                
                $total = $userAsgn->count();

                // Only include users who have shifts in this location
                if ($total > 0) {
                    $open = $userAsgn->where('status', 'open')->count();
                    $closed = $userAsgn->where('status', 'close')->count();
                    $replaced = $userAsgn->whereNotNull('original_user_id')->count();
                    
                    $shiftCounts = [];
                    foreach ($loc->shifts as $shift) {
                        $shiftCounts[$shift->id] = $userAsgn->where('schedule_shift_id', $shift->id)->count();
                    }

                    $komisi = 0;
                    $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
                    $completedCount = $userAsgn->where('date', '<', $todayStr)->count();
                    
                    if (is_array($user->custom_rates) && isset($user->custom_rates[$loc->id])) {
                        $komisi = $completedCount * $user->custom_rates[$loc->id];
                    } else {
                        $komisi = $completedCount * $loc->shift_rate;
                    }

                    $locUsers[] = [
                        'user' => $user,
                        'total_shifts' => $total,
                        'open' => $open,
                        'closed' => $closed,
                        'replaced' => $replaced,
                        'shift_counts' => $shiftCounts,
                        'komisi' => $komisi,
                        'pct_active' => round(($open / $total) * 100),
                    ];
                }
            }
            
            // Sort: users with shifts first, then alphabetically
            usort($locUsers, function($a, $b) {
                if ($a['total_shifts'] === $b['total_shifts']) {
                    return strcmp($a['user']->name, $b['user']->name);
                }
                return $b['total_shifts'] <=> $a['total_shifts'];
            });

            $locationStats[$loc->id] = [
                'location' => $loc,
                'users' => $locUsers
            ];
        }

        // Data Finansial khusus Crew Biasa & Kasir
        $crewFinancial = null;
        if (in_array(auth()->user()->role, ['crew', 'kasir'])) {
            // Gunakan $month yang dipilih di filter, bukan Carbon::now()
            $filterMonth = Carbon::parse($month . '-01');
            $monthStart = $filterMonth->copy()->startOfMonth()->format('Y-m-d');
            $monthEnd = $filterMonth->copy()->endOfMonth()->format('Y-m-d');
            
            $userAsgn = \App\Models\ScheduleAssignment::with('shift.location')
                ->where('user_id', auth()->id())
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->get();
                
            $komisiShift = 0;
            $todayStr = \Carbon\Carbon::today()->format('Y-m-d');
            foreach ($userAsgn as $asgn) {
                if ($asgn->date < $todayStr) {
                    if ($asgn->shift && $asgn->shift->location) {
                        $locId = $asgn->shift->schedule_location_id;
                        if (is_array(auth()->user()->custom_rates) && isset(auth()->user()->custom_rates[$locId])) {
                            $komisiShift += auth()->user()->custom_rates[$locId];
                        } else {
                            $komisiShift += $asgn->shift->location->shift_rate;
                        }
                    }
                }
            }
            $allowance = 0;
            if (auth()->user()->allowance_type === 'daily') {
                $allowance = auth()->user()->allowance_amount * $filterMonth->daysInMonth;
            } elseif (auth()->user()->allowance_type === 'monthly') {
                $allowance = auth()->user()->allowance_amount;
            }
            $payroll = \App\Models\Payroll::where('user_id', auth()->id())->where('period', $filterMonth->format('Y-m'))->first();
            $motret = $payroll ? $payroll->photographer_fee : 0;
            $lembur = $payroll ? $payroll->overtime_fee : 0;
            $bonus = $payroll ? $payroll->bonus : 0;
            $kasbon = $payroll ? $payroll->deduction : 0;
            
            $crewFinancial = [
                'totalKotor' => $komisiShift + $allowance + $motret + $lembur + $bonus,
                'totalBersih' => ($komisiShift + $allowance + $motret + $lembur + $bonus) - $kasbon,
                'kasbon' => $kasbon,
                'lembur' => $lembur,
                'bonus' => $bonus,
                'tambahan' => $lembur + $motret + $bonus,
                'completed_shifts' => $userAsgn->where('date', '<', Carbon::today()->format('Y-m-d'))->count(),
                'total_shifts' => $userAsgn->count(),
                'payroll' => $payroll,
            ];
        }

        return view('schedules.index', compact(
            'locations', 'users', 'activeUsers', 'assignments', 'crewFinancial', 'pendingSwaps',
            'viewMode', 'date', 'month', 'startDate', 'endDate',
            'todayAssignments', 'todayOpen', 'todayClosed', 'totalShifts',
            'activeLocations', 'dates', 'tab',
            'locationStats', 'statsFilter', 'statsDate', 'statsStart', 'statsEnd'
        ));
    }

    // ── LOCATIONS ──────────────────────────────────────────────

    private function parseMapsUrl($url, &$latitude, &$longitude)
    {
        if (empty($url)) return;
        
        // Handle short URLs (maps.app.goo.gl)
        if (str_contains($url, 'maps.app.goo.gl')) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutRedirecting()->get($url);
                $redirectUrl = $response->header('Location');
                if ($redirectUrl) {
                    $url = $redirectUrl;
                }
            } catch (\Exception $e) {}
        }
        
        // Try to extract exact pin first (!3d and !4d for Place URLs)
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $matches)) {
            $latitude = $matches[1];
            $longitude = $matches[2];
        } 
        // Try to extract destination pin from Direction URLs (!1d (lon) !2d (lat))
        elseif (preg_match('/!1d(-?\d+\.\d+)!2d(-?\d+\.\d+)/', $url, $matches)) {
            $longitude = $matches[1];
            $latitude = $matches[2];
        }
        // Fallback to viewport center (@lat,lng)
        elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $latitude = $matches[1];
            $longitude = $matches[2];
        }
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'shift_rate' => 'required|integer|min:0',
            'maps_url' => 'nullable|url',
            'radius' => 'nullable|integer|min:10',
        ]);

        $data = $request->only('name', 'description', 'shift_rate', 'radius');
        
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        
        if ($request->filled('maps_url')) {
            $this->parseMapsUrl($request->maps_url, $lat, $lng);
        }
        
        $data['latitude'] = $lat;
        $data['longitude'] = $lng;

        ScheduleLocation::create($data);
        return back()->with('success', 'Lokasi berhasil ditambahkan!');
    }

    public function updateLocation(Request $request, ScheduleLocation $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'shift_rate' => 'required|integer|min:0',
            'maps_url' => 'nullable|url',
            'radius' => 'nullable|integer|min:10',
        ]);

        $data = $request->only('name', 'description', 'shift_rate', 'radius');
        
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        
        if ($request->filled('maps_url')) {
            $this->parseMapsUrl($request->maps_url, $lat, $lng);
        }
        
        if ($lat && $lng) {
            $data['latitude'] = $lat;
            $data['longitude'] = $lng;
        }

        $location->update($data);
        return back()->with('success', 'Lokasi berhasil diperbarui!');
    }

    public function destroyLocation(ScheduleLocation $location)
    {
        $location->delete();
        return back()->with('success', 'Lokasi berhasil dihapus!');
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
            'user_id' => 'required|exists:users,id',
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
            ->where('user_id', $request->user_id)
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

        $newAsgn = ScheduleAssignment::create($request->only('schedule_shift_id', 'user_id', 'date', 'notes'));

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
        if (\Carbon\Carbon::parse($assignment->date)->startOfDay()->lt(\Carbon\Carbon::today()) && auth()->user()->role !== 'superadmin') {
            return back()->with('error', 'Tidak dapat mengubah shift yang sudah berlalu.');
        }
        $request->validate([
            'new_user_id' => 'required|exists:users,id',
            'change_notes' => 'nullable|string|max:255',
        ]);

        // Check if new crew already assigned to this shift on this date
        $existing = ScheduleAssignment::where('schedule_shift_id', $assignment->schedule_shift_id)
            ->where('user_id', $request->new_user_id)
            ->where('date', $assignment->date->format('Y-m-d'))
            ->first();

        if ($existing) {
            return back()->with('error', 'Crew pengganti sudah terjadwal di shift ini pada tanggal tersebut!');
        }

        $oldCrewId = $assignment->user_id;

        $assignment->update([
            'original_user_id' => $assignment->original_user_id ?? $oldCrewId,
            'user_id' => $request->new_user_id,
            'changed_by' => auth()->user()->name,
            'notes' => $request->change_notes ?? $assignment->notes,
        ]);

        return back()->with('success', 'Crew berhasil diganti!');
    }

    // ── SWAP REQUEST ───────────────────────────────────────────

    public function swapRequest(Request $request, ScheduleAssignment $assignment)
    {
        if (\Carbon\Carbon::parse($assignment->date)->startOfDay()->lt(\Carbon\Carbon::today())) {
            return back()->with('error', 'Tidak dapat menukar shift yang sudah berlalu.');
        }

        if ($assignment->user_id !== auth()->id() && !in_array(auth()->user()->role, ['superadmin', 'owner'])) {
            return back()->with('error', 'Anda hanya dapat menukar shift milik Anda sendiri.');
        }

        $request->validate([
            'swap_requested_to' => 'required|exists:users,id'
        ]);

        $assignment->update([
            'swap_requested_to' => $request->swap_requested_to,
            'swap_status' => 'pending'
        ]);

        return back()->with('success', 'Permintaan tukar shift berhasil dikirim dan menunggu persetujuan.');
    }

    public function swapApprove(ScheduleAssignment $assignment)
    {
        // Only target user or owner/admin can approve
        if ($assignment->swap_requested_to !== auth()->id() && !in_array(auth()->user()->role, ['superadmin', 'owner'])) {
            return back()->with('error', 'Anda tidak berhak menyetujui permintaan ini.');
        }

        $oldUserId = $assignment->user_id;

        $assignment->update([
            'original_user_id' => $assignment->original_user_id ?? $oldUserId,
            'user_id' => $assignment->swap_requested_to,
            'changed_by' => auth()->user()->name,
            'swap_requested_to' => null,
            'swap_status' => 'approved',
            'notes' => 'Tukar shift disetujui',
        ]);

        return back()->with('success', 'Tukar shift berhasil disetujui!');
    }

    public function swapReject(ScheduleAssignment $assignment)
    {
        // Only target user or owner/admin can reject
        if ($assignment->swap_requested_to !== auth()->id() && !in_array(auth()->user()->role, ['superadmin', 'owner'])) {
            return back()->with('error', 'Anda tidak berhak menolak permintaan ini.');
        }

        $assignment->update([
            'swap_status' => 'rejected'
        ]);

        return back()->with('success', 'Permintaan tukar shift telah ditolak.');
    }

    public function swapDismiss(ScheduleAssignment $assignment)
    {
        if ($assignment->user_id !== auth()->id() && $assignment->original_user_id !== auth()->id()) {
            return back()->with('error', 'Anda tidak berhak melakukan ini.');
        }

        $assignment->update([
            'swap_requested_to' => null,
            'swap_status' => null,
        ]);

        return back()->with('success', 'Notifikasi telah ditutup.');
    }



    public function bulkAssign(Request $request)
    {
        $request->validate([
            'schedule_shift_id' => 'required|exists:schedule_shifts,id',
            'user_id' => 'required|exists:users,id',
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
                ->where('user_id', $request->user_id)
                ->where('date', $dateStr)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            ScheduleAssignment::create([
                'schedule_shift_id' => $request->schedule_shift_id,
                'user_id' => $request->user_id,
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
            'user_id' => 'required|exists:users,id',
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
                ->where('user_id', $request->user_id)
                ->where('date', $dateStr)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            ScheduleAssignment::create([
                'schedule_shift_id' => $request->schedule_shift_id,
                'user_id' => $request->user_id,
                'date' => $dateStr,
            ]);
            $created++;
        }

        return back()->with('success', "{$created} jadwal berhasil ditambahkan!" . ($skipped > 0 ? " ({$skipped} dilewati)" : ''));
    }

    public function updateCustomRates(Request $request, User $user)
    {
        $request->validate([
            'custom_rates' => 'nullable|array',
            'custom_rates.*' => 'nullable|numeric|min:0',
        ]);

        $rates = array_filter($request->custom_rates ?? [], function($val) {
            return !is_null($val) && $val !== '';
        });

        $user->update(['custom_rates' => empty($rates) ? null : $rates]);

        return back()->with('success', 'Harga custom berhasil disimpan untuk ' . $user->name);
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
                   ->with('user');
            }]);
        }]);

        if ($locationId) {
            $locationsQuery->where('id', $locationId);
        }

        $locations = $locationsQuery->get();

        return view('schedules.poster', compact('locations', 'startDate', 'endDate', 'reportDates', 'type'));
    }
}
