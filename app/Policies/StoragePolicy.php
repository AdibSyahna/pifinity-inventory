<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Storage;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoragePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Storage');
    }

    public function view(AuthUser $authUser, Storage $storage): bool
    {
        return $authUser->can('View:Storage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Storage');
    }

    public function update(AuthUser $authUser, Storage $storage): bool
    {
        return $authUser->can('Update:Storage');
    }

    public function delete(AuthUser $authUser, Storage $storage): bool
    {
        return $authUser->can('Delete:Storage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Storage');
    }

    public function restore(AuthUser $authUser, Storage $storage): bool
    {
        return $authUser->can('Restore:Storage');
    }

    public function forceDelete(AuthUser $authUser, Storage $storage): bool
    {
        return $authUser->can('ForceDelete:Storage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Storage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Storage');
    }

    public function replicate(AuthUser $authUser, Storage $storage): bool
    {
        return $authUser->can('Replicate:Storage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Storage');
    }

}