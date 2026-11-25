<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::allSettings();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $inputs = $request->except(['_token']);

        foreach ($inputs as $key => $value) {
            StoreSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value, 'updated_at' => now()]
            );
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}
