<?php
namespace Home\Controller;
use Home\Controller\CommonController;
class JobController extends CommonController {

    //人才对接
    public function index(){
        //首页关键字
        $key = M("sys_keyword")->where("name='人才对接首页'")->find();
        $this->assign("key",$key);
        //头部企业图广告
        $companyBanner = M("banner")->where("type ='人才对接banner' AND status = 1")->order("weight DESC")->find();
        if(file_exists('./Public'.$companyBanner['picture'])){
            $url = str_replace('.','_big.',$companyBanner['picture'],$i);
            if(file_exists('./Public'.$url)){
                $companyBanner['picture'] = $url;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$companyBanner['picture']);
                $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_FIXED)->save('./Public'.$url);
                $companyBanner['picture'] = $url;
            }
        }
        $this->assign("banner",$companyBanner);
//        var_dump($companyBanner);
        $types = getDictTypes('companyType');
        $scale = getDictTypes('companySize');
        $this->assign('scale', $scale);
        $this->assign('types', $types);
        $records = getDictTypes('学历');
        $degree = getDictTypes('学位');
        $areas = getAreas();
        $this->assign('types', $types);
        $this->assign('scale', $scale);
        $this->assign('records', $records);
        $this->assign('degree', $degree);
        $this->assign('areas', $areas);
        $this->display();
    }

    /**
     *添加企业招生简章表单
     * 2017-2-23
     * djt
     */
    public function addCompanyJob(){
        if(IS_POST){
            $info = session("userinfo");
            if(empty($info)){
                $this->error("你还没有登录，请先登录");
            }
            if($info['role']!=2){
                $this->error("你不是企业身份无法添加职位");
            }
            $uid = $info['uid'];
            $company = M("company_info")->where("uid = {$uid}")->find();
            if(empty($company)){
                $this->error("你企业还未入驻，暂时无法添加");
            }
            $name = I('post.name');
            $phone = I("post.phone");
            $qq = I("post.qq");
            $job = I("post.job");
            $num = I("post.num");
            $wage = I("post.wage");
            $descp = I("post.descp");
            $starttime = I("post.starttime");
            $starttime = (int)strtotime($starttime);
            $endtime = I("post.endtime");
            $endtime = (int)strtotime($endtime);
            $level = I("post.level");
            $intro = I("post.intro");
            $notice = I("post.notice");
            $fax = I("post.fax");
            $address = I("post.address");
            $link = I("post.link");
            $note = I("post.note");
            $contact = I("post.contact");
            $position = I("post.position");
            $email = I("post.email");
            $status = I("post.status");
            $registernum = I("post.registernum");
            $type = I("post.type");
            $scale = I("post.scale");
            $telphone = I("post.telphone");
            $flag = true;
            $msg = '';
            if(empty($address)){
                $flag = false;
                $msg .= "公司地址，";
            }
            if(empty($name)){
                $flag = false;
                $msg .= "公司名称，";
            }
//            if(empty($phone)){
//                $flag = false;
//                $msg .= "负责人手机号，";
//            }
            if(empty($qq)){
                $flag = false;
                $msg .= "负责人QQ，";
            }
            if(empty($position)){
                $flag = false;
                $msg .= "负责人职位，";
            }
            if(empty($job)){
                $flag = false;
                $msg .= "职位，";
            }
            if(empty($num)){
                $flag = false;
                $msg .= "招聘人数，";
            }
            if(empty($wage)){
                $flag = false;
                $msg .= "薪资，";
            }
//            if(empty($descp)){
//                $flag = false;
//                $msg .= "职位描述，";
//            }
            if(empty($telphone)){
                $flag = false;
                $msg .= "公司座机，";
            }
            if(empty($registernum)){
                $flag = false;
                $msg .= "公司注册号，";
            }
            if(empty($type)){
                $status = false;
                $msg .= "公司类别，";
            }
            if(empty($scale)){
                $flag = false;
                $msg .= "公司规模，";
            }
            if(empty($_FILES['picture']['name'][0])){
                $flag = false;
                $msg .= "执照，";
            }
            if(empty($_FILES['picture']['name'][1])){
                $flag = false;
                $msg .= "环境，";
            }
            if(!$flag){
                $msg = trim($msg,',');
                $msg .= "不能为空";
                $this->error($msg);
            }
            if(!empty($_FILES['picture']['name'])){
                $pictures = parent::_uploads("job");
                $picture = $pictures[0];
                $companypic = $pictures[1];
            }
            if(!($picture&&$companypic)){
                $this->error('图片上传失败');
            }
            $data = array(
                'starttime' => $starttime,
                'endtime' => $endtime,
                'level' => $level,
                'registernum' => $registernum,
                'name' => $name,
                'type' => $type,
                'scale' => $scale,
                'intro' => $intro,
                'picture' => $picture,
                'companypic' => $companypic,
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
                'job' => $job,
                'wage' => $wage,
                'num' => $num,
                'descp' => $descp,
                'status' => $status,
                'created' => time(),
                'updated' => time()
            );
            $job= M('company_job')->add($data);
            if($job){
                $this->success("添加成功",U('Home/Job/index'));
            }else{
                $this->error("添加失败");
            }
        }else{
            $this->error("非法请求");
        }
    }
    /**
     *添加人才
     * 2017-2-23
     * djt
     */
    public function addTalent(){
        if(!empty($_POST)){
            $name = I('post.name');
            $refere = I("post.refere");
            $phone = I("post.phone");
            $card = I("post.card");
            $contactphone = I("post.contactphone");
            $secondcontact = I("post.secondcontact");
            $national = I("post.national");
            $school = I("post.school");
            $height = I("post.height");
            $email = I("post.email");
            $birth = I("post.birth");
            $openid = I("post.openid");
            $weight = I("post.weight");
            $health = I("post.health");
            $sex = I("post.sex");
            $hometown = I("post.hometown");
            $pname = I("post.pname");
            $sname = I("post.sname");
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
            $type = I("post.shape");
            $descp = I("post.descp");
            $education = '';
            $flag = true;
            $msg = '';
            if(empty($start)){
                $msg .="教育开始时间不能为空，";
                $flag = false;
            }
            if(empty($end)){
                $msg .="教育结束时间不能为空，";
                $flag = false;
            }
//            if(empty($eduschool)){
//                $msg .="教育学校不能为空，";
//                $flag = false;
//            }
            if(empty($professional)){
                $msg .="专业不能为空，";
                $flag = false;
            }
            if(empty($record)){
                $msg .="学历不能为空，";
                $flag = false;
            }
            if(empty($degree)){
                $msg .="学位不能为空，";
                $flag = false;
            }
            if(empty($hometown)){
                $msg .="户口所在地不能为空，";
                $flag = false;
            }
//            if(empty($title)){
//                $msg .="技术职称不能为空，";
//                $flag = false;
//            }
            if(empty($national)){
                $msg .="民族不能为空，";
                $flag = false;
            }
//            if(empty($refere)){
//                $msg .="推介人不能为空，";
//                $flag = false;
//            }
            if(empty($birth)){
                $msg .="出生日期不能为空，";
                $flag = false;
            }
//            if(empty($openid)){
//                $msg .="微信号不能为空，";
//                $flag = false;
//            }
            if(empty($school)){
                $msg .="学校不能为空，";
                $flag = false;
            }
            if(empty($name)){
                $msg .="姓名不能为空，";
                $flag = false;
            }
//            if(empty($phone)){
//                $msg .="联系方式不能为空，";
//                $flag = false;
//            }
            $preg = "/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/";
            if(empty($card)){
                $msg +="身份证号码不能为空，";
                $flag = false;
            }elseif(empty(preg_match($preg,$card))){
                $msg .="身份证号码格式不正确，";
                $flag = false;
            }
//            if(empty($secondcontact)){
//                $msg .="紧急联系人不能为空，";
//                $flag = false;
//            }
//            if(empty($contactphone)){
//                $msg.="紧急联系人电话不能为空，";
//                $flag = false;
//            }
//            if(!empty($pid)){
//                $pname = getArea($pid);
//            }else{
//                $pname = '';
//            }
            if(!empty($start)){
                $education = array();
//                foreach($start AS $key=>$v){
                    $education['start'] = $start;
                    $education['end'] = $end;
                    $education['school'] = $eduschool;
                    $education['professional'] = $professional;
                    $education['record'] = $record;
                    $education['degree'] = $degree;
                    $education['type'] = $type;
                    $education['descp'] = $descp;
//                }
                $education = json_encode($education);
            }
            //工作经历
            $expstart = I("post.expstart");
            $expend = I("post.expend");
            $expcompany = I("post.expcompany");
            $telphone = I("post.telphone");
            $position = I("post.position");
            $experience = '';
            if(empty($expstart)){
                $msg .="工作经历开始时间不能为空，";
                $flag = false;
            }
            if(empty($expend)){
                $msg .="工作经历结束时间不能为空，";
                $flag = false;
            }
            if(empty($expcompany)){
                $msg .="公司名称不能为空，";
                $flag = false;
            }
//            if(empty($telphone)){
//                $msg .="公司座机电话不能为空，";
//                $flag = false;
//            }
            if(empty($position)){
                $msg .="工作经历职位不能为空，";
                $flag = false;
            }
            if(!empty($expstart)){
                $experience = array();
//                foreach($expstart AS $k=>$val){
                    $experience['start'] = $expstart;
                    $experience['end'] = $expend;
                    $experience['company'] = $expcompany;
                    $experience['telphone'] = $telphone;
                    $experience['position'] = $position;
//                }
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
            $isfree = I("post.charge");
//            $status = I("post.status");
            if(!$flag){
                $msg = trim($msg,'，');
                $this->error($msg);
            }
            $data = array(
                'name' => $name,
                'phone' => $phone,
                'card' => $card,
                'contactphone' => $contactphone,
                'secondcontact' => $secondcontact,
                'national' => $national,
                'school' => $school,
                'height' => $height,
                'refere' => $refere,
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
                'created' => time(),
                'updated' => time()
            );
            if(!empty($_FILES['picture']['name'])){
                $picture = parent::_upload('job');
                $data['picture'] = $picture;
            }
            $Talent = D('person_job');
            $result = $Talent->add($data);
            if(empty($result)){
                $this->error("添加失败");
            }else{
                $this->success("添加成功",U("Home/Job/index"));
            }
        }else{
            $this->error("非法请求");
        }
    }
    /**
     * 职位详情
     * 2017-2-24
     * djt
     */
    public function jobs(){
        $id = _get('id');
        if(empty($id)){
            $this->error("参数错误");
        }
        //banner
        $jobBanner = M("banner")->where("type ='招聘banner' AND status = 1")->order("weight DESC")->find();
        $this->assign("banner",$jobBanner);
        $job = M("sys_job")->where("id = {$id}")->find();
        $this->assign('job',$job);
        //培训咨询
        $courseArticle = M("review_course")->where("status = 1")->field("id,title,picture,created")->order("id DESC")->select();
        $this->assign("courseArticle",$courseArticle);
        $this->display();
    }

    /**
     * 人才简历
     * 2017-2-24
     * djt
     */
    public function recruit(){
        $id = _get('id');
        if(empty($id)){
            $this->error("参数错误");
        }
        //banner
        $jobBanner = M("banner")->where("type ='应聘banner' AND status = 1")->order("weight DESC")->find();
        $this->assign("banner",$jobBanner);
        $talent = M("sys_talent")->where("id = {$id}")->find();
        $this->assign('talent',$talent);
        if(!empty($talent['education'])){
            foreach($talent['education'] AS &$v){
                if($v['type']=='其他'){
                    $v['type'] = $v['descp'];
                }
            }
        }
        $talent['experience'] = json_decode($talent['experience'],true);
        $talent['family'] = json_decode($talent['family'],true);
        $talent['education'] = json_decode($talent['education'],true);
        $this->assign('talent', $talent);
//        var_dump($talent);
        //培训咨询
        $courseArticle =  M("review_course")->where("status = 1")->field("id,title,picture,created")->order("id DESC")->select();
        $this->assign("courseArticle",$courseArticle);
        $this->display();
    }

}