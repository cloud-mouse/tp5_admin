<?php
namespace app\index\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 客户管理
 */
class Customer extends BaseAdmin
{
	
	//客户列表
	public function index(){
	
		// 获取表中信息
		$data['pagesize']= 15;
		$data['page']=max(1,(int)(input('get.page')));
		$data['wd']=trim(input('get.wd'));
		$where=array();
		$where = 'concat(cname,phone,company) like "%'.$data['wd'].'%" and uid = "'.$this->_user['id'].'"';
		$data['data'] = $this->db->table('custumers')->where($where)->pages($data['pagesize']);

		$this->assign('data',$data);
		return $this->fetch();
		

	}


	// 删除客户
	public function del(){
		$id = (int)input('post.id');
		$res=$this->db->table('custumers')->where(array('id'=>$id))->delete();
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'删除失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'删除成功')));
		}
	}
}