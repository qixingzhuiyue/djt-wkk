<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class CourseController extends CommonController {

    //课程列表
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
        $ispush = _get('ispush');
        if (is_numeric($ispush)) {
            $condstr .= " AND ispush=$ispush";
        }
//        var_dump($_GET);exit;
        $Course = M('course');
        $count      = $Course->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $course = $Course->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($course AS &$v){
            $v['starttime'] = date('Y-m-d',$v['starttime']);
        }
        $this->assign('course', $course);
        $this->assign('page', $show);
        $this->display();
    }
    /**
     * @desc 删除课程
     * 2017-4-6
     */
    public function del(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("Course")->where("courseid={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 删除申请人
     * 2017-4-6
     */
    public function delApply(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("apply_course")->where("applyid={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 删除精彩瞬间
     * 2017-4-6
     */
    public function delReview(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("review_course")->where("id={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 课程详情查看
     * 2017-1-6
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $title = I('post.title');
            $courseurl = I('post.courseurl');
            $teacher = I('post.teacher');
            $starttime = I('post.starttime');
            $starttime = (int)strtotime($starttime);
            $address = I('post.address');
            $cycle = (int)I('post.cycle');
            $price = round(I('post.price'),2);
            $nowprice = round(I('post.nowprice'),2);
            $intro = I('post.intro');
            $content = I('post.content');
            $status = I('post.status');
            $ispush = I('post.ispush');
            $success= I('post.success');
            $time = time();
            $data = array();
            if(!empty($title)){
                $data['title'] = $title;
            }
            if(!empty($intro)){
                $data['intro'] = $intro;
            }
            if(!empty($teacher)){
                $data['teacher'] = $teacher;
            }
            if(!empty($starttime)){
                $data['starttime'] = $starttime;
            }
            if(!empty($address)){
                $data['address'] = $address;
            }
            if(!empty($_FILES['picture']['name'])||!empty($_FILES['picture1']['name'])){
                $pictures = $this->_uploads1('course');
                $picture = $pictures['picture'] ?  $pictures['picture'] : '';
                $picture1 = $pictures['picture1'] ?  $pictures['picture1'] : '';
            }
            if(!empty($picture)){
                $data['picture'] = $picture;
            }
            if(!empty($picture1)){
                $data['picture1'] = $picture1;
            }
            if(!empty($courseurl)){
                $data['courseurl'] = $courseurl;
            }
            if(!empty($cycle)){
                $data['cycle'] = $cycle;
            }
                $data['price'] = round($price,2);
                $data['nowprice'] = round($nowprice,2);
            if(!empty($content)){
                $data['content'] = $content;
            }
            $data['status'] = $status;
            $data['ispush'] = $ispush;
            $data['success'] = $success;
            $data['updated'] = $time;
            $Course = M("Course");
            $Course->where('courseid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Course/index'));
        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Course = M('Course');
            $course = $Course -> where('courseid='.$id) -> find();
            $course['starttime'] = date('Y-m-d',$course['starttime']);
            $course['content'] = htmlspecialchars_decode($course['content']);
            $this->assign('course', $course);
            $this->display();
        }
    }
    /**
     * @desc 课程报名列表
     * 2017-1-6
     */
    public function apply() {
        if (isset($_GET['id'])) {
            $id = I('get.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('get.status');
            $time = time();
            $data = array(
                'status' => $status,
                'updated' => $time
            );
            $Apply = M("apply_course");
            $Apply->where('applyid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Course/apply'));
        } else {
            $condition = 1;
            $name = _get('name');
            $title = _get('title');
            $status = _get('status');
            if($name){
                $condition .= " AND name LIKE '%$name%'";
            }
            if($title){
                $condition .= " AND title LIKE '%$title%'";
            }
            if(is_numeric($status)){
                $condition.="AND status = $status";
            }
            $Apply = M('apply_course');
            $apply = $Apply -> where($condition) -> select();
            $this->assign('applys', $apply);
            $this->display();
        }
    }
    /**
     * @desc 精彩活动列表
     * 2017-1-6
     */
    public function reviewList(){
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
//        var_dump($_GET);exit;
        $Review = M('review_course');
        $count      = $Review->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $reviews = $Review->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('reviews', $reviews);
        $this->assign('page', $show);
        $this->display();
    }
    /**
     * @desc 精彩课程详情查看
     * 2017-1-4
     */
    public function review($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }

            $content = I('post.content');
            $status = I('post.status');
            $ispush = I('post.ispush');
            $time = time();
            $data = array(
                'content' => $content,
                'status' => $status,
                'ispush' => $ispush,
                'updated' => $time
            );
            $Course = M("review_course");
            $Course->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Course/reviewList'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Review = M('review_course');
            $review = $Review -> where('id='.$id) -> find();
            $review['content'] = htmlspecialchars_decode($review['content']);
            $this->assign('review', $review);
            $this->display();
        }
    }

    /**
     *后台添加课程
     * 2017-1-5
     */
    public function add(){
        $this->display();
    }

    /**
     *添加活动表单处理
     * 2017-1-6
     */
    public function addHandle(){
        $title = I('post.title');
        $courseurl = I('post.courseurl');
        $teacher = I('post.teacher');
        $starttime = I('post.starttime');
        $starttime = (int)strtotime($starttime);
        $address = I('post.address');
        $cycle = (int)I('post.cycle');
        $price = round(I('post.price'),2);
        $nowprice = round(I('post.nowprice'),2);
        $intro = I('post.intro');
        $content = I('post.content');
        $status = I('post.status');
        $ispush = I('post.ispush');
        $time = time();
        if(empty($title)){
            $this->error('标题不能为空');
        }
        if(empty($starttime)){
            $this->error('开始时间不能为空');
        }
        if(empty($address)){
            $this->error('地址不能为空');
        }
        if(empty($cycle)){
            $this->error('周期不能为空');
        }
//        if(empty($intro)){
//            $this->error('介绍不能为空');
//        }
        if(empty($content)){
            $this->error('内容不能为空');
        }
        $data = array(
            'title' => $title,
            'teacher' => $teacher,
            'starttime' => $starttime,
            'address' => $address,
            'cycle' => $cycle,
            'nowprice' => $nowprice,
            'price' => $price,
            'content' => $content,
            'status' => $status,
            'ispush' => $ispush,
            'created' => $time,
            'updated' => $time
        );
        if ($intro) {
            $data['intro'] = $intro;
        }
//        if(!empty($_FILES['courseurl']['name'])){
//            $courseurl = parent::_uploadTv("course");
//        }
        if(!empty($_FILES['picture']['name'])||!empty($_FILES['picture1']['name'])){
            $pictures = $this->_uploads1('course');
            $picture = $pictures['picture'] ?  $pictures['picture'] : '';
            $picture1 = $pictures['picture1'] ?  $pictures['picture1'] : '';
        }
        if(empty($picture)){
            $this->error('封面图片不能为空');
        }
        if(empty($picture1)){
            $this->error('精选封面不能为空');
        }
        if ($courseurl) {
            $data['courseurl'] = $courseurl;
        }
        if (!empty($picture)) {
            $data['picture'] = $picture;
        }
        if (!empty($picture1)) {
            $data['picture1'] = $picture1;
        }
        $Activity = D('course');
        if($Activity->create()){
            if(M('course')->add($data)){
                $this->success('添加成功',U('Admin/Course/index'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($Activity->getError());
        }
    }

    /**
     *后台添加精彩课程瞬间
     * 2017-1-6
     */
    public function addReview(){
        $this->display();
    }

    /**
     *添加精彩课程瞬间表单处理
     * 2017-1-6
     */
    public function addReviewHandle(){
        $title = I('post.title');
        $intro = I('post.intro');
        $content = I('post.content');
        $status = I('post.status');
        $ispush = I('post.ispush');
        $time = time();
        if(!empty($_FILES['picture'])){
            $picture = parent::_upload('course');
        }
        if(empty($picture)){
           $this->error("图片必须上传");
        }
        if(empty($title)){
            $this->error("标题必须填写");
        }
        if(empty($content)){
            $this->error("内容不能为空");
        }
        $data = array(
            'title' => $title,
            'content' => $content,
            'status' => $status,
            'ispush' => $ispush,
            'picture' => $picture,
            'created' => $time,
            'updated' => $time
        );
        if ($intro) {
            $data['intro'] = $intro;
        }
        $Review = D('review_course');
        if($Review->create()){
            if(M('review_course')->add($data)){
                $this->success('添加成功',U('Admin/Course/reviewList'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($Review->getError());
        }
    }

    /**
     * @desc 定制课程列表
     * 2017-1-6
     */
    public function needCourse() {
        if (isset($_GET['id'])) {
            $id = I('get.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('get.status');
            $time = time();
            $data = array(
                'status' => $status,
                'updated' => $time
            );
            $Apply = M("need_course");
            $Apply->where('needid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Course/needCourse'));
        } else {
            $condition = 1;
            $name = _get('name');
            $intro = _get('intro');
            $status = _get('status');
            if($name){
                $condition .= " AND name LIKE '%$name%'";
            }
            if($intro){
                $condition .= " AND intro LIKE '%$intro%'";
            }
            if(is_numeric($status)){
                $condition.=" AND status = $status";
            }
            $Need = M('need_course');
            $needs = $Need -> where($condition) -> select();
            $this->assign('needs', $needs);
            $this->display();
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
            $this->success('更新成功',U('Admin/Course/comment'));

        } else {
            //获取列表
            $condstr = "type = 3";
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
                //课程标题
                $Course = M('course');
                $course = $Course->where(['courseid'=>(int)$v['touid']])->find();
                $v['title'] = empty($course['title']) ? '暂无' : $course['title'];
            }
            $this->assign('comments', $comments);
            $this->assign('page', $show);
            $this->display();
        }
    }
}