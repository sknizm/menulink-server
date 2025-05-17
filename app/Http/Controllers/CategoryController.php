<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Fetch all categories for a given restaurant
   public function index(Request $request)
{
    $user = $request->user();

    $restaurant = $user->restaurant;

    if (!$restaurant) {
        return response()->json(['message' => 'Restaurant not found'], 404);
    }

    $categories = $restaurant->categories; // or Category::where('restaurant_id', $restaurant->id)->get();

    return response()->json($categories);
}

    // Create a new category for the restaurant
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

         $user = $request->user();
        $restaurant = $user->restaurant;
        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        $category = Category::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'description' => $request->description ?? '',
            'restaurant_id' => $restaurant->id,
        ]);

        return response()->json($category, 201);
    }
}
