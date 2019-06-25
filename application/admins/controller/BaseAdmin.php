<?php
namespace app\admins\controller;
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
		$this->_admin = session('admin');
		//未登录用户不能访问后台页面
		if(!$this->_admin){
			header('Location:/admins.php/admins/Account/login');
			exit;
		}

		$this->assign('admin',$this->_admin);
		
		// 使用数据库连接方法
		$this->db = new Sysdb;
	}
}