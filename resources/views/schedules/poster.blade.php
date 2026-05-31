<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poster Jadwal {{ ucfirst($type ?? 'Mingguan') }} - {{ $startDate->translatedFormat('d M Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .poster-container {
            width: 1080px;
            min-height: {{ $type === 'monthly' ? '1528px' : '763px' }}; /* A4 Portrait for Monthly, A4 Landscape for Weekly/Daily */
            margin: 0 auto;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            position: relative;
            overflow: hidden;
        }
        .bg-pattern {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0);
            background-size: 40px 40px; pointer-events: none; z-index: 1;
        }
        .glow-circle { position: absolute; border-radius: 50%; filter: blur(100px); z-index: 0; }
        .content { z-index: 10; position: relative; padding: 60px 50px; }
        @media print { 
            .no-print { display: none !important; } 
            body, .poster-container { background: white !important; color: #1e293b !important; background-image: none !important; }
            .poster-container { width: 100% !important; min-height: auto !important; padding: 0 !important; }
            .content { padding: 20px !important; }
            .bg-slate-800\/40, .bg-slate-900 { background: white !important; border-color: #cbd5e1 !important; box-shadow: none !important; }
            .text-white { color: #0f172a !important; }
            .text-slate-400, .text-slate-500 { color: #475569 !important; }
            .border-slate-700, .border-slate-700\/50, .border-slate-700\/30, .border-slate-800 { border-color: #cbd5e1 !important; }
            .bg-pattern, .glow-circle, .bg-gradient-to-b { display: none !important; }
            .text-yellow-400 { color: #b45309 !important; }
            .text-blue-400 { color: #1d4ed8 !important; }
            .bg-yellow-500\/10 { background: transparent !important; color: #b45309 !important; border-color: #b45309 !important; }
            .bg-slate-800\/50 { background: transparent !important; border-color: #94a3b8 !important; color: #475569 !important; }
            .bg-blue-500\/15 { background: transparent !important; border-color: #1d4ed8 !important; color: #1d4ed8 !important; }
            .bg-red-500\/15 { background: transparent !important; border-color: #b91c1c !important; color: #b91c1c !important; }
            .bg-yellow-500\/5 { background: transparent !important; }
            .text-red-400 { color: #dc2626 !important; }
            .text-emerald-400 { color: #10b981 !important; }
            .drop-shadow-md, .drop-shadow-sm { filter: none !important; }
            
            /* Override inline colors for high contrast reading */
            [style*="color:#"] { color: #0f172a !important; font-weight: 800 !important; }
            
            /* Make pill borders darker */
            [style*="border-color:#"] { border-color: #64748b !important; border-width: 1.5px !important; }
            
            /* Remove very light pill backgrounds */
            [style*="background:#"] { background-color: transparent !important; }
            
            /* Except for the little shift color dot, give it a border so it's visible */
            .w-3.h-3.rounded-full { border: 1px solid #000 !important; background-color: transparent !important; }

            /* Prevent rows from splitting across pages */
            tr { page-break-inside: avoid; break-inside: avoid; }
            td, th { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>
</head>
<body class="flex flex-col items-center min-h-screen bg-slate-900 py-10">

    <div class="fixed top-4 left-4 z-50 flex gap-3 no-print">
        <button onclick="window.print()" class="px-5 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-xl hover:bg-blue-500 transition-colors text-sm"><i class="fas fa-file-pdf mr-2"></i>Cetak PDF</button>
        <button onclick="savePNG()" class="px-5 py-3 bg-emerald-600 text-white rounded-xl font-bold shadow-xl hover:bg-emerald-500 transition-colors text-sm"><i class="fas fa-image mr-2"></i>Save PNG</button>
        <form action="{{ route('schedules.poster') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="type" value="{{ $type }}">
            @if(request('location_id'))
                <input type="hidden" name="location_id" value="{{ request('location_id') }}">
            @endif
            @if($type === 'monthly')
                <input type="month" name="month" value="{{ $startDate->format('Y-m') }}" class="px-4 py-3 rounded-xl bg-slate-800 text-white border border-slate-700 outline-none text-sm">
            @else
                <input type="date" name="date" value="{{ $startDate->format('Y-m-d') }}" class="px-4 py-3 rounded-xl bg-slate-800 text-white border border-slate-700 outline-none text-sm">
            @endif
            <button type="submit" class="px-5 py-3 bg-slate-700 text-white rounded-xl font-bold hover:bg-slate-600 transition-colors text-sm">Ubah</button>
        </form>
    </div>

    <div class="poster-container" id="poster">
        <div class="bg-pattern"></div>
        <div class="glow-circle bg-blue-600/30 w-[800px] h-[800px] -top-[400px] -left-[400px]"></div>
        <div class="glow-circle bg-purple-600/20 w-[600px] h-[600px] bottom-[200px] -right-[200px]"></div>

        <div class="content">
            {{-- Header --}}
            <div class="text-center mb-12">
                <div class="inline-block px-6 py-2 rounded-full border border-yellow-500/30 bg-yellow-500/10 text-yellow-400 font-bold tracking-[0.2em] uppercase text-sm mb-4">
                    Jadwal Operasional {{ ucfirst($type ?? 'Mingguan') }}
                </div>
                <h1 class="text-5xl font-black leading-none tracking-tight mb-3 text-white uppercase drop-shadow-md">
                    @if($type === 'daily')
                        {{ $startDate->translatedFormat('l, d F Y') }}
                    @elseif($type === 'monthly')
                        Bulan {{ $startDate->translatedFormat('F Y') }}
                    @else
                        {{ $startDate->translatedFormat('d M') }} — {{ $endDate->translatedFormat('d M Y') }}
                    @endif
                </h1>
            </div>

            @php
                $dateChunks = array_chunk($reportDates, 7);
            @endphp
            {{-- Schedule Grid Per Location --}}
            @foreach($locations as $loc)
            @if($loc->shifts->count() > 0)
            <div class="mb-10 bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-[2rem] p-8 overflow-hidden shadow-2xl relative">
                <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-blue-500 to-purple-500"></div>
                <h3 class="text-3xl font-black text-white mb-6 tracking-widest uppercase text-center">
                    <span class="text-blue-400 drop-shadow-sm">{{ $loc->name }}</span>
                </h3>

                @foreach($dateChunks as $chunkIndex => $chunkDates)
                @if($chunkIndex > 0)
                    <div class="my-8 border-t-2 border-dashed border-slate-700/50"></div>
                @endif
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="text-left text-sm font-bold text-slate-400 uppercase px-3 py-3 border-b border-slate-700 w-[140px]">Shift</th>
                            @foreach($chunkDates as $wd)
                            <th class="text-center text-sm font-bold px-2 py-3 border-b border-slate-700 {{ $wd->isToday() ? 'text-yellow-400' : 'text-slate-400' }} w-[120px]">
                                <div class="text-lg">{{ $wd->translatedFormat('D') }}</div>
                                <div class="text-xs opacity-70">{{ $wd->format('d/m') }}</div>
                            </th>
                            @endforeach
                            @for($i = count($chunkDates); $i < 7; $i++)
                            <th class="border-b border-slate-700 w-[120px]"></th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loc->shifts as $shift)
                        <tr class="border-b border-slate-700/30">
                            <td class="px-3 py-4 align-top">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full flex-shrink-0" style="background:{{ $shift->color }}"></div>
                                    <div>
                                        <div class="font-black text-white text-base" style="color:{{ $shift->color }}">{{ $shift->name }}</div>
                                        <div class="text-xs text-slate-500">{{ substr($shift->start_time,0,5) }}-{{ substr($shift->end_time,0,5) }}</div>
                                    </div>
                                </div>
                            </td>
                            @foreach($chunkDates as $wd)
                            @php $dayAsgn = $shift->assignments->filter(fn($a) => $a->date->format('Y-m-d') === $wd->format('Y-m-d')); @endphp
                            <td class="px-1 py-3 text-center align-top {{ $wd->isToday() ? 'bg-yellow-500/5' : '' }}">
                                @foreach($dayAsgn as $da)
                                <div class="px-2 pt-0.5 pb-2.5 mb-1 rounded-xl text-sm font-bold flex flex-col items-center justify-center min-h-[34px]
                                    {{ $da->isClosed()
                                        ? ($da->closed_at_time ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30')
                                        : 'text-white border' }}"
                                    style="{{ $da->isOpen() ? 'background:' . $shift->color . '22; border-color:' . $shift->color . '55' : '' }}">
                                    @if($da->isClosed())
                                        <div class="flex flex-col items-center justify-center w-full">
                                            <span>{{ $da->crew->name ?? '-' }}</span>
                                            <span class="text-[9px] font-normal opacity-80 leading-none mt-0.5"><i class="fas fa-{{ $da->closed_at_time ? 'clock' : 'ban' }} mr-0.5 text-[8px]"></i>{{ $da->closed_at_time ? 'Selesai ' . substr($da->closed_at_time, 0, 5) : 'Close' }}</span>
                                        </div>
                                    @else
                                        <span>{{ $da->crew->name ?? '-' }}</span>
                                    @endif
                                </div>
                                @endforeach
                                @for($i = $dayAsgn->count(); $i < $shift->max_crew; $i++)
                                <div class="px-2 pt-0.5 pb-2.5 mb-1 rounded-xl text-sm font-bold bg-slate-800/50 text-slate-500 border border-dashed border-slate-700 flex flex-col items-center justify-center min-h-[34px]">
                                    <span>+ Kosong</span>
                                </div>
                                @endfor
                            </td>
                            @endforeach
                            @for($i = count($chunkDates); $i < 7; $i++)
                            <td class="px-1 py-3 border-b border-slate-700/30"></td>
                            @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endforeach
            </div>
            @endif
            @endforeach

            {{-- Legend --}}
            <div class="flex items-center justify-center gap-6 mt-6 text-xs text-slate-500">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500/30 border border-emerald-500/50"></span> Open</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-500/30 border border-red-500/50"></span> Close</span>
                <span class="flex items-center gap-1 text-slate-600">— Kosong</span>
            </div>

            {{-- Footer --}}
            <div class="mt-10 text-center pt-8 border-t border-slate-800">
                <p class="text-slate-500 font-semibold tracking-widest uppercase text-sm">MONOFRAME &bull; INTERNAL SCHEDULE SYSTEM</p>
            </div>
        </div>
    </div>

    <script>
    function savePNG() {
        html2canvas(document.getElementById('poster'), { scale: 2, useCORS: true, backgroundColor: '#0f172a' }).then(canvas => {
            let link = document.createElement('a');
            link.download = 'jadwal-{{ $startDate->format("Y-m-d") }}.png';
            link.href = canvas.toDataURL();
            link.click();
        });
    }
    </script>
</body>
</html>
