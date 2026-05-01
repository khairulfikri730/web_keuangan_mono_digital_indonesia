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
        ]);
        return view('settings.index', compact('settings'));
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
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'active_payment_methods' => 'nullable|array',
        ]);

        $keys = ['store_name', 'store_address', 'store_phone', 'store_email', 'store_footer', 'currency', 'tax_rate'];
        foreach ($keys as $key) {
            Setting::set($key, $request->input($key));
        }

        $activeMethods = $request->input('active_payment_methods', []);
        Setting::set('active_payment_methods', json_encode($activeMethods));

        if ($request->hasFile('store_logo')) {
            $path = $request->file('store_logo')->store('settings', 'public');
            Setting::set('store_logo', $path);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan!');
    }
}
