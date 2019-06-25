<?php
namespace app\admins\controller;
use think\Controller;
use think\Request;
use Util\data\Sysdb;

/**
 * 财务管理
 */
class Financial extends BaseAdmin
{
	
	public function consume(){
		// 获取消费记录表信息
		$data['pagesize']= 15;
		$data['page']=max(1,(int)(input('get.page')));
		$data['wd']=trim(input('get.wd'));
		$where=array();
		$data['wd'] && $where = 'concat(uid,uname,id,uphone,cname,cphone) like "%'.$data['wd'].'%"';
		$data['data'] = $this->db->table('consume')->where($where)->order('id desc')->pages($data['pagesize']);

		$data['goods']= $this->db->table('goods')->cates('id');
		
		$this->assign('data',$data);
		return $this->fetch();
	}

	// 获取充值记录表信息
	public function recharge(){
		// 获取商品列表
		$data['pagesize']= 15;
		$data['page']=max(1,(int)(input('get.page')));
		$data['wd']=trim(input('get.wd'));
		$where=array();
		$where = 'concat(uid,uname,id) like "%'.$data['wd'].'%" and status="1"';
		$data['data'] = $this->db->table('recharge')->where($where)->order('id desc')->pages($data['pagesize']);
		
		$this->assign('data',$data);
		return $this->fetch();
	}
	 //修改消费状态
	public function changeStatus(){
		$id = (int)input('post.id');
		$data = $this->db->table('consume')->where(array('id'=>$id))->item();
	
		if($data['status']==1){
			$data['status'] = 0;
		}else{
			$data['status'] = 1;
		}
		$this->db->table('consume')->where(array('id'=>$id))->update($data);
		exit(json_encode(array('code'=>0,'msg'=>'修改成功')));
	}
}