<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Retorna a view do dashboard
        return view('dashboard.index');
    }
}
