<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getMultiple([
            'store_name', 'store_address', 'store_phone', 'store_email',
            'store_footer', 'currency', 'tax_rate', 'store_logo',
            'active_payment_methods',
            'bank_name', 'bank_account', 'bank_holder', 'qris_image',
            'printer_paper_size', 'printer_auto_print', 'printer_font_small', 'printer_feed_lines',
            'printer_name', 'printer_connection', 'drawer_auto_open', 'drawer_pulse_pin',
            'custom_price_enabled', 'custom_price_allow_hpp', 'custom_price_show_badge',
            'custom_price_require_reason', 'custom_price_access', 'delivery_presets',
            'cashout_source_access', 'cashout_role_access',
            'target_omzet', 'target_profit', 'target_transaksi',
            'shift_approval_required', 'auto_sync_cashflow'
        ]);
        $worksheets = \App\Models\Worksheet::all();
        return view('settings.index', compact('settings', 'worksheets'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:100',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:20',
            'store_email' => 'nullable|email',
            'store_footer' => 'nullable|string|max:200',
            'currency' => 'required|string|max:10',
            'store_logo' => 'nullable|image|max:1024',
            'qris_image' => 'nullable|image|max:2048',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'active_payment_methods' => 'nullable|array',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'bank_holder' => 'nullable|string|max:100',
            'printer_paper_size' => 'nullable|string',
            'printer_auto_print' => 'nullable|string',
            'printer_font_small' => 'nullable|string',
            'printer_feed_lines' => 'nullable|integer|min:0|max:15',
            'printer_name' => 'nullable|string|max:100',
            'printer_connection' => 'nullable|string|in:windows,usb,network',
            'drawer_auto_open' => 'nullable|string',
            'drawer_pulse_pin' => 'nullable|string|in:0,1',
            'custom_price_enabled' => 'nullable|string',
            'custom_price_allow_hpp' => 'nullable|string',
            'custom_price_show_badge' => 'nullable|string',
            'custom_price_require_reason' => 'nullable|string',
            'custom_price_access' => 'nullable|string|in:all,admin_owner,owner',
            'delivery_presets' => 'nullable|string',
            'cashout_source_access' => 'nullable|string|in:cash_only,bank_only,both',
            'cashout_role_access' => 'nullable|string|in:all,admin_owner,owner',
            'target_omzet' => 'nullable|numeric|min:0',
            'target_profit' => 'nullable|numeric|min:0',
            'target_transaksi' => 'nullable|integer|min:0',
            'shift_approval_required' => 'nullable|string|in:0,1',
            'auto_sync_cashflow' => 'nullable|string|in:0,1',
        ]);

        $keys = [
            'store_name', 'store_address', 'store_phone', 'store_email', 'store_footer', 
            'currency', 'tax_rate', 'bank_name', 'bank_account', 'bank_holder',
            'printer_paper_size', 'printer_auto_print', 'printer_font_small', 'printer_feed_lines',
            'printer_name', 'printer_connection', 'drawer_auto_open', 'drawer_pulse_pin',
            'custom_price_enabled', 'custom_price_allow_hpp', 'custom_price_show_badge',
            'custom_price_require_reason', 'custom_price_access', 'delivery_presets',
            'cashout_source_access', 'cashout_role_access',
            'target_omzet', 'target_profit', 'target_transaksi', 'shift_approval_required',
            'auto_sync_cashflow'
        ];
        foreach ($keys as $key) {
            Setting::set($key, $request->input($key));
        }

        $activeMethods = $request->input('active_payment_methods', []);
        Setting::set('active_payment_methods', json_encode($activeMethods));

        if ($request->hasFile('store_logo')) {
            $path = $request->file('store_logo')->store('settings', 'public');
            Setting::set('store_logo', $path);
        }

        if ($request->hasFile('qris_image')) {
            $path = $request->file('qris_image')->store('settings', 'public');
            Setting::set('qris_image', $path);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }

    public function updateTargets(Request $request)
    {
        $request->validate([
            'target_omzet' => 'required|numeric|min:0',
            'target_profit' => 'required|numeric|min:0',
            'target_transaksi' => 'required|numeric|min:0',
        ]);

        Setting::set('target_omzet', $request->target_omzet);
        Setting::set('target_profit', $request->target_profit);
        Setting::set('target_transaksi', $request->target_transaksi);

        return back()->with('success', 'Target bulanan berhasil diperbarui!');
    }

    public function testDrawer()
    {
        try {
            $printerService = app(\App\Services\PrinterService::class);
            $result = $printerService->openDrawer(null, true);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetData(Request $request)
    {
        $request->validate([
            'reset_type' => 'required|string|in:all_data,all_transactions,income_only,expense_only,product_catalog',
        ]);

        $worksheetId = session('active_worksheet_id') ?: \App\Models\Worksheet::first()->id;

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $type = $request->reset_type;

            if (in_array($type, ['all_data', 'all_transactions', 'income_only'])) {
                // Hapus Invoices
                $invoices = \App\Models\Invoice::where('worksheet_id', $worksheetId)->get();
                foreach($invoices as $i) {
                    $i->items()->delete();
                    $i->payments()->delete();
                    $i->delete();
                }

                // Hapus Transactions (Sales)
                $transactions = \App\Models\Transaction::where('worksheet_id', $worksheetId)->get();
                foreach($transactions as $t) {
                    $t->items()->delete();
                    $t->payments()->delete();
                    $t->delete();
                }

                // Hapus Cashflows Pemasukan
                \App\Models\Cashflow::where('worksheet_id', $worksheetId)->where('type', 'income')->delete();
                
                if ($type === 'income_only') {
                    // Reset stats pemasukan shift agar tidak rancu
                    \App\Models\Shift::where('worksheet_id', $worksheetId)
                        ->update(['cash_sales' => 0, 'bank_sales' => 0]);
                }
            }

            if (in_array($type, ['all_data', 'all_transactions', 'expense_only'])) {
                // Hapus Pengeluaran Bulanan
                $usages = \App\Models\MonthlyUsage::where('worksheet_id', $worksheetId)->get();
                foreach($usages as $u) {
                    $u->items()->delete();
                    $u->delete();
                }
                
                // Hapus Cashflows Pengeluaran
                \App\Models\Cashflow::where('worksheet_id', $worksheetId)->where('type', 'expense')->delete();
            }

            if (in_array($type, ['all_data', 'all_transactions'])) {
                // Hapus Seluruh Shift karena riwayat transaksi sudah terhapus total
                \App\Models\Shift::where('worksheet_id', $worksheetId)->delete();
                \Illuminate\Support\Facades\DB::table('cash_drawer_logs')->truncate();
            }

            if (in_array($type, ['all_data', 'product_catalog'])) {
                // Hapus Katalog Produk & Kategori
                // Catatan: stock_mutations akan ikut terhapus berkat onDelete('cascade')
                // transaction_items akan di set null jika ada yang tersisa
                $products = \App\Models\Product::where('worksheet_id', $worksheetId)->get();
                foreach($products as $p) {
                    $p->delete();
                }
                \App\Models\Category::where('worksheet_id', $worksheetId)->delete();
            }

            \Illuminate\Support\Facades\DB::commit();

            return back()->with('success', 'Reset data sistem berhasil dieksekusi secara permanen!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Gagal mereset data: ' . $e->getMessage());
        }
    }
}
