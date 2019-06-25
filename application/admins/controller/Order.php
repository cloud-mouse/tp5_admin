<?php
namespace app\admins\controller;
use think\Controller;
use think\Request;
use Util\data\Sysdb;

/**
 * 订单管理
 */
class Order extends BaseAdmin
{
	
	public function index(){
		// 获取订单列表
		$data['pagesize']= 15;
		$data['page']=max(1,(int)(input('get.page')));
		$data['wd']=trim(input('get.wd'));
		$where=array();
		$data['wd'] && $where = 'concat(uid,uname,uphone,cname,cphone) like "%'.$data['wd'].'%"';
		$data['data'] = $this->db->table('order')->where($where)->order('id desc')->pages($data['pagesize']);

		$data['goods']= $this->db->table('goods')->cates('id');
		$data['users']= $this->db->table('users')->cates('id');
		$data['custumers']= $this->db->table('custumers')->cates('id');
		
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	//查看订单
	public function check(){
		$id= (int)input('get.id');
		// 查看订单表信息
		$data['item'] = $this->db->table('order')->where(array('id'=>$id))->item();
		
		// 查询订单id对应相关的信息
		$data['goods']= $this->db->table('goods')->where(array('id'=>$data['item']['gid']))->item();
		$data['users']= $this->db->table('users')->where(array('id'=>$data['item']['uid']))->item();
		$data['custumers']= $this->db->table('custumers')->where(array('id'=>$data['item']['cid']))->item();
	
		$this->assign('data',$data);
		
		return $this->fetch();
		
	}



	// 删除订单
	public function del(){
		$id = (int)input('post.id');
		$res=$this->db->table('order')->where(array('id'=>$id))->delete();
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'删除失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'删除成功')));
		}
	}


    //改变上下架状态
    public function changeStatus(){
    	$id = (int)input('post.id');
    	$data = $this->db->table('order')->where(array('id'=>$id))->item();

    	if($data['status']==1){
    		$data['status'] = 0;
    	}else{
    		$data['status'] = 1;
    	}
    	$this->db->table('order')->where(array('id'=>$id))->update($data);
    	exit(json_encode(array('code'=>0,'msg'=>'修改成功')));
    }
}