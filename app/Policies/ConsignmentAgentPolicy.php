<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ConsignmentAgent;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsignmentAgentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ConsignmentAgent');
    }

    public function view(AuthUser $authUser, ConsignmentAgent $consignmentAgent): bool
    {
        return $authUser->can('View:ConsignmentAgent');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ConsignmentAgent');
    }

    public function update(AuthUser $authUser, ConsignmentAgent $consignmentAgent): bool
    {
        return $authUser->can('Update:ConsignmentAgent');
    }

    public function delete(AuthUser $authUser, ConsignmentAgent $consignmentAgent): bool
    {
        return $authUser->can('Delete:ConsignmentAgent');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ConsignmentAgent');
    }

    public function restore(AuthUser $authUser, ConsignmentAgent $consignmentAgent): bool
    {
        return $authUser->can('Restore:ConsignmentAgent');
    }

    public function forceDelete(AuthUser $authUser, ConsignmentAgent $consignmentAgent): bool
    {
        return $authUser->can('ForceDelete:ConsignmentAgent');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ConsignmentAgent');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ConsignmentAgent');
    }

    public function replicate(AuthUser $authUser, ConsignmentAgent $consignmentAgent): bool
    {
        return $authUser->can('Replicate:ConsignmentAgent');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ConsignmentAgent');
    }

}