<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
   public function uploadUserPhoto(Request $request)
    {
        $request->validate([
            'photo_avatar_filename' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (!$request->hasFile('photo_avatar_filename')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = $request->file('photo_avatar_filename');
        
        $path = $file->store('photos_avatars', 'public');

        return response()->json([
            'photo_avatar_filename' => basename($path), 
        ], 201);
    }


    public function uploadCardFaces(Request $request)
    {
        $request->validate([
            'cardfaces' => 'required',
            'cardfaces.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedFiles = [];

        $files = is_array($request->file('cardfaces'))
            ? $request->file('cardfaces')
            : [$request->file('cardfaces')];

        foreach ($files as $file) {
            $path = $file->store('cardfaces', 'public');
            $uploadedFiles[] = [
                'cardface_url' => '/storage/' . $path,
            ];
        }

        return response()->json([
            'files' => $uploadedFiles,
        ], 200);
    }
}
