<?php
/**
 * @desc 帖子管理
 */
namespace Admin\Controller;
use Admin\Controller\CommonController;
class TopicController extends CommonController {

    /**
     * @desc 帖子管理
     * 2017-1-4
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
        $ispush = _get('ispush');
        if (is_numeric($ispush)) {
            $condstr .= " AND ispush=$ispush";
        }
//        $recommend = _get('recommend');
//        if (is_numeric($recommend)) {
//            $condstr .= " AND recommend=$recommend";
//        }
        
        $Topic = M('article');
        $count      = $Topic->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        
        $topics = $Topic->where($condstr)->order(array('weight'=>'DESC','created'=>'ASC'))->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('topics', $topics);
        $this->assign('page', $show);
        $this->display();
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
        $result = M("article")->where("articleid={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 删除帖子评价
     * 2017-1-4
     */
    public function delComment(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("person_comment")->where("commentid={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 改变企业帖子状态
     * 2017-1-4
     */
    public function changeStatus(){
        if(IS_GET){
            if(M('article')->save($_GET)){
                $this->success('修改成功',U('Admin/Topic/index'));
            }else{
                $this->error('修改失败');
            }
        }
    }
    /**
     * @desc 帖子详情查看
     * 2017-1-4
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            
            $content = I('post.content');
            $status = I('post.status');
            $ispush = I('post.ispush');
            $weight = I("post.weight");
            $title = I("post.title");
            $author = I("post.author");
            $time = time();
            $data = array(              
                'content' => $content,
                'ispush' => $ispush,
                'status' => $status,
                'weight' => $weight,
                'updated' => $time
            );
            if(!empty($title)){
                $data['title'] = $title;
            }
            if(!empty($author)){
                $data['author'] = $author;
            }
            $Topic = M("article");
            $Topic->where('articleid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Topic/index'));
            
            
        } else {        
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }     
            $Topic = M('article');
            $topic = $Topic -> where('articleid='.$id) -> find();
            $topic['content'] = htmlspecialchars_decode($topic['content']);
            $this->assign('topic', $topic);
            $this->display();
        }              
    }
   /**
    *后台添加文贴
    * 2017-1-4
    */
    public function addArticle(){
        $this->display();
    }
    /**
     *添加文贴表单处理
     * 2017-1-4
     */
    public function addArticleHandle(){
            $Article = M('article');
            $type = I("post.type");
            $author = I("post.author");
            $title = I("post.title");
            $intro = I("post.intro");
            $content = I("post.content");
            $weight = I("post.weight");
//            $browsenum = I("post.browsenum");
            $ispush = I("post.ispush");
            $status = I("post.status");
            $msg = '';
            $flag = true;
            if(empty($type)){
                $flag = false;
                $msg.="类型,";
            }
            if(empty($author)){
                $flag = false;
                $msg.="作者,";
            }
            if(empty($title)){
                $flag = false;
                $msg.="标题,";
            }
        if(empty($_FILES['picture'])){
            $flag = false;
            $msg.="图片,";
        }else{
            $picture = parent::_upload('topic');
        }
        if(empty($intro)){
            $flag = false;
            $msg.="简介,";
        }
            if(empty($content)){
                $flag = false;
                $msg.="内容,";
            }
            if(!$flag){
                $msg = trim($msg,',');
                $this->error($msg);
            }
            $data = array(
               'type' => $type,
                'author' => $author,
                'title' => $title,
                'intro' => $intro,
                'picture' => $picture,
                'weight' => $weight,
                'content' => $content,
                'ispush' => $ispush,
                'created' => time(),
                'updated' => time()
            );
            if($status === 0){
                $data['status'] = 0;
            }
            $result = $Article->add($data);
            if($result){
                $this->success('添加成功',U('Admin/Topic/index'));
            }else{
                $this->error('添加失败');
            }
    }
    /**
     * @desc 评价管理
     * 2017-1-4
     */
    public function comment() {
        if (isset($_GET['commentid'])) {
            $id = I('get.commentid');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('get.status');

            $data['updated'] = time();
            $data['status'] = $status;           
          
            
            $Topic = M("person_comment");
            $Topic->where('commentid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Topic/comment'));
        }
//        if($_GET['keyword']) {
            $condstr = "type=4";
            $keyword = _get('keyword');
            if ($keyword) {
                $condstr .= " AND content LIKE '%$keyword%'";
            }
            $status = _get('status');
            if (is_numeric($status)) {
                $condstr .= " AND status=$status";
            }
            $id = _get('id');
            if (is_numeric($id)) {
                $condstr .= " AND topicid=$id";
            }
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
                //被评论的文章标题
                $articles = M('article');
                $article = $articles->where(['articleid'=>(int)$v['touid']])->find();
                $v['title'] = empty($article['title']) ? '暂无' : $article['title'];
            }
            $this->assign('comments', $comments);
            $this->assign('page', $show);
            $this->display();
        }
//    }
   
}
