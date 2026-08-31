<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\ScheduleLocation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['user', 'location'])
            ->orderBy('created_at', 'desc');
            
        if (in_array(auth()->user()->role, ['crew', 'kasir'])) {
            $query->where('user_id', auth()->id());
        }
        
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        
        if ($request->filled('location_id')) {
            $query->where('schedule_location_id', $request->location_id);
        }
            
        $attendances = $query->paginate(20)->withQueryString();
        $locations = ScheduleLocation::active()->get();
            
        return view('schedules.attendances.index', compact('attendances', 'locations'));
    }

    public function create()
    {
        // Get today's assigned locations for this user
        $todayAssignments = \App\Models\ScheduleAssignment::with('shift.location')
            ->where('user_id', auth()->id())
            ->where('date', now()->format('Y-m-d'))
            ->get();
            
        if (in_array(auth()->user()->role, ['crew', 'kasir']) && $todayAssignments->isEmpty()) {
            return redirect()->route('schedules.attendances.index')
                ->with('error', 'Anda tidak memiliki jadwal shift hari ini. Absensi hanya dapat dilakukan jika Anda memiliki shift aktif.');
        }
        
        if (in_array(auth()->user()->role, ['crew', 'kasir'])) {
            // Only allow locations they are assigned to today
            $locationIds = $todayAssignments->pluck('shift.schedule_location_id')->unique();
            $locations = ScheduleLocation::whereIn('id', $locationIds)->get();
        } else {
            // Admin can choose any location for testing
            $locations = ScheduleLocation::active()->get();
        }
        
        return view('schedules.attendances.create', compact('locations'));
    }

    public function destroy(Attendance $attendance)
    {
        if (!in_array(auth()->user()->role, ['superadmin', 'owner'])) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus data absensi.');
        }
        
        if ($attendance->photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($attendance->photo_path);
        }
        $attendance->delete();
        
        return back()->with('success', 'Data absensi berhasil dihapus.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_location_id' => 'required|exists:schedule_locations,id',
            'type' => 'required|in:in,out',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // base64 webp
        ]);

        $location = ScheduleLocation::findOrFail($request->schedule_location_id);
        
        // Haversine formula to calculate distance
        $distance = null;
        $status = 'out_of_radius';
        
        if ($location->latitude && $location->longitude) {
            $earthRadius = 6371000; // in meters
            
            $latFrom = deg2rad($request->latitude);
            $lonFrom = deg2rad($request->longitude);
            $latTo = deg2rad($location->latitude);
            $lonTo = deg2rad($location->longitude);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                
            $distance = $angle * $earthRadius;
            
            if ($distance <= ($location->radius ?? 100)) {
                $status = 'in_radius';
            }
        } else {
            // If location has no lat/long, we just approve it (or you can reject it)
            $status = 'in_radius';
        }

        // Handle photo upload
        $imageParts = explode(";base64,", $request->photo);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1] ?? 'webp';
        $imageBase64 = base64_decode($imageParts[1]);
        
        $fileName = 'attendance/' . auth()->id() . '/' . Str::uuid() . '.' . $imageType;
        Storage::disk('public')->put($fileName, $imageBase64);

        Attendance::create([
            'user_id' => auth()->id(),
            'schedule_location_id' => $location->id,
            'type' => $request->type,
            'photo_path' => $fileName,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'distance' => $distance,
            'status' => $status,
        ]);

        $msg = $status === 'in_radius' ? 'Berhasil absen!' : 'Berhasil absen, tapi Anda terdeteksi di luar radius lokasi.';
        
        return redirect()->route('schedules.attendances.index')->with('success', $msg);
    }
}
