<?php
namespace Home\Controller;
use Think\Controller;
use Home\Controller\CommonController;
use Think\Model;
use Org\Sms;
class UserController extends CommonController {
    public function index(){
        echo "<script>alert(1111);</script>";
    }

    /**
     * djt
     * 用户注册
     * 2017-2-8
     */
    public function register(){
        $fromid = _get('fromid');
        if(!empty($fromid)){
            session('fromid',$fromid);
        }
        if(IS_POST){
            $name = I('post.name');
            $phone = I('post.phone');
            $pwd = I('post.pwd');
            $repwd = I('post.repwd');
            $role = I('post.role');
            $code = I('post.code');
            $status = 1;
            $msg = '';
            if(empty($name)){
                $status = 0;
                $msg.="用户名不能为空";
            }
            if(empty($pwd)){
                $status = 0;
                $msg.="密码不能为空,";
            }
            if(empty($phone)){
                $status = 0;
                $msg.="手机号不能为空,";
            }
            $pregPhone = "/^1\d{10}$/";
            $pregResult = preg_match($pregPhone, $phone);
            if (empty($pregResult)){
                $status = 0;
                $msg.="手机号格式不正确,";
            }
            if(empty($pwd)||(strlen($pwd)<6)){
                $status = 0;
                $msg.="密码不能为空且长度不小于6位,";
            }
            if(empty($code)){
                $status = 0;
                $msg.="验证码不能为空,";
            }
            if($status == 0){
                $msg = trim($msg,',');
                $returndata = array('msg'=>$msg,'status'=>0);
                $this->ajaxReturn($returndata);
            }
            //验证码是否正确
            $Code = M("phonecode");
            $time = time();
            $expires_time = $time-60*5;//5分钟过期
            $codeResult = $Code->where("phone = '{$phone}' AND status=0 AND created > $expires_time")->order("id DESC")->find();
            if(empty($codeResult)) {
                $msg = '';
                $this->ajaxReturn(array('msg'=>'验证码已失效','status'=>0));
            }
            if($code!=$codeResult['code']) {
                $this->ajaxReturn(array('msg'=>'验证码填写不正确','status'=>0));
            }
            $id = $codeResult['id'];
            //验证码使用后修改它的状态
            if($code==$codeResult['code']) {
                $codeUpdated = $Code->where("id = {$id}")->save(array('status'=>1));
            }
            if($pwd!==$repwd){
                $msg = "密码前后不一致";
                $returndata = array('msg'=>$msg,'status'=>0);
                $this->ajaxReturn($returndata);
            }
            $pwd = md5("wwk".$pwd);
            $data = array(
                'type' => 0,
                'name' => $name,
                'role' => $role,
                'phone' => $phone,
                'password' => $pwd,
                'status' => 1,
                'created' => time(),
                'updated' => time(),
            );

            //用户唯一性根据手机号和昵称
            $User = M('users');
            $user = $User->where("phone = '{$phone}'")->field('uid')->find();
            $msg = '';
            if($user['uid']){
                $msg .= '该手机号已经注册,';
            }
            $username = $User->where("name = '{$name}'")->field('uid')->find();
            if($username['uid']){
                $msg .= '昵称已经被使用';
            }
            if(($user['uid'])||($username['uid'])){
                $this->ajaxReturn(array('msg'=>$msg,'status'=>0));
            }
            $model = new Model();
            $model->startTrans();
            $Info =M('person_info');
//            $Company = M("company_info");
            $userResult = $User->add($data);
                if($userResult){
                    $uid = $userResult;
                    //获取注册任务添加的金币数
                    $Task = M("vcoin_task");
                    $task = $Task->where("action ='注册' AND status = 1")->find();
                    $taskid = $task['id'];
                    $vcoin = $task['value'];
                    $infoArray = array(
                        'uid' => $uid,
                        'vcoin' => $vcoin,//注册送维币
                        'created' => time(),
                        'updated' => time()
                    );
//                    if($role==2){
//                        $company = array(
//                            'uid' => $uid,
//                            'ename' => $name,
//                            'created' => time(),
//                            'updated' => time()
//                        );
//                        $infoResult = $Info->add($infoArray);
//                        $companyResult = $Company->add($company);
//                        if($infoResult&&$companyResult){
//                            $model->commit();
//                            $this->ajaxReturn(array('msg'=>'注册成功1','status'=>1));
//                        }else{
//                            $model->rollback();
//                            $this->ajaxReturn(array('msg'=>'注册失败1','status'=>0));
//                        }
//                    }else{
                        $infoResult = $Info->add($infoArray);
                        if($infoResult){
                            //添加任务记录
                            $Log = M("task_log");
                            $logarray = array(
                                'uid' => $uid,
                                'taskid' => $taskid,
                                'created' => time()
                            );
                            $log = $Log->add($logarray);
                            if(empty($log)){
                                $model->rollback();
                                $this->ajaxReturn(array('msg'=>'注册失败','status'=>0));
                            }
                            //邀请注册邀请用户金币增加
                            $fromid = session("fromid");
                            if(!empty($fromid)){
                                $task = $Task->where("action ='邀请注册' AND status = 1")->find();
                                if(!empty($task)){
                                    $rid = $task['id'];
                                    $rvcoin = $task['value'];
                                    //邀请用户维币增加
                                    $rinfo = $Info->where("uid = {$fromid}")->setInc('vcoin',$rvcoin);
                                    if(!$rinfo){
                                        $model->rollback();
                                        $this->ajaxReturn(array('msg'=>'注册失败1','status'=>0));
                                    }
                                    //添加任务记录
                                    $Log = M("task_log");
                                    $flogarray = array(
                                        'uid' => $fromid,
                                        'taskid' => $rid,
                                        'created' => time()
                                    );
                                    $flog = $Log->add($flogarray);
                                    if(!$flog){
                                        $model->rollback();
                                        $this->ajaxReturn(array('msg'=>'注册失败2','status'=>0));
                                    }
                                    //写入维币记录
                                    $Vlog = M("vcoin_log");
                                    $vlogAarry = array(
                                        'uid' => $fromid,
                                        'action' => "邀请注册",
                                        'organizer' => "维沃珂",
                                        'intro' => "邀请好友注册成功",
                                        'type' => 2,
                                        'value' => $rvcoin,
                                        'created' => time()
                                    );
                                    $vlog = $Vlog->add($vlogAarry);
                                    if(!$vlog){
                                        $model->rollback();
                                        $this->ajaxReturn(array('msg'=>'注册失败3','status'=>0));
                                    }
                                }
                            }
                            $model->commit();
                            $this->ajaxReturn(array('msg'=>'注册成功','status'=>1));
                        }else{
                            $model->rollback();
                            $this->ajaxReturn(array('msg'=>'注册失败','status'=>0));
                        }
//                    }
                }else{
                    $model->rollback();
                    $this->ajaxReturn(array('msg'=>'注册失败','status'=>0));
                }
        }
        $this->display();
    }

    /**2017-2-15
     * @desc 编辑个人资料
     * djt
     */
    public function editInfo() {
        $info = session('userinfo');
        if(empty($info)){
            $this->error('你还没有登录，请先登录');
        }
        $uid = $info['uid'];
        $role = $info['role'];
        $this->assign('role',$role);
        if(IS_POST){
            $sex = I("post.sex");
            $email = I("post.email");
            $birth = I("post.birth");
            $prov = I("post.prov");
            $city = I("post.city");
            $area = I("post.area");
            $intro = I("post.intro");
            $mobile = I("post.mobile");
            $name = I("post.name");
//            $sex = 2;
//            $email = "23242434";
//            $intro = "2一直永远不倒的队伍";
//            $birth = "2017-12-12";
//            $prov = "2北京";
//            $city = "2东城区";
//            $area = "2185号，21栋21楼";
//            $mobile = "13166292960";
            $flag = true;
            $msg = '';
            $Task = M("vcoin_task");
            $task = $Task->where("action = '个人完善资料' AND status = 1")->find();
            $taskid = $task['id'];
            $taskcoin = $task['value'];
            //获取原有维币数
            $Person = M("person_info");
            $coin = $Person->where("uid = {$uid}")->find();
            $coins = $coin['vcoin'];
            $vcoin = $taskcoin + $coins;
            $Log = M("task_log");
            $log = $Log->where("uid = {$uid}  AND taskid={$taskid}")->find();
            $preg = "/^1\d{10}$/";
            //第一次完善资料头像，昵称，等必填
            if(empty($log)){
//                if(empty($email)){
//                    $flag = false;
//                    $msg.="邮箱不能为空,";
//                }
//                if(empty($birth)){
//                    $flag = false;
//                    $msg.="生日不能为空,";
//                }
                if(empty($mobile)){
                    $flag = false;
                    $msg.="手机不能为空,";
                }else{
                    $pregResult = preg_match($preg, $mobile);
                    if (empty($pregResult)){
                        $flag = false;
                        $msg.="手机号格式不正确,";
                    }
                    if(empty($prov)){
                        $flag = false;
                        $msg.="省份不能为空,";
                    }
                    if(empty($city)){
                        $flag = false;
                        $msg.="市不能为空,";
                    }
                    $picture = '';
                    if(!empty($_FILES['picture']['name'])){
                        $picture = parent::_upload("person");
                    }
//                    $picture = "/Uploads/news/20170209/589c46848503e.jpg";
                    if(empty($picture)){
                        $flag = false;
                        $msg.="头像不能为空,";
                    }
                }
                if(!$flag){
                    $msg = trim($msg,',');
                    $this->error($msg);
                }
                $data = array(
                    'vcoin' => $vcoin,
                    'sex' => $sex,
                    'avatar' => $picture,
                    'email' => $email,
                    'mobile'=>$mobile,
                    'birth' => $birth,
                    'prov' => $prov,
                    'city' => $city,
                    'area' => $area,
                    'intro' => $intro,
                    'updated' => time()
                );
                $model = new Model();
                $model->startTrans();
                $infoResult = M("person_info")->where("uid = {$uid}")->save($data);
                if($infoResult===false){
                    $model->rollback();
                    $this->error("更新失败1");
                }
                if(!empty($name)){
                    $nameData = array(
                      'name'=>$name,
                      'updated'=>time()
                    );
                    $nameResult = M('users')->where("uid = {$uid}")->save($nameData);
                    if($nameResult===false){
                        $model->rollback();
                        $this->error("更新失败11");
                    }
                }
               //写入任务记录
                $logarray  = array(
                    'uid' => $uid,
                    'taskid' => $taskid,
                    'created' => time()
                );
//                var_dump($logarray);exit;
                $logResult = $Log->add($logarray);
                if(empty($logResult)){
                    $model->rollback();
                    $this->error("更新失败2");
                }
                //写入维币记录
                $Vlog = M("vcoin_log");
                $vlogAarry = array(
                    'uid' => $uid,
                    'action' => "完善个人资料",
                    'organizer' => "维沃珂",
                    'intro' => "首次完善资料奖励",
                    'type' => 2,
                    'value' => $taskcoin,
                    'created' => time()
                );
                $vlog = $Vlog->add($vlogAarry);
//                echo $Vlog->getLastSql();exit;
                if(empty($vlog)){
                    $model->rollback();
                    $this->error("更新失败4");
                }
                $model->commit();
                $this->success("更新成功1");
            }else{
                $data = array();
                if(!empty($sex)){
                    $data['sex2'] = $sex;
                }
                if(!empty($email)){
                    $data['email'] = $email;
                }
                if(!empty($birth)){
                    $data['birth'] = $birth;
                }
                if(!empty($prov)){
                    $data['prov'] = $prov;
                }
                if(!empty($city)){
                    $data['city'] = $city;
                }
                if(!empty($area)){
                    $data['area'] = $area;
                }
                if(!empty($mobile)){
                    $data['mobile'] = $mobile;
                }
                if(!empty($intro)){
                    $data['intro'] = $intro;
                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload("person");
                }
                if(!empty($picture)){
                    $data['avatar'] = $picture;
                }
                if(!empty($data)){
                    $infoResult1 = M("person_info")->where("uid = {$uid}")->save($data);
//                    echo M("person_info")->getLastSql();
//                    var_dump($infoResult1);
//                    exit;
                    if($infoResult1===false){
                        $this->error("更新失败3");
                    }
                }
                if(!empty($name)){
                    $nameData = array(
                        'name'=>$name,
                        'updated'=>time()
                    );
                    $nameResult = M('users')->where("uid = {$uid}")->save($nameData);
                    if($nameResult===false){
                        $this->error("更新失败31");
                    }
                }
                $this->success("更新成功");
            }
        }
        $areas = getAreas();
        $Info = M("person_info");
        $info = M("person_info as a")->join("LEFT join ww_users as b ON a.uid=b.uid")->where("a.uid = {$uid}")->field("a.*,b.name")->find();
        $this->assign("info",$info);
        $this->assign("areas",$areas);
        $this->display();
    }

    /**
     * djt
     * 用户登录
     * 2017-2-9
     */
    public function login(){
        if(IS_POST){
            $url = session('url');
            $phone = I('post.phone');
            $pwd = I('post.pwd');
            $status = 1;
            $msg = '';
            if(empty($phone)){
                $status = 0;
                $msg .= "手机号不能为空,";
            }
            if(empty($pwd)){
                $status = 0;
                $msg .= "密码不能为空";
            }
            if($status==0){
                $this->error($msg);
            }
            $pwd = md5("wwk".$pwd);
            $User = M("users");
            $info = $User->where("phone = '{$phone}' AND password = '{$pwd}' AND status = 1 ")->find();
            if(empty($info)){
                $this->error("用户名或者密码错误");
            }
            //登录用户是否入驻
            $uid = $info['uid'];
            $com = M("company_info")->where("uid={$uid} AND status = 1 AND ispay = 1 AND companystatus = 1")->find();
            if(empty($com)){
                $info['companystatus'] = 0;
            }else{
                $info['companystatus'] = 1;
            }
            session('userinfo',$info);
            $userinfo = session('userinfo');
            $name = $userinfo['name'];
            $uid = $userinfo['uid'];
            $ip = get_client_ip();
            $num = $info['num'] + 1;
            $updated = array(
                'ip' => $ip,
                'num' => $num,
                'updated' => time()
            );
            $updatedResult = M('users')->where("uid = {$uid}")->save($updated);
            if($updatedResult!==false&&(!empty($userinfo))){
                if(empty($url)){
                    if($num==1&&$info['role']==2){
                        $this->redirect('Home/Index/index');
                    }else{
                        $this->redirect('Home/Index/index');
                    }
                }else{
                    session('url',null);
                    Header("HTTP/1.1 303 See Other");
                    Header("Location: $url");
                    exit;
                }
            }else{
                $this->error('登录失败',U('Home/Index/index'));
            }
        }
        $url = _get('url');
        if(!empty($url)){
//            dump($url);exit;
            session('url',$url);
        }
        $this->display();
    }

    /**
     * djt
     * 用户登录
     * 2017-2-9
     */
    public function logout(){
        if(!empty(session("userinfo"))){
            session("userinfo",null);
            if(empty(session('userinfo'))){
                $this->redirect('Home/Index/index');
            }
        }else{
            $this->error('已经登出了');
        }
    }



    /**
     * @desc 发送验证码
     * @date 2017-2-9
     */
    public function sendCode(){
        //请求参数处理
        $phone = I("post.phone");
//        $phone = "13166292960";
        if (empty($phone)){
            $this->ajaxReturn(array('msg'=>"手机号码不能为空",'status'=>0));
        }
        $pregPhone = "/^1\d{10}$/";
        $result = preg_match($pregPhone, $phone);
        if (empty($result)){
            $this->ajaxReturn(array('msg'=>"手机号码格式不正确",'status'=>0));
        }
        //发送消息
        $time = time();
        $expires_time = $time;//5分钟过期
        $Code = M("phonecode");
        $result = $Code->where("phone = '{$phone}' AND status = 0 AND created > {$expires_time}")->order("id DESC")->find();
        //5min中内不能重复发送
        if ($result) {
            $this->ajaxReturn(array('msg'=>"验证码5分钟内有效，请不要重复发送",'status'=>0));
        }
        //生成手机验证码
        $code = str_pad(mt_rand(0, pow(10, 4) - 1), 4, '0', STR_PAD_LEFT);
        //先将数据写入数据库，防止发送成功后但是为未入数据库的情况
        $time = time();
        $data = array(
            'phone'=>$phone,
            'code'=>$code,
            'created'=>$time
        );
        $insetResult = $Code->add($data);
        if (!$insetResult) {
            $this->ajaxReturn(array('msg'=>"未知错误请稍后重试",'status'=>0));
        }
        //发送消息内容
        $content = '您获得验证码是：'.$code.'，5分钟内有效。如非本人操作，请忽略。';
        $sms  = new \Org\Sms\ChuanglanSmsApi();
        $smsResult = $sms->sendSMS($phone, $content, 1);
        $smsResult = $sms->execResult($smsResult);
        if ($smsResult[1]==0) {
            $this->ajaxReturn(array('msg'=>"发送成功",'status'=>1));
        }else{
            $this->ajaxReturn(array('msg'=>"发送失败,请稍后重试",'status'=>0));
        }
    }

    /**
     * djt
     * 忘记密码，修改密码
     * 2017-2-9
     */
    public function changePwd(){
        if(IS_POST){
            $phone = I('post.phone');
            $pwd = I('post.pwd');
            $repwd = I('post.repwd');
            $code = I('post.code');
//            $phone = "13133292640";
//            $pwd = "123456";
//            $repwd = "123456";
//            $code = "6543";
            if(empty($phone)){
                $this->error("手机号码不能为空");
            }
            if(empty($pwd)){
                $this->error("密码不能为空");
            }
            if($pwd!=$repwd){
                $this->error("密码前后不一致");
            }
            if(empty($code)){
                $this->error("验证码不能为空");
            }
            $preg = "/^1\d{10}$/";
            $pregResult = preg_match($preg,$phone);
            if(empty($pregResult)){
                $this->error("手机号码格式不正确");
            }
            //查询用户是否存在
            $User = M("users");
            $result = $User->where("phone ='{$phone}'")->find();
            if(empty($result)) {
                $this->error("用户不存在,请先注册");
            }
            //验证码是否正确
            $Code = M("phonecode");
            $time = time();
            $expires_time = $time-60*5;//5分钟过期
            $codeResult = $Code->where("phone = '{$phone}' AND status=0 AND created > $expires_time")->order("id DESC")->find();
            if(empty($codeResult)) {
                $this->error("验证码已失效");
            }
            if($code!=$codeResult['code']) {
                $this->error("验证码填写不正确");
            }
            $id = $codeResult['id'];
            //验证码使用后修改它的状态
            if($code==$codeResult['code']) {
                $codeUpdated = $Code->where("id = {$id}")->save(array('status'=>1));
            }
            //修改密码
            $newPassword  = md5("wwk".$pwd);
            $ip = get_client_ip();
            $pwData = array(
                'password'=>$newPassword,
                'updated' => time(),
                'ip' => $ip
            );
            $updated = $User->where("phone = '{$phone}'")->save($pwData);
            if($updated===false) {
                $this->error("修改失败");
            }
            $this->success("修改成功",U("Home/User/login"));
        }else{
            $this->display();
        }

    }
}