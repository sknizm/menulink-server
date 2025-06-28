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
    $path = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');   // uploads/…

    // 🔍 1‑line log:
    Log::debug('DeleteImage candidate', ['relative' => $path]);

    /* ------------------------------------------------------------------
       Choose the *first* location that actually exists on your server.
    ------------------------------------------------------------------ */

    $locations = [
        // ① document‑root (works on CloudPanel/Forge/etc.)
        realpath($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.$path,

        // ② base_path()  → …/current/…
        base_path($path),

        // ③ public_path() → …/current/public/…
        public_path($path),
    ];

    foreach ($locations as $fullPath) {
        if ($fullPath && File::exists($fullPath)) {
            Log::debug('Deleting', ['fullPath' => $fullPath]);
            File::delete($fullPath);
            return response()->json(['message' => 'Image deleted successfully.']);
        }
    }

    Log::warning('File not found in any location', ['checked' => $locations]);
    return response()->json(['error' => 'File not found.'], 404);
}

}
