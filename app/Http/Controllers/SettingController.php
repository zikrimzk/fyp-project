<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function facultySetting()
    {
        try{
            return view('staff.setting.faculty-setting',[
                'title' => 'Faculty Setting'
            ]);
        }
        catch(Exception $e){
            return abort(404);
        }

    }
}
