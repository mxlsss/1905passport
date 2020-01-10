<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\UserModel;
use Illuminate\Support\Str;
class GithubController extends Controller
{
   public function pash(){

        $pash= 'cd /wwwroot/1905passport && git pull';
       shell_exec($pash);

   }

    public function reg(Request $request){
//        echo "$request->input()";
        $password2 = $request->input('password2');

        if($request->input('password')!=$password2){
            echo "两次密码不一致";die;
        };

        $data=[
            'name'=> $request->input('name') ,
            'email'=> $request->input('email') ,
            'tel'=> $request->input('tel') ,
            'password'=>  password_hash($request->input('password'),PASSWORD_BCRYPT),
            'last_login'    => time(),
            'last_ip'       => $_SERVER['REMOTE_ADDR'],     //获取远程IP
        ];
        echo '<pre>';print_r($data);echo'</pre>';

        $aaa=UserModel::insertGetId($data);
        if($aaa){
            echo "注册成功";
        }
    }

    public function login(Request $request){

        $name = $request->input('name');
        $password = $request->input('password');
        $u = UserModel::where(['name'=>$name])->first();
        if($u){
            //验证密码
            if( password_verify($password,$u->password) ){
                // 登录成功
                //echo '登录成功';
                //生成token
                $token = Str::random(32);
                $response = [
                    'errno' => 0,
                    'state'=> '登陆成功',
                    'msg'   => 'ok',
                    'data'  => [
                        'token' => $token
                    ]
                ];
            }else{
                $response = [
                    'errno' => 400003,
                    'msg'   => '密码不正确'
                ];
            }
        }else{
            $response = [
                'errno' => 400004,
                'msg'   => '用户不存在'
            ];
        }
        return $response;
    }

}
