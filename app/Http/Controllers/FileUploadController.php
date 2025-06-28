<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        // 1️⃣  Basic validation
        $request->validate([
            'url' => 'required|url',
        ]);

        // 2️⃣  Grab just the filename (protects against “../../../” tricks)
        $basename = basename(parse_url($request->input('url'), PHP_URL_PATH));

        // 3️⃣  The one fool‑proof path: public/uploads/…
        $fullPath = public_path("uploads/{$basename}");

        // 4️⃣  Guard: file must exist
        if (! File::exists($fullPath)) {
            Log::warning('DeleteImage: not found', compact('fullPath'));
            return response()->json(['error' => 'File not found.'], 404);
        }

        // 5️⃣  Delete
        File::delete($fullPath);
        Log::info('DeleteImage: deleted', compact('fullPath'));

        return response()->json(['message' => 'Image deleted successfully.']);
    }

}
