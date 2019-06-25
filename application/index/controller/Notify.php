<?php
namespace app\index\controller;
use think\Controller;
use Util\data\Sysdb;

use linepay\AliPay; //支付宝支付

/**
 * 
 */
class Notify extends Controller
{
	
	public function index(){
		require_once APP_PATH.'common/WxpayAPI/lib/Wxpay.Api.php';
		$msg='';
		\WxPayApi::notify(function($result){
			//签名校验通过
			//查询订单号,如果不存在,return;
			//如果订单已存在,且订单已经被处理过,return;
			//如果订单没被处理,处理订单
			
		},$msg);
	}
	
	public function notify(){
		/**
		 * 异步回调数据
		 * AliPay::AliPayNotify($params)
		 */
		$Notify = AliPay::AliPayNotify(Request::post('out_trade_no'));
		if($Notify == true) {
			//处理你的逻辑，例如获取订单号 Request::post('out_trade_no') ，订单金额 Request::post('total_amount') 等
			//程序执行完后必须打印输出“success”（不包含引号）。如果商户反馈给支付宝的字符不是success这7个字符，支付宝服务器会不断重发通知，
			//直到超过24小时22分钟。一般情况下，25小时以内完成8次通知（通知的间隔频率一般是：4m,10m,10m,1h,2h,6h,15h）
			echo 'success';exit();
		}else{
			echo 'error';exit();
		}
	}
}
