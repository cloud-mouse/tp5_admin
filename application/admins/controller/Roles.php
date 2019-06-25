<?php
namespace app\admins\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 角色管理
 */
class Roles extends BaseAdmin
{
	//角色列表
	public function index(){
		$data['roles']=$this->db->table('admin_groups')->lists();
		$this->assign('data',$data);
		return $this->fetch();
	}

	//添加角色
	public function add(){
		$gid= (int)input('get.gid');
		$role = $this->db->table('admin_groups')->where(array('gid'=>$gid))->item();
		$role && $role['rights'] && $role['rights']= json_decode($role['rights']);
		$this->assign('role',$role);

		//加载菜单
		$menus_list = $this->db->table('admin_menus')->where(array('status'=>0))->cates('mid');
		$menus=$this->gettreeItems($menus_list);
		$results=array();
		foreach ($menus as $value) {
			$value['children']=isset($value['children'])? $this->formatMenus($value['children']): false;
			$results[]=$value;
		}
		$this->assign('menus',$results);
		// 编辑数据
		
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

	// 格式化菜单
	private function formatMenus($items,&$res=array()){
		foreach ($items as $item) {
			if(!isset($item['children'])){
				$res[]=$item;

			}else{
				$tem=$item['children'];
				unset($item['children']);
				$res[]=$item;
				$this->formatMenus($tem,$res);
			}
		}
		return $res;
	}

	//保存角色
	public function save(){
		$gid = (int)input('post.gid');
		$data['title'] = trim(input('post.title'));
		$menus=input('post.menu/a');
		
		// dump($data);
		if(!$data['title']){
			exit(json_encode(array('code'=>1,'msg'=>'角色名不能为空')));
		}

		$res=true;
		$menus && $data['rights'] = json_encode(array_keys($menus));
		if ($gid==0) {
			# //检查角色是否存在
			$item= $this->db->table('admin_groups')->where(array('title'=>$data['title']))->item();
			if($item){
				exit(json_encode(array('code'=>1,'msg'=>'该角色已存在')));
			}else{
				//保存角色,返回结果
				$res=$this->db->table('admin_groups')->insert($data);
			}
		}else{
			$this->db->table('admin_groups')->where(array('gid'=>$gid))->update($data);
		}
		
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'保存失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'保存成功')));
		}
	}

	// 删除角色
	public function delete(){
		$gid = (int)input('post.gid');
		$res=$this->db->table('admin_groups')->where(array('gid'=>$gid))->delete();
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'删除失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'删除成功')));
		}
	}
}