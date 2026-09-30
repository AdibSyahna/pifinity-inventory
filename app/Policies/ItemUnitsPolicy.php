<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ItemUnits;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemUnitsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ItemUnits');
    }

    public function view(AuthUser $authUser, ItemUnits $itemUnits): bool
    {
        return $authUser->can('View:ItemUnits');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ItemUnits');
    }

    public function update(AuthUser $authUser, ItemUnits $itemUnits): bool
    {
        return $authUser->can('Update:ItemUnits');
    }

    public function delete(AuthUser $authUser, ItemUnits $itemUnits): bool
    {
        return $authUser->can('Delete:ItemUnits');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ItemUnits');
    }

    public function restore(AuthUser $authUser, ItemUnits $itemUnits): bool
    {
        return $authUser->can('Restore:ItemUnits');
    }

    public function forceDelete(AuthUser $authUser, ItemUnits $itemUnits): bool
    {
        return $authUser->can('ForceDelete:ItemUnits');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ItemUnits');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ItemUnits');
    }

    public function replicate(AuthUser $authUser, ItemUnits $itemUnits): bool
    {
        return $authUser->can('Replicate:ItemUnits');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ItemUnits');
    }

}