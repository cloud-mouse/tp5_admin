<?php
namespace app\index\controller;
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
		$site=$this->db->table('sites')->where(array('sitename'=>'site'))->item();
		$this->assign('site',$site);
		// 用户菜单
		$menus = [
              [
				  'title' => '账号管理', 
				  'children' => [
					  [
					  'title' => '账号信息',
					   'controller' => 'Users',
						'method' =>  'index',
					  ],
				  ]
			  
			  ],
			  [
				  'title' => '财务管理', 
				  'children' => [
					  [
					  'title' => '交易明细',
					   'controller' => 'Financial',
						'method' =>  'consume',
					  ],
					  [
					  'title' => '充值记录',
					   'controller' => 'Financial',
						'method' =>  'recharge',
					  ],
				  ]
			  
			  ],
			  [
				  'title' => '客户管理', 
				  'children' => [
					  [
					  'title' => '客户列表',
					  'controller' => 'Customer',
						'method' =>  'index',
					  ],
				  ]
			  
			  ],
			  [
				  'title' => '商品市场', 
				  'children' => [
					  [
					  'title' => '商品列表',
					  'controller' => 'Goods',
						'method' =>  'index',
					  ],
				  ]
			  
			  ],
            ];
		
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
