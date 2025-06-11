<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Membership;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Enums\MembershipStatus;
use Illuminate\Support\Facades\Validator;

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

public function getSlugForAuthenticatedUser(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.'
        ], 401);
    }

    $restaurant = $user->restaurant; // if you have a relationship set up

    if (!$restaurant) {
        return response()->json([
            'success' => false,
            'message' => 'Restaurant not found for this user.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'slug' => $restaurant->slug,
    ]);
}

public function getRestaurantByUser(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.'
        ], 401);
    }

    $restaurant = $user->restaurant; // assuming hasOne relationship

    if (!$restaurant) {
        return response()->json([
            'success' => false,
            'message' => 'No restaurant found for this user.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $restaurant
    ]);
}




public function updateRestaurant(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    }

    $restaurant = $user->restaurant;

    if (!$restaurant) {
        return response()->json(['success' => false, 'message' => 'Restaurant not found'], 404);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:500',
        'whatsapp' => 'nullable|string|max:20',
        'phone' => 'nullable|string|max:20',
        'instagram' => 'nullable|string|max:255',
        'settings' => 'nullable|array', // ✅ Validate settings if provided
    ]);

    try {
        $restaurant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant updated successfully',
            'data' => $restaurant
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Update failed',
            'error' => $e->getMessage()
        ], 500);
    }
}





public function publicRestaurantData(Request $request, $slug)
{
    $page = $request->query('page', 1);
    $perPage = 10;

    $restaurant = Restaurant::where('slug', $slug)->first();

    if (!$restaurant) {
        return response()->json([
            'success' => false,
            'slug' => false,
            'membership' => false,
            'message' => 'Restaurant not found.',
        ], 404);
    }

    $membership = Membership::where('restaurant_id', $restaurant->id)->first();
    $isActive = $membership && $membership->status === MembershipStatus::ACTIVE;

    if (!$isActive) {
        return response()->json([
            'success' => false,
            'slug' => true,
            'membership' => false,
            'message' => 'Membership is not active.'
        ], 403);
    }

    $categories = $restaurant->categories()
        ->with(['menuItems' => function ($query) use ($page, $perPage) {
            $query->where('is_available', true)
                  ->orderBy('created_at', 'asc')
                  ->skip(($page - 1) * $perPage)
                  ->take($perPage);
        }])->get();

    $filteredCategories = $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'menuItems' => $category->menuItems->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'image' => $item->image,
            ])->values(),
        ];
    });

    return response()->json([
        'success' => true,
        'slug' => true,
        'membership' => true,
        'restaurant' => [
            'name' => $restaurant->name,
            'description' => $restaurant->description,
            'address' => $restaurant->address,
            'phone' => $restaurant->phone,
            'whatsapp' => $restaurant->whatsapp,
            'instagram' => $restaurant->instagram,
            'settings' => $restaurant->settings,
            'categories' => $filteredCategories,
        ],
    ]);
}





public function checkMembership(Request $request)
{
    $user = $request->user();

    // Ensure user has a restaurant
    $restaurant = $user->restaurant;

    if (!$restaurant) {
        return response()->json(['error' => 'Restaurant not found'], 404);
    }

    // Load membership relationship
    $membership = $restaurant->membership;

    if (!$membership || $membership->status !== MembershipStatus::ACTIVE) {
        return response()->json([
            'membership' => false
        ]);
    }

    // Use correct field name and format date
    $expiryDate = optional($membership->end_date)->toDateTimeString();

    // Get settings safely (null coalescing in case settings is not an array)
    $settings = is_array($restaurant->settings) ? $restaurant->settings : [];
    $planType = $settings['planType'] ?? null;

    return response()->json([
        'membership' => true,
        'expiry_date' => $expiryDate,
        'planType' => $planType,
    ]);
}

}
