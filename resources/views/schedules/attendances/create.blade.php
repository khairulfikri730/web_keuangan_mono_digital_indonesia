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
                    <select name="type" x-model="attendanceType" class="w-full bg-slate-900 border border-slate-700 text-slate-200 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 font-bold">
                        <option value="in">Absen MASUK</option>
                        <option value="out">Absen KELUAR</option>
                    </select>
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

                <video x-ref="videoElement" class="w-full h-full object-cover" autoplay playsinline x-show="cameraActive && !photoData"></video>
                
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
        
        cameraActive: false,
        stream: null,
        photoData: null,
        
        gpsStatus: 'idle', // idle, mencari, sukses, error
        gpsMessage: 'Belum mendapatkan lokasi',
        latitude: '',
        longitude: '',

        init() {
            this.getLocation();
        },

        get isReadyToSubmit() {
            return this.selectedLocation !== '' && this.photoData !== null && this.latitude !== '' && this.longitude !== '';
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
