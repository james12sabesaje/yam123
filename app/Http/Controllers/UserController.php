<?php

namespace App\Http\UserControllers;
namespace App\Http\Controllers\Auth;


use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard()
    {
        return view('user.dashboard'); // or your actual dashboard view
    }
}
