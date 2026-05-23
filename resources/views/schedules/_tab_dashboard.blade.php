{{-- TAB 1: DASHBOARD --}}
<div x-show="activeTab === 'dashboard'" x-cloak x-transition.opacity class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        <div class="bg-slate-800/80 border border-slate-700 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-bold mb-1">Total Lokasi</p>
                <h3 class="text-2xl font-black text-white">{{ $activeLocations }}</h3>
                <p class="text-[10px] text-emerald-400">Lokasi Aktif</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center text-purple-400 text-xl border border-purple-500/30"><i class="fas fa-building"></i></div>
        </div>
        <div class="bg-slate-800/80 border border-slate-700 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-bold mb-1">Total Shift</p>
                <h3 class="text-2xl font-black text-white">{{ $totalShifts }}</h3>
                <p class="text-[10px] text-emerald-400">Tipe Shift</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl border border-emerald-500/30"><i class="fas fa-clock"></i></div>
        </div>
        <div class="bg-slate-800/80 border border-slate-700 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-bold mb-1">Total Crew</p>
                <h3 class="text-2xl font-black text-white">{{ $crews->count() }}</h3>
                <p class="text-[10px] text-emerald-400">{{ $activeCrews->count() }} Aktif</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 text-xl border border-blue-500/30"><i class="fas fa-users"></i></div>
        </div>
        <div class="bg-slate-800/80 border border-slate-700 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-bold mb-1">Open Hari Ini</p>
                <h3 class="text-2xl font-black text-emerald-400">{{ $todayOpen }}</h3>
                <p class="text-[10px] text-slate-500">Dari {{ $todayAssignments }} jadwal</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl border border-emerald-500/30"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="bg-slate-800/80 border border-slate-700 p-5 rounded-2xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 font-bold mb-1">Close Hari Ini</p>
                <h3 class="text-2xl font-black {{ $todayClosed > 0 ? 'text-red-400' : 'text-slate-500' }}">{{ $todayClosed }}</h3>
                <p class="text-[10px] text-slate-500">{{ now()->translatedFormat('l, d M') }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center text-red-400 text-xl border border-red-500/30"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Lokasi Aktif --}}
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6">
            <h3 class="text-lg font-black text-white mb-4"><i class="fas fa-map-marker-alt text-blue-400 mr-2"></i>Lokasi Aktif</h3>
            <div class="space-y-3">
                @forelse($locations->where('is_active', true) as $loc)
                <div class="bg-slate-900/50 border border-slate-700/50 p-4 rounded-xl flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-yellow-400 text-slate-900 flex items-center justify-center font-black">{{ substr($loc->name, 0, 1) }}</div>
                        <div>
                            <p class="font-bold text-white">{{ $loc->name }}</p>
                            <p class="text-xs text-slate-400">{{ $loc->shifts->count() }} shift · {{ $loc->description ?? '-' }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold rounded-full uppercase">Aktif</span>
                </div>
                @empty
                <div class="text-center p-6 text-slate-500"><i class="fas fa-info-circle mr-1"></i> Belum ada lokasi</div>
                @endforelse
            </div>
        </div>

        {{-- Jadwal Hari Ini --}}
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6">
            <h3 class="text-lg font-black text-white mb-4"><i class="fas fa-calendar-day text-yellow-400 mr-2"></i>Jadwal Hari Ini</h3>
            @php
                $today = now()->format('Y-m-d');
                $todayAsgn = $assignments->filter(fn($a) => $a->date->format('Y-m-d') === $today);
            @endphp
            <div class="space-y-3">
                @forelse($locations->where('is_active', true) as $loc)
                    @foreach($loc->shifts as $shift)
                        @php $people = $todayAsgn->where('schedule_shift_id', $shift->id); @endphp
                        @if($people->count() > 0 || $shift->max_crew > 0)
                        <div class="bg-slate-900/50 border border-slate-700/50 p-3 rounded-xl">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-3 h-3 rounded-full" style="background:{{ $shift->color }}"></div>
                                <span class="text-xs font-bold text-white">{{ $loc->name }} — {{ $shift->name }}</span>
                                <span class="text-[10px] text-slate-500 bg-slate-800 px-1.5 py-0.5 rounded border border-slate-700">{{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }}</span>
                                <span class="text-[10px] {{ $people->count() >= $shift->max_crew ? 'text-emerald-400' : 'text-orange-400' }}">{{ $people->count() }}/{{ $shift->max_crew }}</span>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($people as $p)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full flex items-center gap-1
                                    {{ $p->isClosed() 
                                        ? 'bg-red-500/10 text-red-400 border border-red-500/20 line-through' 
                                        : 'bg-blue-500/10 text-blue-400 border border-blue-500/20' }}">
                                    @if($p->isClosed())<i class="fas fa-times text-[8px]"></i>@else<i class="fas fa-check text-[8px]"></i>@endif
                                    {{ $p->crew->name ?? '?' }}
                                </span>
                                @endforeach
                                @if($people->count() == 0)
                                <span class="text-[10px] text-red-400 italic">Belum ada crew</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                @empty
                <div class="text-center p-6 text-slate-500">Belum ada data</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══ CREW STATISTICS ═══ --}}
    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6">
        <div class="flex flex-wrap gap-4 justify-between items-center mb-4">
            <h3 class="text-lg font-black text-white"><i class="fas fa-chart-bar text-purple-400 mr-2"></i>Statistik Kerja Crew</h3>
            <form action="{{ route('schedules.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="dashboard">
                <div class="flex rounded-xl overflow-hidden border border-slate-700">
                    <button type="submit" name="stats_filter" value="daily" class="px-3 py-1.5 text-[10px] font-bold transition-colors {{ $statsFilter === 'daily' ? 'bg-purple-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">Harian</button>
                    <button type="submit" name="stats_filter" value="weekly" class="px-3 py-1.5 text-[10px] font-bold transition-colors border-x border-slate-700 {{ $statsFilter === 'weekly' ? 'bg-purple-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">Mingguan</button>
                    <button type="submit" name="stats_filter" value="monthly" class="px-3 py-1.5 text-[10px] font-bold transition-colors {{ $statsFilter === 'monthly' ? 'bg-purple-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white' }}">Bulanan</button>
                </div>
                @if($statsFilter === 'monthly')
                <input type="month" name="stats_month" value="{{ $statsStart->format('Y-m') }}" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-xs focus:outline-none focus:border-purple-500">
                @else
                <input type="date" name="stats_date" value="{{ $statsDate ?? now()->format('Y-m-d') }}" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-white text-xs focus:outline-none focus:border-purple-500">
                @endif
                <button type="submit" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded-lg transition-colors border border-slate-600"><i class="fas fa-filter"></i></button>
            </form>
        </div>

        <div class="text-[10px] text-slate-500 mb-3 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-400"></i>
            Periode: <b class="text-slate-300">{{ $statsStart->translatedFormat('d M Y') }}</b>
            @if($statsFilter !== 'daily')
                s.d <b class="text-slate-300">{{ $statsEnd->translatedFormat('d M Y') }}</b>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-slate-300">
                <thead class="text-[10px] font-bold text-slate-400 uppercase bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Crew</th>
                        <th class="px-4 py-3 text-center">Total Shift</th>
                        <th class="px-4 py-3 text-center"><span class="text-emerald-400"><i class="fas fa-check-circle"></i></span> Open</th>
                        <th class="px-4 py-3 text-center"><span class="text-red-400"><i class="fas fa-times-circle"></i></span> Close</th>
                        <th class="px-4 py-3 text-center"><span class="text-orange-400"><i class="fas fa-exchange-alt"></i></span> Ganti</th>
                        <th class="px-4 py-3 text-center">% Aktif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($crewStats as $cs)
                    <tr class="hover:bg-slate-700/20 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center text-[10px] font-black text-white flex-shrink-0">{{ substr($cs['crew']->name, 0, 1) }}</div>
                                <div>
                                    <span class="font-bold text-white text-xs">{{ $cs['crew']->name }}</span>
                                    @if($cs['crew']->position)
                                    <span class="text-[9px] text-slate-500 block">{{ $cs['crew']->position }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-white">{{ $cs['total_shifts'] }}</td>
                        <td class="px-4 py-3 text-center font-bold text-emerald-400">{{ $cs['open'] }}</td>
                        <td class="px-4 py-3 text-center font-bold {{ $cs['closed'] > 0 ? 'text-red-400' : 'text-slate-600' }}">{{ $cs['closed'] }}</td>
                        <td class="px-4 py-3 text-center font-bold {{ $cs['replaced'] > 0 ? 'text-orange-400' : 'text-slate-600' }}">{{ $cs['replaced'] }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($cs['total_shifts'] > 0)
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 h-2 bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all {{ $cs['pct_active'] >= 80 ? 'bg-emerald-500' : ($cs['pct_active'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $cs['pct_active'] }}%"></div>
                                </div>
                                <span class="text-[10px] font-bold {{ $cs['pct_active'] >= 80 ? 'text-emerald-400' : ($cs['pct_active'] >= 50 ? 'text-yellow-400' : 'text-red-400') }}">{{ $cs['pct_active'] }}%</span>
                            </div>
                            @else
                            <span class="text-[10px] text-slate-600">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada data crew aktif</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
