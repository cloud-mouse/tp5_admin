<?php
namespace app\admins\controller;
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
		$data['pagesize']= 15;
		$data['page']=max(1,(int)(input('get.page')));
		$data['wd']=trim(input('get.wd'));
		$where=array();
		$data['wd'] && $where = 'title like "%'.$data['wd'].'%"';
		$data['data'] = $this->db->table('goods')->where($where)->order('id desc')->pages($data['pagesize']);

		$data['classfy']= $this->db->table('goods_type')->cates('id');
		$this->assign('data',$data);
		return $this->fetch();
	}

	//添加商品
	public function add(){
		$id= (int)input('get.id');
		//编辑时加载当前影片信息
		$data['item'] = $this->db->table('goods')->where(array('id'=>$id))->item();

		// 获取分类
		$data['classfy']= $this->db->table('goods_type')->lists();
		$this->assign('classfy',$data['classfy']);

		$this->assign('data',$data);
		return $this->fetch();
		
	}

	// 图片上传方法
	public function upload_img(){
		$file=request()->file('file');
		if($file==null){
			exit(json_encode(array('code'=>1,'msg'=>'没有文件上传')));
		}

		$info= $file->move(ROOT_PATH.'public'.DS.'upload');
		$ext=($info->getExtension());
		if(!in_array($ext, array('jpg','png','jpeg','gif'))){
			exit(json_encode(array('code'=>1,'msg'=>'文件格式不支持')));
		}
		$getsaveName=str_replace("\\", "/", $info->getSaveName());
		$img='/upload/'.$getsaveName;
		exit(json_encode(array('code'=>1,'msg'=>'上传成功','url'=>$img)));
	}

	// 保存商品
	public function save(){

		$id = (int)input('post.id');
		$data['title'] = trim(input('post.title'));
		$data['pid'] = trim(input('post.pid'));
		$data['img'] = trim(input('post.img'));
		$data['price'] = input('post.price');
		$data['keywords'] = trim(input('post.keywords'));
		$data['stock'] = (int)(input('post.stock'));
		$data['desc'] = trim(input('post.desc'));
		$data['status'] = (int)(input('post.status'));
		$data['details'] = input('post.details');
		

		if(!$data['title']){
			exit(json_encode(array('code'=>1,'msg'=>'商品标题不能为空')));
		}
		
		if ($id==0) {
			$data['add_time']= time();
			$this->db->table('goods')->insert($data);
		}else{
			$this->db->table('goods')->where(array('id'=>$id))->update($data);
		}
		exit(json_encode(array('code'=>0,'msg'=>'保存成功')));
	}

	// 删除商品
	public function delete(){
		$id = (int)input('post.id');
		$res=$this->db->table('goods')->where(array('id'=>$id))->delete();
		if(!$res){
			exit(json_encode(array('code'=>1,'msg'=>'删除失败')));
		}else{
			exit(json_encode(array('code'=>0,'msg'=>'删除成功')));
		}
	}

	// 商品分类列表
	public function classList(){
		$pid = (int)input('get.pid');
		// 获取admins表中所有数据
		$this->db = new Sysdb;
		$data['lists'] = $this->db->table('goods_type')->where(array('pid'=>$pid))->lists();
		
		// 返回上一级
		$backid=0;
		if($pid>0){
			$parent = $this->db->table('goods_type')->where(array('id'=>$pid))->item();
			$backid=$parent['pid'];
		}
		// 放到模板里面去
		$this->assign('pid',$pid);
		$this->assign('backid',$backid);
		$this->assign('data',$data);

		return $this->fetch('class_list');
	}

	//保存分类
	public function classSave(){
		$pid=(int)input('post.pid');
		$ords=input('post.ords/a');
		$titles=input('post.titles/a');
		$status=input('post.status/a');

		foreach ($ords as $key => $value) {
			//新增菜单
			$data['pid']=$pid;
			$data['ord']=$value;
			$data['title']=$titles[$key];
			$data['status']=isset($status[$key])? 1 : 0;

			if($key==0 && $data['title']){
				// 新增
				$this->db->table('goods_type')->insert($data);
			}
			if($key>0){
				if($data['title']==''){
					//删除
					$this->db->table('goods_type')->where(array('id'=>$key))->delete();
				}else{
					//修改
					$this->db->table('goods_type')->where(array('id'=>$key))->update($data);
				}
			}
		}
		exit(json_encode(array('code'=>0,'msg'=>'保存成功')));
	}

	//layui编辑器图片上传接口
    public function lay_img_upload(){
        $file = Request::instance()->file('file');
        if(empty($file)){
            $result["code"] = "1";
            $result["msg"] = "请选择图片";
            $result['data']["src"] = '';
        }else{
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/layui' );
            if($info){
                $name_path =str_replace('\\',"/",$info->getSaveName());
                //成功上传后 获取上传信息
                $result["code"] = '0';
                $result["msg"] = "上传成功";
                $result['data']["src"] = "/uploads/layui/".$name_path;
            }else{
                  // 上传失败获取错误信息
                $result["code"] = "2";
                $result["msg"] = "上传出错";
                $result['data']["src"] ='';
            }
        

        }

        return json_encode($result);
    }

    //改变上下架状态
    public function changeStatus(){
    	$id = (int)input('post.id');
    	$data = $this->db->table('goods')->where(array('id'=>$id))->item();

    	if($data['status']==1){
    		$data['status'] = 0;
    	}else{
    		$data['status'] = 1;
    	}
    	$this->db->table('goods')->where(array('id'=>$id))->update($data);
    	exit(json_encode(array('code'=>0,'msg'=>'修改成功')));
    }
}