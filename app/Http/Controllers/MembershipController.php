<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\MembershipStatus;

class MembershipController extends Controller
{
 public function index()
{
    $memberships = Membership::with('restaurant')->get()->map(function ($membership) {
        return [
            'id' => $membership->id,
            'restaurant_id' => $membership->restaurant_id,
            'status' => $membership->status,
            'startDate' => $membership->start_date->toISOString(),
            'endDate' => $membership->end_date->toISOString(),
            'restaurant' => [
                'id' => $membership->restaurant->id,
                'name' => $membership->restaurant->name,
                'slug' => $membership->restaurant->slug,
            ],
        ];
    });

    return response()->json($memberships);
}

 public function checkStatus()
{
    $memberships = Membership::with('restaurant')->get();

    $updatedCount = 0;

    foreach ($memberships as $membership) {
        if ($membership->end_date->isPast() && $membership->status !== MembershipStatus::EXPIRED->value) {
            $membership->update(['status' => MembershipStatus::EXPIRED->value]);
            $updatedCount++;
        } elseif ($membership->end_date->isFuture() && $membership->status !== MembershipStatus::ACTIVE->value) {
            $membership->update(['status' => MembershipStatus::ACTIVE->value]);
            $updatedCount++;
        }
    }

    return response()->json([
        'message' => "Checked {$updatedCount} memberships. Status updated successfully."
    ]);
}


  public function update(Request $request, $id)
{
    $request->validate([
        'end_date' => 'required|date',
    ]);

    $membership = Membership::findOrFail($id);

    $membership->update([
        'end_date' => $request->end_date,
    ]);

    return response()->json(['message' => 'Membership updated successfully']);
}

}
