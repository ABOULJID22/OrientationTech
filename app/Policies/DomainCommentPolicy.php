<?php

namespace App\Policies;

use App\Models\DomainComment;
use App\Models\User;

class DomainCommentPolicy
{
    public function update(User $user, DomainComment $comment): bool
    {
        return $user->isSuperAdmin() || $comment->user_id === $user->id;
    }

    public function delete(User $user, DomainComment $comment): bool
    {
        return $user->isSuperAdmin() || $comment->user_id === $user->id;
    }

    public function report(User $user, DomainComment $comment): bool
    {
        return $comment->user_id !== $user->id;
    }
}
