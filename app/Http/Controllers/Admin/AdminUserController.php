<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminUserController extends Controller
{
    public function profile()
    {
        return view('admins.profile.show', [
            'request' => request(),
            'user' => request()->user('admin'),
        ]);
    }
}
