<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function viewPublic(Request $request, $path)
    {
        $decoded = ltrim(str_replace('\\', '/', urldecode((string) $path)), '/');

        if ($decoded === '' || str_contains($decoded, '..')) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($decoded)) {
            abort(404);
        }

        return Storage::disk('public')->response($decoded);
    }
}
