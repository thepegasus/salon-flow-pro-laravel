<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SuperAdminDashboardController extends Controller
{
    public function index(): Response
    {
        return response('Super admin dashboard');
    }
}
