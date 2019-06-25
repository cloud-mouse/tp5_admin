<?php
namespace app\admins\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 首页
 */
class Home extends BaseAdmin
{
	// 首页
	public function index(){

		$menu=false;
		// 获取角色权限
		$role=$this->db->table('admin_groups')->where(array('gid'=>$this->_admin['gid']))->item();
		if($role){
			$role['rights']= (isset($role['rights']) && $role['rights'])? json_decode($role['rights'],true): [];
		}
		// 查询角色菜单
		if($role['rights']){
			$where= 'mid in('.implode(',' ,$role['rights']).') and ishidden=0 and status=0';
			$menus=$this->db->table('admin_menus')->where($where)->cates('mid');
			$menus && $menus=$this->gettreeItems($menus);
		}
		// 加载网站设置
		$site=$this->db->table('sites')->where(array('sitename'=>'site'))->item();
		$this->assign('site',$site);
		// 处理菜单结构
		$this->assign('role',$role);

		// dump($menus);
		$this->assign('menus',$menus);
		return $this->fetch();

	}

	// 私有方法
	private function gettreeItems($items){
		$tree=array();
		foreach ($items as $item) {
			if(isset($items[$item['pid']])){
				$items[$item['pid']]['children'][]=&$items[$item['mid']];

			}else{
				$tree[]=&$items[$item['mid']];
			}
		}
		return $tree;
	}

	//欢迎页面
	public function welcome(){
		return $this->fetch();

	}
}
