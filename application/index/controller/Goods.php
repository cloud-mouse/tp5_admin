<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use Util\data\Sysdb;

/**
 * 影片管理
 */
class Goods extends BaseAdmin
{
	
	public function index(){
		// 获取商品列表
		$data['lists']=$this->db->table('goods')->where(array('status'=>1))->cates('id');
		$data['classfy']= $this->db->table('goods_type')->cates('id');
		$results=array();
		foreach($data['classfy'] as $class){
			$class['list']=$this->db->table('goods')->where(array('status'=>1,'pid'=>$class['id']))->lists();
			$results[]=$class;
		}
		$this->assign('data',$results);
		$this->assign('menu',$data['classfy']);
		return $this->fetch();
	}
	
	//购买商品
	public function goodsClick(){
		$id= (int)input('get.id');
		$data['item'] = $this->db->table('goods')->where(array('id'=>$id))->item();
		$data['users'] = $this->db->table('users')->where(array('id'=>$this->_user['id']))->item();
		// 查询等级
		$data['level'] = $this->db->table('user_level')->where(array('id'=>$this->_user['lid']))->item();
		
		$peice= $data['item']['price'] * $data['level']['level'] /10;
	
		// 获取分类
		$data['classfy']= $this->db->table('goods_type')->lists();
		$this->assign('classfy',$data['classfy']);
	
		$this->assign('data',$data);
		$this->assign('price',$peice);
		
		return $this->fetch('goods_click');
		
	}
	
	// 确认支付
	public function dopay(){
		$data['gid'] =(int)(input('post.gid'));
		$data['item'] = $this->db->table('goods')->where(array('id'=>$data['gid']))->item();
		$data['users'] = $this->db->table('users')->where(array('id'=>$this->_user['id']))->item();
		$data['price'] = trim(input('post.price'));
		$stock = (int)(input('post.stock'));
		$item= $this->db->table('custumers')->where(array('cname'=>trim(input('post.cname'))))->item();
		
		//判断用户的钱够不够
		if($data['users']['balance']<trim(input('post.goodsPrice'))){
			exit(json_encode(array('code'=>1,'msg'=>'购买失败,您的猴币余额不足。')));
		}
		if($item==''){
			$custumer['uid'] = $data['users']['id'];
			$custumer['uname'] =  $data['users']['username'];
			$custumer['cname'] = trim(input('post.cname'));
			$custumer['company'] = trim(input('post.company'));
			$custumer['phone'] = trim(input('post.cphone'));
			$custumer['add_time'] = time();
			$this->db->table('custumers')->insert($custumer);
			$item = $this->db->table('custumers')->where(array('cname'=>trim(input('post.cname'))))->item();
		}
		
		
		
		$consume['gid']=(int)(input('post.gid'));
		$consume['price']=trim(input('post.price'));
		$consume['uid']=$data['users']['id'];
		$consume['uname']=$data['users']['username'];
		$consume['uphone']=$data['users']['phone'];
		$consume['cid']=$item['id'];
		$consume['cname']=trim(input('post.cname'));
		$consume['cphone']=trim(input('post.cphone'));
		$consume['company']=trim(input('post.company'));
		$consume['payway']=0;
		$consume['status'] = 0;
		$consume['add_time'] = time();
		
		$this->db->table('consume')->insert($consume);
		
		
		$order['gid']=(int)(input('post.gid'));
		$order['gname']=(int)(input('post.gid'));
		$order['price']=trim(input('post.price'));
		$order['out_trade_no']=date('YmdHis');
		$order['uid']=$data['users']['id'];
		$order['uname']=$data['users']['username'];
		$order['uphone']=$data['users']['phone'];
		$order['payway']=0;
		$order['cid']= $item['id'];
		$order['cname']=trim(input('post.cname'));
		$order['cphone']=trim(input('post.cphone'));
		$order['status'] = 0;
		$order['add_time'] = time();
		
		$res= $this->db->table('order')->insert($order);
		
		if(!$res){
			
			exit(json_encode(array('code'=>1,'msg'=>'购买失败')));
			
		}else{
			
			$time= date("Y-m-d");
			$table= $this->db->table('echarts_data')->where(array('uid'=>$this->_user['id'],'datetime'=>$time))->item();
			if($table){
				$table['datas'] = (int)$table['datas'] + trim(input('post.price'));
				$this->db->table('echarts_data')->where(array('id'=>$table['id']))->update($table);
			}else{
				$table['datetime'] = $time;
				$table['datas']=trim(input('post.price'));
				$table['uid'] = $this->_user['id'];
				$this->db->table('echarts_data')->insert($table);
			}
			
			$data['item']['stock']= $data['item']['stock']-1;
			$data['users']['balance'] = $data['users']['balance'] -$data['price'];
			
			
			$this->db->table('goods')->where(array('id'=>$data['gid']))->update($data['item']);
			
			$this->db->table('users')->where(array('id'=>$this->_user['id']))->update($data['users']);
			
			exit(json_encode(array('code'=>0,'msg'=>'购买成功')));
		}
			
		
	}
	
	// 微信支付
	public function doWxPay(){
		$data['gid'] =(int)(input('post.gid'));
		$data['item'] = $this->db->table('goods')->where(array('id'=>$data['gid']))->item();
		$users = $this->db->table('users')->where(array('id'=>$this->_user['id']))->item();
		$data['price'] = trim(input('post.goodsPrice'));
		$data['order_id'] = date('YmdHis');
		$data['status'] = 0;
		$data['add_time']= time();
		$stock = (int)(input('post.stock'));
		
		// 查询客户是否存在
		$item= $this->db->table('custumers')->where(array('cname'=>trim(input('post.cname'))))->item();
		// 如果不存在,则插入客户.存在即什么都不做
		if($item==''){
			$custumer['uid'] = $users['id'];
			$custumer['uname'] =  $users['username'];
			$custumer['cname'] = trim(input('post.cname'));
			$custumer['company'] = trim(input('post.company'));
			$custumer['phone'] = trim(input('post.cphone'));
			$custumer['add_time'] = time();
			$this->db->table('custumers')->insert($custumer);
			$item = $this->db->table('custumers')->where(array('cname'=>trim(input('post.cname'))))->item();
		}
		
		
		// 向消费记录表中插入记录
		$consume['gid']=(int)(input('post.gid'));
		$consume['price']=trim(input('post.goodsPrice'));
		$consume['uid']=$users['id'];
		$consume['uname']=$users['username'];
		$consume['uphone']=$users['phone'];
		$consume['cid']=$item['id'];
		$consume['cname']=trim(input('post.cname'));
		$consume['cphone']=trim(input('post.cphone'));
		$consume['company']=trim(input('post.company'));
		$consume['payway']=1;
		$consume['status'] = 0;
		$consume['add_time'] = time();
		
		$this->db->table('consume')->insert($consume);
		
		// 向订单表中插入记录
		$order['gid']=(int)(input('post.gid'));
		$order['gname']=(int)(input('post.gid'));
		$order['price']=trim(input('post.goodsPrice'));
		$order['out_trade_no']=$data['order_id'];
		$order['uid']=$users['id'];
		$order['uname']=$users['username'];
		$order['payway']=1;
		$order['uphone']=$users['phone'];
		$order['cid']= $item['id'];
		$order['cname']=trim(input('post.cname'));
		$order['cphone']=trim(input('post.cphone'));
		$order['status'] = 0;
		$order['add_time'] = time();
		
		$res= $this->db->table('order')->insert($order);
		
		
		if(!$res){
			
			exit(json_encode(array('code'=>1,'msg'=>'购买失败')));
			
		}else{
			require_once APP_PATH.'common/WxpayAPI/lib/WxPay.Api.php';
			$input = new \WxPayUnifiedOrder();
			//设置商品描述
			$input->SetBody('微信支付');
			//设置订单号
			$input->SetOut_trade_no($data['order_id']);
			
			//设置订单金额<这里是以分为单位>
			$input->SetTotal_fee($data['price']*100);
			//设置异步通知地址
			$input->SetNotify_url('http://localhost/index.php/index/Notify/index');
			//设置交易类型
			$input->SetTrade_type('NATIVE');
			//设置商品ID(这里设置一个假商品id)
			$input->SetProduct_id((int)(input('post.gid')));
			//调用统一下单ApI
			$result=\WxPayAPI::unifiedOrder($input);
			// dump($result);
			// 生成图片二维码
			$code_url=$result['code_url'];
			
			// 商品库存减少一件
			$data['item']['stock']= $data['item']['stock']-1;
			// 然后更新商品表的数据
			$this->db->table('goods')->where(array('id'=>$data['gid']))->update($data['item']);
			
			exit(json_encode(array('code'=>0,'msg'=>'请扫码支付','data'=>$data,'result'=>$result)));
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
				
				$order = $this->db->table('order')->where(array('out_trade_no'=>$result['out_trade_no']))->item();
				$order['status'] = 1;
				$this->db->table('order')->where(array('out_trade_no'=>$result['out_trade_no']))->update($order);
				
				$time= date("Y-m-d");
				$table= $this->db->table('echarts_data')->where(array('uid'=>$this->_user['id'],'datetime'=>$time))->item();
				if($table){
					$table['datas'] = (int)$table['datas'] + (int)$order['price'];
					$this->db->table('echarts_data')->where(array('id'=>$table['id']))->update($table);
				}else{
					$table['datetime'] = $time;
					$table['datas']=(int)$order['price'];
					$table['uid'] = $this->_user['id'];
					$this->db->table('echarts_data')->insert($table);
				}
			}
            echo json_encode(\WxPayApi::orderQuery($input));
            exit();
        }
    }
	
	
	

}