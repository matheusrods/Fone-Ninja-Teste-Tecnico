<?php

namespace App\Events;

use App\Models\Venda;
use Illuminate\Foundation\Events\Dispatchable;

final class VendaRegistrada
{
    use Dispatchable;

    public function __construct(public readonly Venda $venda) {}
}
