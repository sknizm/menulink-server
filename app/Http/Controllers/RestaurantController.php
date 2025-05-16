<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Membership;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Enums\MembershipStatus;

class RestaurantController extends Controller
{
    public function restaurantExist(Request $request)
    {
        $user = auth()->user(); // Sanctum-authenticated user

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $restaurant = Restaurant::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'exists' => $restaurant ? true : false,
            'data' => $restaurant
        ]);
    }


    public function createRestaurant(Request $request)
{
    try {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:restaurants,slug',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        // Check if user already has a restaurant
        if (Restaurant::where('user_id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'User already has a restaurant'], 409);
        }

        // Create restaurant
        $restaurant = Restaurant::create([
            'id' => Str::uuid(),
            'name' => $request->title,
            'slug' => $request->slug,
            'whatsapp' => $request->whatsapp,
            'user_id' => $user->id,
        ]);

        // Create membership (3 days)
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays(3);

        Membership::create([
            'id' => Str::uuid(),
            'restaurant_id' => $restaurant->id,
            'plan_id' => 'trial', // Adjust as needed
            'status' => MembershipStatus::ACTIVE->value,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'renews_at' => $endDate,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant and trial membership created',
            'data' => $restaurant
        ]);
    } catch (\Illuminate\Validation\ValidationException $ve) {
        return response()->json([
            'success' => false,
            'error' => 'Validation failed',
            'errors' => $ve->errors(),
        ], 422);
    } catch (\Exception $e) {
        // Log error for debugging (optional)
        \Log::error('Create restaurant error: '.$e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to create restaurant',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function checkSlug($slug)
{
    $exists = \App\Models\Restaurant::where('slug', $slug)->exists();

    return response()->json([
        'success' => true,
        'exists' => $exists,
    ]);
}
}
