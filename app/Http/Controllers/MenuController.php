<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    // Get all categories and their menu items for the authenticated user's restaurant
    public function allMenu(Request $request)
    {
        $user = $request->user();
        $restaurantId = $user->restaurant->id;

        $categories = Category::with(['menuItems' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('restaurant_id', $restaurantId)->get();

        return response()->json($categories);
    }

    // Delete category
    public function deleteCategory(Request $request, $id)
    {
        $user = $request->user();
        $category = Category::where('id', $id)->where('restaurant_id', $user->restaurant->id)->firstOrFail();

        // Optionally delete related menu items and images
        foreach ($category->menuItems as $item) {
            if ($item->image) {
                $this->deleteImageFromUrl($item->image);
            }
            $item->delete();
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    // Delete menu item
    public function deleteMenuItem(Request $request, $id)
    {
        $user = $request->user();
        $item = MenuItem::where('id', $id)->where('restaurant_id', $user->restaurant->id)->firstOrFail();

        if ($item->image) {
            $this->deleteImageFromUrl($item->image);
        }

        $item->delete();

        return response()->json(['message' => 'Menu item deleted successfully']);
    }

    // Helper to delete image
    private function deleteImageFromUrl($url)
    {
        $path = str_replace(url('storage'), 'public', $url);
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }


    public function store(Request $request)
{
    $user = $request->user();

    // Validate input
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'image' => 'nullable|string',
        'categoryId' => 'required|uuid|exists:categories,id',
        'isAvailable' => 'required|boolean',
    ]);

    // Get the restaurant for the user
    $restaurant = $user->restaurant;

    // Create menu item
    $menuItem = $restaurant->menuItems()->create([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? '',
        'price' => $validated['price'],
        'image' => $validated['image'] ?? null,
        'category_id' => $validated['categoryId'],
        'is_available' => $validated['isAvailable'],
    ]);

    return response()->json($menuItem, 201);
}

public function update(Request $request, $id)
{
    $user = $request->user();
    $restaurant = $user->restaurant;

    $menuItem = $restaurant->menuItems()->findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'image' => 'nullable|string',
        'categoryId' => 'required|uuid|exists:categories,id',
        'isAvailable' => 'required|boolean',
    ]);

    $menuItem->update([
        'name' => $validated['name'],
        'description' => $validated['description'] ?? '',
        'price' => $validated['price'],
        'image' => $validated['image'] ?? null,
        'category_id' => $validated['categoryId'],
        'is_available' => $validated['isAvailable'],
    ]);

    return response()->json($menuItem);
}

public function show(Request $request, $id)
{
    $user = $request->user();
    $restaurant = $user->restaurant;

    // Find the menu item only if it belongs to the user's restaurant
    $menuItem = $restaurant->menuItems()->where('id', $id)->firstOrFail();

    return response()->json($menuItem);
}
}
