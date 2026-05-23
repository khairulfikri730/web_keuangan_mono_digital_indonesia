import sys

filepath = r'resources\views\cashflow\index.blade.php'

with open(filepath, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Lines are 1-indexed in editor, 0-indexed in Python
# Replace lines 1455..1720 (0-indexed: 1454..1719) with new content
before = lines[:1454]   # lines 1..1454
after  = lines[1720:]   # lines 1721..end

new_content = r"""
{{-- ============================================================
     MODAL ADJUSTMENT KAS - Koreksi saldo Tunai/Bank
============================================================ --}}
<div id="modalAdjKas" class="fixed inset-0 z-[90] hidden" aria-modal="true" role="dialog">
    <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-lg" onclick="closeModalAdjKas()"></div>
    <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div id="adjKasCard" class="bg-[#0d1526] border border-white/10 rounded-[2.5rem] w-full max-w-md shadow-[0_0_80px_-10px_rgba(245,158,11,0.25)] overflow-hidden">
            <div class="relative px-8 pt-8 pb-5 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/8 to-transparent pointer-events-none"></div>
                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                            <i class="fas fa-sliders-h text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-white tracking-tighter leading-none">Adjustment Kas</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Koreksi Saldo Tunai / Bank</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModalAdjKas()"
                        class="w-10 h-10 rounded-full bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-all flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="px-8 pb-8 pt-2">
                <form id="formAdjKas" onsubmit="submitAdjKas(event)">
                    @csrf
                    <input type="hidden" id="adjTypeHidden" name="type" value="expense">
                    <input type="hidden" id="adjSourceHidden" name="source" value="pos_cash">
                    <input type="hidden" name="transaction_date" id="adjDate" value="{{ date('Y-m-d') }}">
                    <div class="space-y-5">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Jenis Adjustment</p>
                            <div class="grid grid-cols-2 gap-2 bg-slate-900/60 rounded-2xl p-1.5 border border-white/5">
                                <button type="button" id="adjBtnKurangi" onclick="setAdjType('expense')"
                                    class="adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-red-600 text-white shadow-lg">
                                    <i class="fas fa-minus-circle"></i> Kurangi Saldo
                                </button>
                                <button type="button" id="adjBtnTambah" onclick="setAdjType('income')"
                                    class="adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white">
                                    <i class="fas fa-plus-circle"></i> Tambah Saldo
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Sumber Saldo</p>
                            <div class="grid grid-cols-2 gap-2 bg-slate-900/60 rounded-2xl p-1.5 border border-white/5">
                                <button type="button" id="adjBtnLaci" onclick="setAdjSource('pos_cash')"
                                    class="adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-amber-600 text-white shadow-lg">
                                    <i class="fas fa-cash-register text-xs"></i> Tunai / Laci
                                </button>
                                <button type="button" id="adjBtnBank" onclick="setAdjSource('pos_bank')"
                                    class="adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white">
                                    <i class="fas fa-university text-xs"></i> Saldo Bank
                                </button>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nominal Koreksi (Rp)</p>
                            <div class="relative group">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-black group-focus-within:text-amber-400 transition-colors">Rp</div>
                                <input type="text" id="adjAmountDisplay"
                                    class="w-full bg-slate-800/60 border border-white/10 rounded-2xl pl-12 pr-4 py-5 text-3xl font-black text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500/50 transition-all placeholder-slate-700"
                                    placeholder="0" autocomplete="off" required>
                                <input type="hidden" id="adjAmountRaw" name="amount">
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Keterangan</p>
                            <input type="text" name="notes" id="adjNotes"
                                class="w-full bg-slate-800/60 border border-white/10 rounded-2xl px-4 py-3 text-sm text-white focus:ring-2 focus:ring-amber-500/40 transition-all placeholder-slate-600"
                                placeholder="Contoh: Selisih saldo bank 2jt dipindah ke adjustment..." required>
                        </div>
                        <div id="adjInfoBox" class="bg-red-500/8 border border-red-500/20 rounded-2xl p-4">
                            <p id="adjInfoText" class="text-[10px] text-red-300/70 font-medium leading-relaxed">
                                <i class="fas fa-arrow-down mr-2 text-red-400/70"></i>
                                <strong>Kurangi</strong>: Saldo berkurang, selisih dicatat sebagai <em>Adjustment Kas Keluar</em>.
                            </p>
                        </div>
                        <div class="flex flex-col gap-3 pt-1">
                            <button type="submit" id="adjSubmitBtn"
                                class="w-full py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-500 text-white shadow-xl shadow-red-500/20">
                                <i class="fas fa-minus-circle" id="adjSubmitIcon"></i>
                                <span id="adjSubmitText">Kurangi Saldo &amp; Catat Adjustment</span>
                            </button>
                            <button type="button" onclick="closeModalAdjKas()"
                                class="w-full py-3 text-slate-500 hover:text-slate-300 font-black transition-all uppercase tracking-widest text-[10px]">
                                Batalkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// MODAL ADJUSTMENT KAS
// ============================================================
function openModalAdjKas() {
    const modal = document.getElementById('modalAdjKas');
    modal.classList.remove('hidden');
    document.getElementById('adjAmountDisplay').value = '';
    document.getElementById('adjAmountRaw').value = '';
    document.getElementById('adjNotes').value = '';
    document.getElementById('adjDate').value = new Date().toISOString().split('T')[0];
    setAdjType('expense');
    setAdjSource('pos_cash');
    const card = document.getElementById('adjKasCard');
    if (card) {
        card.style.transform = 'scale(0.9) translateY(20px)';
        card.style.opacity = '0';
        card.style.transition = 'all 0.3s cubic-bezier(0.34,1.56,0.64,1)';
        requestAnimationFrame(() => {
            card.style.transform = 'scale(1) translateY(0)';
            card.style.opacity = '1';
        });
    }
    setTimeout(() => document.getElementById('adjAmountDisplay').focus(), 350);
}

function closeModalAdjKas() {
    const modal = document.getElementById('modalAdjKas');
    const card = document.getElementById('adjKasCard');
    if (card) {
        card.style.transform = 'scale(0.95) translateY(10px)';
        card.style.opacity = '0';
        card.style.transition = 'all 0.2s ease';
        setTimeout(() => modal.classList.add('hidden'), 200);
    } else {
        modal.classList.add('hidden');
    }
}

function setAdjType(type) {
    document.getElementById('adjTypeHidden').value = type;
    const btnK = document.getElementById('adjBtnKurangi');
    const btnT = document.getElementById('adjBtnTambah');
    const infoBox = document.getElementById('adjInfoBox');
    const infoText = document.getElementById('adjInfoText');
    const submitBtn = document.getElementById('adjSubmitBtn');
    const submitIcon = document.getElementById('adjSubmitIcon');
    const submitText = document.getElementById('adjSubmitText');
    if (type === 'expense') {
        btnK.className = 'adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-red-600 text-white shadow-lg';
        btnT.className = 'adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white';
        infoBox.className = 'bg-red-500/8 border border-red-500/20 rounded-2xl p-4';
        infoText.innerHTML = '<i class="fas fa-arrow-down mr-2 text-red-400/70"></i><strong>Kurangi</strong>: Saldo berkurang, selisih dicatat sebagai <em>Adjustment Kas Keluar</em>.';
        submitBtn.className = 'w-full py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 bg-red-600 hover:bg-red-500 text-white shadow-xl shadow-red-500/20';
        submitIcon.className = 'fas fa-minus-circle';
        submitText.textContent = 'Kurangi Saldo & Catat Adjustment';
    } else {
        btnK.className = 'adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white';
        btnT.className = 'adj-type-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-emerald-600 text-white shadow-lg';
        infoBox.className = 'bg-emerald-500/8 border border-emerald-500/20 rounded-2xl p-4';
        infoText.innerHTML = '<i class="fas fa-arrow-up mr-2 text-emerald-400/70"></i><strong>Tambah</strong>: Saldo bertambah, dicatat sebagai <em>Adjustment Kas Masuk</em>.';
        submitBtn.className = 'w-full py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white shadow-xl shadow-emerald-500/20';
        submitIcon.className = 'fas fa-plus-circle';
        submitText.textContent = 'Tambah Saldo & Catat Adjustment';
    }
}

function setAdjSource(source) {
    document.getElementById('adjSourceHidden').value = source;
    const btnL = document.getElementById('adjBtnLaci');
    const btnB = document.getElementById('adjBtnBank');
    if (source === 'pos_cash') {
        btnL.className = 'adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-amber-600 text-white shadow-lg';
        btnB.className = 'adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white';
    } else {
        btnL.className = 'adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-slate-500 hover:text-white';
        btnB.className = 'adj-src-btn py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 bg-purple-600 text-white shadow-lg';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const display = document.getElementById('adjAmountDisplay');
    const raw = document.getElementById('adjAmountRaw');
    if (display) {
        display.addEventListener('input', function () {
            let val = this.value.replace(/[^0-9]/g, '');
            raw.value = val;
            this.value = val ? new Intl.NumberFormat('id-ID').format(parseInt(val)) : '';
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const adjModal = document.getElementById('modalAdjKas');
            if (adjModal && !adjModal.classList.contains('hidden')) closeModalAdjKas();
        }
    });
});

async function submitAdjKas(event) {
    event.preventDefault();
    const amount = document.getElementById('adjAmountRaw').value;
    if (!amount || parseInt(amount) <= 0) {
        alert('Masukkan nominal koreksi yang valid!');
        return;
    }
    const btn = document.getElementById('adjSubmitBtn');
    const btnText = document.getElementById('adjSubmitText');
    const origText = btnText.textContent;
    btn.disabled = true;
    btnText.textContent = 'Menyimpan...';
    try {
        const form = document.getElementById('formAdjKas');
        const formData = new FormData(form);
        const response = await fetch('{{ route("cashflow.quick-store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json'
            },
            body: formData
        });
        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            throw new Error(data.message || 'Gagal menyimpan adjustment');
        }
        closeModalAdjKas();
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Adjustment kas berhasil dicatat.', timer: 2000, showConfirmButton: false, background: '#0d1526', color: '#f8fafc' });
        }
        setTimeout(() => location.reload(), 1600);
    } catch (err) {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btnText.textContent = origText;
    }
}
</script>
"""

result_lines = before + [line + '\n' for line in new_content.split('\n')] + after

with open(filepath, 'w', encoding='utf-8') as f:
    f.writelines(result_lines)

print(f"Done. Total lines: {len(result_lines)}")
