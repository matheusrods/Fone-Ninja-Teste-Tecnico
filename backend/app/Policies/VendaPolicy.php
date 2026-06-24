<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venda;

class VendaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isVendedor();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isVendedor();
    }

    public function cancelar(User $user, Venda $venda): bool
    {
        return $user->isAdmin();
    }
}
