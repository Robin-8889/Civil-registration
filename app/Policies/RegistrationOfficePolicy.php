<?php

namespace App\Policies;

use App\Models\RegistrationOffice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegistrationOfficePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only system admin can view offices
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RegistrationOffice $registrationOffice): bool
    {
        // Only system admin can view office details
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only system admin can create offices
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RegistrationOffice $registrationOffice): bool
    {
        // Only system admin can update offices
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegistrationOffice $registrationOffice): bool
    {
        // Only system admin can delete offices
        return $user->isSystemAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RegistrationOffice $registrationOffice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RegistrationOffice $registrationOffice): bool
    {
        return false;
    }
}
