<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{

    public function test ()
    {
//        $array = ['a', 'b', 'c'];
//        $x = '';
//        foreach ($array as $value) {
//            $x = $value;
//
//            if($value === 'b'){
//                break;
//            }
//        }
//        $x = in_array('b', $array) ? 'b' : 'false' ;
//        return $x;

//        $color = 'blue';
//       switch($color){
//           case 'red' :
//           echo 'color red';
//           break;
//           case 'green' :
//           echo 'color green';
//           break;
//           case 'blue' :
//           echo 'color blue';
//           break;
//           default :
//           echo 'color';
//       }
        $user = User::first();
        $user->notify(new \App\Notifications\UserRegisteredNotification());
    }

}
