<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * Upload the file and return public URL.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048', // You can allow other types if needed
        ]);

        $file = $request->file('file');

        $uploadFolder = public_path('uploads');

        // Create folder if it doesn't exist
        if (!File::exists($uploadFolder)) {
            File::makeDirectory($uploadFolder, 0755, true);
        }

        // Store file
        $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadFolder, $fileName);

        $publicUrl = asset("public/uploads/{$fileName}");

        return response()->json([
            'url' => $publicUrl,
            'success'=>true,
            'ok'=>true,
            'message'=>"Uploaded Successfully"
        ], 201);
    }

    /**
     * Delete the image based on the URL.
     */
  public function delete(Request $request)
{
    $request->validate([
        'url' => 'required|url',
    ]);

    $url = $request->input('url');
    $parsedUrl = parse_url($url);
    $relativePath = $parsedUrl['path'] ?? null;

    if (!$relativePath) {
        return response()->json(['error' => 'Invalid URL.'], 400);
    }

    // Remove leading slash
    $relativePath = ltrim($relativePath, '/');

    // Remove the first "public/" from the path if it exists
    if (str_starts_with($relativePath, 'public/')) {
        $relativePath = substr($relativePath, strlen('public/'));
    }

    $fullPath = public_path($relativePath);

    if (File::exists($fullPath)) {
        File::delete($fullPath);
        return response()->json(['message' => 'Image deleted successfully.']);
    }

    return response()->json(['error' => 'File not found.'], 404);
}
}
