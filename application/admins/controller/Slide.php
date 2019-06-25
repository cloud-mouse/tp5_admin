<?php
namespace app\admins\controller;
use think\Controller;
use Util\data\Sysdb;

/**
 * 幻灯片管理
 */
class Slide extends BaseAdmin
{
	
	public function index(){
		$data['data']= $this->db->table('slide')->where(array('type'=>0))->order('ord desc')->lists();
		$this->assign('data',$data);
		return $this->fetch();
	}

	//添加幻灯片数据
	public function add(){
		$id= (int)input('get.id');
		//编辑时加载当前影片信息
		$data['item'] = $this->db->table('slide')->where(array('id'=>$id))->item();

		$this->assign('data',$data);
		return $this->fetch();
	}

	// 保存幻灯片
	public function save(){
		$id = (int)input('post.id');
		$data['title'] = trim(input('post.title'));
		$data['ord'] = trim(input('post.ord'));
		$data['url'] = trim(input('post.url'));
		$data['img'] = trim(input('post.img'));

		if(!$data['title']){
			exit(json_encode(array('code'=>1,'msg'=>'幻灯片标题不能为空')));
		}
		
		if ($id==0) {
			$data['add_time']= time();
			$this->db->table('slide')->insert($data);
		}else{
			$this->db->table('slide')->where(array('id'=>$id))->update($data);
		}
		exit(json_encode(array('code'=>0,'msg'=>'保存成功')));
	}

	// 图片上传方法
	public function upload_img(){
		$file=request()->file('file');
		if($file==null){
			exit(json_encode(array('code'=>1,'msg'=>'没有文件上传')));
		}

		$info= $file->move(ROOT_PATH.'public'.DS.'upload'.DS.'banner');
		$ext=($info->getExtension());
		if(!in_array($ext, array('jpg','png','jpeg','gif'))){
			exit(json_encode(array('code'=>1,'msg'=>'文件格式不支持')));
		}
		//把反斜杠(\)替换成斜杠(/) 因为在windows下上传路是反斜杠径
		$getsaveName=str_replace("\\", "/", $info->getSaveName());
		$img='/upload/banner/'.$getsaveName;
		exit(json_encode(array('code'=>1,'msg'=>'上传成功','url'=>$img)));
	}

	// 删除幻灯片
	public function delete(){
		$id = (int)input('post.id');
		$res=$this->db->table('slide')->where(array('id'=>$id))->delete();
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'删除失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'删除成功')));
		}
	}
}