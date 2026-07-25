<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $recentOrders = $user->orders()->with('items')->latest()->limit(5)->get();
        $ordersCount = $user->orders()->count();
        $wishlistCount = $user->wishlist()->count();

        return view('profile.edit', [
            'user' => $user,
            'recentOrders' => $recentOrders,
            'ordersCount' => $ordersCount,
            'wishlistCount' => $wishlistCount,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's additional profile details (phone, full_name).
     */
    public function updateDetails(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[\+\d\s\-\(\)]+$/'],
            'full_name' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = $request->user()->profile;

        if (!$profile) {
            $profile = new Profile(['user_id' => $request->user()->id]);
        }

        $profile->fill($data);
        $profile->save();

        return Redirect::route('profile.edit')->with('status', 'details-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
