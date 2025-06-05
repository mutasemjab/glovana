<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Clas; // Replace with your actual class model name
use App\Models\Driver;
use App\Models\Provider;

class DashboardController extends Controller
{
    public function index()
    {
        $usersCount = User::count();
        $driversCount = Provider::count();
    
        return view('admin.dashboard', compact('usersCount', 'driversCount',));
    }
}
