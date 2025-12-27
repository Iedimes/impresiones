<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests, ValidatesRequests;
}
