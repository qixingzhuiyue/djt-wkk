<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class JobController extends CommonController {

    //人才对接
    public function index(){

    }

    /**
     * @desc 企业招生简章列表
     * 2017-2-7
     */
    public function company(){
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
        $Job = M('company_job');
        $count      = $Job->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $job = $Job->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('job', $job);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 查看编辑企业简章
     * 2017-2-7
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.status');
            $time = time();
            $data = array(
                'status' => $status,
                'updated' => $time
            );
            $Job = M("company_job");
            $Job->where('jobid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Job/company'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Job = M('company_job');
            $job = $Job -> where('jobid='.$id) -> find();
            $job['jobs'] = json_decode($job['jobs'],true);
//            $types = getDictDisposition('activityType');
//            $activity['type'] = $types[$activity['type']];
//            $activity['content'] = htmlspecialchars_decode($activity['content']);
            $this->assign('job', $job);
            $this->display();
        }
    }

    /**
     * @desc 职位申请列表
     * 2017-2-7
     */
    public function apply(){
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
        $Job = M('person_job');
        $count      = $Job->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $job = $Job->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('job', $job);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 查看编辑个人申请
     * 2017-2-7
     */
    public function applyView($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.status');
            $time = time();
            $data = array(
                'status' => $status,
                'updated' => $time
            );
            $Info = M("person_job");
            $Info->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Job/apply'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Info = M('person_job');
            $info = $Info -> where('id='.$id) -> find();
//            $types = getDictDisposition('activityType');
//            $activity['type'] = $types[$activity['type']];
//            $activity['content'] = htmlspecialchars_decode($activity['content']);
            $this->assign('info', $info);
            $this->display();
        }
    }

    /**
     * @desc 推荐招聘职位列表
     * 2017-2-10
     */
    public function job(){
        $condstr = 1;
        $keyword = _get('keyword');
        $name = _get('name');
        if ($keyword) {
            $condstr .= " AND job LIKE '%$keyword%'";
        }
        if ($name) {
            $condstr .= " AND name LIKE '%$name%'";
        }
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
//        var_dump($_GET);exit;
        $Job = M('sys_job');
        $count      = $Job->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $job = $Job->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('job', $job);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 查看编辑推荐的职位
     * 2017-2-7
     */
    public function jobView($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.status');
            $time = time();
            $data = array(
                'status' => $status,
                'updated' => $time
            );
            $starttime = I('post.starttime');
            $endtime = I('post.endtime');
            $contact = I('post.contact');
            $phone = I('post.phone');
            $job = I('post.job');
            $num = I('post.num');
            $wage = I('post.wage');
            $descp = I('post.descp');
            if(!empty($starttime)){
                $data['starttime'] = $starttime;
            }
            if(!empty($endtime)){
                $data['endtime'] = $endtime;
            }
            if(!empty($contact)){
                $data['contact'] = $contact;
            }
            if(!empty($phone)){
                $data['phone'] = $phone;
            }
            if(!empty($job)){
                $data['job'] = $job;
            }
            if(!empty($num)){
                $data['num'] = $num;
            }
            if(!empty($wage)){
                $data['wage'] = $wage;
            }
            if(!empty($descp)){
                $data['descp'] = $descp;
            }
            $Job = M("sys_job");
            $Job->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Job/job'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Job = M('sys_job');
            $job = $Job -> where('id='.$id) -> find();
//            $types = getDictDisposition('activityType');
//            $activity['type'] = $types[$activity['type']];
//            $activity['content'] = htmlspecialchars_decode($activity['content']);
            $this->assign('job', $job);
            $this->display();
        }
    }

    /**
     *后台添加推荐职位
     * 2017-2-7
     */
    public function addCompanyJob(){
        $id = I('get.id');
        if(!empty($id)){
            $Job = M('company_job');
            $job = $Job -> where('jobid='.$id) -> find();
            $job['jobs'] = json_decode($job['jobs'],true);
            $this->assign('job', $job);
        }
        $types = getDictDisposition('companyType');
        $scale = getDictDisposition('companySize');
        $this->assign('scale', $scale);
        $this->assign('types', $types);
        $this->display();
    }

    /**
     *添加企业招生简章表单处理
     * 2017-2-7
     */
    public function addCompanyJobHandle(){
        if(!empty($_POST)){
            $id = I("post.id");
//            if(empty($id)){
//                $this->error('参数错误');
//            }
            $name = I('post.name');
            $phone = I("post.phone");
            $qq = I("post.qq");
            $job = I("post.job");
            $num = I("post.num");
            $wage = I("post.wage");
            $descp = I("post.descp");
            $status = true;
            $msg = '';
            if(empty($name)){
                $status = false;
                $msg .= "公司名称，";
            }
            if(empty($phone)){
                $status = false;
                $msg .= "负责人手机号，";
            }
            if(empty($qq)){
                $status = false;
                $msg .= "负责人QQ，";
            }
            if(empty($job)){
                $status = false;
                $msg .= "职位，";
            }
            if(empty($num)){
                $status = false;
                $msg .= "招聘人数，";
            }
            if(empty($wage)){
                $status = false;
                $msg .= "薪资，";
            }
            if(empty($descp)){
                $status = false;
                $msg .= "职位描述，";
            }
            if(!$status){
                $msg = trim($msg,',');
                $msg .= "不能为空";
                $this->error($msg);
            }
            if(!empty($_FILES['picture'])){
                $picture = parent::_upload("job");
            }
            if(empty($picture)){
                $this->error("图片不能为空");
            }
            $registernum = I("post.registernum");
            $starttime = I("post.starttime");
            $starttime = (int)strtotime($starttime);
            $endtime = I("post.endtime");
            $endtime = (int)strtotime($endtime);
            $type = I("post.type");
            $level = I("post.level");
            $scale = I("post.scale");
            $intro = I("post.intro");
            $notice = I("post.notice");
            $telphone = I("post.telphone");
            $fax = I("post.fax");
            $address = I("post.address");
            $link = I("post.link");
            $note = I("post.note");
            $contact = I("post.contact");
            $position = I("post.position");
            $email = I("post.email");
            $status = I("post.status");
            $data = array(
                'starttime' => $starttime,
                'endtime' => $endtime,
                'level' => $level,
                'registernum' => $registernum,
                'picture' => $picture,
                'name' => $name,
                'type' => $type,
                'scale' => $scale,
                'intro' => $intro,
                'notice' => $notice,
                'telphone' => $telphone,
                'fax' => $fax,
                'address' => $address,
                'note' => $note,
                'link' => $link,
                'contact' => $contact,
                'position' => $position,
                'phone' => $phone,
                'qq' => $qq,
                'email' => $email,
                'jobid' => $id,
                'job' => $job,
                'wage' => $wage,
                'num' => $num,
                'descp' => $descp,
                'status' => $status,
                'created' => time(),
                'updated' => time()
            );
        }
        $Job = D('sys_job');
        if($Job->create()){
            if(M('sys_job')->add($data)){
                $this->success('添加成功',U('Admin/Job/company'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($Job->getError());
        }
    }

    /**
     * @desc 推荐人才列表
     * 2017-2-13
     */
    public function talent(){
        $condstr = 1;
        $keyword = _get('keyword');
        $name = _get('name');
        if ($keyword) {
            $condstr .= " AND title LIKE '%$keyword%'";
        }
        if ($name) {
            $condstr .= " AND name LIKE '%$name%'";
        }
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
//        var_dump($_GET);exit;
        $Talent = M('sys_talent');
        $count      = $Talent->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $talent = $Talent->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('talent', $talent);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 查看编辑推荐的人才
     * 2017-2-13
     */
    public function talentView($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.status');
            $time = time();
            $data = array(
                'status' => $status,
                'updated' => $time
            );
            $Job = M("sys_talent");
            $Job->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Job/talent'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Job = M('sys_talent');
            $talent = $Job -> where('id='.$id) -> find();
            $talent['education'] = json_decode($talent['education'],true);
            if(!empty($talent['education'])){
                foreach($talent['education'] AS &$v){
                    if($v['type']=='其他'){
                        $v['type'] = $v['descp'];
                    }
                }
            }
            $talent['experience'] = json_decode($talent['experience'],true);
            $talent['family'] = json_decode($talent['family'],true);
//            $types = getDictDisposition('activityType');
//            $activity['type'] = $types[$activity['type']];
//            $activity['content'] = htmlspecialchars_decode($activity['content']);
            $this->assign('talent', $talent);
            $this->display();
        }
    }

    /**
     *后台添加推荐人才
     * 2017-2-13
     */
    public function addTalent(){
        $id = I('get.id');
        if(!empty($id)){
            $Job = M('person_job');
            $job = $Job -> where('id='.$id) -> find();
            if(!empty($job['address'])){
                $address = explode(',',$job['address']);
                $job['prov'] = $address[0];
                $job['city'] = $address[1];
            }
            $job['jobs'] = json_decode($job['education'],true);
            $job['experience'] = json_decode($job['experience'],true);
//            var_dump($job['jobs']);
            $this->assign('job', $job);
        }
        $records = getDictTypes('学历');
        $areas = getAreas();
        $this->assign('records', $records);
        $this->assign('areas', $areas);
        $this->display();
    }

    /**
     *添加退推荐人才表单处理
     * 2017-2-7
     */
    public function addTalentHandle(){
        if(!empty($_POST)){
            $pid = I("post.applyid");
            $name = I('post.name');
            $phone = I("post.phone");
            $card = I("post.card");
            $contactphone = I("post.contactphone");
            $secondcontact = I("post.secondcontact");
            $flag = true;
            $msg = '';
            if(empty($name)){
                $msg .="姓名不能为空，";
                $flag = false;
            }
            if(empty($phone)){
                $msg .="联系方式不能为空，";
                $flag = false;
            }
            $preg = "/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/";
            if(empty($card)){
                $msg +="身份证号码不能为空，";
                $flag = false;
            }elseif(empty(preg_match($preg,$card))){
                $msg .="身份证号码格式不正确，";
                $flag = false;
            }
            if(empty($secondcontact)){
                $msg .="紧急联系人不能为空，";
                $flag = false;
            }
            if(empty($contactphone)){
                $msg.="紧急联系人电话不能为空，";
                $flag = false;
            }
            if(!$flag){
                $msg = trim($msg,'，');
                $this->error($msg);
            }
            $national = I("post.national");
            $school = I("post.school");
            $refere = I("post.refere");
            $height = I("post.height");
            $email = I("post.email");
            $birth = I("post.birth");
            $openid = I("post.openid");
            $weight = I("post.weight");
            $health = I("post.health");
            $sex = I("post.sex");
            $hometown = I("post.hometown");
            $sname = I("post.sname");
            if(!empty($pid)){
                $pname = getArea($pid);
            }else{
                $pname = '';
            }
            $address = $pname.','.$sname;
            $drivertype = I("post.drivertype");
            $marital = I("post.marital");
            $title = I("post.title");
            $english = I("post.english");
            $secondlanguage = I("post.secondlanguage");
            $computer = I("post.computer");
            $familyaddress = I("post.familyaddress");
            //教育经历
            $start = I("post.start");
            $end = I("post.end");
            $eduschool = I("post.eduschool");
            $professional = I("post.professional");
            $record = I("post.record");
            $degree = I("post.degree");
            $type = I("post.type");
            $descp = I("post.descp");
            $education = '';
            if(!empty($start)){
                $education = array();
                foreach($start AS $key=>$v){
                    $education[$key]['start'] = $v;
                    $education[$key]['end'] = $end[$key];
                    $education[$key]['school'] = $eduschool[$key];
                    $education[$key]['professional'] = $professional[$key];
                    $education[$key]['record'] = $record[$key];
                    $education[$key]['degree'] = $degree[$key];
                    $education[$key]['type'] = $type[$key];
                    $education[$key]['descp'] = $descp[$key];
                }
                $education = json_encode($education);
            }
            //工作经历
            $expstart = I("post.expstart");
            $expend = I("post.expend");
            $expcompany = I("post.expcompany");
            $telphone = I("post.telphone");
            $position = I("post.position");
            $experience = '';
            if(!empty($expstart)){
                $experience = array();
                foreach($expstart AS $k=>$val){
                    $experience[$k]['start'] = $val;
                    $experience[$k]['end'] = $expend[$k];
                    $experience[$k]['company'] = $expcompany[$key];
                    $experience[$k]['telphone'] = $telphone[$key];
                    $experience[$k]['position'] = $position[$key];
                }
                $experience = json_encode($experience);
            }
            //家庭成员
            $fname = I("post.fname");
            $relation = I("post.relation");
            $fcompany = I("post.fcompany");
            $fposition = I("post.fposition");
            $fphone = I("post.fphone");
            $family = '';
            if(!empty($fname)){
                $family  = array();
                foreach($fname AS $ky=>$item){
                    $family[$ky]['name'] = $item;
                    $family[$ky]['relation'] = $relation[$ky];
                    $family[$ky]['company'] = $fcompany[$ky];
                    $family[$ky]['phone'] = $fphone[$ky];
                    $family[$ky]['position'] = $fposition[$ky];
                }
                $family = json_encode($family);
            }
            $isfree = I("post.isfree");
            $status = I("post.status");
            $data = array(
                'pid' => $pid,
                'name' => $name,
                'phone' => $phone,
                'card' => $card,
                'contactphone' => $contactphone,
                'secondcontact' => $secondcontact,
                'national' => $national,
                'school' => $school,
                'height' => $height,
                'weight' => $weight,
                'birth' => $birth,
                'address' => $address,
                'openid' => $openid,
                'health' => $health,
                '$sex' => $sex,
                'hometown' => $hometown,
                'drivertype' => $drivertype,
                'email' => $email,
                'marital' => $marital,
                'title' => $title,
                'english' => $english,
                'secondlanguage' => $secondlanguage,
                'computer' => $computer,
                'familyaddress' => $familyaddress,
                'education' => $education,
                'experience' => $experience,
                'family' => $family,
                'isfree' => $isfree,
                'status' => $status,
                'created' => time(),
                'updated' => time()
            );
        }
        if(!empty($_FILES['picture']['name'])){
            $picture = parent::_upload('job');
            $data['picture'] = $picture;
        }
        $Talent = D('sys_talent');
        if($Talent->create()){
            if(M('sys_talent')->add($data)){
                $this->success('添加成功',U('Admin/Job/talent'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($Talent->getError());
        }
    }

}