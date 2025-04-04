<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthenticateController extends Controller
{
    public function staffProfile()
    {
        return view('staff.auth.staff-profile',[
            'title'=>'My Profile',
        ]);
    }
}
