<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Consignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsignmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Consignment');
    }

    public function view(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('View:Consignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Consignment');
    }

    public function update(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('Update:Consignment');
    }

    public function delete(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('Delete:Consignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Consignment');
    }

    public function restore(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('Restore:Consignment');
    }

    public function forceDelete(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('ForceDelete:Consignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Consignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Consignment');
    }

    public function replicate(AuthUser $authUser, Consignment $consignment): bool
    {
        return $authUser->can('Replicate:Consignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Consignment');
    }

}