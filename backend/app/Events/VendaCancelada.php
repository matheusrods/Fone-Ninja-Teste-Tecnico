<?php

namespace App\Events;

use App\Models\Venda;
use Illuminate\Foundation\Events\Dispatchable;

final class VendaCancelada
{
    use Dispatchable;

    public function __construct(public readonly Venda $venda) {}
}
