<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PreferencesController extends Controller
{
    private const DEFAULTS = [
        'sidebar_collapsed' => false,
        'show_help_tips' => true,
        'dense_tables' => false,
        'confirm_deletes' => true,
        'unread_badge' => true,
    ];

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sidebar_collapsed' => ['sometimes', 'boolean'],
            'show_help_tips' => ['sometimes', 'boolean'],
            'dense_tables' => ['sometimes', 'boolean'],
            'confirm_deletes' => ['sometimes', 'boolean'],
            'unread_badge' => ['sometimes', 'boolean'],
            'color' => ['sometimes', 'string', Rule::in(['auto', 'light', 'dark'])],
        ]);

        $prefs = array_map(fn ($v) => (bool) $v, array_intersect_key($validated, self::DEFAULTS));

        if (isset($validated['color'])) {
            $prefs['color'] = $validated['color'];
        }

        try {
            $request->user()->updateSidebarPrefs($prefs);
        } catch (\Throwable $e) {
            Log::error('Failed to save sidebar preferences', ['error' => $e->getMessage()]);
            return back()->withErrors(['preferences' => 'Could not save preferences. Please try again.']);
        }

        return back()->with('status', 'Preferences saved.');
    }

    public function reset(Request $request)
    {
        try {
            $request->user()->updateSidebarPrefs(array_fill_keys(array_keys(self::DEFAULTS), false) + self::DEFAULTS);
        } catch (\Throwable $e) {
            Log::error('Failed to reset sidebar preferences', ['error' => $e->getMessage()]);
            return back()->withErrors(['preferences' => 'Could not reset preferences. Please try again.']);
        }

        return back()->with('status', 'Preferences reset to defaults.');
    }
}
