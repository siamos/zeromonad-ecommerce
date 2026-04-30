<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'is_default' => ['boolean'],
        ]);

        $user = auth()->user();

        if (! empty($validated['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($validated);

        return back()->with('flash', ['success' => 'Address saved.']);
    }

    public function update(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'is_default' => ['boolean'],
        ]);

        if (! empty($validated['is_default'])) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('flash', ['success' => 'Address updated.']);
    }

    public function destroy(UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $address->delete();

        return back()->with('flash', ['success' => 'Address removed.']);
    }
}
