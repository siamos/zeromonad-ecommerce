<?php

namespace App\Policies;

use App\Models\SavedItem;
use App\Models\User;

class SavedItemPolicy
{
    public function delete(User $user, SavedItem $savedItem): bool
    {
        return $user->id === $savedItem->user_id;
    }
}
