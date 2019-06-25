<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use Util\data\Sysdb;

/**
 * 财务管理
 */
class Financial extends BaseAdmin
{
	
	public function consume(){
		// 获取消费记录表中信息
		$data['pagesize']= 15;
		$data['page']=max(1,(int)(input('get.page')));
		$data['wd']=trim(input('get.wd'));
		$where=array();
		$where = 'concat(id,cname,cphone) like "%'.$data['wd'].'%" and uid = "'.$this->_user['id'].'"';
		$data['data'] = $this->db->table('consume')->where($where)->order('id desc')->pages($data['pagesize']);
		$data['goods']= $this->db->table('goods')->cates('id');
		$this->assign('data',$data);
		return $this->fetch();
	}

	// 获取充值记录表信息
	public function recharge(){
		// 获取充值记录列表
		$data['pagesize']= 15;
		$data['page']=max(1,(int)(input('get.page')));
		$data['wd']=trim(input('get.wd'));
		$where=array();
		$where = 'concat(uid,uname,id) like "%'.$data['wd'].'%" and uid = "'.$this->_user['id'].'" and status="1"';
		$data['data'] = $this->db->table('recharge')->where($where)->order('id desc')->pages($data['pagesize']);
		$this->assign('data',$data);
		return $this->fetch();
	}
	
}