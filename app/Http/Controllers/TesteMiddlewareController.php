<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TesteMiddlewareController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkNivel:admin,gerente');
    }

    public function index()
    {
        return "Middleware funcionando via controller! ✅";
    }
}
