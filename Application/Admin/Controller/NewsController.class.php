<?php
/**
 * @desc 企业及案例管理
 */
namespace Admin\Controller;
use Admin\Controller\CommonController;
class NewsController extends CommonController {

    /**
     * @desc 新闻管理
     */
    public function index() {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND title LIKE '%$keyword%'";
        }
        
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
        $row = _get('row');
        if (is_numeric($row)) {
            $condstr .= " AND row=$row";
        }
        $type = _get('type');
        if (is_numeric($type)) {
            $condstr .= " AND type=$type";
        }
        
        $News = M('news');
        $count      = $News->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        
        $news = $News->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('news', $news);
        $this->assign('page', $show);
        $this->display();
    }
    /**
     *后台添加新闻资讯
     * 2017-2-9
     */
    public function add(){
        $this->display();
    }
    /**
     *添加新闻表单处理
     * 2017-1-4
     */
    public function addHandle(){
        $News = D('news');
        $title = I("post.title");
        $intro = I('post.intro');
        $content = I("post.content");
        $row = 1;
        $type = I('post.type');
        $status = I('post.status');
        $flag = true;
        $msg = '';
        if(empty($title)){
            $flag = false;
            $msg .="标题,";
        }
        if(empty($intro)){
            $flag = false;
            $msg .="摘要,";
        }
        if(empty($content)){
            $flag = false;
            $msg .="内容,";
        }
        if(empty($_FILES['picture']['name'])){
            $flag = false;
            $msg .="图片,";
        }
        $picture = parent::_upload('news');
        if(!$flag){
            $msg = trim($msg,',');
            $msg.="不能为空";
            $this->error($msg);
        }else{
            $data = array(
                'title' => $title,
                'intro' => $intro,
                'picture' => $picture,
                'content' => $content,
                'row' => $row,
                'type' => $type,
                'status' => $status,
                'created' => time(),
                'updated' => time(),
            );
            if($News->create()){
                if(M('news')->add($data)){
                    $this->success('添加成功',U('Admin/News/index'));
                }else{
                    $this->error('添加失败');
                }
            }else{
                $this->error($News->getError());
            }
        }
    }
    /**
     * @desc 删除新闻
     * 2017-1-4
     */
    public function del(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("news")->where("id={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 改变新闻状态
     * 2017-2-9
     */
    public function changeStatus(){
        if(IS_GET){
            if(M('news')->save($_GET)){
                $this->success('修改成功',U('Admin/News/index'));
            }else{
                $this->error('修改失败');
            }
        }
    }

    /**
     * @desc 新闻资讯详情查看
     * 2017-2-9
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $content = I('post.content');
            $status = I('post.status');
            $intro = I('post.intro');
            $type = I('post.type');
            $time = time();
            $flag = true;
            $msg = '';
            if(empty($intro)){
                $flag = false;
                $msg .="摘要,";
            }
            if(empty($content)){
                $flag = false;
                $msg .="内容,";
            }
            if(!$flag){
                $msg = trim($msg,',');
                $msg .="不能为空";
                $this->error($msg);
            }
            $data = array(
                'content' => $content,
                'type' => $type,
                'intro' => $intro,
                'status' => $status,
                'updated' => $time
            );
            $News = M("news");
            $newResult = $News->where('id='.$id)->save($data);
            if($newResult){
                $this->success('更新成功',U('Admin/News/index'));
            }else{
                $this->error("更新失败");
            }


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $News = M('news');
            $new = $News -> where('id='.$id) -> find();
            $new['content'] = htmlspecialchars_decode($new['content']);
            $this->assign('new', $new);
            $this->display();
        }
    }

    /**
     * @desc 评价管理
     * 2017-1-4
     */
    public function comment() {
        if (isset($_GET['id'])) {
            $id = I('get.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('get.status');

            $data['updated'] = time();
            $data['status'] = $status;


            $Topic = M("person_comment");
            $Topic->where('commentid='.$id)->save($data);
            $this->success('更新成功',U('Admin/News/comment'));

        } else {
            $condstr = "type=6";
            $keyword = _get('keyword');
            if ($keyword) {
                $condstr .= " AND content LIKE '%$keyword%'";
            }
            $status = _get('status');
            if (is_numeric($status)) {
                $condstr .= " AND status=$status";
            }
//            $id = _get('id');
//            if (is_numeric($id)) {
//                $condstr .= " AND topicid=$id";
//            }
            $Comment = M('person_comment');
            $count      = $Comment->where($condstr)->count();
            $Page       = new \Think\Page($count, 15);
            $show       = $Page->show();
            $comments = $Comment->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
            foreach($comments AS &$v) {
                //评价者姓名
                $users= M('users');
                $userinfo = $users->where(['uid'=>(int)$v['uid']])->find();
                $v['name'] = empty($userinfo['name']) ? '暂无' : $userinfo['name'];
                //被评论的新闻标题
                $New = M('news');
                $new = $New->where(['id'=>(int)$v['touid']])->find();
                $v['title'] = empty($new['title']) ? '暂无' : $new['title'];
            }
            $this->assign('comments', $comments);
            $this->assign('page', $show);
            $this->display();
        }
    }
}
