<?php

namespace App\Exceptions;

use RuntimeException;

final class VendaJaCanceladaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Esta venda ja foi cancelada.');
    }
}
