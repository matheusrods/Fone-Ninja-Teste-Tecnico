<?php

namespace App\Policies;

use App\Models\User;

class CompraPolicy
{
    /** Admin e Comprador podem listar compras. */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isComprador();
    }

    /** Admin e Comprador podem registrar compras. */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isComprador();
    }
}
