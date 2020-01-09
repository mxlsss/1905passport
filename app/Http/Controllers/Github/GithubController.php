<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GithubController extends Controller
{
   public function pash(){

        $pash= 'cd /wwwroot/1905passport && git pull';
       shell_exec($pash);


   }


}
