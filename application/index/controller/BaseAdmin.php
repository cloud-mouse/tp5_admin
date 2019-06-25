<?php
namespace app\index\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 
 */
class BaseAdmin extends Controller
{
	
	public function __construct()
	{
		parent::__construct();
		$this->_user = session('user');
		//未登录用户不能访问后台页面
		if(!$this->_user){
			header('Location:/index.php/index/Account/login');
			exit;
		}

		$this->assign('user',$this->_user);
		
		// 使用数据库连接方法
		$this->db = new Sysdb;
	}
}