<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function findUserName($value){
    $data = [];
    if(filter_var($value, FILTER_VALIDATE_EMAIL)){
        $data['type'] = 'email';
        $data['regex'] = 'email';
    } else {
        $data['type'] = 'uuid';
        $data['regex'] = 'regex:/^(MBZ|mbz)[0-9]{7}$/'; // ✅ Corrected regex
    }
    return $data;
}

}
