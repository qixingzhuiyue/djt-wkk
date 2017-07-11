<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class IndexController extends CommonController {

    //后台首页
    public function index(){
        $uid = $_SESSION[C('USER_AUTH_KEY')];
        $user = M('sysuser')->find($uid);
        $this->user = $user;
//        var_dump($user);
        $this->display();
    }
    //后台编辑关于平台的信息
    public function introView(){
        if (IS_POST) {
            $name = I('post.name');
            $title = I('post.title');
            $type = 1;
            $url = I('post.url');
            $intro = I('post.intro');
            $content = I('post.content');
            $status = I('post.status');
            $id = I('post.id');
            $time = time();
            if(empty($id)){
                if(empty($title)||empty($name)||empty($intro)||empty($_FILES['picture']['name'])){
                    $this->error("信息不完整，图片，标题，内容,视频URL链接不能为空");
                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload("intro");
                }
                if (empty($url)) {
                    $this->error("视频地址为空");
                }
                if (empty($picture)) {
                    $this->error("图片上传失败");
                }
                $data = array(
                    'title' => $title,
                    'name' => $name,
                    'url' => $url,
                    'intro' => $intro,
                    'type' => $type,
                    'content' => $content,
                    'picture' => $picture,
                    'status' => $status,
                    'created' => $time,
                    'updated' => $time
                );
                $insert = M("sys_intro")->add($data);
                if(!$insert){
                    $this->error("添加失败");
                }else{
                    $this->success("添加成功");
                }
            }else{
                $data = array();
                if(!empty($title)){
                    $data['title'] = $title;
                }
                if(!empty($name)){
                    $data['name'] = $name;
                }
//                if(!empty($_FILES['url']['name'])){
//                    $url = parent::_uploadTv("intro");
//                }
                if(!empty($url)){
                    $data['url'] = $url;
                }
                if(!empty($intro)){
                    $data['intro'] = $intro;
                }
                if(!empty($content)){
                    $data['content'] = $content;
                }
                if(!empty($status)){
                    $data['status'] = $status;
                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload("intro");
                }
                if(!empty($picture)){
                    $data['picture'] = $picture;
                }
                if(!empty($data)){
                    $data['updated'] = time();
                    $uResult = M("sys_intro")->where("id = {$id}")->save($data);
                    if(!$uResult){
                        $this->error("更新失败");
                    }
                }
                $this->success("更新成功");
            }
        } else {
            $Intro = M("sys_intro");
            $intro = $Intro->where("status = 1 AND type=1")->find();
            if(!empty($intro)){
                $intro['content'] = htmlspecialchars_decode($intro['content']);
                $this->assign("intro",$intro);
            }
            $this->display();
        }
    }
    //后台编辑关于平台地图的信息
    public function navView(){
        if (IS_POST) {
//            $name = I('post.name');
            $title = I('post.title');
            $type = 2;
//            $url = I('post.url');
//            $intro = I('post.intro');
            $content = I('post.content');
            $status = I('post.status');
            $id = I('post.id');
            $time = time();
            if(empty($id)){
                if(empty($title)||empty($content)){
                    $this->error("信息不完整:标题，内容");
                }
                $data = array(
                    'title' => $title,
                    'type' => $type,
                    'content' => $content,
                    'status' => $status,
                    'created' => $time,
                    'updated' => $time
                );
                $insert = M("sys_intro")->add($data);
                if(!$insert){
                    $this->error("添加失败");
                }else{
                    $this->success("添加成功");
                }
            }else{
                $data = array();
                if(!empty($title)){
                    $data['title'] = $title;
                }
//                if(!empty($name)){
//                    $data['name'] = $name;
//                }
//                if(!empty($_FILES['url']['name'])){
//                    $url = parent::_uploadTv("intro");
//                }
//                if(!empty($url)){
//                    $data['url'] = $url;
//                }
//                if(!empty($intro)){
//                    $data['intro'] = $intro;
//                }
                if(!empty($content)){
                    $data['content'] = $content;
                }
//                if(!empty($status)){
                $data['status'] = $status;
//                }
//                if(!empty($_FILES['picture']['name'])){
//                    $picture = parent::_upload("intro");
//                }
//                if(!empty($picture)){
//                    $data['picture'] = $picture;
//                }
                if(!empty($data)){
                    $data['updated'] = time();
                    $uResult = M("sys_intro")->where("id = {$id}")->save($data);
                    if(!$uResult){
                        $this->error("更新失败");
                    }
                }
                $this->success("更新成功");
            }
        } else {
            $Intro = M("sys_intro");
            $intro = $Intro->where("status = 1 AND type=2")->find();
//            var_dump($intro);
            if(!empty($intro)){
                $intro['content'] = htmlspecialchars_decode($intro['content']);
                $this->assign("intro",$intro);
            }
            $this->display();
        }
    }
    //后台平台优势展示
    public function adView(){
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND title LIKE '%$keyword%'";
        }

        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
//        var_dump($_GET);exit;
        $Adv = M('sys_advantage');
        $count      = $Adv->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $advs = $Adv->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('advs', $advs);
        $this->assign('page', $show);
        $this->display();
    }
    //后台关键字列表
    public function keysView(){
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND name LIKE '%$keyword%'";
        }

//        $status = _get('status');
//        if (is_numeric($status)) {
//            $condstr .= " AND status=$status";
//        }
//        var_dump($_GET);exit;
        $Key = M('sys_keyword');
        $count      = $Key->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $keys = $Key->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('keys', $keys);
        $this->assign('page', $show);
        $this->display();
    }
    //后台添加平台优势
    public function addKeys(){
        if (IS_POST) {
            $title = I('post.title');
            $id = I('post.id');
            $name = I('post.name');
            $intro= I('post.intro');
            $keyword = I('post.keyword');
            $time = time();
            if(empty($id)){
//                var_dump($_POST);EXIT;
                if(empty($title)||empty($name)||empty($intro)||empty($keyword)){
                    $this->error("信息完整有益于搜索引擎的抓取");
                }
                $data = array(
                    'title' => $title,
                    'name' => $name,
                    'created' => $time,
                    'intro' => $intro,
                    'keyword'=>$keyword
                );
                $insert = M("sys_keyword")->add($data);
                if(!$insert){
                    $this->error("添加失败");
                }else{
                    $this->success("添加成功",U('Admin/Index/keysView'));
                }
            }else{
                $data = array();
                if(!empty($title)){
                    $data['title'] = $title;
                }
                if(!empty($intro)){
                    $data['intro'] = $intro;
                }
                if(!empty($keyword)){
                    $data['keyword'] = $keyword;
                }
//                if(!empty($status)){
//                    $data['status'] = $status;
//                }
//                if(!empty($_FILES['picture']['name'])){
//                    $picture = parent::_upload("intro");
//                }
//                if(!empty($picture)){
//                    $data['picture'] = $picture;
//                }
                if(!empty($data)){
                    $data['updated'] = time();
                    $uResult = M("sys_keyword")->where("id = {$id}")->save($data);
                    if(!$uResult){
                        $this->error("更新失败");
                    }
                }
                $this->success("更新成功",U('Admin/Index/keysView'));
            }
        } else {
            $id = _get('id');
            if(!empty($id)){
                $key = M("sys_keyword")->where("id={$id}")->find();
                $this->assign("key",$key);
            }
            $this->display();
        }
    }
    //后台添加平台优势
    public function addAdvantage(){
        if (IS_POST) {
            $title = I('post.title');
            $status = I('post.status');
            $id = I('post.id');
            $time = time();
            if(empty($id)){
                if(empty($title)||empty($_FILES['picture']['name'])){
                    $this->error("图片，标题");
                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload("intro");
                }
                if(empty($picture)){
                    $this->error("图片上传失败");
                }
                $data = array(
                    'title' => $title,
                    'picture' => $picture,
                    'status' => $status,
                    'created' => $time,
                    'updated' => $time
                );
                $insert = M("sys_advantage")->add($data);
                if(!$insert){
                    $this->error("添加失败");
                }else{
                    $this->success("添加成功");
                }
            }else{
                $data = array();
                if(!empty($title)){
                    $data['title'] = $title;
                }
//                if(!empty($status)){
                    $data['status'] = $status;
//                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload("intro");
                }
                if(!empty($picture)){
                    $data['picture'] = $picture;
                }
                if(!empty($data)){
                    $data['updated'] = time();
                    $uResult = M("sys_advantage")->where("id = {$id}")->save($data);
                    if(!$uResult){
                        $this->error("更新失败");
                    }
                }
                $this->success("更新成功");
            }
        } else {
            $id = _get('id');
            if(!empty($id)){
               $adv = M("sys_advantage")->where("id={$id}")->find();
                $this->assign("adv",$adv);
            }
            $this->display();
        }
    }
    //后台首页
    public function main(){
        $uid = $_SESSION[C('USER_AUTH_KEY')];
        $user = M('sysuser')->find($uid);
        echo '<h1 style="text-align: center;margin-top: 150px;">欢迎回来'.$user['name'].'</h1>';exit;
    }
    //登出后台
    Public function logout () {
        if(!empty($_SESSION[C('USER_AUTH_KEY')])){
            unset($_SESSION[C('USER_AUTH_KEY')]);
            $_SESSION=array();
            session_destroy();
            $this->redirect('Admin/Login/index');
        }else{
            $this->error('已经登出了');
        }
    }

    //留言列表
    public function note(){
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND name LIKE '%$keyword%'";
        }

        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
//        var_dump($_GET);exit;
        $Note = M('note');
        $count      = $Note->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $notes = $Note->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('notes', $notes);
        $this->assign('page', $show);
        $this->display();
    }
    //修改留言状态
    public function noteStatus()
    {
        if (IS_GET) {
            if (M('note')->save($_GET)) {
                $this->success('修改成功', U('Admin/Index/note'));
            } else {
                $this->error('修改失败');
            }
        }
    }
}
