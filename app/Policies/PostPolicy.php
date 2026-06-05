<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTeacher();
    }

    public function view(User $user, Post $post): bool
    {
        return $post->status === Post::STATUS_PUBLISHED
            || $user->isSuperAdmin()
            || ($user->isTeacher() && $post->author_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isTeacher();
    }

    public function update(User $user, Post $post): bool
    {
        return $user->isSuperAdmin()
            || ($user->isTeacher() && $post->author_id === $user->id && $post->status !== Post::STATUS_PUBLISHED);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isSuperAdmin()
            || ($user->isTeacher() && $post->author_id === $user->id && in_array($post->status, [
                Post::STATUS_DRAFT,
                Post::STATUS_PENDING,
                Post::STATUS_REJECTED,
            ], true));
    }

    public function approve(User $user, Post $post): bool
    {
        return $user->isSuperAdmin();
    }

    public function interact(User $user, Post $post): bool
    {
        return $post->status === Post::STATUS_PUBLISHED
            && in_array(true, [$user->isStudent(), $user->isUser(), $user->isTeacher(), $user->isSuperAdmin()], true);
    }
}
