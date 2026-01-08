<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function update(Request $request, Guest $guest)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'table_name' => 'nullable|string|max:50',
            'hall' => 'nullable|string|max:100',
        ]);

        $guest->update($validated);

        return redirect()->back()->with('success', 'Guest updated successfully');
    }
}
