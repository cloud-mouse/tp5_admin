<?php
namespace app\index\controller;
use think\Controller;
use Util\data\Sysdb;

use linepay\AliPay; //支付宝支付

/**
 * 用户管理
 */
class Users extends BaseAdmin
{
	
	public function index(){
		// 获取会员表中的会员信息
		$data['users'] = $this->db->table('users')->where(array('id'=>$this->_user['id']))->item();
		$data['level'] = $this->db->table('user_level')->where(array('id'=>$this->_user['lid']))->item();
		$data['ad'] = $this->db->table('slide')->where(array('id'=> 10))->item();
		$data['pagesize']= 15;
		$data['goods'] = $this->db->table('goods')->pages($data['pagesize']);
		$data['custumer'] = $this->db->table('custumers')->where(array('uid'=> $this->_user['id']))->pages($data['pagesize']);
		$data['consume'] = $this->db->table('consume')->where(array('uid'=> $this->_user['id']))->pages($data['pagesize']);
		$data['recharge'] = $this->db->table('recharge')->where(array('uid'=> $this->_user['id'],'status'=>1))->pages($data['pagesize']);
		
		$this->assign('data',$data);
		
		return $this->fetch();
	}
	public function  echartsData(){
		$echarts = $this->db->table('echarts_data')->where(array('uid'=> $this->_user['id']))->lists();
		exit(json_encode(array('code'=>0,'msg'=>'请求成功','data'=>$echarts)));
	}

	//添加会员信息
	public function recharge(){
		$id= (int)input('get.id');
		//加载会员
		$data['item'] = $this->db->table('users')->where(array('id'=>$id))->item();

		$this->assign('data',$data);
		return $this->fetch();
		
	}
	
	//确认充值
	public function save(){
		$id = (int)input('post.id'); //代理商账户id
		$data['uid'] = (int)input('post.id');
		$data['uname'] = trim(input('post.username'));
		$data['payway'] = (int)input('post.payway');
		$data['amounts'] = input('post.amounts');
		$data['order_id'] = date('YmdHis');
		$data['status'] = 0;
		$data['add_time']= time();

		// dump($data);
		if($data['payway']!=1){
			$data['principal'] = input('post.amounts');
		}
		
		$res=true;
		if ($data['amounts']==0) {
			exit(json_encode(array('code'=>1,'msg'=>'请输入正确的金额')));
		}else{
			// 进行支付逻辑
			// 支付宝支付
			if($data['payway']==2){
				$alipay = AliPay::ComputerPay($data['amounts'],$data['order_id'],'猴币充值');
					if($alipay[0] == true) {
						$this->db->table('recharge')->insert($data);
						return $alipay[1];
					}else{
						var_dump($alipay[1]);
						return '发起支付请求失败';
					}
			}
			// 微信支付
			if($data['payway']==3){
				require_once APP_PATH.'common/WxpayAPI/lib/WxPay.Api.php';
				$input = new \WxPayUnifiedOrder();
				//设置商品描述
				$input->SetBody('猴币充值');
				//设置订单号
				$input->SetOut_trade_no(date('YmdHis'));

				//设置订单金额<这里是以分为单位>
				$input->SetTotal_fee((int)input('post.amounts')*100);
				//设置异步通知地址
				$input->SetNotify_url('http://localhost/index.php/index/Notify/index');
				//设置交易类型
				$input->SetTrade_type('NATIVE');
				//设置商品ID(这里设置一个假商品id)
				$input->SetProduct_id('1');
				//调用统一下单ApI
				$result=\WxPayAPI::unifiedOrder($input);
				// dump($result);
				// 生成图片二维码
				$code_url=$result['code_url'];
				
				$this->db->table('recharge')->insert($data);
				exit(json_encode(array('code'=>0,'msg'=>'请扫码支付','data'=>$data,'result'=>$result)));
				
				
			}
		}
	}
	
	//
	public function payResult($code_url,$order_id){
		$order_id= $order_id;
		$code_url=$code_url;
		$this->assign('order_id',$order_id);
		$this->assign('code_url',$code_url);
		return $this->fetch('payResult');
	}
	
	//订单查询结果
    public function orderstate()
    {
        error_reporting(E_ERROR);
        ini_set('date.timezone','Asia/Shanghai');
        require_once APP_PATH.'common/WxpayAPI/lib/WxPay.Api.php';

        if(isset($_REQUEST["transaction_id"]) && $_REQUEST["transaction_id"] != ""){
            $transaction_id = $_REQUEST["transaction_id"];
            $input = new \WxPayOrderQuery();
            $input->SetTransaction_id($transaction_id);
            echo json_encode(\WxPayApi::orderQuery($input));
            exit();
        }

        if(isset($_REQUEST["order_id"]) && $_REQUEST["order_id"] != ""){
            $order_id = $_REQUEST["order_id"];
            $input = new \WxPayOrderQuery();
            $input->SetOut_trade_no($order_id);
			$result = \WxPayApi::orderQuery($input);
			if($result['trade_state_desc']=="支付成功"){
				$data['users'] = $this->db->table('users')->where(array('id'=>$this->_user['id']))->item();
				$data['users']['balance'] = $data['users']['balance']+ (int)$result['total_fee']/100;
				$this->db->table('users')->where(array('id'=>$this->_user['id']))->update($data['users']);
				
				$data['recharge'] = $this->db->table('recharge')->where(array('order_id'=>$result['out_trade_no']))->item();
				$data['recharge']['status'] = 1;
				$this->db->table('recharge')->where(array('order_id'=>$result['out_trade_no']))->update($data['recharge']);
			}
            echo json_encode(\WxPayApi::orderQuery($input));
            exit();
        }
    }
	
	// 修改信息
	public function changeInfo(){
		
		$data['users'] = $this->db->table('users')->where(array('id'=>$this->_user['id']))->item();
		$this->assign('data',$data);
		return $this->fetch('change_info');
		
	}
	
	// 保存修改
	public function doChange(){
		$id = (int)input('post.id');
		$password = trim(input('post.pwd'));
		$data['phone'] = trim(input('post.phone'));
		$data['truename'] = trim(input('post.truename'));
		
		// dump($data);
		if($id==0 && !$password){
			exit(json_encode(array('code'=>1,'msg'=>'密码不能为空')));
		}
		if(!$data['truename']){
			exit(json_encode(array('code'=>1,'msg'=>'真实姓名不能为空')));
		}
		
		if($password){
			//密码处理
			$data['password']= md5($data['username'].$password);
		}
		
		$this->db->table('users')->where(array('id'=>$id))->update($data);
		
		exit(json_encode(array('code'=>0,'msg'=>'修改成功')));
	}
	

}