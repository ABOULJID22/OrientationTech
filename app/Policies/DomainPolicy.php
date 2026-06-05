<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Domain $domain): bool
    {
        return $domain->is_active || (bool) ($user?->isSuperAdmin() || $user?->isTeacher());
    }

    public function manage(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function interact(User $user, Domain $domain): bool
    {
        return $domain->is_active && ($user->isStudent() || $user->isUser() || $user->isTeacher() || $user->isSuperAdmin());
    }
}
