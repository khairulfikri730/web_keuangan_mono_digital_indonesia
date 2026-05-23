@props([
    'period' => null,
    'dateFrom' => null,
    'dateTo' => null,
])

@php
    $requestPeriod = request('period', 'today');
    $dateFromVal = request('date_from', $dateFrom ?? now()->format('Y-m-d'));
    $dateToVal = request('date_to', $dateTo ?? now()->format('Y-m-d'));

    if (is_array($dateFromVal) || empty($dateFromVal)) {
        $dateFromVal = now()->format('Y-m-d');
    }
    if (is_array($dateToVal) || empty($dateToVal)) {
        $dateToVal = now()->format('Y-m-d');
    }

    $initialPeriod = 'hari';
    if (in_array($requestPeriod, ['week', 'minggu', 'weekly'])) {
        $initialPeriod = 'minggu';
    } elseif (in_array($requestPeriod, ['month', 'bulan', 'monthly'])) {
        $initialPeriod = 'bulan';
    }

    $selectedDate = $dateFromVal;
@endphp

<div x-data="customFilterApp()" class="flex flex-wrap items-center gap-3 w-fit select-none relative">
    {{-- Card Selector --}}
    <div class="relative">
        <div @click="calendarOpen = !calendarOpen" 
             class="bg-slate-800 hover:bg-slate-700/80 border border-slate-700/85 rounded-2xl px-5 py-3.5 flex items-center gap-4 cursor-pointer transition-premium shadow-lg min-w-[220px]">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center border border-blue-500/20 text-blue-400">
                <i class="far fa-calendar-alt text-lg"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tanggal Dipilih</p>
                <p class="text-sm font-black text-white mt-0.5" x-text="formatDisplayDate(selectedDate)"></p>
            </div>
            <div class="ml-auto text-slate-500">
                <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="calendarOpen ? 'rotate-180' : ''"></i>
            </div>
        </div>

        {{-- Dropdown Calendar --}}
        <div x-show="calendarOpen" 
             @click.away="calendarOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
             class="absolute left-0 mt-2 bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl z-[99] w-80"
             x-cloak>
            
            {{-- Calendar Header --}}
            <div class="flex items-center justify-between mb-4">
                <button type="button" @click="prevMonth()" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-colors">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                
                <div class="flex gap-1.5 items-center relative">
                    {{-- Month Dropdown --}}
                    <div class="relative">
                        <button type="button" @click="monthDropdownOpen = !monthDropdownOpen; yearDropdownOpen = false" 
                                class="text-xs font-black text-white bg-slate-800 hover:bg-slate-700 px-3 py-1.5 rounded-xl flex items-center gap-1.5 transition-colors">
                            <span x-text="months[currentMonth]"></span>
                            <i class="fas fa-chevron-down text-[8px] text-slate-400"></i>
                        </button>
                        <div x-show="monthDropdownOpen" @click.away="monthDropdownOpen = false" 
                             class="absolute top-full left-0 mt-1 bg-slate-850 border border-slate-800 rounded-2xl py-2 shadow-2xl z-[100] max-h-48 overflow-y-auto w-32 custom-scrollbar">
                            <template x-for="(m, index) in months" :key="index">
                                <button type="button" @click="selectMonth(index)" 
                                        class="w-full text-left px-4 py-1.5 text-xs text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                                        :class="currentMonth === index ? 'text-blue-400 font-bold bg-blue-500/5' : ''"
                                        x-text="m"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Year Dropdown --}}
                    <div class="relative">
                        <button type="button" @click="yearDropdownOpen = !yearDropdownOpen; monthDropdownOpen = false" 
                                class="text-xs font-black text-white bg-slate-800 hover:bg-slate-700 px-3 py-1.5 rounded-xl flex items-center gap-1.5 transition-colors">
                            <span x-text="currentYear"></span>
                            <i class="fas fa-chevron-down text-[8px] text-slate-400"></i>
                        </button>
                        <div x-show="yearDropdownOpen" @click.away="yearDropdownOpen = false" 
                             class="absolute top-full left-0 mt-1 bg-slate-850 border border-slate-800 rounded-2xl py-2 shadow-2xl z-[100] max-h-48 overflow-y-auto w-24 custom-scrollbar">
                            <template x-for="y in years" :key="y">
                                <button type="button" @click="selectYear(y)" 
                                        class="w-full text-left px-4 py-1.5 text-xs text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                                        :class="currentYear === y ? 'text-blue-400 font-bold bg-blue-500/5' : ''"
                                        x-text="y"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <button type="button" @click="nextMonth()" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-colors">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>

            {{-- Days of Week --}}
            <div class="grid grid-cols-7 gap-1 text-center mb-1">
                <template x-for="d in daysOfWeek" :key="d">
                    <span class="text-[10px] font-black text-slate-500 uppercase py-1" x-text="d"></span>
                </template>
            </div>

            {{-- Calendar Grid --}}
            <div class="grid grid-cols-7 gap-1">
                <template x-for="day in getDays()" :key="day.dateStr">
                    <button type="button" 
                            @click="selectDate(day.dateStr)"
                            class="h-8 rounded-xl flex items-center justify-center text-xs transition-premium border font-black"
                            :class="[
                                day.isCurrentMonth ? '' : 'text-slate-600 opacity-40',
                                selectedDate === day.dateStr 
                                    ? 'bg-blue-600 border-blue-500 text-white shadow-lg shadow-blue-600/30' 
                                    : 'border-transparent text-slate-300 hover:bg-slate-800'
                            ]"
                            x-text="day.day">
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- Pill Selector --}}
    <div class="flex bg-slate-900/50 rounded-full p-1 border border-slate-800/80 w-fit">
        <button type="button" @click="setPeriod('hari')" 
                class="px-5 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-full transition-all duration-300"
                :class="period === 'hari' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30'">
            Hari
        </button>
        <button type="button" @click="setPeriod('minggu')" 
                class="px-5 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-full transition-all duration-300"
                :class="period === 'minggu' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30'">
            Minggu
        </button>
        <button type="button" @click="setPeriod('bulan')" 
                class="px-5 py-1.5 text-[10px] font-black uppercase tracking-wider rounded-full transition-all duration-300"
                :class="period === 'bulan' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30'">
            Bulan
        </button>
    </div>
</div>

<script>
function customFilterApp() {
    return {
        selectedDate: '{{ $selectedDate }}',
        currentMonth: 0,
        currentYear: 2026,
        calendarOpen: false,
        monthDropdownOpen: false,
        yearDropdownOpen: false,
        period: '{{ $initialPeriod }}',
        
        months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        years: [],

        init() {
            let d = new Date(this.selectedDate);
            if (isNaN(d.getTime())) {
                d = new Date();
                this.selectedDate = this.formatDate(d);
            }
            this.currentMonth = d.getMonth();
            this.currentYear = d.getFullYear();
            
            // Populate years around selected year
            let startYear = this.currentYear - 6;
            for (let i = 0; i < 12; i++) {
                this.years.push(startYear + i);
            }
        },

        prevMonth() {
            this.currentMonth--;
            if (this.currentMonth < 0) {
                this.currentMonth = 11;
                this.currentYear--;
            }
        },

        nextMonth() {
            this.currentMonth++;
            if (this.currentMonth > 11) {
                this.currentMonth = 0;
                this.currentYear++;
            }
        },

        selectMonth(m) {
            this.currentMonth = m;
            this.monthDropdownOpen = false;
        },

        selectYear(y) {
            this.currentYear = y;
            this.yearDropdownOpen = false;
        },

        selectDate(dateStr) {
            this.selectedDate = dateStr;
            this.calendarOpen = false;
            this.applyFilter();
        },

        setPeriod(p) {
            this.period = p;
            let todayObj = new Date();
            this.selectedDate = this.formatDate(todayObj);
            this.applyFilter();
        },

        formatDate(d) {
            let year = d.getFullYear();
            let month = String(d.getMonth() + 1).padStart(2, '0');
            let day = String(d.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        formatDisplayDate(dateStr) {
            let d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            let day = String(d.getDate()).padStart(2, '0');
            let month = String(d.getMonth() + 1).padStart(2, '0');
            let year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },

        getDays() {
            let year = this.currentYear;
            let month = this.currentMonth;
            
            let firstDayIndex = new Date(year, month, 1).getDay();
            let daysInMonth = new Date(year, month + 1, 0).getDate();
            let daysInPrevMonth = new Date(year, month, 0).getDate();
            
            let days = [];
            
            // Previous month buffer days
            for (let i = firstDayIndex - 1; i >= 0; i--) {
                let d = daysInPrevMonth - i;
                let m = month - 1;
                let y = year;
                if (m < 0) { m = 11; y--; }
                days.push({
                    day: d,
                    month: m,
                    year: y,
                    isCurrentMonth: false,
                    dateStr: `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
                });
            }
            
            // Current month days
            for (let d = 1; d <= daysInMonth; d++) {
                days.push({
                    day: d,
                    month: month,
                    year: year,
                    isCurrentMonth: true,
                    dateStr: `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
                });
            }
            
            // Next month buffer days to pad to 42 items (6 rows)
            let remaining = 42 - days.length;
            for (let d = 1; d <= remaining; d++) {
                let m = month + 1;
                let y = year;
                if (m > 11) { m = 0; y++; }
                days.push({
                    day: d,
                    month: m,
                    year: y,
                    isCurrentMonth: false,
                    dateStr: `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
                });
            }
            
            return days;
        },

        applyFilter() {
            let dateFrom = '';
            let dateTo = '';
            let periodParam = '';
            
            let dateParts = this.selectedDate.split('-');
            let yearVal = parseInt(dateParts[0]);
            let monthVal = parseInt(dateParts[1]) - 1; // 0-indexed
            let dayVal = parseInt(dateParts[2]);
            
            let baseDate = new Date(yearVal, monthVal, dayVal);
            
            if (this.period === 'hari') {
                dateFrom = this.selectedDate;
                dateTo = this.selectedDate;
                periodParam = 'today';
            } else if (this.period === 'minggu') {
                // weekly range is from selectedDate - 6 days to selectedDate
                let dateToObj = new Date(yearVal, monthVal, dayVal);
                let dateFromObj = new Date(dateToObj);
                dateFromObj.setDate(dateToObj.getDate() - 6);
                
                dateFrom = this.formatDate(dateFromObj);
                dateTo = this.selectedDate;
                periodParam = 'week';
            } else if (this.period === 'bulan') {
                // monthly range is from 1st day of the selected month to the last day of that month
                let y = baseDate.getFullYear();
                let m = baseDate.getMonth();
                
                dateFrom = `${y}-${String(m + 1).padStart(2, '0')}-01`;
                let lastDay = new Date(y, m + 1, 0).getDate();
                dateTo = `${y}-${String(m + 1).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
                periodParam = 'month';
            }
            
            let url = new URL(window.location.href);
            url.searchParams.set('date_from', dateFrom);
            url.searchParams.set('date_to', dateTo);
            url.searchParams.set('period', periodParam);
            url.searchParams.delete('shift'); // Switch away from live shift to show dates!
            
            // For custom pages like monthly_expenses, cashflow, reports etc., also keep other standard query params if needed
            window.location.href = url.toString();
        }
    };
}
</script>
