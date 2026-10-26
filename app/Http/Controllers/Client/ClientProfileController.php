<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClientProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        return view('client.profile.edit', compact('user'));
    }

    /**
     * Update user profile details.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address_1'    => ['nullable', 'string', 'max:255'],
            'address_2'    => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:100'],
            'state'        => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:2'],
            'postal_code'  => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return redirect()->route('client.profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update account password.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('client.profile.edit')->with('success', 'Password changed successfully.');
    }
}
