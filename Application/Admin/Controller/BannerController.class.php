<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class BannerController extends CommonController {

    /**广告列表
     * djt
     * 2017-2-8
     */
    public function index(){
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND title LIKE '%$keyword%'";
        }

        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }

        $type = _get('type');
        if (!empty($type)) {
            $condstr .= " AND type='{$type}'";
        }
//        var_dump($_GET);exit;
        $Banner = M('banner');
        $count      = $Banner->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $banner = $Banner->where($condstr)->limit($Page->firstRow.','.$Page->listRows)->order(array('weight'=>'DESC','created'=>'DESC'))->select();
//        $types = getBannerType();
//        $locations = getBannerLocation();
        $types = getDictTypes("广告类型");
//        foreach($banner AS &$v){
//            $v['type'] = $types[ $v['type']];
//            $v['location'] = $locations[$v['location']];
//        }
        $this->assign('banner', $banner);
        $this->assign('page', $show);
        $this->assign('types',$types);
        $this->display();
    }

    /**
     *后台添加广告
     * 2017-2-8
     */
    public function addBanner(){
//        $types = getBannerType();
        $types = getDictTypes("广告类型");
        $this->assign('types', $types);
        $this->display();
    }

    /**
     *添加广告表单处理
     * 2017-2-8
     * djt
     */
    public function addBannerHandle(){
        $title = I('post.title');
        if(empty($title)){
            $this->error("广告标题不能为空");
        }
//        if(empty($_FILES['picture']['name'])){
//            $this->error("广告图片不能为空");
//        }
        $type = I('post.type');
        $intro = I('post.intro');
        $url = I('post.url');
//        $location = I('post.location');
        $weight = I('post.weight');
        $status = I('post.status');
        $time = time();
        $data = array(
            'title' => $title,
            'type' => $type,
            'url' => $url,
//            'location' => $location,
            'weight' => $weight,
            'status' => $status,
            'created' => $time,
            'updated' => $time
        );
        if ($intro) {
            $data['intro'] = $intro;
        }
        if(!empty($_FILES['picture']['name'])){
            $picture = parent::_upload('banner');
            $data['picture'] = $picture;
        }
        $Banner = D('banner');
        if($Banner->create()){
            if(M('banner')->add($data)){
                $this->success('添加成功',U('Admin/Banner/index'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($Banner->getError());
        }
    }

    /**
     * @desc 广告详情查看
     * 2017-2-8
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }

            $weight = I('post.weight');
            $status = I('post.status');
            $time = time();
            $data = array(
                'weight' => $weight,
                'status' => $status,
                'updated' => $time
            );
            if(!empty($_FILES['picture']['name'])){
                $picture = parent::_upload('banner');
                if(!empty($picture)){
                    $data['picture'] = $picture;
                }
            }
            $Banner = M("banner");
            $Banner->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Banner/index'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Banner = M('banner');
            $banner = $Banner -> where('id='.$id) -> find();
            $types = getBannerType();
            $locations = getBannerLocation();
            $banner['type'] = $types[$banner['type']];
            $banner['location'] = $locations[$banner['location']];
            $this->assign('banner', $banner);
            $this->display();
        }
    }
    /**
     * @desc 删除帖子
     * 2017-1-4
     */
    public function del(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("banner")->where("id={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**模板图片列表
     * djt
     * 2017-2-8
     */
    public function picture(){
        $condstr = 1;
//        $keyword = _get('keyword');
//        if ($keyword) {
//            $condstr .= " AND title LIKE '%$keyword%'";
//        }

        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }

        $pid = _get('pid');
        if (is_numeric($pid)) {
            $condstr .= " AND pid=$pid";
        }
//        var_dump($_GET);exit;
        $Pic = M('picture');
        $count      = $Pic->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $picture = $Pic->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($picture AS &$v){
           $info = $Pic->where("id = {$v['pid']}")->find();
            $v['pname'] = $info['name'] ? $info['name'] : "无";
        }
        $pLists = $Pic->where("pid = 0 AND status = 1")->field("id,name")->select();
        $this->assign('picture', $picture);
        $this->assign('page', $show);
        $this->assign('pLists',$pLists);
        $this->display();
    }

    /**
     *后台添加模板图片
     * 2017-2-8
     */
    public function addPicture(){
        $Pic = M("picture");
        $types = $Pic->where("pid = 0 AND status = 1")->field('id,name')->select();
        $this->assign('types', $types);
        $this->display();
    }

    /**
     *添加图片表单处理
     * 2017-2-8
     * djt
     */
    public function addPictureHandle(){
        $pid = I('post.pid');
        $name =  I('post.name');
        if(empty($name)){
            $this->error("模板名称不能为空");
        }
        if(!empty($pid)){
            if(empty($_FILES['picture']['name'])){
                $this->error("广告图片不能为空");
            }
        }else{
            $pid  = 0;
        }
        $weight = I('post.weight');
        $status = I('post.status');
        $time = time();
        $data = array(
            'pid' => $pid,
            'name' => $name,
            'weight' => $weight,
            'status' => $status,
            'created' => $time,
            'updated' => $time
        );
        if(!empty($_FILES['picture']['name'])){
            $picture = $this->_upload('banner');
            $data['picture'] = $picture;
        }
        $Picture = D('picture');
        if($Picture->create()){
            if(M('picture')->add($data)){
                $this->success('添加成功',U('Admin/Banner/picture'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($Picture->getError());
        }
    }

    /**
     * @desc 图片详情查看
     * 2017-2-8
     */
    public function picview($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }

            $weight = I('post.weight');
            $status = I('post.status');
            $time = time();
            $data = array(
                'weight' => $weight,
                'status' => $status,
                'updated' => $time
            );
            if(!empty($_FILES['picture']['name'])){
                $picture = parent::_upload('banner');
                $data['picture'] = $picture;
            }
            $Picture = M("picture");
            $Picture->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Banner/picture'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Picture = M('picture');
            $picture = $Picture -> where('id='.$id) -> find();
            $info = $Picture->where("id = {$picture['pid']}")->find();
            $picture['pname'] = $info['name'] ? $info['name'] : "无";
            $this->assign('picture', $picture);
            $this->display();
        }
    }

}