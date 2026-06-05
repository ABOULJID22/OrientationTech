<?php

namespace App\Policies;

use App\Models\TestPersonnalise;
use App\Models\User;

class TestPersonnalisePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isStudent();
    }

    public function view(User $user, TestPersonnalise $testPersonnalise): bool
    {
        return $user->isSuperAdmin() || $testPersonnalise->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->isStudent()) {
            return false;
        }

        return ! TestPersonnalise::query()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function update(User $user, TestPersonnalise $testPersonnalise): bool
    {
        return $user->isSuperAdmin() || $testPersonnalise->user_id === $user->id;
    }

    public function delete(User $user, TestPersonnalise $testPersonnalise): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, TestPersonnalise $testPersonnalise): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, TestPersonnalise $testPersonnalise): bool
    {
        return $user->isSuperAdmin();
    }
}
