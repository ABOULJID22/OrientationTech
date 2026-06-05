<?php

namespace App\Policies;

use App\Models\PostComment;
use App\Models\User;

class PostCommentPolicy
{
    public function update(User $user, PostComment $comment): bool
    {
        return $comment->user_id === $user->id || $user->isSuperAdmin();
    }

    public function delete(User $user, PostComment $comment): bool
    {
        return $user->isSuperAdmin()
            || $comment->user_id === $user->id
            || ($user->isTeacher() && $comment->post?->author_id === $user->id);
    }

    public function hide(User $user, PostComment $comment): bool
    {
        return $user->isSuperAdmin()
            || ($user->isTeacher() && $comment->post?->author_id === $user->id);
    }

    public function report(User $user, PostComment $comment): bool
    {
        return $comment->user_id !== $user->id;
    }
}
