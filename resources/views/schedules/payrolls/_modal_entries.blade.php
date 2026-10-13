@php
    $user = $data['user'];
    $payroll = $data['payroll'];
    $componentColors = [
        'photographer_fee' => 'text-yellow-400',
        'overtime_fee' => 'text-orange-400',
        'bonus' => 'text-emerald-400',
        'deduction' => 'text-red-400',
    ];
@endphp

<div x-data="{ show: false }" @open-modal.window="if ($event.detail === 'edit-payroll-{{ $user->id }}') show = true" @close-modal.window="show = false">
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 z-[160] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false"></div>
            <div class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-2xl z-10 overflow-hidden max-h-[calc(100vh-2.5rem)] overflow-y-auto custom-scrollbar">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50 sticky top-0 z-10">
            <h3 class="text-lg font-black text-white"><i class="fas fa-money-check-alt text-emerald-400 mr-2"></i>Edit Komponen Gaji: {{ $user->name }}</h3>
            <button @click="show = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>

        <div class="p-6 space-y-5">
            <div class="bg-yellow-500/10 border border-yellow-500/20 p-3 rounded-xl text-xs text-yellow-400">
                Setiap komponen bisa diisi lebih dari satu kali pada tanggal berbeda. Entri baru ditambahkan tanpa menghapus entri lama; hapus entri tertentu lewat tombol silang di barisnya.
            </div>

            @foreach(\App\Models\PayrollEntry::COMPONENTS as $component => $label)
            @php $entries = $data['entries']->where('component', $component); @endphp
            <div class="bg-slate-900/40 border border-slate-700 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase {{ $componentColors[$component] }}">{{ $label }}</span>
                    <span class="text-xs font-black text-white">Total: Rp {{ number_format($entries->sum('amount'), 0, ',', '.') }}</span>
                </div>

                @if($entries->isNotEmpty())
                <div class="space-y-1 mb-3">
                    @foreach($entries as $entry)
                    <div class="flex items-center justify-between gap-3 bg-slate-900/60 border border-slate-700/50 rounded-lg px-3 py-2">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-slate-200">
                                {{ $entry->date->translatedFormat('d M') }} &middot; Rp {{ number_format($entry->amount, 0, ',', '.') }}
                                @if($entry->source !== 'manual')
                                <span class="text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-purple-500/15 text-purple-300 ml-1">{{ str_replace('_', ' ', $entry->source) }}</span>
                                @endif
                            </p>
                            @if($entry->note)
                            <div x-data="{ buka: false }">
                                <p class="text-[10px] text-slate-400 italic" :class="buka ? 'whitespace-pre-wrap' : 'truncate'">{{ $entry->note }}</p>
                                @if(strlen($entry->note) > 48)
                                <button type="button" @click="buka = !buka" class="text-[9px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-300 inline-flex items-center gap-1 transition-colors">
                                    <span x-text="buka ? 'Tutup' : 'Detail'"></span>
                                    <i class="fas fa-chevron-down transition-transform" :class="buka && 'rotate-180'"></i>
                                </button>
                                @endif
                            </div>
                            @endif
                        </div>
                        <form action="{{ route('schedules.payrolls.entries.destroy', $entry) }}" method="POST" class="m-0 shrink-0" onsubmit="return confirm('Hapus entri ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-500 hover:text-red-400 transition-colors" title="Hapus entri"><i class="fas fa-times text-xs"></i></button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif

                <form action="{{ route('schedules.payrolls.entries.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="period" value="{{ $month }}">
                    <input type="hidden" name="component" value="{{ $component }}">
                    <input type="date" name="date" value="{{ $defaultEntryDate }}" class="sm:col-span-3 bg-slate-900 border border-slate-700 rounded-lg px-2 py-2 text-slate-200 text-xs focus:outline-none focus:border-emerald-500" required>
                    <div x-data="{ tampil: '', raw: '' }" class="sm:col-span-3">
                        <input type="text" inputmode="numeric" x-model="tampil"
                            @input="raw = tampil.replace(/\D/g, ''); tampil = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''"
                            @blur="tampil = raw ? new Intl.NumberFormat('id-ID').format(raw) : ''"
                            placeholder="50.000" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-xs text-right focus:outline-none focus:border-emerald-500" required>
                        <input type="hidden" name="amount" :value="raw">
                    </div>
                    <input type="text" name="note" placeholder="Catatan (opsional)" class="sm:col-span-4 bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-xs italic focus:outline-none focus:border-emerald-500">
                    <button type="submit" class="sm:col-span-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg px-3 py-2 text-[10px] font-black uppercase tracking-wider transition-colors">
                        <i class="fas fa-plus mr-1"></i> Tambah
                    </button>
                </form>
            </div>
            @endforeach

            <form action="{{ route('schedules.payrolls.store') }}" method="POST" class="bg-slate-900/40 border border-slate-700 rounded-2xl p-4">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="period" value="{{ $month }}">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Catatan Keseluruhan (Opsional)</label>
                <textarea name="notes" rows="3" placeholder="Catatan umum yang akan tampil di bawah Total Bersih..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500">{{ $payroll->notes ?? '' }}</textarea>
                <div class="flex justify-end mt-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-xl text-sm font-bold transition-colors">Simpan Catatan</button>
                </div>
            </form>
        </div>
            </div>
        </div>
    </template>
</div>
