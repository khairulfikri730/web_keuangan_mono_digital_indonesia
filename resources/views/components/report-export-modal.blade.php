<!-- Report Export Modal (Enterprise SaaS Style) -->
<div 
    x-data="{ 
        isOpen: false, 
        isGenerating: false, 
        format: 'pdf', 
        period: 'bulan_ini', 
        showPreview: false,
        sections: ['summary', 'sales', 'expenses', 'profit', 'chart_sales', 'top_products', 'ai_insights'],
        roles: ['Owner', 'Manager', 'Kasir'],
        async submitExport(type) {
            this.isGenerating = true;
            this.format = type;
            
            // Capture Charts
            const container = document.getElementById('chartImagesContainer');
            container.innerHTML = '';
            if (typeof Chart !== 'undefined') {
                Object.values(Chart.instances).forEach((chart, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `chart_images[${chart.canvas.id || 'chart_' + index}]`;
                    input.value = chart.toBase64Image();
                    container.appendChild(input);
                });
            }

            const form = document.getElementById('exportReportForm');
            if (type === 'pdf') form.action = '{{ route('reports.export-pdf') }}';
            else if (type === 'excel') form.action = '{{ route('reports.export-excel') }}';
            
            form.submit();
            
            setTimeout(() => { this.isGenerating = false; }, 5000);
        }
    }"
    x-show="isOpen"
    @open-export-modal.window="isOpen = true"
    @keydown.escape.window="isOpen = false"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
>
    <div class="bg-slate-900 border border-white/10 w-full max-w-6xl h-[90vh] rounded-[2.5rem] shadow-2xl flex flex-col overflow-hidden">
        
        <!-- Header -->
        <div class="px-8 py-6 border-b border-white/5 flex justify-between items-center bg-gradient-to-r from-blue-600/20 to-transparent">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-xl shadow-blue-900/40 border border-white/20">
                    <i class="fas fa-file-invoice-dollar text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight">ENTERPRISE REPORT CENTER</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">MONOFRAME PREMIUM ANALYTICS</p>
                </div>
            </div>
            <button @click="isOpen = false" class="w-12 h-12 rounded-full hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-all duration-300 group">
                <i class="fas fa-times text-xl group-hover:rotate-90 transition-transform"></i>
            </button>
        </div>

        <form id="exportReportForm" method="POST" class="flex-1 flex flex-col min-h-0">
            @csrf
            <div class="flex-1 flex min-h-0">
                
                <!-- Left Column: Configuration -->
                <div class="w-2/3 p-8 overflow-y-auto border-r border-white/5 custom-scrollbar">
                    
                    <div class="space-y-10">
                        <!-- 1. Selection -->
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <label class="text-[11px] font-black text-blue-400 uppercase tracking-[0.2em]">1. PILIH MODUL LAPORAN</label>
                                <button type="button" @click="sections = ['summary', 'sales', 'expenses', 'profit', 'chart_sales', 'top_products', 'ai_insights', 'history_trx', 'history_expenses', 'balances', 'roi', 'shift_details', 'full_cashflow', 'category_analysis', 'internal_mutations', 'invoice_analytics']" class="text-[9px] font-bold text-slate-500 hover:text-white transition-colors">PILIH SEMUA</button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @php
                                    $options = [
                                        ['id' => 'summary', 'label' => 'Ringkasan Utama', 'icon' => 'fa-tachometer-alt'],
                                        ['id' => 'sales', 'label' => 'Performa Omzet', 'icon' => 'fa-shopping-bag'],
                                        ['id' => 'expenses', 'label' => 'Total Biaya', 'icon' => 'fa-arrow-circle-up'],
                                        ['id' => 'profit', 'label' => 'Laba Bersih', 'icon' => 'fa-heart'],
                                        ['id' => 'ai_insights', 'label' => 'AI Business Insight', 'icon' => 'fa-brain'],
                                        ['id' => 'invoice_analytics', 'label' => 'Analisa Invoice', 'icon' => 'fa-file-invoice'],
                                        ['id' => 'chart_sales', 'label' => 'Grafik Penjualan', 'icon' => 'fa-chart-line'],
                                        ['id' => 'chart_expenses', 'label' => 'Grafik Pengeluaran', 'icon' => 'fa-chart-pie'],
                                        ['id' => 'top_products', 'label' => 'Ranking Produk', 'icon' => 'fa-trophy'],
                                        ['id' => 'history_trx', 'label' => 'Detail Penjualan', 'icon' => 'fa-list-ul'],
                                        ['id' => 'internal_mutations', 'label' => 'Mutasi Internal', 'icon' => 'fa-sync'],
                                        ['id' => 'full_cashflow', 'label' => 'Arus Kas Lengkap', 'icon' => 'fa-exchange-alt'],
                                        ['id' => 'roi', 'label' => 'Analisa ROI', 'icon' => 'fa-chart-bar'],
                                        ['id' => 'shift_details', 'label' => 'Laporan Shift', 'icon' => 'fa-user-clock'],
                                    ];
                                @endphp

                                @foreach($options as $opt)
                                <label class="group cursor-pointer">
                                    <input type="checkbox" name="sections[]" value="{{ $opt['id'] }}" x-model="sections" class="hidden">
                                    <div 
                                        :class="sections.includes('{{ $opt['id'] }}') ? 'bg-blue-600/20 border-blue-500/50 text-white' : 'bg-slate-800/50 border-white/5 text-slate-500'"
                                        class="flex items-center gap-3 p-4 rounded-2xl border transition-all duration-300 group-hover:border-blue-500/30"
                                    >
                                        <div :class="sections.includes('{{ $opt['id'] }}') ? 'bg-blue-500 text-white' : 'bg-slate-700 text-slate-400'" class="w-8 h-8 rounded-lg flex items-center justify-center transition-all">
                                            <i class="fas {{ $opt['icon'] }} text-xs"></i>
                                        </div>
                                        <span class="text-[11px] font-bold">{{ $opt['label'] }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- 2. Filters -->
                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <label class="text-[11px] font-black text-blue-400 uppercase tracking-[0.2em] mb-4 block">2. PERIODE WAKTU</label>
                                <select name="period" x-model="period" class="w-full bg-slate-950 border border-white/10 rounded-2xl px-5 py-4 text-sm text-white focus:outline-none focus:border-blue-500 transition-premium shadow-inner">
                                    <option value="hari_ini">Hari Ini</option>
                                    <option value="kemarin">Kemarin</option>
                                    <option value="minggu_ini">Minggu Ini</option>
                                    <option value="bulan_ini">Bulan Ini</option>
                                    <option value="tahun_ini">Tahun Ini</option>
                                    <option value="custom">Rentang Kustom</option>
                                </select>
                                
                                <div x-show="period === 'custom'" x-transition class="grid grid-cols-2 gap-3 mt-4">
                                    <div class="relative">
                                        <input type="date" name="start_date" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:border-blue-500">
                                        <span class="absolute -top-2 left-3 px-2 bg-slate-900 text-[9px] text-slate-500 font-bold">MULAI</span>
                                    </div>
                                    <div class="relative">
                                        <input type="date" name="end_date" class="w-full bg-slate-950 border border-white/10 rounded-xl px-4 py-3 text-xs text-white focus:border-blue-500">
                                        <span class="absolute -top-2 left-3 px-2 bg-slate-900 text-[9px] text-slate-500 font-bold">AKHIR</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="text-[11px] font-black text-blue-400 uppercase tracking-[0.2em] mb-4 block">3. CABANG / UNIT</label>
                                <select name="worksheet_id" class="w-full bg-slate-950 border border-white/10 rounded-2xl px-5 py-4 text-sm text-white focus:outline-none focus:border-blue-500 transition-premium shadow-inner">
                                    <option value="all">Seluruh Jaringan Bisnis</option>
                                    @php $wsList = \App\Models\Worksheet::all(); @endphp
                                    @foreach($wsList as $ws)
                                        <option value="{{ $ws->id }}" {{ session('active_worksheet_id') == $ws->id ? 'selected' : '' }}>{{ $ws->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 3. Styles -->
                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <label class="text-[11px] font-black text-blue-400 uppercase tracking-[0.2em] mb-4 block">4. ORIENTASI & TEMA</label>
                                <div class="flex gap-2">
                                    <label class="flex-1">
                                        <input type="radio" name="orientation" value="portrait" checked class="peer hidden">
                                        <div class="p-3 text-center rounded-xl border border-white/5 bg-slate-950 peer-checked:bg-blue-600 peer-checked:border-blue-500 transition-all cursor-pointer">
                                            <i class="fas fa-file-alt text-lg mb-1"></i>
                                            <p class="text-[9px] font-black">PORTRAIT</p>
                                        </div>
                                    </label>
                                    <label class="flex-1">
                                        <input type="radio" name="orientation" value="landscape" class="peer hidden">
                                        <div class="p-3 text-center rounded-xl border border-white/5 bg-slate-950 peer-checked:bg-blue-600 peer-checked:border-blue-500 transition-all cursor-pointer">
                                            <i class="fas fa-file-alt rotate-90 text-lg mb-1"></i>
                                            <p class="text-[9px] font-black">LANDSCAPE</p>
                                        </div>
                                    </label>
                                    <div class="w-px bg-white/10 mx-2"></div>
                                    <select name="theme" class="flex-1 bg-slate-950 border border-white/10 rounded-xl px-2 text-[10px] font-bold text-white focus:border-blue-500">
                                        <option value="white">MODERN LIGHT</option>
                                        <option value="dark">PREMIUM DARK</option>
                                        <option value="blue">CORPORATE BLUE</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="text-[11px] font-black text-blue-400 uppercase tracking-[0.2em] mb-4 block">5. VALIDASI & TTD</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['Owner', 'Manager', 'Finance', 'Kasir'] as $role)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="signature_roles[]" value="{{ $role }}" checked class="peer hidden">
                                        <span class="px-3 py-2 rounded-lg border border-white/10 bg-slate-950 text-[10px] font-bold text-slate-500 peer-checked:bg-emerald-600/20 peer-checked:border-emerald-500 peer-checked:text-emerald-400 transition-all">
                                            {{ strtoupper($role) }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                                <label class="flex items-center gap-3 mt-4 group cursor-pointer">
                                    <input type="checkbox" name="include_signature" checked class="peer hidden">
                                    <div class="w-10 h-5 rounded-full bg-slate-950 border border-white/10 peer-checked:bg-emerald-600 peer-checked:border-emerald-500 transition-all relative after:content-[''] after:absolute after:top-1 after:left-1 after:w-2.5 after:h-2.5 after:bg-white after:rounded-full after:transition-all peer-checked:after:left-6"></div>
                                    <span class="text-[10px] font-black text-slate-400 peer-checked:text-emerald-400">AKTIFKAN BLOK TANDA TANGAN</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Live Summary & Actions -->
                <div class="w-1/3 p-8 bg-black/20 flex flex-col justify-between">
                    <div>
                        <label class="text-[11px] font-black text-blue-400 uppercase tracking-[0.2em] mb-8 block">RINGKASAN EXPORT</label>
                        
                        <div class="space-y-6">
                            <div class="bg-slate-950/50 rounded-3xl p-6 border border-white/5 relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-shield-alt text-4xl"></i>
                                </div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Sistem Keamanan</p>
                                <h4 class="text-white font-black text-sm mb-4">MONOFRAME SECURE EXPORT</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3 text-[10px]">
                                        <i class="fas fa-check-circle text-emerald-500"></i>
                                        <span class="text-slate-400">Data Integrity Check</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-[10px]">
                                        <i class="fas fa-check-circle text-emerald-500"></i>
                                        <span class="text-slate-400">Digital Watermarking</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-[10px]">
                                        <i class="fas fa-check-circle text-emerald-500"></i>
                                        <span class="text-slate-400">Auto-Calculated Insights</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Jumlah Modul</span>
                                    <span class="text-white font-black" x-text="sections.length"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kualitas Grafik</span>
                                    <span class="text-emerald-400 font-black">HD VECTOR</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">File Size</span>
                                    <span class="text-slate-300 font-black">OPTIMIZED</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div x-show="isGenerating" x-transition class="mb-4">
                            <div class="flex justify-between text-[10px] font-black text-blue-400 mb-2">
                                <span>GENERATING <span x-text="format.toUpperCase()"></span>...</span>
                                <span>85%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-600 to-indigo-500 animate-pulse" style="width: 85%"></div>
                            </div>
                        </div>

                        <button 
                            type="button" 
                            @click="submitExport('pdf')"
                            :disabled="isGenerating"
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-blue-900/40 flex items-center justify-center gap-3 text-xs uppercase tracking-[0.2em] active:scale-95 disabled:opacity-50"
                        >
                            <i class="fas fa-file-pdf text-lg"></i>
                            GENERATE PDF REPORT
                        </button>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <button 
                                type="button" 
                                @click="submitExport('excel')"
                                :disabled="isGenerating"
                                class="bg-slate-800 hover:bg-slate-700 text-emerald-400 font-black py-4 rounded-2xl border border-white/5 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center gap-2"
                            >
                                <i class="fas fa-file-excel"></i> EXCEL
                            </button>
                            <button 
                                type="button" 
                                class="bg-slate-800 hover:bg-slate-700 text-slate-300 font-black py-4 rounded-2xl border border-white/5 transition-all text-[10px] uppercase tracking-widest flex items-center justify-center gap-2"
                            >
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden chart images -->
            <div id="chartImagesContainer" class="hidden"></div>
        </form>
    </div>
</div>

</style>

<script>
    window.openExportModal = function() {
        window.dispatchEvent(new CustomEvent('open-export-modal'));
    }
</script>
