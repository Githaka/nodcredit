<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Setting;

class UiAdminSettingsController extends Controller
{
    public function index()
    {

        return view('admin.settings')
                    ->with('settings', Setting::all());
    }

    public function store(Request $request)
    {
        if(!\Hash::check($request->input('password'), $request->user()->password))
        {
            return redirect()->route('admin.settings')->with('error', 'Your password is not valid');
        }

        foreach($request->all() as $key=>$value)
        {
            Setting::where('k', $key)->update(['v' => $value]);
        }

        return redirect()->route('admin.settings')->with('success', 'Settings updated');
    }
}
