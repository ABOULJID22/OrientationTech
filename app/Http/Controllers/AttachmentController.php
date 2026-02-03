<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    // Simple redirect to the storage public URL for a stored path
    public function viewPublic(Request $request, $path)
    {
        if (! $path) {
            abort(404);
        }

        $decoded = $path;

        // If file exists on public disk, redirect to its URL; otherwise 404
        if (Storage::disk('public')->exists($decoded)) {
            return redirect(Storage::disk('public')->url($decoded));
        }

        abort(404);
    }
}
