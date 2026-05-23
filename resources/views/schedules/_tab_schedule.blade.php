{{-- TAB 5: JADWAL TIM --}}
<div x-show="activeTab === 'jadwal'" x-cloak x-transition.opacity class="space-y-6">
    <div class="flex flex-wrap gap-4 justify-between items-center">
        <div>
            <h2 class="text-xl font-black text-white">Jadwal Tim</h2>
            <p class="text-sm text-slate-400">Atur penugasan shift untuk crew. Bisa close, reopen, atau ganti crew.</p>
        </div>
        <div class="flex gap-2">
            <button @click="$dispatch('open-modal', 'weekly-bulk-assign')" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-purple-500/20 flex items-center gap-2">
                <i class="fas fa-calendar-week"></i> Assign Mingguan
            </button>
            <button @click="$dispatch('open-modal', 'bulk-assign')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                <i class="fas fa-layer-group"></i> Bulk Assign
            </button>
        </div>
    </div>

    {{-- Filter Mode --}}
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4">
        <form action="{{ route('schedules.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="jadwal">
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

    {{-- Quick Add Assignment --}}
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4">
        <h3 class="text-sm font-bold text-white mb-3"><i class="fas fa-plus-circle text-emerald-400 mr-2"></i>Tambah Jadwal Cepat</h3>
        <form action="{{ route('schedules.assignments.store') }}" method="POST" class="flex flex-col md:flex-row gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Shift / Lokasi</label>
                <select name="schedule_shift_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
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
                <select name="schedule_crew_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
                    <option value="">-- Pilih Crew --</option>
                    @foreach($activeCrews as $crew)
                    <option value="{{ $crew->id }}">{{ $crew->name }}{{ $crew->position ? " ($crew->position)" : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500" required>
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

    {{-- Schedule Grid --}}
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-4">
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
            <div class="flex items-center gap-2">
                @php
                    $openCount = $assignments->where('status', 'open')->count();
                    $closedCount = $assignments->where('status', 'close')->count();
                @endphp
                <span class="text-[10px] text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded border border-emerald-500/20"><i class="fas fa-check-circle mr-1"></i>{{ $openCount }} Open</span>
                @if($closedCount > 0)
                <span class="text-[10px] text-red-400 bg-red-500/10 px-2 py-1 rounded border border-red-500/20"><i class="fas fa-times-circle mr-1"></i>{{ $closedCount }} Close</span>
                @endif
                <span class="text-[10px] text-slate-500 bg-slate-900 px-2 py-1 rounded border border-slate-700">{{ $assignments->count() }} total</span>
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
                                        ? 'bg-red-500/5 border-red-500/20' 
                                        : 'bg-slate-800 border-slate-600' }}">
                                    {{-- Status Badge --}}
                                    @if($asgn->isClosed())
                                        <span class="w-6 h-6 rounded-full bg-red-500/20 border border-red-500/30 flex items-center justify-center flex-shrink-0" title="CLOSE">
                                            <i class="fas fa-times text-red-400 text-[10px]"></i>
                                        </span>
                                    @else
                                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center flex-shrink-0" title="OPEN">
                                            <i class="fas fa-check text-emerald-400 text-[10px]"></i>
                                        </span>
                                    @endif

                                    {{-- Crew Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-bold {{ $asgn->isClosed() ? 'text-red-300 line-through' : 'text-white' }}">{{ $asgn->crew->name ?? '?' }}</span>
                                            @if($asgn->wasReplaced())
                                                <span class="text-[9px] text-orange-400 bg-orange-500/10 px-1.5 py-0.5 rounded border border-orange-500/20">
                                                    <i class="fas fa-exchange-alt mr-0.5"></i>ganti dari {{ $asgn->originalCrew->name ?? '?' }}
                                                </span>
                                            @endif
                                            @if($asgn->notes)<span class="text-[9px] text-slate-500">({{ $asgn->notes }})</span>@endif
                                        </div>
                                        @if($asgn->isClosed())
                                            <div class="text-[9px] text-red-400 mt-0.5">
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
                                        @if($asgn->isOpen())
                                            {{-- Close Button --}}
                                            <button type="button" @click="$dispatch('open-modal', 'close-assignment-{{ $asgn->id }}')" class="w-7 h-7 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 flex items-center justify-center border border-red-500/20 transition-colors" title="Close Shift">
                                                <i class="fas fa-ban text-[10px]"></i>
                                            </button>
                                            {{-- Change Button --}}
                                            <button type="button" @click="$dispatch('open-modal', 'change-assignment-{{ $asgn->id }}')" class="w-7 h-7 rounded-lg bg-orange-500/10 text-orange-400 hover:bg-orange-500/20 flex items-center justify-center border border-orange-500/20 transition-colors" title="Ganti Crew">
                                                <i class="fas fa-exchange-alt text-[10px]"></i>
                                            </button>
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
            <div class="overflow-x-auto">
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
                                            ? 'bg-red-500/10 text-red-400 border border-red-500/20 line-through' 
                                            : 'text-white' }}"
                                        style="{{ $ca->isOpen() ? 'background:' . $shift->color . '33; border-left: 2px solid ' . $shift->color : '' }}"
                                        title="{{ $ca->isClosed() ? 'CLOSE - ' . $ca->closed_by . ': ' . $ca->closed_reason : 'OPEN' }}">
                                        @if($ca->isClosed())<i class="fas fa-times text-[7px]"></i>@endif
                                        {{ Str::limit($ca->crew->name ?? '?', 8) }}
                                    </div>
                                    {{-- Action Popover --}}
                                    <div x-show="pop" x-transition.scale.origin.top @click.outside="pop = false" class="absolute z-30 top-full left-1/2 -translate-x-1/2 mt-1 bg-slate-800 border border-slate-600 rounded-xl shadow-2xl p-2 min-w-[140px]" style="display:none;">
                                        <div class="text-[9px] text-slate-400 px-2 py-1 border-b border-slate-700 mb-1 truncate"><b class="text-white">{{ $ca->crew->name ?? '?' }}</b> · {{ \Carbon\Carbon::parse($dt)->translatedFormat('D d/m') }}</div>
                                        @if($ca->isClosed())
                                        <div class="text-[8px] text-red-400 px-2 py-0.5 mb-1"><i class="fas fa-lock mr-0.5"></i>{{ $ca->closed_by }} · {{ $ca->closed_reason }}</div>
                                        @endif
                                        <div class="flex flex-col gap-1">
                                            @if($ca->isOpen())
                                            <button type="button" @click="pop=false; $dispatch('open-modal', 'close-assignment-{{ $ca->id }}')" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[10px] font-bold text-red-400 hover:bg-red-500/10 transition-colors w-full text-left">
                                                <i class="fas fa-ban w-3 text-center"></i> Close Shift
                                            </button>
                                            <button type="button" @click="pop=false; $dispatch('open-modal', 'change-assignment-{{ $ca->id }}')" class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-[10px] font-bold text-orange-400 hover:bg-orange-500/10 transition-colors w-full text-left">
                                                <i class="fas fa-exchange-alt w-3 text-center"></i> Ganti Crew
                                            </button>
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
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @if($cellAsgn->count() < $shift->max_crew)
                                <div class="text-[8px] text-slate-600 text-center">+{{ $shift->max_crew - $cellAsgn->count() }}</div>
                                @endif
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
