<?php
namespace app\index\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 账号管理
 */
class Account extends Controller
{
	
	public function login(){

		return $this->fetch();

	}

	//管理员登录
	public function dologin(){
		$username = trim(input('post.username'));
		$pwd = trim(input('post.pwd'));
		$verifycode = trim(input('post.verifycode'));

		if($username == ''){
			exit(json_encode(array('code'=>1,'msg'=>'用户名不能为空')));
		}
		if($pwd == ''){
			exit(json_encode(array('code'=>1,'msg'=>'密码不能为空')));
		}
		if($verifycode == ''){
			exit(json_encode(array('code'=>1,'msg'=>'验证码不能为空')));
		}

		//验证验证码
		if(!captcha_check($verifycode)){
			exit(json_encode(array('code'=>1,'msg'=>'验证码错误')));
		}

		//验证用户
		$this->db = new Sysdb;
		$user = $this->db->table('users')->where(array('username'=>$username))->item();

		if(!$user){

			exit(json_encode(array('code'=>1,'msg'=>'用户不存在')));

		}else if(md5($user['username'].$pwd) != $user['password']){
			
			exit(json_encode(array('code'=>1,'msg'=>'密码错误')));

		}else if($user['status']==1){
			
			exit(json_encode(array('code'=>1,'msg'=>'用户已被禁用')));
		}
		else{
			//设置用户session
			session('user',$user);
			exit(json_encode(array('code'=>0,'msg'=>'登录成功')));
		}	
	}

	// 退出登录
	public function logout(){
		session('user',null);
		exit(json_encode(array('code'=>0,'msg'=>'退出成功')));
	}
}
