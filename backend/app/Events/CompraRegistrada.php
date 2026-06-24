<?php

namespace App\Events;

use App\Models\Compra;
use Illuminate\Foundation\Events\Dispatchable;

final class CompraRegistrada
{
    use Dispatchable;

    public function __construct(public readonly Compra $compra) {}
}
