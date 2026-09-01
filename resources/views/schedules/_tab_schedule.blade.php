{{-- TAB 5: JADWAL TIM --}}
<div x-show="activeTab === 'jadwal'" x-cloak x-transition.opacity class="space-y-6">
    <div class="flex flex-col sm:flex-row gap-4 justify-between sm:items-center">
        <div>
            <h2 class="text-xl font-black text-white">
                {{ in_array(auth()->user()->role, ['crew', 'kasir']) ? 'Overview Kinerja & Jadwal' : 'Jadwal Tim' }}
            </h2>
            <p class="text-sm text-slate-400">
                {{ in_array(auth()->user()->role, ['crew', 'kasir']) ? 'Pantau performa dan jadwal shift kamu bulan ini.' : 'Atur penugasan shift untuk crew. Bisa close, reopen, atau ganti crew.' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(in_array(auth()->user()->role, ['crew', 'kasir']))
            @else
            <button @click="$dispatch('open-modal', 'poster-modal')" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-blue-500/20 flex items-center gap-2">
                <i class="fas fa-image"></i> Poster
            </button>
            <button @click="$dispatch('open-modal', 'weekly-bulk-assign')" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-purple-500/20 flex items-center gap-2">
                <i class="fas fa-calendar-week"></i> Assign Mingguan
            </button>
            <button @click="$dispatch('open-modal', 'bulk-assign')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                <i class="fas fa-layer-group"></i> Bulk Assign
            </button>
            @endif
        </div>
    </div>

    @if(isset($crewFinancial))
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 mt-6 gap-4" id="performa-pribadi">
        <h3 class="font-black text-white uppercase tracking-wider text-sm flex items-center gap-2">
            <i class="fas fa-user-circle text-orange-400"></i> Performa Pribadi Saya {{ $crewFinancialMonth->translatedFormat('F Y') }}
        </h3>
        <form action="{{ route('schedules.index') }}#performa-pribadi" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="jadwal">
            @if(isset($viewMode)) <input type="hidden" name="view" value="{{ $viewMode }}"> @endif
            @if(isset($date)) <input type="hidden" name="date" value="{{ $date }}"> @endif
            @if(isset($month)) <input type="hidden" name="month" value="{{ $month }}"> @endif
            <div class="relative">
                <select name="crew_month" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 text-white text-sm rounded-xl px-4 py-2 pr-10 focus:outline-none focus:border-blue-500 cursor-pointer appearance-none min-w-[160px] hover:bg-slate-800 transition-colors">
                    @php
                        $currentYear = \Carbon\Carbon::now()->year;
                        $selectedMonth = $crewFinancialMonth->format('Y-m');
                    @endphp
                    @for($i = 1; $i <= 12; $i++)
                        @php
                            $val = $currentYear . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                        @endphp
                        <option value="{{ $val }}" {{ $selectedMonth === $val ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }} {{ $currentYear }}
                        </option>
                    @endfor
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
            </div>
        </form>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">
        <!-- Pendapatan Kotor -->
        <div class="bg-gradient-to-br from-emerald-900/40 to-emerald-800/20 border border-emerald-700/50 rounded-2xl p-4 flex flex-col justify-between">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider mb-1">Gaji Kotor</p>
                    <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['totalKotor'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30 text-emerald-400">
                    <i class="fas fa-money-bill-wave text-xs"></i>
                </div>
            </div>
            <p class="text-[10px] text-emerald-400/80">Termasuk gaji pokok & tambahan</p>
        </div>

        <!-- Potongan Kasbon -->
        <div class="bg-gradient-to-br from-red-900/40 to-red-800/20 border border-red-700/50 rounded-2xl p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[10px] text-red-400 font-bold uppercase tracking-wider mb-1">Potongan</p>
                    <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['kasbon'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center border border-red-500/30 text-red-400">
                    <i class="fas fa-hand-holding-usd text-xs"></i>
                </div>
            </div>
            <p class="text-[10px] text-red-400/80">Total kasbon ditarik</p>
        </div>

        <!-- Project / Motret -->
        <div class="bg-gradient-to-br from-cyan-900/40 to-cyan-800/20 border border-cyan-700/50 rounded-2xl p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[10px] text-cyan-400 font-bold uppercase tracking-wider mb-1">Project</p>
                    <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['project'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-8 h-8 rounded-full bg-cyan-500/20 flex items-center justify-center border border-cyan-500/30 text-cyan-400">
                    <i class="fas fa-camera text-xs"></i>
                </div>
            </div>
            <p class="text-[10px] text-cyan-400/80">Fee motret / project</p>
        </div>

        <!-- Lembur -->
        <div class="bg-gradient-to-br from-orange-900/40 to-orange-800/20 border border-orange-700/50 rounded-2xl p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[10px] text-orange-400 font-bold uppercase tracking-wider mb-1">Lembur</p>
                    <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['lembur'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-8 h-8 rounded-full bg-orange-500/20 flex items-center justify-center border border-orange-500/30 text-orange-400">
                    <i class="fas fa-clock text-xs"></i>
                </div>
            </div>
            <p class="text-[10px] text-orange-400/80">Tambahan lembur</p>
        </div>

        <!-- Bonus -->
        <div class="bg-gradient-to-br from-purple-900/40 to-purple-800/20 border border-purple-700/50 rounded-2xl p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[10px] text-purple-400 font-bold uppercase tracking-wider mb-1">Bonus</p>
                    <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['bonus'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center border border-purple-500/30 text-purple-400">
                    <i class="fas fa-gift text-xs"></i>
                </div>
            </div>
            <p class="text-[10px] text-purple-400/80">Bonus/reward ekstra</p>
        </div>

        <!-- Pendapatan Bersih -->
        <div class="bg-gradient-to-br from-blue-900/40 to-blue-800/20 border border-blue-700/50 rounded-2xl p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-[10px] text-blue-400 font-bold uppercase tracking-wider mb-1">Total Diterima</p>
                    <h3 class="text-lg font-black text-white">Rp {{ number_format($crewFinancial['totalBersih'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center border border-blue-500/30 text-blue-400">
                    <i class="fas fa-wallet text-xs"></i>
                </div>
            </div>
            <p class="text-[10px] text-blue-400/80">Take-home pay bulan ini</p>
            @if(isset($crewFinancial['payroll']))
                <div class="mt-2 text-[10px] font-bold px-2 py-1 rounded {{ $crewFinancial['payroll']->is_finalized ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-500/20 text-slate-400' }}">
                    Status: {{ $crewFinancial['payroll']->is_finalized ? 'Gaji Pas (Selesai)' : 'Sedang Proses' }}
                </div>
            @endif
        </div>

        <!-- Kehadiran -->
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2 relative z-10">
                <div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Shift Selesai</p>
                    <h3 class="text-lg font-black text-white">
                        {{ $crewFinancial['completed_shifts'] }}<span class="text-sm text-slate-500 font-medium">/{{ $crewFinancial['total_shifts'] }}</span>
                    </h3>
                </div>
                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center border border-slate-600 text-slate-300">
                    <i class="fas fa-user-check text-xs"></i>
                </div>
            </div>
            <div class="w-full bg-slate-900 rounded-full h-1.5 mt-3">
                <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $crewFinancial['total_shifts'] > 0 ? ($crewFinancial['completed_shifts'] / $crewFinancial['total_shifts']) * 100 : 0 }}%"></div>
            </div>
            <div class="mt-3 relative z-10">
                <a href="{{ route('schedules.payrolls.export-pdf', ['month' => $crewFinancialMonth->format('Y-m')]) }}" class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 bg-red-500/20 hover:bg-red-500/40 text-red-400 border border-red-500/30 rounded-lg text-[10px] font-bold uppercase tracking-widest transition-colors">
                    <i class="fas fa-file-pdf"></i> Slip Gaji
                </a>
            </div>
        </div>
    </div>
    @if(isset($crewFinancial['payroll']))
        @php
            $p = $crewFinancial['payroll'];
            $hasNotes = $p->notes || $p->deduction_note || $p->overtime_fee_note || $p->bonus_note || $p->photographer_fee_note;
        @endphp
        @if($hasNotes)
        <div class="mt-4 bg-slate-800/80 border border-slate-700 p-4 rounded-xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
            <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-2"><i class="fas fa-clipboard-list text-blue-400"></i> Evaluasi Bulanan</h4>
            
            <div class="space-y-2">
                @if($p->deduction_note)
                <div class="flex items-start gap-2">
                    <i class="fas fa-minus-circle text-red-400 mt-0.5 text-xs"></i>
                    <p class="text-sm text-slate-300"><span class="text-slate-400 font-semibold mr-1">Potongan:</span> {{ $p->deduction_note }}</p>
                </div>
                @endif
                @if($p->photographer_fee_note)
                <div class="flex items-start gap-2">
                    <i class="fas fa-camera text-cyan-400 mt-0.5 text-xs"></i>
                    <p class="text-sm text-slate-300"><span class="text-slate-400 font-semibold mr-1">Project:</span> {{ $p->photographer_fee_note }}</p>
                </div>
                @endif
                @if($p->overtime_fee_note)
                <div class="flex items-start gap-2">
                    <i class="fas fa-clock text-orange-400 mt-0.5 text-xs"></i>
                    <p class="text-sm text-slate-300"><span class="text-slate-400 font-semibold mr-1">Lembur:</span> {{ $p->overtime_fee_note }}</p>
                </div>
                @endif
                @if($p->bonus_note)
                <div class="flex items-start gap-2">
                    <i class="fas fa-gift text-purple-400 mt-0.5 text-xs"></i>
                    <p class="text-sm text-slate-300"><span class="text-slate-400 font-semibold mr-1">Bonus:</span> {{ $p->bonus_note }}</p>
                </div>
                @endif
                @if($p->notes)
                <div class="mt-4 bg-red-900/20 border border-red-700/50 rounded-lg p-3 flex items-start gap-3">
                    <div class="mt-0.5">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-red-400 uppercase tracking-wider mb-1">Perhatian Khusus / Evaluasi</p>
                        <p class="text-sm text-red-200">{{ $p->notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    @endif
    @endif
    
    @if(isset($crewFinancial))
    <!-- Uang Cash Photobox -->
    <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-black text-white uppercase tracking-widest"><i class="fas fa-wallet text-emerald-400 mr-2"></i> Uang Cash Photobox</h3>
            <button x-data @click="$dispatch('open-modal', 'add-crew-cash')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                <i class="fas fa-plus mr-1"></i> Input Transaksi
            </button>
        </div>
        
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-3">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Total Pemasukan</p>
                <h4 class="text-sm font-black text-emerald-400">Rp {{ number_format($crewFinancial['cash_income'], 0, ',', '.') }}</h4>
            </div>
            <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-3">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Total Pengeluaran</p>
                <h4 class="text-sm font-black text-red-400">Rp {{ number_format($crewFinancial['cash_expense'], 0, ',', '.') }}</h4>
            </div>
            <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-3">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Saldo di Tangan</p>
                <h4 class="text-sm font-black text-white">Rp {{ number_format($crewFinancial['cash_balance'], 0, ',', '.') }}</h4>
            </div>
        </div>

        <div class="bg-slate-800/80 border border-slate-700 rounded-xl overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/50 border-b border-slate-700">
                            <th class="p-3 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Tanggal</th>
                            <th class="p-3 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Tipe</th>
                            <th class="p-3 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap w-full">Keterangan</th>
                            <th class="p-3 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap text-right">Jumlah</th>
                            @if(auth()->user()->isOwner())
                            <th class="p-3 text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-700/50">
                        @forelse($crewFinancial['cash_transactions'] as $trx)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="p-3 text-slate-300 whitespace-nowrap">{{ $trx->date->format('d/m/Y') }}</td>
                            <td class="p-3 whitespace-nowrap">
                                @if($trx->type === 'income')
                                    <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded text-[9px] font-bold">Pemasukan</span>
                                @elseif($trx->type === 'expense_operational')
                                    <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded text-[9px] font-bold">Operasional</span>
                                @else
                                    <span class="px-2 py-0.5 bg-red-500/20 text-red-400 border border-red-500/30 rounded text-[9px] font-bold">Pribadi/Kasbon</span>
                                @endif
                            </td>
                            <td class="p-3 text-slate-300">{{ $trx->description }}</td>
                            <td class="p-3 text-right font-bold {{ $trx->type === 'income' ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $trx->type === 'income' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            @if(auth()->user()->isOwner())
                            <td class="p-3 text-center">
                                <form action="{{ route('schedules.crew-cash.destroy', $trx) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus transaksi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isOwner() ? 5 : 4 }}" class="p-4 text-center text-slate-500 text-xs italic">Belum ada transaksi cash bulan ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Input Crew Cash -->
    <div x-data="{ 
        show: false, 
        type: 'income',
        rawAmount: '',
        displayAmount: '',
        formatRupiah(value) {
            let val = value.toString().replace(/\D/g, '');
            if (!val) return '';
            return new Intl.NumberFormat('id-ID').format(val);
        },
        updateAmount(value) {
            let raw = value.toString().replace(/\D/g, '');
            this.rawAmount = raw ? parseInt(raw) : '';
            this.displayAmount = this.formatRupiah(raw);
        },
        resetForm() {
            this.type = 'income';
            this.rawAmount = '';
            this.displayAmount = '';
        }
    }" 
    x-show="show" 
    @open-modal.window="if ($event.detail === 'add-crew-cash') show = true" 
    @close-modal.window="show = false; resetForm()" 
    class="fixed inset-0 z-[99] flex items-center justify-center overflow-y-auto overflow-x-hidden" style="display: none;">
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="show = false; resetForm()"></div>
        <div x-show="show" x-transition.scale.origin.bottom class="relative bg-slate-800 rounded-3xl shadow-2xl border border-slate-700 w-full max-w-md m-4 z-10 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <i class="fas fa-wallet text-emerald-400"></i> Input Uang Cash
                </h3>
                <button @click="show = false; resetForm()" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('schedules.crew-cash.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tipe Transaksi <span class="text-red-400">*</span></label>
                        <select name="type" x-model="type" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all" required>
                            <option value="income">Pemasukan (Terima Tunai)</option>
                            <option value="expense_operational">Dipakai Operasional (Beli Barang/Becak)</option>
                            <option value="expense_personal">Dipakai Pribadi (Kasbon/Makan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jumlah Uang (Rp) <span class="text-red-400">*</span></label>
                        <input type="text" x-model="displayAmount" @input="updateAmount($event.target.value)" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all" required placeholder="Contoh: 150.000">
                        <input type="hidden" name="amount" :value="rawAmount">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Keterangan / Rincian <span class="text-red-400">*</span></label>
                        <input type="text" name="description" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all" required placeholder="Contoh: 6 orang bayar tunai / Beli makan">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tanggal Transaksi <span class="text-red-400">*</span></label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-emerald-500 transition-all" required>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-700/50 flex justify-end gap-3 bg-slate-800/50">
                    <button type="button" @click="show = false" class="px-4 py-2 rounded-xl text-sm font-bold text-slate-300 hover:bg-slate-700 transition-colors">Batal</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/30 transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($pendingSwaps->count() > 0)
    <div class="bg-orange-900/20 border border-orange-700/50 rounded-2xl p-4 mb-6">
        <h3 class="text-sm font-bold text-orange-400 mb-3"><i class="fas fa-bell mr-2"></i>Permintaan Tukar Shift Menunggu Persetujuan</h3>
        <div class="space-y-2">
            @foreach($pendingSwaps as $swap)
            <div class="bg-slate-800/80 border border-slate-700 p-3 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="text-xs text-slate-300">
                        <b class="text-white">{{ $swap->user->name }}</b> mengajukan tukar shift ke <b class="text-white">{{ $swap->swapTargetUser->name ?? '?' }}</b>
                    </div>
                    <div class="text-[10px] text-slate-500 mt-1">
                        {{ $swap->shift->name }} ({{ $swap->shift->location->name }}) - {{ $swap->date->translatedFormat('l, d M Y') }}
                    </div>
                </div>
                <div class="flex gap-2">
                    <form action="{{ route('schedules.assignments.swap_approve', $swap) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold rounded-lg transition-colors"><i class="fas fa-check mr-1"></i> Setujui</button>
                    </form>
                    <form action="{{ route('schedules.assignments.swap_reject', $swap) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-[10px] font-bold rounded-lg transition-colors"><i class="fas fa-times mr-1"></i> Tolak</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filter Mode --}}
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4" id="jadwal-tim">
        <form action="{{ route('schedules.index') }}#jadwal-tim" method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="jadwal">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            @if(isset($crewFinancialMonth)) <input type="hidden" name="crew_month" value="{{ $crewFinancialMonth->format('Y-m') }}"> @endif
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Mode Tampilan</label>
                <div class="flex rounded-xl overflow-hidden border border-slate-700">
                    <button type="submit" name="view" value="daily" class="px-4 py-2 text-xs font-bold transition-colors {{ $viewMode === 'daily' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                        <i class="fas fa-calendar-day mr-1"></i>Harian
                    </button>
                    <button type="submit" name="view" value="weekly" class="px-4 py-2 text-xs font-bold transition-colors border-x border-slate-700 {{ $viewMode === 'weekly' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                        <i class="fas fa-calendar-week mr-1"></i>Mingguan
                    </button>
                    <button type="submit" name="view" value="monthly" class="px-4 py-2 text-xs font-bold transition-colors {{ $viewMode === 'monthly' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                        <i class="fas fa-calendar mr-1"></i>Bulanan
                    </button>
                </div>
            </div>
            @if($viewMode === 'monthly')
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Bulan</label>
                <input type="month" name="month" value="{{ $month }}" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            @else
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
            </div>
            @endif
            <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold rounded-xl transition-colors border border-slate-600">
                <i class="fas fa-filter mr-1"></i>Filter
            </button>
        </form>
    </div>

    @if(auth()->user()->role !== 'crew')
    {{-- Quick Add Assignment --}}
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4">
        <h3 class="text-sm font-bold text-white mb-3"><i class="fas fa-plus-circle text-emerald-400 mr-2"></i>Tambah Jadwal Cepat</h3>
        <form action="{{ route('schedules.assignments.store') }}" method="POST" class="flex flex-col md:flex-row gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Shift / Lokasi</label>
                <select name="schedule_shift_id" id="quick_shift_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    <option value="">-- Pilih Shift --</option>
                    @foreach($locations as $loc)
                    <optgroup label="{{ $loc->name }}">
                        @foreach($loc->shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }}) [Max: {{ $shift->max_crew }}]</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Crew</label>
                <select name="user_id" id="quick_user_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    <option value="">-- Pilih Crew --</option>
                    @foreach($activeUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}{{ $user->role ? " ($user->role)" : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                <input type="date" name="date" id="quick_date" value="{{ $date }}" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Catatan</label>
                <input type="text" name="notes" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 w-full md:w-32" placeholder="Opsional">
            </div>
            <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold rounded-lg transition-colors shadow-lg shadow-emerald-500/20 whitespace-nowrap">
                <i class="fas fa-plus mr-1"></i> Tugaskan
            </button>
        </form>
    </div>
    @endif

    {{-- Schedule Grid --}}
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4 w-full max-w-full overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fas fa-calendar-alt text-blue-400"></i>
                @if($viewMode === 'daily')
                    Jadwal {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                @elseif($viewMode === 'weekly')
                    Minggu {{ $startDate->translatedFormat('d M') }} - {{ $endDate->translatedFormat('d M Y') }}
                @else
                    Bulan {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}
                @endif
            </h3>
            <div class="flex items-center gap-2 flex-wrap">
                @php
                    $openCount = $assignments->where('status', 'open')->count();
                    $selesaiCount = $assignments->where('status', 'close')->filter(fn($a) => !is_null($a->closed_at_time))->count();
                    $closedDbCount = $assignments->where('status', 'close')->filter(fn($a) => is_null($a->closed_at_time))->count();
                    
                    $totalCapacity = 0;
                    foreach($locations as $loc) {
                        foreach($loc->shifts as $s) {
                            $totalCapacity += $s->max_crew * count($dates);
                        }
                    }
                    
                    $emptySlots = max(0, $totalCapacity - $assignments->count());
                    $totalClose = $closedDbCount + $emptySlots;
                    $actualTotal = $openCount + $selesaiCount + $totalClose;
                @endphp
                <span class="text-[10px] text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded border border-emerald-500/20"><i class="fas fa-check-circle mr-1"></i>{{ $openCount }} Open</span>
                <span class="text-[10px] text-blue-400 bg-blue-500/10 px-2 py-1 rounded border border-blue-500/20"><i class="fas fa-clock mr-1"></i>{{ $selesaiCount }} Selesai</span>
                <span class="text-[10px] text-red-400 bg-red-500/10 px-2 py-1 rounded border border-red-500/20"><i class="fas fa-times-circle mr-1"></i>{{ $totalClose }} Close</span>
                <span class="text-[10px] text-slate-500 bg-slate-900 px-2 py-1 rounded border border-slate-700">{{ $actualTotal }} total</span>
            </div>
        </div>

        @if($viewMode === 'daily')
            {{-- Daily View --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($locations as $loc)
                @if($loc->shifts->count() > 0)
                <div class="border border-slate-700 bg-slate-900/30 rounded-xl overflow-hidden">
                    <div class="bg-slate-800 px-4 py-2 border-b border-slate-700 font-bold text-white text-sm flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-yellow-400 text-slate-900 flex items-center justify-center font-black text-[10px]">{{ substr($loc->name,0,1) }}</div>
                        {{ $loc->name }}
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach($loc->shifts as $shift)
                        @php $shiftAsgn = $assignments->where('schedule_shift_id', $shift->id)->filter(fn($a) => $a->date->format('Y-m-d') === $date); @endphp
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-3 h-3 rounded-full" style="background:{{ $shift->color }}"></div>
                                <span class="text-xs font-bold text-slate-300">{{ $shift->name }}</span>
                                <span class="text-[10px] text-slate-500 bg-slate-800 px-1.5 py-0.5 rounded border border-slate-700">{{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }}</span>
                                <span class="text-[10px] {{ $shiftAsgn->count() >= $shift->max_crew ? 'text-emerald-400' : 'text-orange-400' }}">{{ $shiftAsgn->count() }}/{{ $shift->max_crew }}</span>
                            </div>
                            <div class="space-y-2">
                                @forelse($shiftAsgn as $asgn)
                                <div class="flex items-center gap-2 px-3 py-2 rounded-xl border transition-all group
                                    {{ $asgn->isClosed() 
                                        ? ($asgn->closed_at_time ? 'bg-blue-500/5 border-blue-500/20' : 'bg-red-500/5 border-red-500/20')
                                        : 'bg-slate-800 border-slate-600' }}">
                                    {{-- Status Badge --}}
                                    @if($asgn->isClosed())
                                        <span class="w-6 h-6 rounded-full {{ $asgn->closed_at_time ? 'bg-blue-500/20 border-blue-500/30' : 'bg-red-500/20 border-red-500/30' }} border flex items-center justify-center flex-shrink-0" title="CLOSE">
                                            <i class="fas fa-{{ $asgn->closed_at_time ? 'clock text-blue-400' : 'times text-red-400' }} text-[10px]"></i>
                                        </span>
                                    @else
                                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center flex-shrink-0" title="OPEN">
                                            <i class="fas fa-check text-emerald-400 text-[10px]"></i>
                                        </span>
                                    @endif

                                    {{-- Crew Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            @if($asgn->isClosed())
                                                <span class="text-xs font-bold {{ $asgn->closed_at_time ? 'text-blue-300' : 'text-red-300' }}">
                                                    {{ $asgn->user->name ?? '?' }} <span class="text-[9px] opacity-80 font-normal ml-1">({{ $asgn->closed_at_time ? 'Selesai ' . substr($asgn->closed_at_time, 0, 5) : 'Close' }})</span>
                                                </span>
                                            @else
                                                <span class="text-xs font-bold text-white">{{ $asgn->user->name ?? '?' }}</span>
                                            @endif
                                            @if($asgn->wasReplaced())
                                                <span class="text-[9px] text-orange-400 bg-orange-500/10 px-1.5 py-0.5 rounded border border-orange-500/20">
                                                    <i class="fas fa-exchange-alt mr-0.5"></i>ganti dari {{ $asgn->originalUser->name ?? '?' }}
                                                </span>
                                            @endif
                                            @if($asgn->notes)<span class="text-[9px] text-slate-500">({{ $asgn->notes }})</span>@endif
                                        </div>
                                        @if($asgn->isClosed())
                                            <div class="text-[9px] {{ $asgn->closed_at_time ? 'text-blue-400' : 'text-red-400' }} mt-0.5">
                                                <i class="fas fa-lock mr-0.5"></i>Ditutup oleh: <b>{{ $asgn->closed_by }}</b>
                                                @if($asgn->closed_reason) · {{ $asgn->closed_reason }}@endif
                                            </div>
                                        @endif
                                        @if($asgn->changed_by)
                                            <div class="text-[9px] text-orange-400 mt-0.5">
                                                <i class="fas fa-user-edit mr-0.5"></i>Diganti oleh: <b>{{ $asgn->changed_by }}</b>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                        @if(auth()->user()->role !== 'crew')
                                            @if($asgn->isOpen())
                                                {{-- Close Button --}}
                                                <button type="button" @click="$dispatch('open-modal', 'close-assignment-{{ $asgn->id }}')" class="w-7 h-7 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center border border-red-500/20 transition-colors" title="Close Shift">
                                                    <i class="fas fa-ban text-[10px]"></i>
                                                </button>
                                                {{-- Change Button --}}
                                                @if(auth()->user()->role === 'superadmin' || \Carbon\Carbon::parse($asgn->date)->startOfDay()->gte(\Carbon\Carbon::today()))
                                                <button type="button" @click="$dispatch('open-modal', 'change-assignment-{{ $asgn->id }}')" class="w-7 h-7 rounded-lg bg-orange-500/10 text-orange-400 hover:bg-orange-500/20 flex items-center justify-center border border-orange-500/20 transition-colors" title="Ganti Crew">
                                                    <i class="fas fa-exchange-alt text-[10px]"></i>
                                                </button>
                                                @endif
                                            @else
                                                {{-- Reopen Button --}}
                                                <form action="{{ route('schedules.assignments.reopen', $asgn) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 flex items-center justify-center border border-emerald-500/20 transition-colors" title="Buka Kembali">
                                                        <i class="fas fa-undo text-[10px]"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            {{-- Delete Button --}}
                                            <form action="{{ route('schedules.assignments.destroy', $asgn) }}" method="POST" class="m-0">
                                                @csrf @method('DELETE')
                                                <button type="button" onclick="confirmDelete(this.form, 'Hapus penugasan ini?')" class="w-7 h-7 rounded-lg bg-slate-700/50 text-slate-400 hover:bg-red-500/20 hover:text-red-400 flex items-center justify-center border border-slate-600 transition-colors" title="Hapus">
                                                    <i class="fas fa-trash text-[10px]"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($asgn->isOpen())
                                            @if($asgn->swap_status === 'pending')
                                                <span class="px-2 py-1 bg-orange-500/10 text-orange-400 rounded-lg text-[9px] font-bold border border-orange-500/20" title="Menunggu persetujuan tukar shift">
                                                    <i class="fas fa-hourglass-half mr-1"></i> Pending
                                                </span>
                                            @elseif($asgn->swap_status === 'rejected' && $asgn->user_id == auth()->id())
                                                <div class="flex items-center gap-1">
                                                    <span class="px-2 py-1 bg-red-500/10 text-red-400 rounded-lg text-[9px] font-bold border border-red-500/20" title="Permintaan ditolak">
                                                        <i class="fas fa-times mr-1"></i> Ditolak
                                                    </span>
                                                    <form action="{{ route('schedules.assignments.swap_dismiss', $asgn) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="w-7 h-7 rounded-lg bg-slate-700/50 text-slate-400 hover:bg-slate-600 hover:text-white flex items-center justify-center border border-slate-600 transition-colors" title="Tutup Notifikasi">
                                                            <i class="fas fa-check text-[10px]"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif(\Carbon\Carbon::parse($asgn->date)->startOfDay()->gte(\Carbon\Carbon::today()) && ($asgn->user_id == auth()->id() || in_array(auth()->user()->role, ['superadmin', 'owner'])))
                                                <button type="button" @click="$dispatch('open-modal', 'swap-request-{{ $asgn->id }}')" class="px-2 h-7 rounded-lg bg-orange-500/10 text-orange-400 hover:bg-orange-500/20 flex items-center justify-center border border-orange-500/20 transition-colors text-[10px] font-bold" title="Ajukan Tukar Shift">
                                                    <i class="fas fa-exchange-alt mr-1"></i> Tukar
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                @empty
                                <span class="text-xs text-slate-500 italic px-3">Belum ada crew</span>
                                @endforelse
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
            </div>

        @else
            {{-- Weekly / Monthly Grid --}}
            <div class="overflow-x-auto pb-8 min-h-[300px]">
                <table class="w-full text-xs text-slate-300 border-collapse">
                    <thead>
                        <tr class="bg-slate-900/50">
                            <th class="px-3 py-2 text-left font-bold text-slate-400 border border-slate-700 sticky left-0 bg-slate-900 z-10 min-w-[120px]">Shift</th>
                            @foreach($dates as $dt)
                            @php $dtCarbon = \Carbon\Carbon::parse($dt); @endphp
                            <th class="px-2 py-2 text-center font-bold border border-slate-700 min-w-[110px] {{ $dt === now()->format('Y-m-d') ? 'bg-blue-500/10 text-blue-400' : 'text-slate-400' }}">
                                <div>{{ $dtCarbon->translatedFormat('D') }}</div>
                                <div class="text-[10px]">{{ $dtCarbon->format('d/m') }}</div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locations as $loc)
                        @foreach($loc->shifts as $shift)
                        <tr class="hover:bg-slate-700/20">
                            <td class="px-3 py-2 border border-slate-700 sticky left-0 bg-slate-800 z-10">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full" style="background:{{ $shift->color }}"></div>
                                    <div>
                                        <div class="font-bold text-white text-[11px]">{{ $shift->name }}</div>
                                        <div class="text-[9px] text-slate-500">{{ $loc->name }} · {{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }}</div>
                                    </div>
                                </div>
                            </td>
                            @foreach($dates as $dt)
                            @php $cellAsgn = $assignments->where('schedule_shift_id', $shift->id)->filter(fn($a) => $a->date->format('Y-m-d') === $dt); @endphp
                            <td class="px-1 py-1 border border-slate-700/50 align-top {{ $dt === now()->format('Y-m-d') ? 'bg-blue-500/5' : '' }}">
                                @foreach($cellAsgn as $ca)
                                <div x-data="{ pop: false }" class="relative mb-0.5 group">
                                    <div @click="pop = !pop" class="px-1.5 py-1 rounded cursor-pointer text-[9px] font-bold flex items-center gap-0.5 transition-all hover:ring-1 hover:ring-slate-500
                                        {{ $ca->isClosed() 
                                            ? ($ca->closed_at_time ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20')
                                            : 'text-white' }}"
                                        style="{{ $ca->isOpen() ? 'background:' . $shift->color . '33; border-left: 2px solid ' . $shift->color : '' }}"
                                        title="{{ $ca->isClosed() ? 'CLOSE - ' . $ca->closed_by . ': ' . $ca->closed_reason : 'OPEN' }}">
                                        @if($ca->isClosed())
                                            <div class="flex flex-col items-center leading-tight">
                                                <span>{{ Str::limit($ca->user->name ?? '?', 8) }}</span>
                                                <span class="text-[7px] font-normal opacity-80 mt-0.5"><i class="fas fa-{{ $ca->closed_at_time ? 'clock' : 'ban' }} mr-0.5"></i>{{ $ca->closed_at_time ? 'Selesai ' . substr($ca->closed_at_time, 0, 5) : 'Close' }}</span>
                                            </div>
                                        @else
                                            {{ Str::limit($ca->user->name ?? '?', 8) }}
                                        @endif
                                    </div>
                                    {{-- Action Popover --}}
                                    <div x-show="pop" x-transition.scale.origin.top @click.outside="pop = false" class="absolute z-30 top-full left-1/2 -translate-x-1/2 mt-1 bg-slate-800 border border-slate-600 rounded-xl shadow-2xl p-2 min-w-[140px]" style="display:none;">
                                        <div class="text-[9px] text-slate-400 px-2 py-1 border-b border-slate-700 mb-1 truncate"><b class="text-white">{{ $ca->user->name ?? '?' }}</b> · {{ \Carbon\Carbon::parse($dt)->translatedFormat('D d/m') }}</div>
                                        @if($ca->isClosed())
                                        <div class="text-[8px] text-red-400 px-2 py-0.5 mb-1"><i class="fas fa-lock mr-0.5"></i>{{ $ca->closed_by }} · {{ $ca->closed_reason }}</div>
                                        @endif
                                        <div class="flex flex-col gap-1">
                                            @if(auth()->user()->role !== 'crew')
                                                @if($ca->isOpen())
                                                <button type="button" @click="pop=false; $dispatch('open-modal', 'close-assignment-{{ $ca->id }}')" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[10px] font-bold text-red-400 hover:bg-red-500/10 transition-colors w-full text-left">
                                                    <i class="fas fa-ban w-3 text-center"></i> Close Shift
                                                </button>
                                                @if(auth()->user()->role === 'superadmin' || \Carbon\Carbon::parse($dt)->startOfDay()->gte(\Carbon\Carbon::today()))
                                                <button type="button" @click="pop=false; $dispatch('open-modal', 'change-assignment-{{ $ca->id }}')" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[10px] font-bold text-orange-400 hover:bg-orange-500/10 transition-colors w-full text-left">
                                                    <i class="fas fa-exchange-alt w-3 text-center"></i> Ganti Crew
                                                </button>
                                                @endif
                                                @else
                                                <form action="{{ route('schedules.assignments.reopen', $ca) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[10px] font-bold text-emerald-400 hover:bg-emerald-500/10 transition-colors w-full text-left">
                                                        <i class="fas fa-undo w-3 text-center"></i> Buka Kembali
                                                    </button>
                                                </form>
                                                @endif
                                                <form action="{{ route('schedules.assignments.destroy', $ca) }}" method="POST" class="m-0">
                                                    @csrf @method('DELETE')
                                                    <button type="button" onclick="confirmDelete(this.form, 'Hapus penugasan ini?')" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[10px] font-bold text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-colors w-full text-left">
                                                        <i class="fas fa-trash w-3 text-center"></i> Hapus
                                                    </button>
                                                </form>
                                            @endif

                                            @if($ca->isOpen())
                                                @if($ca->swap_status === 'pending')
                                                    <span class="text-[9px] text-orange-400 px-2 italic"><i class="fas fa-hourglass-half mr-1"></i>Menunggu Persetujuan</span>
                                                @elseif($ca->swap_status === 'rejected' && $ca->user_id == auth()->id())
                                                    <div class="flex items-center justify-between px-2 w-full">
                                                        <span class="text-[9px] text-red-400 italic"><i class="fas fa-times mr-1"></i>Ditolak</span>
                                                        <form action="{{ route('schedules.assignments.swap_dismiss', $ca) }}" method="POST" class="m-0">
                                                            @csrf
                                                            <button type="submit" class="text-[9px] text-slate-400 hover:text-white underline" title="Tutup Notifikasi">Dismiss</button>
                                                        </form>
                                                    </div>
                                                @elseif(\Carbon\Carbon::parse($dt)->startOfDay()->gte(\Carbon\Carbon::today()) && ($ca->user_id == auth()->id() || in_array(auth()->user()->role, ['superadmin', 'owner'])))
                                                    <button type="button" @click="pop=false; $dispatch('open-modal', 'swap-request-{{ $ca->id }}')" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[10px] font-bold text-orange-400 hover:bg-orange-500/10 transition-colors w-full text-left">
                                                        <i class="fas fa-random w-3 text-center"></i> Ajukan Tukar Shift
                                                    </button>
                                                @elseif($ca->user_id != auth()->id() && auth()->user()->role === 'crew')
                                                    <span class="text-[9px] text-slate-500 px-2 italic">Aksi tidak tersedia</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @for($i = $cellAsgn->count(); $i < $shift->max_crew; $i++)
                                <div class="px-1.5 py-1 mt-0.5 rounded {{ auth()->user()->role !== 'crew' ? 'cursor-pointer hover:bg-slate-700/50' : 'cursor-default' }} text-[9px] font-bold flex items-center justify-center gap-0.5 transition-all bg-slate-800/50 text-slate-500 border border-dashed border-slate-600" {!! auth()->user()->role !== 'crew' ? '@click="openQuickAssign(\''.$dt.'\', '.$shift->id.')" title="Klik untuk tugaskan crew (Slot kosong)"' : 'title="Slot kosong"' !!}>
                                    <i class="fas fa-plus text-[7px]"></i> Kosong
                                </div>
                                @endfor
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($locations->sum(fn($l) => $l->shifts->count()) === 0)
        <div class="text-center p-10 border border-dashed border-slate-700 rounded-xl">
            <i class="fas fa-calendar-times text-4xl text-slate-600 mb-3"></i>
            <p class="text-slate-400">Buat lokasi dan shift terlebih dahulu.</p>
        </div>
        @endif
    </div>
</div>
