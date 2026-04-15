<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index(Request $request)
    {
        return view('help.index');
    }

    public function detail(Request $request, $section)
    {
        $validSections = [
            'getting-started',
            'dashboard',
            'system-settings',
            'customers',
            'pets',
            'inventory',
            'services',
            'time-slots',
            'appointments',
            'service-dashboard',
            'archives',
            'incident-reports',
            'notifications',
            'support'
        ];

        if (!in_array($section, $validSections)) {
            abort(404);
        }

        return view('help.detail', compact('section'));
    }
}
