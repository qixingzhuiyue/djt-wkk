<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class ActivityController extends CommonController {

    //活动列表
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
//        if (is_numeric($type)) {
//            $condstr .= " AND type=$type";
//        }
        if (!empty($type)) {
            $condstr .= " AND type='{$type}'";
        }
        $ispush = _get('ispush');
        if (is_numeric($ispush)) {
            $condstr .= " AND ispush=$ispush";
        }
//        var_dump($_GET);exit;
        $Activity = M('activity');
        $count      = $Activity->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $activity = $Activity->where($condstr)->order(array("ispush"=>'DESC','status'=>'DESC','updated'=>'DESC'))->limit($Page->firstRow.','.$Page->listRows)->select();
        $types = getDictDisposition('activityType');
//        foreach($activity AS &$v){
//            $v['type'] = $types[ $v['type']];
//        }
        $this->assign('activity', $activity);
        $this->assign('page', $show);
        $this->assign('types', $types);
        $this->display();
    }
    /**
     * @desc 删除活动
     * 2017-4-6
     */
    public function del(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("activity")->where("activityid={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 删除精彩活动
     * 2017-4-6
     */
    public function delReview(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("review_activity")->where("id={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 改变活动状态 是否正常，是否热门推荐
     * 2017-1-5
     */
    public function changeActivityStatus(){
        if(IS_GET){
            $data = $_GET;
            $data['updated'] = time();
            if(M('activity')->save($data)){
                $this->success('修改成功',U('Admin/Activity/index'));
            }else{
                $this->error('修改失败');
            }
        }
    }

    /**
     * @desc 精彩活动列表
     * 2017-1-5
     */
    public function review(){
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
        if (is_numeric($type)) {
            $condstr .= " AND type=$type";
        }
//        var_dump($_GET);exit;
        $Activity = M('review_activity');
        $count      = $Activity->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $activity = $Activity->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $types = getDictDisposition('activityType');
        foreach($activity AS &$v){
            $v['type'] = $types[ $v['type']];
        }
        $this->assign('activity', $activity);
        $this->assign('page', $show);
        $this->assign('types', $types);
        $this->display();
    }

    /**
     * @desc 活动详情查看
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
            $time = time();
            $data = array(
                'content' => $content,
                'status' => $status,
                'updated' => $time
            );
            $Activity = M("review_activity");
            $Activity->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Activity/review'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Activity = M('review_activity');
            $activity = $Activity -> where('id='.$id) -> find();
            $types = getDictDisposition('activityType');
            $activity['type'] = $types[$activity['type']];
            $activity['content'] = htmlspecialchars_decode($activity['content']);
            $this->assign('activity', $activity);
            $this->display();
        }
    }

    /**
     *后台添加精彩活动瞬间
     * 2017-1-5
     */
    public function addActivity(){
        $types = getDictDisposition('activityType');
        $this->assign('types', $types);
        $this->display();
    }

    /**
     *添加精彩活动表单处理
     * 2017-1-5
     */
    public function addActivityHandle(){
        $title = I('post.title');
        $type = I('post.type');
        $intro = I('post.intro');
        $content = I('post.content');
        $status = I('post.status');
        $time = time();
        $data = array(
            'title' => $title,
            'type' => $type,
            'content' => $content,
            'status' => $status,
            'created' => $time,
            'updated' => $time
        );
        if ($intro) {
            $data['intro'] = $intro;
        }
        if ($_FILES['picture']['name']) {
            $picture = parent::_upload('activity');
            $data['picture'] = $picture;
        }
        $Activity = D('review_activity');
        if($Activity->create()){
            if(M('review_activity')->add($data)){
                $this->success('添加成功',U('Admin/Activity/review'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($Activity->getError());
        }
    }

    /**
     * @desc 评价管理
     * 2017-1-5
     */
    public function comment() {
        if (isset($_GET['commentid'])) {
            //修改状态
            $id = I('get.commentid');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('get.status');

            $data['updated'] = time();
            $data['status'] = $status;


            $Topic = M("person_comment");
            $Topic->where('commentid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Activity/comment'));

        } else {
            //获取列表
            $condstr = "type in(1,5)";
            $keyword = _get('keyword');
            if ($keyword) {
                $condstr .= " AND content LIKE '%$keyword%'";
            }
            $status = _get('status');
            if (is_numeric($status)) {
                $condstr .= " AND status=$status";
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
                $v['name'] = empty($userinfo['username']) ? '暂无' : $userinfo['username'];
                //被评论的活动标题分活动和精彩活动两种分别差不同的表
                if($v['type']==1){
                    $type = '活动';
                    $Activity = M('activity');
                    $activity = $Activity->where(['articleid'=>(int)$v['touid']])->find();
                }else{
                    $type = '精彩活动回顾';
                    $Activity = M('review_activity');
                    $activity = $Activity->where(['id'=>(int)$v['touid']])->find();
                }
                $v['type'] = $type;
                $v['title'] = empty($activity['title']) ? '暂无' : $activity['title'];
            }
            $this->assign('comments', $comments);
            $this->assign('page', $show);
            $this->display();
        }
    }
}