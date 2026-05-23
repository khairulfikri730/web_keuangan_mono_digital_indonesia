<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poster Jadwal Mingguan - {{ $weekStart->translatedFormat('d M') }} - {{ $weekEnd->translatedFormat('d M Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0f172a; color: #f8fafc; }
        .poster-container {
            width: 1080px;
            min-height: 1920px;
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
        @media print { .no-print { display: none !important; } body { background: white; } }
    </style>
</head>
<body class="flex flex-col items-center min-h-screen bg-slate-900 py-10">

    <div class="fixed top-4 left-4 z-50 flex gap-3 no-print">
        <button onclick="window.print()" class="px-5 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-xl hover:bg-blue-500 transition-colors text-sm"><i class="fas fa-file-pdf mr-2"></i>Cetak PDF</button>
        <button onclick="savePNG()" class="px-5 py-3 bg-emerald-600 text-white rounded-xl font-bold shadow-xl hover:bg-emerald-500 transition-colors text-sm"><i class="fas fa-image mr-2"></i>Save PNG</button>
        <form action="{{ route('schedules.poster') }}" method="GET" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $weekStart->format('Y-m-d') }}" class="px-4 py-3 rounded-xl bg-slate-800 text-white border border-slate-700 outline-none text-sm">
            <button type="submit" class="px-5 py-3 bg-slate-700 text-white rounded-xl font-bold hover:bg-slate-600 transition-colors text-sm">Ubah Minggu</button>
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
                    Jadwal Operasional Mingguan
                </div>
                <h1 class="text-5xl font-black leading-none tracking-tight mb-3 text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 uppercase">
                    {{ $weekStart->translatedFormat('d M') }} — {{ $weekEnd->translatedFormat('d M Y') }}
                </h1>
            </div>

            {{-- Schedule Grid Per Location --}}
            @foreach($locations as $loc)
            @if($loc->shifts->count() > 0)
            <div class="mb-10 bg-slate-800/40 backdrop-blur-md border border-slate-700/50 rounded-[2rem] p-8 overflow-hidden shadow-2xl relative">
                <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-blue-500 to-purple-500"></div>
                <h3 class="text-3xl font-black text-white mb-6 tracking-widest uppercase text-center">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">{{ $loc->name }}</span>
                </h3>

                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="text-left text-sm font-bold text-slate-400 uppercase px-3 py-3 border-b border-slate-700 w-[140px]">Shift</th>
                            @foreach($weekDates as $wd)
                            <th class="text-center text-sm font-bold px-2 py-3 border-b border-slate-700 {{ $wd->isToday() ? 'text-yellow-400' : 'text-slate-400' }}">
                                <div class="text-lg">{{ $wd->translatedFormat('D') }}</div>
                                <div class="text-xs opacity-70">{{ $wd->format('d/m') }}</div>
                            </th>
                            @endforeach
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
                            @foreach($weekDates as $wd)
                            @php $dayAsgn = $shift->assignments->filter(fn($a) => $a->date->format('Y-m-d') === $wd->format('Y-m-d')); @endphp
                            <td class="px-1 py-3 text-center align-top {{ $wd->isToday() ? 'bg-yellow-500/5' : '' }}">
                                @forelse($dayAsgn as $da)
                                <div class="px-2 py-1.5 mb-1 rounded-xl text-sm font-bold
                                    {{ $da->isClosed()
                                        ? 'bg-red-500/15 text-red-400 border border-red-500/30 line-through'
                                        : 'text-white border' }}"
                                    style="{{ $da->isOpen() ? 'background:' . $shift->color . '22; border-color:' . $shift->color . '55' : '' }}">
                                    @if($da->isClosed())<i class="fas fa-times text-[10px] mr-1"></i>@endif
                                    {{ $da->crew->name ?? '-' }}
                                </div>
                                @empty
                                <div class="text-xs text-slate-600 italic py-1">—</div>
                                @endforelse
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
            link.download = 'jadwal-{{ $weekStart->format("Y-m-d") }}.png';
            link.href = canvas.toDataURL();
            link.click();
        });
    }
    </script>
</body>
</html>
