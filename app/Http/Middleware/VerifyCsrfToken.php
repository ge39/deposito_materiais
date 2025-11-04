<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Os URIs que devem ser excluídos da verificação de CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
