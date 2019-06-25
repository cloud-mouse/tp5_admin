<?php
namespace app\admins\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 菜单管理
 */
class Menu extends BaseAdmin
{
	//菜单列表
	public function index(){
		$pid = (int)input('get.pid');
		// 获取admins表中所有数据
		$this->db = new Sysdb;
		$data['lists'] = $this->db->table('admin_menus')->where(array('pid'=>$pid))->lists();
		
		// 返回上一级
		$backid=0;
		if($pid>0){
			$parent = $this->db->table('admin_menus')->where(array('mid'=>$pid))->item();
			$backid=$parent['pid'];
		}
		// 放到模板里面去
		$this->assign('pid',$pid);
		$this->assign('backid',$backid);
		$this->assign('data',$data);

		return $this->fetch();

	}

	//保存菜单
	public function save(){
		$pid=(int)input('post.pid');
		$oids=input('post.oids/a');
		$titles=input('post.titles/a');
		$controller=input('post.controller/a');
		$methods=input('post.methods/a');
		$ishiddens=input('post.ishiddens/a');
		$status=input('post.status/a');

		foreach ($oids as $key => $value) {
			//新增菜单
			$data['pid']=$pid;
			$data['oid']=$value;
			$data['title']=$titles[$key];
			$data['controller']=$controller[$key];
			$data['method']=$methods[$key];
			$data['ishidden']= isset($ishiddens[$key]) ? 1:0;
			$data['status']=isset($status[$key])? 1 : 0;

			if($key==0 && $data['title']){
				// 新增
				$this->db->table('admin_menus')->insert($data);
			}
			if($key>0){
				if($data['title']=='' && $data['controller']=='' && $data['method']==''){
					//删除
					$this->db->table('admin_menus')->where(array('mid'=>$key))->delete();
				}else{
					//修改
					$this->db->table('admin_menus')->where(array('mid'=>$key))->update($data);
				}
			}
		}
		exit(json_encode(array('code'=>0,'msg'=>'保存成功')));
	}

}
