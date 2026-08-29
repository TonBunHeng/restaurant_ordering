<?php

namespace App\Policies;

use App\Models\Place;
use App\Models\User;

class PlacePolicy
{
    /**
     * Anyone can view published places.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Place $place): bool
    {
        if ($place->status === 'published') {
            return true;
        }

        return $user && $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Place $place): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Place $place): bool
    {
        return $user->isAdmin();
    }
}
