<?php

namespace App\Policies;

use App\Models\User;

/**
 * Base policy for record authorization
 * Shared logic for Birth, Marriage, and Death records
 */
class RecordPolicy
{
    /**
     * Check if user can view any records
     */
    public function viewAny(User $user): bool
    {
        // System admin can view all
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Approved registrars/clerks can view their office records
        if ($user->isApproved() && ($user->isRegistrar() || $user->isClerk())) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can view a specific record
     */
    public function view(User $user, $record): bool
    {
        // System admin can view all
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Approved registrar/clerk can view records from their office
        if ($user->isApproved() && ($user->isRegistrar() || $user->isClerk())) {
            return $user->canAccessOffice($record->registration_office_id);
        }

        return false;
    }

    /**
     * Check if user can create records
     */
    public function create(User $user): bool
    {
        // System admin can create
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Approved registrars can create in their office
        if ($user->isApproved() && $user->isRegistrar()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can update/edit records
     */
    public function update(User $user, $record): bool
    {
        // System admin can update all
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Approved registrar in same office can update
        if ($user->isApproved() && $user->isRegistrar()) {
            return $user->canAccessOffice($record->registration_office_id);
        }

        return false;
    }

    /**
     * Check if user can delete records
     */
    public function delete(User $user, $record): bool
    {
        // System admin can delete all
        if ($user->isSystemAdmin()) {
            return true;
        }

        // Approved registrar in same office can delete
        if ($user->isApproved() && $user->isRegistrar()) {
            return $user->canAccessOffice($record->registration_office_id);
        }

        return false;
    }
}
