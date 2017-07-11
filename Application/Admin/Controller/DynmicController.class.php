<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class DynmicController extends CommonController {

    //动态列表
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

//        $type = _get('type');
//        if (is_numeric($type)) {
//            $condstr .= " AND type=$type";
//        }
//        $ispush = _get('ispush');
//        if (is_numeric($ispush)) {
//            $condstr .= " AND ispush=$ispush";
//        }
//        var_dump($_GET);exit;
        $Dyn = M('sys_dynmic');
        $count      = $Dyn->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $dynmics = $Dyn->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('dynmics', $dynmics);
        $this->assign('page', $show);
        $this->display();
    }
    /**
     * @desc 删除动态
     * 2017-4-10
     */
    public function del(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("sys_dynmic")->where("id={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 动态添加编辑
     * 2017-2-18
     */
    public function addDynmic() {
        if (IS_POST) {
            $title = I('post.title');
            $content = I('post.content');
            $status = I('post.status');
            $id = I('post.id');
            $time = time();
            if(empty($id)){
                if(empty($title)||empty($content)||empty($_FILES['picture'])){
                    $this->error("信息不完整，图片，标题，内容不能为空");
                }
                $picture = parent::_upload("dnymic");
                if(empty($picture)){
                    $this->error("图片上传失败");
                }
                $data = array(
                    'title' => $title,
                    'content' => $content,
                    'picture' => $picture,
                    'status' => $status,
                    'created' => $time,
                    'updated' => $time
                );
                $insert = M("sys_dynmic")->add($data);
                if(!$insert){
                    $this->error("添加失败");
                }else{
                    $this->success("添加成功",U('Admin/Dynmic/index'));
                }
            }else{
                $data = array();
                if(!empty($title)){
                    $data['title'] = $title;
                }
                if(!empty($content)){
                    $data['content'] = $content;
                }
                if(!empty($status)){
                    $data['status'] = $status;
                }
                if(!empty($_FILES['picture'])){
                    $picture = parent::_upload("dnymic");
                }
                if(!empty($picture)){
                    $data['picture'] = $picture;
                }
                if(!empty($data)){
                    $data['updated'] = time();
                    $uResult = M("sys_dynmic")->where("id = {$id}")->save($data);
                    if(!$uResult){
                        $this->error("更新失败");
                    }
                }
                $this->success("更新成功",U('Admin/Dynmic/index'));
            }
        } else {
            if (!empty(_get('id'))) {
                $id = _get('id');
                $Dyn= M('sys_dynmic');
                $dynmic = $Dyn -> where('id='.$id) -> find();
                $dynmic['content'] = htmlspecialchars_decode( $dynmic['content']);
                $this->assign('dynmic', $dynmic);
            }
            $this->display();
        }
    }
}