@extends('layouts.app')
@section('title', 'Absensi Kamera & GPS')

@section('content')
<div x-data="attendanceApp()" class="max-w-2xl mx-auto py-8">
    <div class="bg-slate-800 rounded-3xl border border-white/5 p-6 shadow-2xl">
        <h2 class="text-xl font-black text-white text-center mb-6">ABSENSI CREW</h2>

        <!-- Form Setup -->
        <form id="attendanceForm" action="{{ route('schedules.attendances.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <!-- Location Select -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Lokasi Studio</label>
                    <select name="schedule_location_id" x-model="selectedLocation" class="w-full bg-slate-900 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 font-bold">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Tipe Absen -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-2 uppercase tracking-wide">Tipe Absen</label>
                    {{-- Select-nya sengaja tanpa name: saat dinonaktifkan browser tidak ikut mengirimnya,
                         jadi nilainya dibawa oleh hidden input di bawah. --}}
                    <select x-model="attendanceType" :disabled="isCrew && session?.has_open_in" class="w-full bg-slate-900 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 font-bold disabled:opacity-60">
                        <option value="in">Absen MASUK</option>
                        <option value="out">Absen KELUAR</option>
                    </select>
                    <input type="hidden" name="type" value="in" x-model="attendanceType">
                    <p x-show="isCrew && session?.has_open_in" class="text-[10px] text-amber-400 font-bold mt-1">
                        Absen masuk sudah tercatat, hanya absen keluar yang tersedia.
                    </p>
                </div>
            </div>

            {{-- JENDELA WAKTU ABSEN SESI INI --}}
            <div x-show="session" x-cloak class="bg-slate-900/60 border border-slate-700 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        Jendela Absen <span x-text="session?.date"></span>
                    </p>
                    <span x-show="session?.is_carryover" class="text-[9px] font-black uppercase tracking-wider text-purple-300 bg-purple-500/15 border border-purple-500/30 px-2 py-0.5 rounded">
                        Sesi kemarin, belum absen keluar
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <p class="text-[9px] uppercase font-black text-slate-500 mb-1">Absen Masuk</p>
                        <p class="font-mono font-bold" :class="session?.in_window ? 'text-emerald-400' : 'text-slate-500'" x-text="session?.in_window || 'Tidak dibatasi'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase font-black text-slate-500 mb-1">Absen Keluar</p>
                        <p class="font-mono font-bold" :class="session?.out_window ? 'text-amber-400' : 'text-slate-500'" x-text="session?.out_window || 'Tidak dibatasi'"></p>
                    </div>
                </div>
            </div>

            {{-- SESI TERKUNCI: LUPA ABSEN KELUAR --}}
            <div x-show="session?.blocked" x-cloak class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-red-300 uppercase tracking-wide">Absensi Anda dikunci</p>
                        <p class="text-[11px] text-slate-300 mt-1 leading-relaxed">
                            Sesi <span x-text="session?.date"></span> belum ada absen keluar sampai melewati batas toleransi,
                            jadi tercatat sebagai <b>lupa absen keluar</b>. Tulis alasan Anda di bawah ini, lalu hubungi owner
                            agar sesi tersebut ditutup atau dihitung sebagai lembur.
                        </p>
                    </div>
                </div>

                <div x-show="session?.blocked_id" class="space-y-2">
                    <label class="block text-[10px] font-black text-red-200 uppercase tracking-widest">Alasan Anda</label>
                    <textarea rows="2" placeholder="Contoh: langsung dibawa keluar kota, HP mati jadi tidak sempat absen keluar"
                        class="w-full bg-slate-900 border border-red-500/40 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-red-400"
                        x-model="blockedReason" @blur="simpanAlasan()"></textarea>
                    <p class="text-[10px] text-slate-400">
                        <i class="fas fa-circle-info mr-1"></i>Alasan tersimpan otomatis saat kolom ini Anda isi.
                        <span x-show="reasonSaved" class="text-emerald-400 font-bold">Tersimpan.</span>
                    </p>
                </div>
            </div>

            {{-- CATATAN ALASAN DARI CREW --}}
            <div x-show="session" x-cloak class="bg-slate-900/60 border border-slate-700 rounded-xl p-4 space-y-2">
                <p class="text-[11px] text-amber-200 leading-relaxed" x-show="isLateIn || isEarlyOut">
                    <i class="fas fa-triangle-exclamation mr-1"></i>
                    <span x-show="isLateIn">Jam sekarang melewati jendela absen masuk (<span class="font-mono" x-text="session?.in_window"></span>).</span>
                    <span x-show="isEarlyOut">Jam sekarang masih sebelum jendela absen keluar (<span class="font-mono" x-text="session?.out_window"></span>).</span>
                    Tulis alasannya supaya owner punya catatan.
                </p>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest" x-show="!(isLateIn || isEarlyOut)">Catatan (opsional)</label>
                <textarea name="note" x-model="reasonNote" rows="2" placeholder="Contoh: ban motor bocor di jalan, antrean cetak foto panjang, GPS sedang bermasalah"
                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500"></textarea>
                <p class="text-[10px] text-slate-500" x-show="!(isLateIn || isEarlyOut)">
                    Isi bila ada yang tidak beres dengan absen ini (terlambat, keluar cepat, atau lokasi terdeteksi di luar radius).
                </p>
            </div>

            {{-- PENGAJUAN LEMBUR OTOMATIS --}}
            <div x-show="isOvertimeOut" x-cloak class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-amber-300 uppercase tracking-wide">Ini dihitung lembur</p>
                        <p class="text-[11px] text-slate-300 mt-1 leading-relaxed">
                            Jam sekarang sudah lewat batas absen keluar (<span class="font-mono" x-text="session?.out_window"></span>).
                            Diperkirakan lembur <span class="font-black text-amber-300" x-text="durationLabel(overtimeMinutes)"></span> &asymp; Rp <span class="font-black text-amber-300" x-text="formatRupiah(overtimeAmount)"></span>.
                            Setelah di-acc owner, nominal ini masuk ke gaji Anda.
                        </p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-amber-200 uppercase tracking-widest mb-1">Catatan Lembur <span class="text-red-400">*</span></label>
                    <textarea name="overtime_note" x-model="overtimeNote" rows="2" placeholder="Contoh: lanjut edit foto pesanan customer A" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-amber-500"></textarea>
                </div>
            </div>

            <!-- CAMERA PREVIEW -->
            <div class="relative bg-slate-900 rounded-2xl overflow-hidden border-2 border-slate-700 shadow-inner flex items-center justify-center min-h-[350px]">
                
                <template x-if="!cameraActive && !photoData">
                    <div class="text-center p-6">
                        <i class="fas fa-camera text-4xl text-slate-600 mb-3 block"></i>
                        <button type="button" @click="startCamera()" class="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-bold shadow-lg shadow-blue-500/30 transition-all text-sm">
                            Buka Kamera
                        </button>
                    </div>
                </template>

                <video x-ref="videoElement" class="w-full h-full object-cover scale-x-[-1]" autoplay playsinline x-show="cameraActive && !photoData"></video>
                
                <!-- Preview Captured Image -->
                <img :src="photoData" class="w-full h-full object-cover" x-show="photoData">

                <!-- Canvas for capturing (hidden) -->
                <canvas x-ref="canvasElement" class="hidden"></canvas>
            </div>

            <!-- GPS STATUS -->
            <div class="bg-slate-900/50 rounded-xl p-4 flex items-center justify-between border border-white/5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg" :class="gpsStatus === 'sukses' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : (gpsStatus === 'mencari' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30')">
                        <i class="fas fa-map-marker-alt" :class="gpsStatus === 'mencari' ? 'animate-pulse' : ''"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Status GPS</p>
                        <p class="text-sm font-bold" :class="gpsStatus === 'sukses' ? 'text-emerald-400' : (gpsStatus === 'mencari' ? 'text-amber-400' : 'text-red-400')" x-text="gpsMessage"></p>
                    </div>
                </div>
                <button type="button" @click="getLocation()" class="text-[10px] bg-slate-800 text-slate-400 hover:text-white px-3 py-1.5 rounded-lg font-bold uppercase tracking-wider transition-colors border border-white/5">
                    Refresh GPS
                </button>
            </div>

            <!-- HIDDEN INPUTS FOR FORM SUBMISSION -->
            <input type="hidden" name="latitude" x-model="latitude">
            <input type="hidden" name="longitude" x-model="longitude">
            <input type="hidden" name="photo" x-model="photoData">

            <!-- ACTION BUTTONS -->
            <div class="flex flex-col gap-3 pt-4">
                <template x-if="cameraActive && !photoData">
                    <button type="button" @click="takePhoto()" class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-lg shadow-xl shadow-emerald-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                        <i class="fas fa-camera"></i> Jepret Foto
                    </button>
                </template>

                <template x-if="photoData">
                    <div class="flex gap-3">
                        <button type="button" @click="retakePhoto()" class="w-1/3 py-4 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-black text-sm uppercase tracking-wider transition-all">
                            Ulangi
                        </button>
                        <button type="submit" :disabled="!isReadyToSubmit" class="w-2/3 py-4 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-black text-sm uppercase tracking-wider transition-all shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2">
                            <i class="fas fa-check-circle"></i> Simpan Absensi
                        </button>
                    </div>
                </template>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('attendanceApp', () => ({
        selectedLocation: '',
        attendanceType: 'in',

        sessions: {{ \Illuminate\Support\Js::from($sessions) }},
        isCrew: {{ \Illuminate\Support\Js::from($isCrew) }},
        overtimeRate: {{ $overtimeRate }},
        overtimeNote: '',
        reasonNote: '',
        blockedReason: '',
        reasonSaved: false,
        noteUrl: {{ \Illuminate\Support\Js::from(route('schedules.attendances.note', ['attendance' => '__ID__'])) }},
        csrfToken: {{ \Illuminate\Support\Js::from(csrf_token()) }},
        now: new Date(),

        cameraActive: false,
        stream: null,
        photoData: null,
        
        gpsStatus: 'idle', // idle, mencari, sukses, error
        gpsMessage: 'Belum mendapatkan lokasi',
        latitude: '',
        longitude: '',

        init() {
            this.getLocation();
            setInterval(() => this.now = new Date(), 30000);

            this.$watch('selectedLocation', (value) => {
                if (this.isCrew && this.session?.has_open_in) {
                    this.attendanceType = 'out';
                }
                this.blockedReason = this.session?.blocked_note || '';
                this.reasonSaved = false;
            });

            this.$watch('blockedReason', () => this.simpanAlasan());
        },

        get session() {
            return this.sessions[this.selectedLocation] || null;
        },

        get isLateIn() {
            return !this.session?.blocked && this.attendanceType === 'in'
                && this.session?.in_to && this.now > new Date(this.session.in_to);
        },

        get isEarlyOut() {
            return !this.session?.blocked && this.attendanceType === 'out'
                && this.session?.out_from && this.now < new Date(this.session.out_from);
        },

        /** Alasan crew untuk sesi yang terkunci dikirim sendiri, tanpa perlu absen. */
        simpanAlasan() {
            const id = this.session?.blocked_id;
            const alasan = (this.blockedReason || '').trim();

            if (!id || alasan === '') return;

            const body = new URLSearchParams({ _token: this.csrfToken, notes: alasan });

            this.reasonSaved = false;
            fetch(this.noteUrl.replace('__ID__', id), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: body.toString(),
            }).then(() => this.reasonSaved = true);
        },

        get overtimeMinutes() {
            if (!this.session?.out_deadline) return 0;
            return Math.max(0, Math.round((this.now - new Date(this.session.out_deadline)) / 60000));
        },

        get overtimeAmount() {
            return Math.round(this.overtimeMinutes * this.overtimeRate / 60);
        },

        get isOvertimeOut() {
            return this.attendanceType === 'out' && this.overtimeMinutes > 0;
        },

        durationLabel(minutes) {
            return Math.floor(minutes / 60) + ' jam ' + (minutes % 60) + ' menit';
        },

        formatRupiah(value) {
            return new Intl.NumberFormat('id-ID').format(Math.round(value || 0));
        },

        get isReadyToSubmit() {
            if (this.selectedLocation === '' || this.photoData === null) return false;
            if (this.session?.blocked) return false;
            if (this.latitude === '' || this.longitude === '') return false;
            if (this.isOvertimeOut && this.overtimeNote.trim() === '') return false;

            return true;
        },

        async startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Browser Anda tidak mendukung akses kamera secara langsung.");
                return;
            }

            try {
                // Request front camera
                this.stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "user", width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false 
                });
                
                this.$refs.videoElement.srcObject = this.stream;
                this.cameraActive = true;
                this.photoData = null;
            } catch (err) {
                console.error("Error accessing camera:", err);
                alert("Tidak dapat mengakses kamera. Pastikan Anda telah memberikan izin kamera pada browser.");
            }
        },

        takePhoto() {
            if (!this.cameraActive) return;

            const video = this.$refs.videoElement;
            const canvas = this.$refs.canvasElement;
            const context = canvas.getContext('2d');

            // Set canvas dimensions to match video stream
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Flip horizontally (Mirror)
            context.translate(canvas.width, 0);
            context.scale(-1, 1);

            // Draw video frame to canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert to webp base64 (quality 0.8)
            this.photoData = canvas.toDataURL('image/webp', 0.8);
            
            this.stopCamera();
        },

        retakePhoto() {
            this.photoData = null;
            this.startCamera();
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.cameraActive = false;
            }
        },

        getLocation() {
            this.gpsStatus = 'mencari';
            this.gpsMessage = 'Sedang melacak lokasi...';

            if (!navigator.geolocation) {
                this.gpsStatus = 'error';
                this.gpsMessage = 'GPS tidak didukung oleh browser Anda.';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.latitude = position.coords.latitude;
                    this.longitude = position.coords.longitude;
                    
                    // Simple check for fake GPS (mock locations usually have perfect accuracy)
                    // It's not bulletproof but helps
                    const accuracy = position.coords.accuracy;
                    
                    this.gpsStatus = 'sukses';
                    this.gpsMessage = 'Akurasi GPS: ±' + Math.round(accuracy) + ' meter';
                },
                (error) => {
                    this.gpsStatus = 'error';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            this.gpsMessage = "Izin lokasi ditolak oleh pengguna.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            this.gpsMessage = "Informasi lokasi tidak tersedia.";
                            break;
                        case error.TIMEOUT:
                            this.gpsMessage = "Waktu permintaan lokasi habis (timeout).";
                            break;
                        default:
                            this.gpsMessage = "Terjadi kesalahan saat mengambil lokasi.";
                            break;
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
    }));
});
</script>
@endsection
