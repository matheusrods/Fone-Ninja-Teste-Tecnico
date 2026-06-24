<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:fone-ninja', function (): void {
    $this->info('Fone Ninja Estoque API');
});
