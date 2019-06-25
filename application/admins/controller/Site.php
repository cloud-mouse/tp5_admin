<?php
namespace app\admins\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 网站设置
 */
class Site extends BaseAdmin
{
	
	public function index(){
		$site=$this->db->table('sites')->where(array('sitename'=>'site'))->item();
		$this->assign('site',$site);
		return $this->fetch();
	}

	//保存
	public function save(){
		$sitename= trim(input('post.sitename'));
		$seo= trim(input('post.seo'));
		$copyright= trim(input('post.copyright'));
		$phone= trim(input('post.phone'));

		$site=$this->db->table('sites')->where(array('sitename'=>'site'))->item();
		if(!$site){
			$this->db->table('sites')->insert(array('value'=>$sitename,'seo'=>$seo,'copyright'=>$copyright,'phone'=>$phone));
		}else{
			$data['value']=$sitename;
			$data['seo']=$seo;
			$data['copyright']=$copyright;
			$data['phone']=$phone;
			$this->db->table('sites')->where(array('sitename'=>'site'))->update($data);
		}
		exit(json_encode(array('code'=>0,'msg'=>'保存成功')));
	}
}