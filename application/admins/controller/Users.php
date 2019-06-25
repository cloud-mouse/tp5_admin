<?php
namespace app\admins\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 用户管理
 */
class Users extends BaseAdmin
{
	
	public function index(){

		// 获取代理商表中信息
		$data['lists'] = $this->db->table('users')->lists();

		$data['level'] = $this->db->table('user_level')->cates('id');
		$this->assign('data',$data);

		return $this->fetch();
	}

	//添加代理商信息
	public function add(){
		$id= (int)input('get.id');
		//加载代理商
		$data['item'] = $this->db->table('users')->where(array('id'=>$id))->item();

		// 获取user_level表中数据
		$data['level'] = $this->db->table('user_level')->cates('id');
		$this->assign('data',$data);
		return $this->fetch();
		
	}
	//保存代理商
	public function save(){
		$id = (int)input('post.id');

		$data['username'] = trim(input('post.username'));
		$password = trim(input('post.pwd'));
		$data['lid'] = (int)input('post.lid');
		$data['phone'] = trim(input('post.phone'));
		$data['balance'] = (int)input('post.balance');
		$data['truename'] = trim(input('post.truename'));
		$data['status'] = (int)(input('post.status'));

		// dump($data);
		if(!$data['username']){
			exit(json_encode(array('code'=>1,'msg'=>'用户名不能为空')));
		}
		if($id==0 && !$password){
			exit(json_encode(array('code'=>1,'msg'=>'密码不能为空')));
		}
		if(!$data['lid']){
			exit(json_encode(array('code'=>1,'msg'=>'用户等级不能为空')));
		}
		if(!$data['truename']){
			exit(json_encode(array('code'=>1,'msg'=>'真实姓名不能为空')));
		}

		if($password){
			//密码处理
			$data['password']= md5($data['username'].$password);
		}

		$res=true;
		if ($id==0) {
			# //检查用户是否存在
			$item= $this->db->table('users')->where(array('username'=>$data['username']))->item();
			if($item){
				exit(json_encode(array('code'=>1,'msg'=>'该用户已存在')));
			}else{
				$data['add_time']= time();
				//保存用户,返回结果
				$res=$this->db->table('users')->insert($data);
			}
		}else{
			$this->db->table('users')->where(array('id'=>$id))->update($data);
		}
		
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'保存失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'保存成功')));
		}
	}

	// 删除代理商
	public function delete(){
		$id = (int)input('post.id');
		$res=$this->db->table('users')->where(array('id'=>$id))->delete();
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'删除失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'删除成功')));
		}
	}

	// 代理商等级
	public function level(){

		$data['level'] = $this->db->table('user_level')->cates('id');
		$this->assign('data',$data);
		return $this->fetch();
	}

	// 添加代理商等级
	public function addLevel(){
		$id= (int)input('get.id');
		//加载代理商
		$data['item'] = $this->db->table('user_level')->where(array('id'=>$id))->item();
		
		// 获取user_level表中数据
		$data['level'] = $this->db->table('user_level')->cates('id');
		$this->assign('data',$data);
		return $this->fetch('addLevel');
	}


	//保存代理商等级
	public function saveLevel(){
		$id = (int)input('post.id');

		$data['title'] = trim(input('post.title'));
		$data['level'] = (int)input('post.level');
		$data['desc'] = trim(input('post.desc'));

		if(!$data['title']){
			exit(json_encode(array('code'=>1,'msg'=>'代理商等级不能为空')));
		}
		$res = true;
		if ($id==0) {
			# //检查等级是否存在
			$item= $this->db->table('user_level')->where(array('title'=>$data['title']))->item();
			if($item){
				exit(json_encode(array('code'=>1,'msg'=>'该代理商等级已存在')));
			}else{
				$res=$this->db->table('user_level')->insert($data);
			}
		}else{
			$this->db->table('user_level')->where(array('id'=>$id))->update($data);
		}
		
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'保存失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'保存成功')));
		}
	}
	
	//代理商充值页面
	public function recharge(){
		$id= (int)input('get.id');
		//获取当前代理商信息
		$data['item'] = $this->db->table('users')->where(array('id'=>$id))->item();
		
		
		$this->assign('data',$data);
		return $this->fetch();
	}

	//保存
	public function saveRecharge(){
		$id = (int)input('post.userid');
		
		$data['uid'] = $id;
		$data['uname'] = trim(input('post.username'));
		$data['order_id'] = date('YmdHis');
		$data['amounts'] = (int)input('post.principal') + (int)input('post.bonus');
		$data['principal'] = (int)input('post.principal');
		$data['bonus'] = (int)input('post.bonus');
		$data['payway'] = (int)input('post.payway');
		$data['status'] = 1;
		$data['add_time'] = time();
		
		// 获取用户表的信息
		$user = $this->db->table('users')->where(array('id'=>$id))->item();
		// 计算用户表的余额变化
		$user['balance'] = $user['balance'] + (int)input('post.principal') + (int)input('post.bonus');
		
		
		$res=$this->db->table('recharge')->insert($data);
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'重值失败')));
		}else{
			$this->db->table('users')->where(array('id'=>$id))->update($user);
			exit(json_encode(array('code'=>0,'msg'=>'充值成功')));
		}
		
	}
}