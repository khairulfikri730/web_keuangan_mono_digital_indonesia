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
            'printer_paper_size', 'printer_auto_print', 'printer_font_small', 'printer_feed_lines'
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
        ]);

        $keys = [
            'store_name', 'store_address', 'store_phone', 'store_email', 'store_footer', 
            'currency', 'tax_rate', 'bank_name', 'bank_account', 'bank_holder',
            'printer_paper_size', 'printer_auto_print', 'printer_font_small', 'printer_feed_lines'
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

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }
}
