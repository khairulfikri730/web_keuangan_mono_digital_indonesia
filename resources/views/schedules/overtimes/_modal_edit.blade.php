@php
    $rate = (int) ($row->rate_per_hour ?: 0);
@endphp

<div x-data="{
        show: false,
        menit: {{ (int) $row->minutes }},
        nominalRupiah: '{{ number_format($row->amount, 0, ',', '.') }}',
        nominal: '{{ $row->amount }}',
        tarif: {{ $rate }},
        nominalDiketik: false,
        format(angka) { return angka ? new Intl.NumberFormat('id-ID').format(angka) : ''; },
        gantiMenit() {
            if (this.nominalDiketik || !this.tarif) return;
            this.nominal = Math.round(this.menit * this.tarif / 60);
            this.nominalRupiah = this.format(this.nominal);
        }
    }"
    @open-modal.window="if ($event.detail === 'edit-overtime-{{ $row->id }}') show = true"
    @close-modal.window="show = false">
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 z-[160] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>

            <div class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-lg z-10 max-h-[calc(100vh-2.5rem)] overflow-y-auto">
                <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center sticky top-0 bg-slate-800">
                    <h3 class="text-lg font-black text-white"><i class="fas fa-pen text-purple-400 mr-2"></i>Koreksi Lembur</h3>
                    <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
                </div>

                <form action="{{ route('schedules.overtimes.update', $row) }}" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    <div class="text-[10px] text-slate-400 bg-slate-900/50 border border-slate-700 rounded-xl p-3">
                        {{ $row->user->name ?? '-' }} &middot; {{ $row->location->name ?? '-' }} &middot;
                        {{ $row->date->translatedFormat('D, d M Y') }}
                        <div class="mt-1 text-slate-500">
                            Batas jendela <b class="text-slate-300">{{ $row->window_end_at->format('d/m Y H:i') }}</b>,
                            absen keluar <b class="text-slate-300">{{ $row->check_out_at->format('d/m Y H:i') }}</b>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Durasi (menit)</label>
                            <input type="number" name="minutes" min="1" max="1440" x-model.number="menit" @input="gantiMenit()"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500" required>
                            <p class="text-[9px] text-slate-500 mt-1" x-show="tarif">
                                Tarif Rp {{ number_format($rate, 0, ',', '.') }} / jam
                            </p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nominal</label>
                            <div class="flex items-center gap-1">
                                <span class="text-[10px] text-slate-500 font-bold">Rp</span>
                                <input type="text" inputmode="numeric" x-model="nominalRupiah"
                                    @input="nominal = nominalRupiah.replace(/\D/g, ''); nominalDiketik = true; gantiMenit()"
                                    @blur="nominalRupiah = format(nominal)"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-right text-white text-sm focus:outline-none focus:border-purple-500">
                                <input type="hidden" name="amount" :value="nominal">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Status</label>
                        <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500">
                            @foreach([\App\Models\OvertimeRequest::STATUS_PENDING, \App\Models\OvertimeRequest::STATUS_APPROVED, \App\Models\OvertimeRequest::STATUS_REJECTED] as $statusOption)
                                <option value="{{ $statusOption }}" {{ $row->status === $statusOption ? 'selected' : '' }}>
                                    {{ \App\Models\OvertimeRequest::statusLabel($statusOption) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[9px] text-slate-500 mt-1">
                            Status Di-acc menambahkan entri lembur pada Komponen Gaji; status lainnya menghapusnya kembali.
                        </p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Catatan</label>
                        <textarea name="note" rows="3" maxlength="500" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500">{{ $row->note }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="show = false" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-[10px] font-black uppercase tracking-wider transition-colors">
                            <i class="fas fa-floppy-disk mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
