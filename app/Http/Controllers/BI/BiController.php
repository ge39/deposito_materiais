<?php

namespace App\Http\Controllers\BI;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class BiController extends Controller
{
    public function index(): View
    {
        abort_unless(
            auth()->check()
            && in_array(
                auth()->user()->nivel_acesso,
                [
                    'admin',
                    'gerente',
                ],
                true
            ),
            403
        );

        return view('bi.index');
    }
}