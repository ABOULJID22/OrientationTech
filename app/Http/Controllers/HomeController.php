<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ResourceContent;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $posts = Schema::hasTable('posts')
            ? Post::query()
                ->with('category')
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(['id','slug','title','content','cover_image','category_id','published_at','likes_count','favorites_count'])
            : collect();

        $resourceContents = Schema::hasTable('resource_contents')
            ? ResourceContent::query()
                ->published()
                ->latest('is_featured')
                ->latest('published_at')
                ->limit(6)
                ->get([
                    'id',
                    'type',
                    'title',
                    'slug',
                    'summary',
                    'cover_image',
                    'file_path',
                    'video_url',
                    'domain_key',
                    'career_name',
                    'views_count',
                    'likes_count',
                    'favorites_count',
                    'published_at',
                ])
            : collect();

    $onlyVideo = Schema::hasTable('site_settings')
        ? SiteSetting::query()->latest('id')->first(['presentationvideo_url','bgvideo_url'])
        : null;

    $fallback = asset('video/vide1.mp4');
    $raw = $onlyVideo?->presentationvideo_url;
        $resolved = $fallback;
        if (filled($raw)) {
            if (Str::startsWith($raw, ['http://', 'https://', '//'])) {
                $resolved = $raw;
            } elseif (Str::startsWith($raw, ['/'])) {
                $resolved = asset(ltrim($raw, '/'));
            } elseif (Str::startsWith($raw, ['videos/'])) {
                $resolved = Storage::disk('public')->url($raw);
            } elseif (Str::startsWith($raw, ['storage/', '/storage/'])) {
                $resolved = asset(ltrim($raw, '/'));
            } elseif (Str::startsWith($raw, ['video/', 'images/', 'assets/', 'build/'])) {
                $resolved = asset($raw);
            } else {
                $resolved = Storage::disk('public')->url($raw);
            }
        }

        $bgFallback = asset('video/vide2.mp4');
        $bgRaw = $onlyVideo?->bgvideo_url;
        $bgResolved = $bgFallback;
        if (filled($bgRaw)) {
            if (Str::startsWith($bgRaw, ['http://', 'https://', '//'])) {
                $bgResolved = $bgRaw;
            } elseif (Str::startsWith($bgRaw, ['/'])) {
                $bgResolved = asset(ltrim($bgRaw, '/'));
            } elseif (Str::startsWith($bgRaw, ['videos/'])) {
                $bgResolved = Storage::disk('public')->url($bgRaw);
            } elseif (Str::startsWith($bgRaw, ['storage/', '/storage/'])) {
                $bgResolved = asset(ltrim($bgRaw, '/'));
            } elseif (Str::startsWith($bgRaw, ['video/', 'images/', 'assets/', 'build/'])) {
                $bgResolved = asset($bgRaw);
            } else {
                $bgResolved = Storage::disk('public')->url($bgRaw);
            }
        }

        return view('welcome', [
            'posts' => $posts,
            'resourceContents' => $resourceContents,
            'presentationVideoSrc' => $resolved,
            'bgVideoSrc' => $bgResolved,
        ]);
    }
}
