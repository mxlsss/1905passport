<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\UserModel;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
    class GithubController extends Controller
    {
       public function pash(){

            $pash= 'cd /www/1905passport && git pull';
           shell_exec($pash);

       }

       public function onelist(){
            $token=$_SERVER['HTTP_TOKEN'];
       }

        //注册
        public function reg(Request $request){
    //        echo "123";die;
            $password2 = $request->input('password2');
    //           echo $password2;die;
            if($request->input('password')!=$password2){
                echo "两次密码不一致";die;
            };

            $data=[
                'name'=> $request->input('name'),
                'email'=> $request->input('email'),
                'tel'=> $request->input('tel'),
                'appid'=> $request->input('appid'),
                'password'=>  password_hash($request->input('password'),PASSWORD_BCRYPT),
                'last_login'    => time(),
                'last_ip'       => $_SERVER['REMOTE_ADDR'],     //获取远程IP
            ];
            $aaa=UserModel::insertGetId($data);
            if($aaa){
                $response = [
                    'errno' => 400000,
                    'msg'   => '注册成功'
                ];
            }
            return $response;

        }

        //登陆
        public function login(Request $request){
            $data = $request->input();
    //        dd($data);
            $password = $request->input('password');
            if(strpos($data['account'],'@')){
                $where=['email'=>$data['account']];
            }else{
                $where=['tel'=>$data['account']];
            }
            $u = UserModel::where($where)->first();
    //        dd($u);
            if($u){
                //验证密码
                if( password_verify($password,$u->password) ){
                    // 登录成功
                    //echo '登录成功';
                    //生成token
                    $redis_key='token:user:appid:'.$u['appid'];
                    $redis_val=md5(time().$u['p_id'].$u['name']);
                    Redis::set($redis_key,$redis_val);
                    Redis::expire($redis_key,60480);
                    $response = [
                        'errno' => 0,
                        'state'=> '登陆成功',
                        'msg'   => 'ok',
                        'data'  => [
                            'appid'=>$u['appid'],
                            'token' => $redis_val
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
                    'msg'   => '用户不存在'];
            }
            return $response;
        }


        public function getuserinfo(){
            $data=$_POST;
            dd($data);
            if(strpos($data['account'],'@')){
                $where=['email'=>$data['account']];
            }else{
                $where=['tel'=>$data['account']];
            }
            $info=UserModel::where($where)->first();
            if(!$info){
                $error1=json_encode(['errorcode'=>'0009','errmsg'=>'account或token有误1'],JSON_UNESCAPED_UNICODE);
                echo $error1;exit;
            }
//        $redis_key='token:user:id:'.$info['appid'];
            $redis_key='token:user:appid:'.$info['appid'];
            $redis_token=Redis::get($redis_key);
            $data_token=$_SERVER['HTTP_TOKEN'];
            if($redis_token!=$data_token){
                $error1=json_encode(['errorcode'=>'0009','errmsg'=>'account或token有误2'],JSON_UNESCAPED_UNICODE);
                echo $error1;exit;
            }
            unset($info['p_id']);
            unset($info['password']);
            echo json_encode($info->toarray(),JSON_UNESCAPED_UNICODE);
        }

        //鉴权
        /**
         * 接口鉴权
         */
        public function auth()
        {
            $appid = $_POST['appid'];
            $token = $_POST['token'];

            if(empty($_POST['appid']) || empty($_POST['token'])){
                $response = [
                    'errno' => 40003,
                    'msg'   => 'Need token or uid'
                ];
                return $response;
            }

            $redis_key='token:user:appid:'.$appid;
            //验证token是否有效
            $cache_token = Redis::get($redis_key);

            if($token==$cache_token)        // token 有效
            {
                $response = [
                    'errno' => 0,
                    'msg'   => 'ok'
                ];
            }else{
                $response = [
                    'errno' => 40003,
                    'msg'   => 'Token Not Valid!'
                ];
            }
            return $response;
        }

        //验签
        public function yq(){

        echo '<pre>';print_r($_GET);echo '</pre>';

        $key = "mxl";          // 计算签名的KEY 与发送端保持一致

        //验签
        $data = $_GET['data'];  //接收到的数据
        $signature = $_GET['signature'];    //发送端的签名

        // 计算签名
        echo "接收到的签名：". $signature;echo '</br>';
        $s = md5($data.$key);
        echo '接收端计算的签名：'. $s;echo '</br>';

        //与接收到的签名 比对
        if($s == $signature)
        {
            echo "验签成功";
        }else{
            echo "验签失败";
        }

        }


        //post 验签
        public  function  yq2(){
            $key = "1905mxl";      // 计算签名的key

            echo '<pre>';print_r($_POST);
            //接收数据
            $json_data = $_POST['data'];
            //签名
            $sign = $_POST['sign'];

            //计算签名
            $sign2 = md5($json_data.$key);
            echo "接收端计算的签名：".$sign2;echo "<br>";

            // 比较接收到的签名
            if($sign2==$sign){
                echo "验签成功";
            }else{
                echo "验签失败";
            }
        }

        //公钥私钥 解密
        public function jiemi(){
            $data=$_GET['data'];
            echo '<pre>原值:';print_r($data);echo'</pre>';
            $ent_data=base64_decode($data);
            echo '<pre>ba64和url解码码后';print_r($ent_data);echo'</pre>';
            $key=file_get_contents(storage_path('keys/pub.key'));
            openssl_public_decrypt($ent_data,$data,$key);
            echo '<pre>';print_r("解密后原文:".$data);echo'</pre>';
        }


        //对称解密
        public  function jiemi2(){
            $data=$_GET['data'];
            echo '<pre>base64解密前:';print_r($data);echo'</pre>';
            $data=base64_decode($data);
            echo '<pre>aes解密前';print_r($data);echo'</pre>';
            $method='AES-256-CBC';
            $key='1905';
            $iv='qqqwerdhryfjguth';

            $ent_data=openssl_decrypt($data,$method,$key,OPENSSL_RAW_DATA,$iv);

            $json_data=json_decode($ent_data);
            echo '<pre>解密:';print_r($json_data);echo'</pre>';
        }


    }
