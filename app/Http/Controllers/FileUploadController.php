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

        $publicUrl = asset("uploads/{$fileName}");

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

    $url  = $request->input('url');
    $path = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/'); // uploads/…

    // If the public URL starts with “storage/”, strip it because the “public”
    // disk is already configured to point to storage/app/public
    $storagePath = preg_replace('#^storage/#', '', $path);

    if (Storage::disk('public')->exists($storagePath)) {
        Storage::disk('public')->delete($storagePath);
        return response()->json(['message' => 'Image deleted successfully.']);
    }

    return response()->json(['error' => 'File not found.'], 404);
}

}
