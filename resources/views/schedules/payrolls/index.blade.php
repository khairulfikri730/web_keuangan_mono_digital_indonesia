@extends('layouts.app')
@section('title', 'Rekap Gaji & Payroll')
@section('page-title', 'Penggajian (Payroll)')
@section('page-subtitle', 'Manajemen gaji bulanan, bonus, lembur, dan potongan crew.')

@section('content')
<div class="space-y-6 text-slate-200" x-data="{ 
    editModal: false, 
    currentUser: null,
    form: { user_id: '', period: '{{ $month }}', photographer_fee: 0, overtime_fee: 0, bonus: 0, deduction: 0, photographer_fee_note: '', overtime_fee_note: '', bonus_note: '', deduction_note: '', notes: '' },
    formatted: { photographer_fee: '0', overtime_fee: '0', bonus: '0', deduction: '0' },
    formatRupiah(value) {
        let val = value.toString().replace(/\D/g, '');
        if (!val) return '';
        return new Intl.NumberFormat('id-ID').format(val);
    },
    updateRaw(field, value) {
        let raw = value.toString().replace(/\D/g, '');
        this.form[field] = raw ? parseInt(raw) : 0;
        this.formatted[field] = this.formatRupiah(raw);
    },
    openEdit(userStr, payrollStr) {
        let u = JSON.parse(userStr);
        let p = payrollStr ? JSON.parse(payrollStr) : null;
        this.currentUser = u;
        this.form.user_id = u.id;
        
        this.form.photographer_fee = p ? p.photographer_fee : 0;
        this.formatted.photographer_fee = p && p.photographer_fee != 0 ? this.formatRupiah(p.photographer_fee) : '0';
        this.form.photographer_fee_note = p ? p.photographer_fee_note : '';
        
        this.form.overtime_fee = p ? p.overtime_fee : 0;
        this.formatted.overtime_fee = p && p.overtime_fee != 0 ? this.formatRupiah(p.overtime_fee) : '0';
        this.form.overtime_fee_note = p ? p.overtime_fee_note : '';
        
        this.form.bonus = p ? p.bonus : 0;
        this.formatted.bonus = p && p.bonus != 0 ? this.formatRupiah(p.bonus) : '0';
        this.form.bonus_note = p ? p.bonus_note : '';
        
        this.form.deduction = p ? p.deduction : 0;
        this.formatted.deduction = p && p.deduction != 0 ? this.formatRupiah(p.deduction) : '0';
        this.form.deduction_note = p ? p.deduction_note : '';
        
        this.form.notes = p ? p.notes : '';
        this.editModal = true;
    },
    selectedUsers: [],
    selectAll: true,
    allUserIds: {{ json_encode(collect($salaryData)->pluck('user.id')->map(fn($id) => (string)$id)->values()) }},
    toggleAll() {
        if (this.selectAll) {
            this.selectedUsers = [...this.allUserIds];
        } else {
            this.selectedUsers = [];
        }
    },
    init() {
        this.selectedUsers = [...this.allUserIds];
        this.$watch('selectedUsers', val => {
            this.selectAll = val.length === this.allUserIds.length && this.allUserIds.length > 0;
        });
    }
}">

    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('schedules.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 hover:text-white rounded-xl text-sm font-bold border border-slate-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Jadwal
        </a>

        <div class="flex items-center gap-2">
            <form method="GET" class="flex gap-2 mr-2">
                <input type="month" name="month" value="{{ $month }}" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:border-emerald-500">
                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-lg transition-colors">Tampilkan</button>
            </form>
            <a :href="`{{ route('schedules.payrolls.export-pdf', ['month' => $month]) }}&users=${selectedUsers.join(',')}`" class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 text-sm font-bold rounded-lg border border-red-500/30 transition-colors" target="_blank" title="Export PDF">
                <i class="fas fa-file-pdf"></i>
            </a>
            <a :href="`{{ route('schedules.payrolls.export-excel', ['month' => $month]) }}&users=${selectedUsers.join(',')}`" class="px-3 py-1.5 bg-green-600/20 hover:bg-green-600/40 text-green-400 text-sm font-bold rounded-lg border border-green-500/30 transition-colors" title="Export Excel">
                <i class="fas fa-file-excel"></i>
            </a>
        </div>
    </div>

    <div class="bg-emerald-500/10 border border-emerald-500/30 p-6 rounded-2xl flex items-center justify-between">
        <div>
            <p class="text-sm text-emerald-400 font-bold mb-1">Total Pengeluaran Gaji Bulan Ini</p>
            <h2 class="text-3xl font-black text-white">Rp {{ number_format($totalSistem, 0, ',', '.') }}</h2>
            <p class="text-xs text-slate-400 mt-1">Periode: {{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</p>
        </div>
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-3xl">
            <i class="fas fa-wallet"></i>
        </div>
    </div>

    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300">
                <thead class="text-[10px] font-bold text-slate-400 uppercase bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll" class="w-4 h-4 text-emerald-500 bg-slate-900 border-slate-700 rounded focus:ring-emerald-500 focus:ring-2">
                        </th>
                        <th class="px-4 py-3 text-left">Crew</th>
                        <th class="px-4 py-3 text-right">Tunjangan/Gaji Pokok</th>
                        <th class="px-4 py-3 text-right">Komisi Shift</th>
                        <th class="px-4 py-3 text-right">Motret / Project</th>
                        <th class="px-4 py-3 text-right">Lembur</th>
                        <th class="px-4 py-3 text-right">Bonus</th>
                        <th class="px-4 py-3 text-right text-red-400">Potongan/Kasbon</th>
                        <th class="px-4 py-3 text-right text-emerald-400 font-black">TOTAL BERSIH</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($salaryData as $data)
                    <tr class="hover:bg-slate-700/20 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" x-model="selectedUsers" value="{{ $data['user']->id }}" class="w-4 h-4 text-emerald-500 bg-slate-900 border-slate-700 rounded focus:ring-emerald-500 focus:ring-2">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-xs font-black text-white flex-shrink-0">{{ substr($data['user']->name, 0, 1) }}</div>
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-white">{{ $data['user']->name }}</span>
                                    @if($data['user']->allowance_type !== 'none')
                                    <span class="text-[9px] px-1.5 py-0.5 bg-blue-500/20 text-blue-400 rounded-full ml-1">{{ $data['user']->allowance_type == 'daily' ? 'Harian' : 'Bulanan' }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-blue-400">Rp {{ number_format($data['allowance'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-300">
                            Rp {{ number_format($data['komisi_shift'], 0, ',', '.') }}
                            <div class="text-[9px] font-normal text-slate-500">{{ $data['total_shifts'] }} shift</div>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-yellow-400 align-top">
                            Rp {{ number_format($data['motret'], 0, ',', '.') }}
                            @if($data['payroll'] && $data['payroll']->photographer_fee_note)
                            <div class="text-[9px] font-normal text-slate-400 mt-1 ml-auto max-w-[120px] leading-tight break-words italic">{{ $data['payroll']->photographer_fee_note }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-orange-400 align-top">
                            Rp {{ number_format($data['lembur'], 0, ',', '.') }}
                            @if($data['payroll'] && $data['payroll']->overtime_fee_note)
                            <div class="text-[9px] font-normal text-slate-400 mt-1 ml-auto max-w-[120px] leading-tight break-words italic">{{ $data['payroll']->overtime_fee_note }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-400 align-top">
                            Rp {{ number_format($data['bonus'], 0, ',', '.') }}
                            @if($data['payroll'] && $data['payroll']->bonus_note)
                            <div class="text-[9px] font-normal text-slate-400 mt-1 ml-auto max-w-[120px] leading-tight break-words italic">{{ $data['payroll']->bonus_note }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-red-400 align-top">
                            - Rp {{ number_format($data['kasbon'], 0, ',', '.') }}
                            @if($data['payroll'] && $data['payroll']->deduction_note)
                            <div class="text-[9px] font-normal text-slate-400 mt-1 ml-auto max-w-[120px] leading-tight break-words italic">{{ $data['payroll']->deduction_note }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-black text-emerald-400 text-base bg-emerald-500/5 align-top">
                            Rp {{ number_format($data['total_bersih'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-400 align-top">
                            {{ $data['payroll'] && $data['payroll']->notes ? $data['payroll']->notes : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center align-top">
                            @if($data['payroll'])
                                <form action="{{ route('schedules.payrolls.toggle-status', $data['payroll']->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $data['payroll']->is_finalized ? 'bg-emerald-500' : 'bg-slate-600' }}" title="{{ $data['payroll']->is_finalized ? 'Gaji Pas' : 'Sedang Proses' }}">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $data['payroll']->is_finalized ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                    <div class="text-[9px] mt-1 font-bold {{ $data['payroll']->is_finalized ? 'text-emerald-400' : 'text-slate-400' }}">
                                        {{ $data['payroll']->is_finalized ? 'Selesai' : 'Proses' }}
                                    </div>
                                </form>
                            @else
                                <span class="text-[9px] text-slate-500 italic">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center align-top">
                            <button @click="openEdit('{{ $data['user']->toJson() }}', '{{ $data['payroll'] ? $data['payroll']->toJson() : '' }}')" class="w-8 h-8 rounded-full bg-slate-700 hover:bg-emerald-500 hover:text-white transition-colors text-slate-400 flex items-center justify-center inline-flex">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="px-4 py-8 text-center text-slate-500">Belum ada data crew.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL INPUT --}}
    <div x-show="editModal" class="fixed inset-0 z-[99] flex items-center justify-center" style="display:none;">
        <div x-show="editModal" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="editModal = false"></div>
        <div x-show="editModal" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-lg m-4 z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-black text-white"><i class="fas fa-money-check-alt text-emerald-400 mr-2"></i>Edit Komponen Gaji: <span x-text="currentUser?.name"></span></h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            <form action="{{ route('schedules.payrolls.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" x-model="form.user_id">
                <input type="hidden" name="period" x-model="form.period">
                
                <input type="hidden" name="photographer_fee" x-model="form.photographer_fee">
                <input type="hidden" name="overtime_fee" x-model="form.overtime_fee">
                <input type="hidden" name="bonus" x-model="form.bonus">
                <input type="hidden" name="deduction" x-model="form.deduction">
                
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="bg-yellow-500/10 border border-yellow-500/20 p-3 rounded-xl text-xs text-yellow-400 mb-4">
                        Masukkan nominal (Rp) tanpa titik/koma. Tunjangan & Komisi Shift dihitung otomatis oleh sistem.
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase">Total Motret/Project</label>
                            <input type="text" x-model="formatted.photographer_fee" @input="updateRaw('photographer_fee', $event.target.value)" @focus="if(formatted.photographer_fee === '0') formatted.photographer_fee = ''" @blur="if(formatted.photographer_fee === '') formatted.photographer_fee = '0'" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500">
                            <input type="text" name="photographer_fee_note" x-model="form.photographer_fee_note" placeholder="Catatan (Opsional)" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2 text-slate-300 text-xs focus:outline-none focus:border-emerald-500 italic">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase">Total Lembur</label>
                            <input type="text" x-model="formatted.overtime_fee" @input="updateRaw('overtime_fee', $event.target.value)" @focus="if(formatted.overtime_fee === '0') formatted.overtime_fee = ''" @blur="if(formatted.overtime_fee === '') formatted.overtime_fee = '0'" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500">
                            <input type="text" name="overtime_fee_note" x-model="form.overtime_fee_note" placeholder="Catatan (Opsional)" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2 text-slate-300 text-xs focus:outline-none focus:border-emerald-500 italic">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase">Bonus Tambahan</label>
                            <input type="text" x-model="formatted.bonus" @input="updateRaw('bonus', $event.target.value)" @focus="if(formatted.bonus === '0') formatted.bonus = ''" @blur="if(formatted.bonus === '') formatted.bonus = '0'" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500">
                            <input type="text" name="bonus_note" x-model="form.bonus_note" placeholder="Catatan (Opsional)" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2 text-slate-300 text-xs focus:outline-none focus:border-emerald-500 italic">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase text-red-400">Potongan / Kasbon</label>
                            <input type="text" x-model="formatted.deduction" @input="updateRaw('deduction', $event.target.value)" @focus="if(formatted.deduction === '0') formatted.deduction = ''" @blur="if(formatted.deduction === '') formatted.deduction = '0'" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-red-500">
                            <input type="text" name="deduction_note" x-model="form.deduction_note" placeholder="Catatan (Opsional)" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-2 text-slate-300 text-xs focus:outline-none focus:border-red-500 italic">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Catatan Keseluruhan (Opsional)</label>
                        <textarea name="notes" x-model="form.notes" rows="2" placeholder="Catatan umum yang akan tampil di bawah Total Bersih..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700">Batal</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30">Simpan Gaji</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
