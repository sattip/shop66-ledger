<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-income') || $user->can('view-expenses');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        $permission = $transaction->type === 'income' ? 'view-income' : 'view-expenses';

        return $user->can($permission) && $user->hasStoreAccess($transaction->store_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-income') || $user->can('create-expenses');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        $permission = $transaction->type === 'income' ? 'edit-income' : 'edit-expenses';

        return $user->can($permission) && $user->hasStoreAccess($transaction->store_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        $permission = $transaction->type === 'income' ? 'delete-income' : 'delete-expenses';

        return $user->can($permission) && $user->hasStoreAccess($transaction->store_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        $permission = $transaction->type === 'income' ? 'delete-income' : 'delete-expenses';

        return $user->can($permission) && $user->hasStoreAccess($transaction->store_id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        $permission = $transaction->type === 'income' ? 'delete-income' : 'delete-expenses';

        return $user->can($permission) && $user->hasStoreAccess($transaction->store_id);
    }
}
