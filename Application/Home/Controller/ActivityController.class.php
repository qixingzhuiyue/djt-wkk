<?php
/**
 * @desc 活动管理
 */
namespace Home\Controller;
use Think\Crypt\Driver\Think;
use Think\Model;
use Org\Util\Page;
use Home\Controller\CommonController;
class ActivityController extends CommonController {
    /**
     * 活动主页
     * @desc djt
     * 2017-2-24
     */
    public function index() {
        //头部企业图广告
        $companyBanner = M("banner")->where("type ='活动index' AND status = 1")->order("weight DESC")->find();
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
        //精彩活动回顾
        $review = M("review_activity")->where("status = 1")->field("picture,title,intro,id")->limit(7)->order("created DESC")->select();
        $this->assign("review",$review);
        //活动
        $now = time();
        $condition = "status = 1 AND endtime>{$now}";
        $type = _get("type");
        $isfree = _get("isfree");
        $limit = _get("limit");
        if(!empty($type)){
            $condition.=" AND type = '{$type}'";
            $this->assign("type1",$type);
        }
        $this->assign("isfree",$isfree);
        if(!empty($limit)){
            $this->assign("limit",$limit);
            $time = time();
            if($limit==1){
                $start = $time-86400;
                $condition.=" AND created > $start";
            }
            if($limit==2){
                $start = $time-86400*7;
                $condition.=" AND created > $start";
            }
            if($limit==3){
                $start = $time-86400*30;
                $condition.=" AND created > $start";
            }
//            if($limit==4){
//                $str = " AND datepart(weekday,created)=1 or datepart(weekday,created)=7";
//                $condition.=$str;
//            }
        }
//        if(!empty($time)){
//
//        }
            if($isfree!==''){
                $isfree=(int)$isfree;
                if($isfree!=3){
                    $condition.=" AND isfree = $isfree";
                }
            }else{
                $isfree = 3;
                $this->assign('isfree',$isfree);
            }
        //活动列表
        $Activity = M("activity");
        //类型
        $types = getDictTypes("activityType");
        $this->assign('types',$types);
        $this->assign('type',$type);
        //查询
        $count= $Activity->where($condition)->count();
        $Page       = new \Think\Page($count, 12);
        $show     = $Page->show();
        $activitys = $Activity->where($condition)->limit($Page->firstRow.','.$Page->listRows)->order(array('ispush'=>'DESC','updated'=>'DESC','created'=>'DESC'))->select();
        $num = C("VCOIN_NUM");
        foreach($activitys AS &$v){
            if($v['isfree']==0){
                $v['vcoin'] = "免费";
            }else{
                $v['vcoin'] = (int)round($v['price']*$num)."维币";
            }
            //头像
            $pic = M("users as a")->join("LEFT join ww_person_info as b ON a.uid=b.uid")->where("a.uid={$v['uid']}")->field('a.role as role,b.avatar as avatar')->find();
            if($pic['role']==1){
                $v['avatar'] = $pic['avatar'];
            }else{
                $companyAvatar = M("company_info")->where("uid={$v['uid']}")->find();
                $v['avatar'] = $companyAvatar['logo'];
            }
            if(file_exists('./Public'.$v['avatar'])){
                $url1 = str_replace('.','_little.',$v['avatar'],$i);
                if(file_exists('./Public'.$url1)){
                    $v['avatar'] = $url1;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$v['avatar']);
                    $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url1);
                    $v['avatar'] = $url1;
                }
            }
            if(file_exists('./Public'.$v['picture'])){
                $url2 = str_replace('.','_mid.',$v['picture'],$i);
                if(file_exists('./Public'.$url2)){
                    $v['picture'] = $url2;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$v['picture']);
                    $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url2);
                    $v['picture'] = $url2;
                }
            }
            //报名人数
            $applyCount = M("apply_activity")->where("ispay = 1 AND activityid = {$v['activityid']}")->count();
            $activity['applyNum'] = (int)$applyCount;
            if(empty($v['limit'])){
                $v['applyLimit'] = 1;
            }elseif($applyCount<$v['limit']){
                $v['applyLimit'] = 1;
            }else{
                $v['applyLimit'] = 0;
            }
            $info = session('userinfo');
            if(!empty($info['uid'])){
                //是否报名
                $uid = $info['uid'];
                $apply = M("apply_activity")->where("uid = {$uid} AND activityid = {$v['activityid']}")->find();
                if(!empty($apply)){
//            $this->ajaxReturn(array("msg"=>'你已经报名，可前往订单管理查看进度','status'=>0));
                    $v['applyStatus'] = 1;
                }else{
                    $v['applyStatus'] =0;
                }
            }
        }
        $this->assign("activitys",$activitys);
        $this->assign("page",$show);
//        echo $page;exit;
//        var_dump($company);
//        echo $company;exit;
        $this->display();
    }

    /**
     * 发布活动
     * 2017-2-25
     * djt
     */
    public function activityPublish(){
        $info = session("userinfo");
        if(empty($info)){
            $this->error('你还没有登录，请前往登录',U('Home/User/login'));
        }
        if($info['role'] !=2){
            $this->error("当前身份不能操作");
//            $this->ajaxReturn(array('msg'=>'当前身份状态不能操作','status'=>0));
        }
//        $uid = $info['uid'];
//        $Company = M("company_info");
//        $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
//        if(empty($company)){
//            $this->error("你还未成功完成入驻流程，不能发布文贴");
////            $this->ajaxReturn(array('msg'=>'你还未成功完成入驻流程，不能添加产品','status'=>0));
//        }
//        $this->assign('company',$company);
//        $companyid = $company['companyid'];
        if(IS_POST){
            if(empty($info['uid'])){
                $this->error("您还没有登录，请先登录");
            }
            $uid = $info['uid'];
            $title = I("post.title");
            $starttime = I("post.starttime");
            $endtime = I("post.endtime");
            $signendtime = I("post.signendtime");
            $prov = I("post.prov");
            $city = I("post.city");
            $address = I("post.address");
            $type = I("post.type");
            $content = I("post.content");
            $isfree = I("post.isfree");
            $limit = I("post.limit");
            $price = I("post.price");
            $contact = I("post.contact");
            $telephone = I("post.telephone");
            $id = I('post.id');
            $msg = '';
//            var_dump($_POST);
////
//            var_dump(strtotime($starttime));
            if(empty($id)){
                $flag = true;
                if(empty($title)){
                    $msg .="名称,";
                    $flag = false;
                }
                if(empty($starttime)){
                    $msg .="开始时间,";
                    $flag = false;
                }
                if(empty($endtime)){
                    $msg .="结束时间,";
                    $flag = false;
                }
                if(empty($prov)){
                    $msg .="省份,";
                    $flag = false;
                }
                if(empty($city)){
                    $msg .="市区,";
                    $flag = false;
                }
                if(empty($address)){
                    $msg .="详细地址,";
                    $flag = false;
                }
                if(empty($telephone)){
                    $msg .="电话,";
                    $flag = false;
                }
                if(empty($contact)){
                    $msg .="联系人,";
                    $flag = false;
                }
                if(empty($_FILES['picture']['name'])){
                    $msg .="图片,";
                    $flag = false;
                }
                if(empty($type)){
                    $msg .="类型,";
                    $flag = false;
                }
                if(empty($content)){
                    $msg .="活动详情,";
                    $flag = false;
                }
                if(!$flag){
                    $msg =trim($msg,',');
                    $this->error($msg."不能为空");
                }
                $picture = parent::_upload('activity');
                if($isfree==1){
                    if(empty($price)){
                        $this->error("收费活动必须填写价格");
                    }
                }else{
                    $price = 0.00;
                }
                //如果无报名结束时间默认为活动开始时间
                $signendtime = empty($signendtime) ? (int)strtotime($starttime) : (int)strtotime($signendtime);
                $data = array(
                    'uid' => $uid,
                    'starttime' => (int)strtotime($starttime),
                    'endtime' => (int)strtotime($endtime),
                    'signendtime' => $signendtime,
                    'prov' => $prov,
                    'city' => $city,
                    'address' => $address,
                    'picture' => $picture,
                    'type' => $type,
                    'organizer' => $info['name'],
                    'title' => $title,
                    'content' => $content,
                    'isfree' => $isfree,
                    'limit' => $limit,
                    'price' => $price,
                    'contact'=> $contact,
                    'telephone' => $telephone,
                    'created' => time(),
                    'updated' => time()
                );
                //添加活动
                $result = M("activity")->add($data);
                if(!$result){
                    $this->error("发布失败");
                }
                $this->success("发布成功",U("Home/Activity/activityMange"));
            }else{
                $upData = array();
                if(!empty($title)){
                    $upData['title'] = $title;
                }
                if(!empty($starttime)){
                    $upData['starttime'] = (int)strtotime($starttime);
                }
                if(!empty($endtime)){
                    $upData['endtime'] = (int)strtotime($endtime);
                }
                if(!empty($prov)){
                    $upData['prov'] = $prov;
                }
                if(!empty($city)){
                    $upData['city'] = $city;
                }
                if(!empty($address)){
                    $upData['address'] = $address;
                }
                if(!empty($telephone)){
                    $upData['telephone'] = $telephone;
                }
                if(!empty($contact)){
                    $upData['contact'] = $contact;
                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload('activity');
                }
                if(!empty($picture)){
                    $upData['picture'] = $picture;
                }
                if(!empty($type)){
                    $upData['type'] = $type;
                }
                if(!empty($content)){
                    $upData['content'] = $content;
                }
                $upData['limit'] = $limit;
                $upData['isfree'] = $isfree;
                $upData['price'] = $price;
                $upData['updated'] = time();
//                if($isfree==1){
//                    if(empty($price)){
//                        $this->error("收费活动必须填写价格");
//                    }
//                }else{
//                    $price = 0.00;
//                }
                //如果无报名结束时间默认为活动开始时间
//                $signendtime = empty($signendtime) ? (int)strtotime($starttime) : (int)strtotime($signendtime);
                //添加活动
                $result = M("activity")->where("activityid={$id}")->save($upData);
                $this->success("编辑成功",U("Home/Activity/activityMange"));
            }
        }else{
            //获取活动类型
            $id = _get('id');
            if(!empty($id)){
                $activity = M('activity')->where("activityid={$id}")->find();
                if(!empty($activity)){
                    $activity['starttime'] = date('Y-m-d H:i',$activity['starttime']);
                    $activity['endtime'] = date('Y-m-d H:i',$activity['endtime']);
                    $activity['content'] = htmlspecialchars_decode( $activity['content']);
                }
                $this->assign('activity',$activity);
            }
            $types = getDictTypes("activityType");
            $this->assign('types',$types);
            $this->display();
        }

    }
    /**
     * 活动报名详情
     * @desc
     * 2017-4-7
     * djt
     */
    public function activitySignUp() {
        $num = C("VCOIN_NUM");
        $info = session("userinfo");
        $id = (int)_get("id");
        if (empty($id)) {
            $this->error('参数错误');
        }
        $activity = M("activity")->where("activityid={$id}")->find();
        if(empty($activity)){
            $this->error('非法操作，参数错误');
        }
        $condition = "activityid={$id}";
        $keywords = _get('keywords');
        if(!empty($keywords)){
            $condition.=" AND (name like '%{$keywords}%' OR mobile like '%{$keywords}%')";
        }
        $Apply = M('apply_activity');
        $applys = $Apply ->where($condition)->order('created DESC')->select();
        $pay = empty($activity['price']) ? "免费" : (int)round($activity['price'] * $num) . "维币";
        if($activity['isfree']==0){
            $pay = '免费';
        }
        foreach ($applys AS &$value) {
            $value['pay'] = $pay;
            if ($value['ispay'] == 0) {
                $value['ispay'] = "未支付";
            } else {
                $value['ispay'] = "已支付";
            }
        }
        $this->assign('applys',$applys);
        $this->display();
    }
    /**
     * 活动详情
     * @desc
     * 2017-2-22
     * djt
     */
    public function actDetail() {
        //获取微信凭证
        $jssdk = new JsSdkController;
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        //登录信息
        $info = session("userinfo");
        $num = C("VCOIN_NUM");
        $id = (int)_get("id");
        if (empty($id)) {
            $this->error('非法操作，参数错误');
        }
        $Activity = M('activity');
        $activity = $Activity ->where('activityid='.$id) -> find();
        //获取头像
        $pic = M("person_info")->where("uid={$activity['uid']}")->find();
        if(!empty($pic['avatar'])){
            $activity['avatar'] = $pic['avatar'];
        }
        $pic1 = M('company_info')->where("uid={$activity['uid']}")->find();
        if(!empty($pic1['logo'])){
            $activity['avatar'] = $pic1['logo'];
        }
        if(file_exists('./Public'.$activity['avatar'])){
            $url2 = str_replace('.','_little.',$activity['avatar'],$i);
            if(file_exists('./Public'.$url2)){
                $activity['avatar'] = $url2;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$activity['avatar']);
                $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url2);
                $activity['avatar'] = $url2;
            }
        }
        $activity['content'] = htmlspecialchars_decode($activity['content']);
        if($activity['isfree']!=1){
            $activity['vcoin'] = "免费";
            $activity['vcoinNum'] = 0;
        }else{
            $price = (int)round($activity['price']*$num);
            $activity['vcoin'] = $price."维币";
            $activity['vcoinNum'] = $price;
        }
        $activity['content'] = htmlspecialchars_decode($activity['content']);
        //报名人数
        $applyCount = M("apply_activity")->where("ispay = 1 AND activityid = {$id}")->count();
        $activity['applyNum'] = (int)$applyCount;
        if(empty($activity['limit'])){
            $activity['applyLimit'] = 1;
            $activity['applyStr'] = "不限制名额";
        }elseif($applyCount<$activity['limit']){
            $activity['applyLimit'] = 1;
            $anum = $activity['limit'] - $applyCount;
            $activity['applyStr'] = "还有".$anum."个名额";
        }else{
            $activity['applyLimit'] = 0;
            $activity['applyStr'] ="名额已满";
        }
        //往期活动不可点击
        if($activity['endtime']<=time()){
            $activity['endStatus'] = 0;
        }else{
            $activity['endStatus'] = 1;
        }
//            var_dump($course);exit;
        //评价
        //用户登录时评论给出用户信息
        if(!empty($info['uid'])){
            $this->assign("info",$info);
            //头像
            if($info['role']==1){
                $avatar = M("person_info")->where("uid={$info['uid']}")->find();
                $this->assign("avatar",$avatar['avatar']);
            }
            //公司信息
            if($info['role']==2){
                $company = M("company_info")->where("uid={$info['uid']}")->find();
                $this->assign("avatar",$company['logo']);
                $this->assign("company",$company);
            }
            //是否收藏
            $collect = M("collect")->where("type = 4 AND uid={$info['uid']} AND collectid = {$id}")->count();
            //是否报名
            $uid = $info['uid'];
            $apply = M("apply_activity")->where("uid = {$uid} AND activityid = {$id}")->find();
            if(!empty($apply)){
//            $this->ajaxReturn(array("msg"=>'你已经报名，可前往订单管理查看进度','status'=>0));
                $applyStatus = 1;
            }else{
                $applyStatus = 0;
            }
            $this->assign('applyStatus',$applyStatus);
            $collectStatus = $collect ? 1 : 0;
            $this->assign("collectStatus",$collectStatus);
        }
        $comment = M("person_comment as a")->join("LEFT join ww_person_info as b ON a.uid = b.uid")->field("a.*,b.avatar")->where("a.type = 1 AND a.status = 1 AND a.touid = {$id}")->limit(10)->select();
        foreach($comment AS &$v){
            $name = M("users")->where("uid={$v['uid']}")->find();
            if($name['role']==2){
                $companyAvatar = M("company_info")->where("uid={$v['uid']}")->find();
                $v['avatar'] = $companyAvatar['logo'];
            }
            if(file_exists('./Public'.$v['avatar'])){
                $url = str_replace('.','_little.',$v['avatar'],$i);
                if(file_exists('./Public'.$url)){
                    $v['avatar'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$v['avatar']);
                    $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $v['avatar'] = $url;
                }
            }
            $v['name'] = $name['name'];
        }
        $commentNum = count($comment);
        $this->assign("commentNum",$commentNum);
        $this->assign("comment",$comment);
//            var_dump($comment[0]);
        //推荐活动
        $time = time();
        $pushs = $Activity->where("status = 1 AND ispush = 1 AND starttime > $time")->field("activityid,title,picture")->limit(8)->select();
//        foreach($pushs AS &$v){
//            $v['vcoin'] = empty($v['nowprice']) ? (int)round($v['price']*$num) : (int)round($v['nowprice']*$num);
//        }
//        var_dump($pushs[0]);
//        var_dump($pushs);exit;
        $this->assign('pushs', $pushs);
        $this->assign('activity', $activity);
//        var_dump($activity);
        $this->display();
    }

    /**
     * 分享记录
     * djt
     * 2017-2-22
     */
    public function addShare(){
        //用户当天完成的次数
//        $id = 4;
//        $shopInfo = M("company_shop as a")->join("INNER join ww_person_info as b ON a.uid = b.uid")->where("a.shopid = {$id}")->field("a.*,b.vcoin")->find();
        $info = session("userinfo");
//        $info = array(
//           'uid'=>7,
//            'name'=>"七星国1"
//        );
        if(empty($info)){
            $this->ajaxReturn(array("msg"=>'登录分享可赢金币','status'=>1));
        }
        $uid = $info['uid'];
        $id = _post('id');
//        $id = 4;
        if(empty($id)){
            $this->ajaxReturn(array("msg"=>'参数错误','status'=>0));
        }
        //获取企业信息
        $activity = M("activity as a")->join("LEFT join ww_person_info as b ON a.uid = b.uid")->where("a.activityid={$id}")->field("a.*,b.vcoin as vcoin")->find();
        $acUid= $activity['uid'];
//        if(empty($arcUid)){
//            $organizer = "上海维沃珂营销平台";
//        }else{
//            $organizer = $article['name'];
//        }
        $data = array(
            'type' =>4,
            'uid' => $uid,
            'shareid' => $id,
            'created' => time()
        );
        //查看任务维币
        $Task = M("vcoin_task");
        $TaskLog = M("task_log");
        $start = (int)strtotime(date("Y-m-d"));
        $end = time();
        $task = $Task->where("action = '分享' AND status = 1")->find();
//        var_dump($task);exit;
        $taskid = $task['id'];
        $taskCoin = $task['value'];
        $limit = $task['limit'];
        //用户当天完成的次数
        $count = $TaskLog->where("uid = {$uid} AND taskid = {$taskid} AND created>$start AND created<$end")->count();
//        $arcVcoins = M("person_info")->where("uid={$comUid}")->find();
        $acvcoinStatus = (int)$activity['vcoin'];
        $acvcoinStatus = $acvcoinStatus>=$taskCoin ? true : false;
        if(empty($taskCoin)||empty($acvcoinStatus)){
            $result = M("share")->add($data);
            if(empty($result)){
//                $this->error("分享记录失败");
                $this->ajaxReturn(array("msg"=>'分享记录失败','status'=>0));
            }
            $this->ajaxReturn(array("msg"=>'分享成功1','status'=>1));
        }else{
            $model = new Model();
            $model->startTrans();
            $result = M("share")->add($data);
            if(!$result){
                $model->rollback();
//                $this->error("分享记录失败1");
                $this->ajaxReturn(array("msg"=>'分享记录失败','status'=>0));
//                $this->error("添加失败");
            }
            if($count >= $limit){
                $model->commit();
//                $this->error("分享记录成功1");
                $this->ajaxReturn(array("msg"=>'分享记录成功2','status'=>1));
            }
            //增加用户维币
            $addCoin = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$taskCoin);
            //减少发帖人维币:$arcUid 不为空
            $delCoin = true;
//            if(!empty($acUid)){
                $delCoin = M("person_info")->where("uid={$acUid}")->setDec("vcoin",$taskCoin);
//            }
            if(empty($addCoin)||empty($delCoin)){
                $model->rollback();
//                $this->error("维币更新失败");
                $this->ajaxReturn(array("msg"=>'维币更新失败','status'=>0));
            }
            //添加任务记录
            $Log = M("task_log");
            $flogarray = array(
                'uid' => $uid,
                'taskid' => $taskid,
                'created' => time()
            );
            $flog = $Log->add($flogarray);
            if(!$flog){
                $model->rollback();
//                $this->error("任务记录失败");
                $this->ajaxReturn(array('msg'=>'任务记录失败','status'=>0));
//                        $this->ajaxReturn(array('msg'=>'注册失败2','status'=>0));
            }
            //写入维币记录
            $Vlog = M("vcoin_log");
            $vlogAarry = array(
                'uid' => $uid,
                'action' => "分享活动",
                'organizer' => $activity['organizer'],
                'intro' => "分享活动增加维币",
                'type' => 2,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog = $Vlog->add($vlogAarry);
            //发帖人维币扣除
            $acUid = $acUid ? $acUid : 0;
            $vlogAarry1 = array(
                'uid' => $acUid,
                'action' => "支付用户分享活动",
                'organizer' => $info['name'],
                'intro' => "支付用户分享活动扣除维币",
                'type' => 3,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog1 = $Vlog->add($vlogAarry1);
            if((!$vlog)||(!$vlog1)){
                $model->rollback();
//                $this->error("维币记录失败");
                $this->ajaxReturn(array('msg'=>'维币记录失败','status'=>0));
            }
            $model->commit();
            $this->ajaxReturn(array('msg'=>'分享成功3','status'=>1));
        }
//        $this->success("分享成功");
    }
    /**
     *添加评论
     * 2017-2-23
     * djt
     */
    public function addComment(){
        $info = session("userinfo");
        if(empty($info['uid'])){
            $this->ajaxReturn(array("msg"=>"你还没有登录",'status'=>0));
        }
        $id = I("post.id");
        $content = I("post.content");
        if(empty($id)){
            $this->ajaxReturn(array("msg"=>"参数错误",'status'=>0));
        }
        if(empty($content)){
            $this->ajaxReturn(array("msg"=>"内容不能为空",'status'=>0));
        }elseif(mb_strlen($content,'utf-8')>200){
            $this->ajaxReturn(array("msg"=>'最多可以输入200字','status'=>0));
        }
        $data = array(
            'uid' => $info['uid'],
            'touid' => $id,
            'type' => 1,
            'content' => $content,
            'created' => time(),
            'updated' => time()
        );
        $result = M("person_comment")->add($data);
        if($result){
            $this->ajaxReturn(array("msg"=>"评价成功",'status'=>1));
        }else{
            $this->ajaxReturn(array("msg"=>"评价失败",'status'=>0));
        }
    }
    /**
     * 报名活动
     * 2017-2-23
     * djt
     */
    public function apply() {
        $info = session('userinfo');
        if(empty($info)){
            $this->error('你还没有登录，请先登录');
        }
        $uid = $info['uid'];
        $id = I("post.id");
        $name = I("post.name");
        $company = I("post.company");
        $mobile = I("post.mobile");
        if(empty($name)){
//            $this->ajaxReturn(array("msg"=>'姓名不能为空','status'=>0));
            $this->error("姓名不能为空");
        }
        if(empty($company)){
//            $this->ajaxReturn(array("msg"=>'公司名称不能为空','status'=>0));
            $this->error("公司名称不能为空");
        }
        if(empty($mobile)){
//            $this->ajaxReturn(array("msg"=>'联系方式不能为空','status'=>0));
            $this->error("联系方式不能为空");
        }
        $activity = M("activity")->where("activityid = {$id} AND status=1")->find();
        if(empty($activity['title'])){
//            $this->ajaxReturn(array("msg"=>'参数异常，该课程不可报名','status'=>0));
            $this->error("参数异常1，该活动不可报名");
        }
        $Apply= M("apply_activity");
        //有人数限制
        if(!empty($activity['limit'])){
            $count = $Apply->where("activityid = {$id}")->count();
            if($count>=$activity['limit']){
                $this->error("该活动名额已满");
            }
        }
        $apply = M("apply_activity")->where("uid = {$uid} AND activityid = {$id}")->find();
        if(!empty($apply)){
//            $this->ajaxReturn(array("msg"=>'你已经报名，可前往订单管理查看进度','status'=>0));
            $this->error("你已经报名，可前往订单管理查看");
        }
        //申请数据
        $data = array(
            'activityid' => $id,
            'uid' => $uid,
            'name' => $name,
            'mobile' => $mobile,
            'company' => $company,
            'created' => time(),
            'updated' => time()
        );
        //免费活动直接放到报名表里，标为已支付状态
        if(empty($activity['isfree'])){
//            $type = 1;
            $activity['price'] = round($activity['price'],2);
            $data['ispay'] = 1;
            $result = M("apply_activity")->add($data);
            if(!$result){
                $this->error("报名失败");
            }else{
                $this->success("报名成功",U("Home/Activity/signSuccess",array("id"=>$activity['activityid'])));
            }
        }
        //付费活动支付维币成功报名成功失败，不记录报名
        if(!empty($activity['isfree'])){
//            $this->ajaxReturn(array("msg"=>'参数异常1，该课程不可报名','status'=>0));
            $activity['price'] = round($activity['price'],2);
            if(empty($activity['price'])){
                $this->error("参数异常2，该活动不可报名");
            }
            //
            //维币判断
            $personinfo = M("person_info")->where("uid= {$uid}")->find();
            //维币支付判断
//            if($type!=1){
                $pay = (int)round($activity['price']*C("VCOIN_NUM"));
                if($pay>$personinfo['vcoin']){
                    $this->error("你当前的维币数不足",U('Home/Activity/actDetail',array('id'=>$id)));
                }
                $data['ispay'] = 1;
                $order['orderstatus'] = 1;
//            }

            $amount = $activity['price'];
            //订单数据
            $order['amount'] = $amount;
            $order['shopid'] = $id;
            $order['uid'] = $uid;
            $order['ordernum'] = date("YmdHis").rand(1000,9999).$uid;
            $order['orderdate'] = time();
            $order['descp'] = "报名参加活动";
            $order['type'] = 2;
            $order['seller'] = $activity['organizer'];
            $order['created'] = time();
            $order['updated'] = time();
            $Order = M("order");
            $model = new Model();
            $model->startTrans();
            //添加申请
            $result = M("apply_activity")->add($data);
            if(!$result){
                $model->rollback();
//            $this->ajaxReturn(array("msg"=>'报名失败','status'=>0));
                $this->error("报名失败");
            }
            $addResult = $Order->add($order);
            if(!$addResult){
                $model->rollback();
                $this->error("报名失败1");
//            $this->ajaxReturn(array("msg"=>'报名失败','status'=>0));
            }
            //维币支付
//            if($type!=1){
            if(!empty($pay)){
                $uppersoninfo = M("person_info")->where("uid = {$uid}")->setDec("vcoin",$pay);
                $uppersoninfo1 = M("person_info")->where("uid ={$activity['uid']}")->setInc("vcoin",$pay);
                if(empty($uppersoninfo)||empty($uppersoninfo1)){
                    $model->rollback();
                    $this->error("报名失败2");
                }
            }
                //举办方维币增加记录
                $Vlog = M("vcoin_log");
                $vlogAarry = array(
                    'uid' => $activity['uid'],
                    'action' => "活动收入",
                    'organizer' => $info['name'],
                    'intro' => "举办活动的收入",
                    'type' => 4,
                    'value' => $pay,
                    'created' => time()
                );
                $vlog = $Vlog->add($vlogAarry);
                //写入维币记录
                $vlogAarry1 = array(
                    'uid' => $uid,
                    'action' => "报名参加了活动",
                    'organizer' => $activity['organizer'],
                    'intro' => "报名参加活动",
                    'type' => 3,
                    'value' => $pay,
                    'created' => time()
                );
                $vlog1 = $Vlog->add($vlogAarry1);
                $model->commit();
                $this->success("报名成功",U("Home/Activity/signSuccess",array("id"=>$activity['activityid'])));
//            if (!$vlog) {
//                $model->rollback();
//                $this->ajaxReturn(array('msg' => '支付失败3', 'status' => 0));
//            }
//            }
//            $model->commit();
//            $this->success("报名成功",U("Home/Activity/applyPay",array("id"=>$addResult)));
        }

//        $this->success("报名成功,前往支付",U('Home/Activity/applyPay',array('id'=>$addResult)));
//        $this->ajaxReturn(array("msg"=>'报名成功','id'=>$addResult,'status'=>1));
    }
    /**
     * 活动现金支付页面
     * djt
     * 2017-2-14
     */
    public function applyPay(){
        $info = session('userinfo');
        if(empty($info)){
            $this->error('你还没有登录，请先登录');
        }
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }

        $Order = M("order");
        $uid = $info['uid'];
        $time = time() - 30*60;
        $order = $Order->where("uid ={$uid} AND type = 2 AND id={$id} AND orderstatus = 0")->find();
        if(empty($order)){
            $this->error("订单不存在",U('Home/Activity/index'));
        }
        $ordernum = $order['ordernum'];
        if(empty($ordernum)){
            $this->error("订单号不存在");
        }
        //刷新更新微信支付订单号并记录与平台订单的关系
        $wxordernum = date("YmdHis").rand(1000,9000);
        $data = array(
            "wxordernum"=>$wxordernum,
            "ordernum"=>$ordernum,
            "created"=>time(),
            "updated"=>time()
        );
        $reult = M("wxordernum")->add($data);
        if(empty($reult)){
            $this->error("微信订单生成失败");
        }
        $num = (int)C('VCOIN_NUM');
//        //当前用户维币
//        $coin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
//        $order['vcoin']= $coin['vcoin'];
//        $order['vnum']= (int)round($order['amount']*$num);
        $this->assign("order",$order);
        $this->assign("wxordernum",$wxordernum);
        $this->display();
    }
    /**
     * 报名成功页面
     * 3-4
     * djt
     */
    public function signSuccess(){
        $id = _get("id");
//        if(empty($id)){
//            $this->error('参数错误');
//        }
        if(!empty($id)){
            $activity = M("activity")->where("activityid = {$id}")->find();
        }
        $this->assign('activity',$activity);
        $this->display();
    }



//    /**
//     * 活动支付页面
//     * djt
//     * 2017-2-23
//     */
//    public function applyPay(){
//        $info = session('userinfo');
//        if(empty($info)){
//            $this->error('你还没有登录，请先登录');
//        }
//        $id = _get("id");
//        $Order = M("order");
//        $uid = $info['uid'];
//        $order = $Order->where("uid ={$uid} AND id = {$id} AND orderstatus = 0")->find();
//        if(empty($order)){
//            $this->error("订单不存在",U('Home/Course/index'));
//        }
//        //课程详情
//        $course = M("course")->where("courseid = {$order['shopid']} AND status = 1")->find();
//        if(empty($course)){
//            $this->error("报名课程处于异常状态，无法支付");
//        }
//        $this->assign("course",$course);
//        $num = (int)C('VCOIN_NUM');
//        //当前用户维币
//        $coin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
//        //用户维币
//        $order['vcoin']= $coin['vcoin'];
//        //需支付维币
//        $order['vnum']= (int)round($order['amount']*$num);
//        $this->assign("order",$order);
//        $this->display();
//    }

//    /**
//     * 报名课程维币支付
//     * djt
//     * 2017-2-14
//     */
//    public function vPay(){
//        //获取一元兑换维币数
//        $num = (int)C('VCOIN_NUM');
//        $info = session('userinfo');
//        if(empty($info)){
//            $this->ajaxReturn(array('msg'=>'你还没有登录，请先登录','status'=>0));
////            $this->error('你还没有登录，请先登录');
//        }
//        $id = _post("id");
//        if(empty($id)){
//            $this->ajaxReturn(array('msg'=>'参数错误','status'=>0));
//        }
//        $Order = M("order");
//        $uid = $info['uid'];
////        var_dump($info);
//        $order = $Order->where("uid ={$uid} AND id = {$id} AND orderstatus = 0")->find();
////        echo $Order->getLastSql();exit;
//        if(empty($order)){
//            $this->ajaxReturn(array('msg'=>'当前用户不存在该订单','status'=>0));
////            $this->error("当前用户不存在入驻企业订单");
//        }
//        $orderid = $order['id'];
//        $shopid = $order['shopid'];
//        $money = round($order['amount']*$num);
//        $pay = intval($money);
//        $Person = M("person_info");
//        $personInfo = $Person->where("uid = {$uid}")->find();
//        $vcions = intval($personInfo['vcoin']);
//        $vcoin = $vcions - $pay;
//        if($vcoin>=0){
//            $model = new Model();
//            $model->startTrans();
//            //用户维币减少
//            $infoResult = $Person->where("uid = {$uid}")->setDec('vcoin',$pay);
//            if(!$infoResult){
//                $model->rollback();
//                $this->ajaxReturn(array('msg'=>'支付失败1','status'=>0));
//            }
//            //写入维币记录
//            $Vlog = M("vcoin_log");
//            $vlogAarry = array(
//                'uid' => $uid,
//                'action' => "购买课程",
//                'organizer' => "维沃珂",
//                'intro' => "支付课程",
//                'type' => 3,
//                'value' => $pay,
//                'created' => time()
//            );
//            $vlog = $Vlog->add($vlogAarry);
//            if(!$vlog){
//                $model->rollback();
//                $this->ajaxReturn(array('msg'=>'支付失败3','status'=>0));
//            }
//
//            //更新报名状态
//            $applyUpDatated = array(
//                'ispay' => 1,
//                'status' => 1,
//                'updated' =>time()
//            );
//            $cResult = M("apply_course")->where("uid = {$shopid}")->save($applyUpDatated);
//            if(!$cResult){
//                $model->rollback();
//                $this->ajaxReturn(array('msg'=>'支付失败','status'=>0));
////                    $this->error("支付失败");
//            }
//            //更新订单状态
//            $orderUpdated = array(
//                'paydate' => time(),
//                "orderstatus" => 2,
//                'payment' => 'wwkpay',
//                'updated' => time()
//            );
//            $oResult = $Order->where("id = {$orderid}")->save($orderUpdated);
//            if(!$oResult){
//                $model->rollback();
//                $this->ajaxReturn(array('msg'=>'订单变更失败','status'=>0));
////                    $this->error("订单变更失败");
//            }
//            $model->commit();
//            $this->ajaxReturn(array('msg'=>'支付成功','id'=>$shopid,'status'=>1));
////                $this->success("支付成功",U("Home/Comapny/businessLocated"));//后续跳转到选择企业模板页面
//
//        }else{
//            $this->ajaxReturn(array('msg'=>'你的维币数不足，请充值','status'=>0));
////            $this->error("你的维币数不足，请充值");
//        }
//    }

    /**
     * 活动管理
     * 2017-2-25
     * djt
     */
    public function activityMange(){
        $num = C("VCOIN_NUM");
        $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录，暂时无法查看");
        }
        $uid = $info['uid'];
        //参加的活动
        $inActivity = M("apply_activity as a")->join("LEFT join ww_activity as b ON a.activityid = b.activityid")->where("a.uid = {$uid} AND a.ispay = 1")->order("b.starttime DESC")->field("a.created as applytime,a.ispay,b.*")->select();
//        var_dump($inActivity);
        foreach($inActivity AS $k=>&$v){
            if(!empty($v['activityid'])){
                if($v['starttime']>time()){
                    $v['acStatus'] = "即将开始";
                }elseif(($v['starttime']<time()) && (time()<$v['endtime'])){
                    $v['acStatus'] = "进行中";
                }elseif(time()>$v['endtime']){
                    $v['acStatus'] = "已结束";
                }
                if(time()>$v['endtime']||$v['ispay']==0){
                    $v['applyType'] = "无效票";
                }else{
                    $v['applyType'] = "有效票";
                }
                //报名人数
                $v['count'] = M("apply_activity")->where("activityid = {$v['activityid']}")->count();
                //email
                $puinfo = M("person_info")->where("uid = {$v['uid']}")->find();
                $v['email'] = $puinfo['email'];
            }else{
                unset($inActivity[$k]);
            }
        }
        $this->assign("inActivity",$inActivity);
        //发布的活动
        $addActivity = M("activity")->where("uid = {$uid} AND status =1")->order("created DESC")->select();
        foreach($addActivity AS &$val) {
            if ($val['starttime'] > time()) {
                $val['acStatus'] = "即将开始";
            } elseif (($val['starttime'] < time()) && (time() < $val['endtime'])) {
                $val['acStatus'] = "进行中";
            } elseif (time() > $val['endtime']) {
                $val['acStatus'] = "已结束";
            }
            $activityid = $val['activityid'];
            $info = M("apply_activity")->where("activityid = {$activityid}")->order("created DESC")->select();
            $val['num'] = count($info);
            $pay = empty($val['price']) ? "免费" : (int)round($val['price'] * $num) . "维币";
            foreach ($info AS &$value) {
                $value['pay'] = $pay;
                if ($value['ispay'] == 0) {
                    $value['ispay'] = "未支付";
                } else {
                    $value['ispay'] = "已支付";
                }
            }
            $val['info'] = $info;
        }
            $this->assign("addActivity",$addActivity);
            //收藏的活动
            $collects = M("collect as a")->join("LEFT join ww_activity as b ON a.collectid = b.activityid")->where("a.uid = {$uid} AND a.type = 4")->field("a.id,a.collectid,b.title,b.starttime,b.organizer,b.picture")->order("a.created DESC")->select();
//        var_dump($collects[0]);
        $this->assign("collects",$collects);
        $this->display();
    }
    /**
     * 删除活动
     * djt
     * 2017-4-7
     */
    public function del(){
        $info = session("userinfo");
        if(empty($info)){
//            $this->error("请先登录");
            $this->ajaxReturn(array("msg"=>"请先登录",'status'=>0));
        }
        $uid = $info['uid'];
        $id = _post('id');
        if(empty($id)){
//            $this->error("参数错误");
            $this->ajaxReturn(array("msg"=>"参数错误",'status'=>0));
        }
        //有报名的活动不予以删除
        $num = M('apply_activity')->where("activityid={$id}")->count();
        if(!empty($num)){
            $this->ajaxReturn(array("msg"=>"该活动已有人参与不可删除",'status'=>0));
        }
        $data = array(
            'status' => 0,
            'updated' => time()
        );
        $result = M("activity")->where("uid={$uid} AND activityid={$id}")->save($data);
        if($result===false){
//            $this->error("添加失败");
            $this->ajaxReturn(array("msg"=>"删除失败",'status'=>0));
        }
//        $this->success("添加成功");
        $this->ajaxReturn(array("msg"=>"删除成功",'status'=>1));
    }
    /**
     * 收藏文贴
     * djt
     * 2017-2-24
     */
    public function addCollect(){
        $info = session("userinfo");
        if(empty($info)){
//            $this->error("请先登录");
            $this->ajaxReturn(array("msg"=>"请先登录",'status'=>0));
        }
        $uid = $info['uid'];
        $id = _post('id');
        if(empty($id)){
//            $this->error("参数错误");
            $this->ajaxReturn(array("msg"=>"参数错误",'status'=>0));
        }
        //不能重复收藏
        $collect = M("collect")->where("collectid ={$id} AND type = 4 AND uid = {$uid}")->find();
        if(!empty($collect)){
            $this->ajaxReturn(array("msg"=>'你已经收藏过该文贴','status'=>0));
        }
        $data = array(
            'type' => 4,
            'uid' => $uid,
            'collectid' => $id,
            'created' => time()
        );
        $result = M("collect")->add($data);
        if(!$result){
//            $this->error("添加失败");
            $this->ajaxReturn(array("msg"=>"参数错误",'status'=>0));
        }
//        $this->success("添加成功");
        $this->ajaxReturn(array("msg"=>"收藏成功",'status'=>1));
    }

    /**
     * 取消收藏
     * djt
     * 2017-2-24
     */
    public function delCollect(){
        $info = session("userinfo");
        if(empty($info)){
//            $this->error("请先登录");
            $this->ajaxReturn(array("msg"=>"请先登录",'status'=>0));
        }
        $uid = $info['uid'];
        $id = _post('id');
        if(empty($id)){
//            $this->error("参数错误");
            $this->ajaxReturn(array("msg"=>"参数错误",'status'=>0));
        }
        //取消收藏
        $collect = M("collect")->where("collectid ={$id} AND uid = {$uid}")->delete();
        if(empty($collect)){
//            $this->error("取消收藏失败1");
            $this->ajaxReturn(array("msg"=>"取消收藏失败1",'status'=>0));
        }
//        $this->success("取消收藏成功");
        $this->ajaxReturn(array("msg"=>"取消收藏成功",'status'=>1));
    }
    /**
     * 回顾活动详情
     * 2017-2-24
     * djt
     */
    public function news(){
        $info = session("userinfo");
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误1");
        }
        $Article = M("review_activity");
        //浏览数加1
        $result = $Article->where("id = {$id}")->setInc("browsenum",1);
        $article = $Article->where("id = {$id}")->find();
        if(empty($article)){
            $this->error("暂无相关内容");
        }
//        //是否收藏
//        if(!empty($info['uid'])){
//            $avatar = M("person_info")->where("uid = {$info['uid']}")->field('avatar')->find();
//            $this->assign('avatar',$avatar);
//            $collect = M("collect")->where("collectid = {$id} AND type=3 AND uid ={$info['uid']}")->count();
//        }
//        $article['collect'] = $collect ? 1 : 0;
//        if($article['type']!=2){
            $article['content'] = htmlspecialchars_decode($article['content']);
//        }
        //评论数
        $article['commentNum'] = M("person_comment")->where("touid = {$id} AND type = 5 AND status =1")->count();
        //评论
        $comments =  M("person_comment")->where("touid = {$id} AND type = 5 AND status =1")->order("created DESC")->limit(10)->select();
        foreach($comments AS &$v){
            //用户信息
            $infos = M("users as a")->join("LEFT join ww_person_info as b ON a.uid = b.uid")->where("a.uid ={$v['uid']}")->field("a.name,b.avatar")->find();
            $v['name'] = $infos['name'];
            $v['avatar'] = $infos['avatar'];
            //评论回复
            $reply = M("comment_reply")->where("commentid = {$v['commentid']} AND type = 1")->select();
            foreach($reply AS &$val){
                $replyer = M("users as a ")->join("LEFT join ww_person_info as b ON a.uid = b.uid")->field("a.name,b.avatar")->where("a.uid = {$val['uid']} ")->find();
                $val['name'] = $replyer['name'];
                $val['avatar'] = $replyer['avatar'];
            }
            //评论支持
            if(!empty($info['uid'])){
                $support = M("comment_reply")->where("commentid = {$v['commentid']} AND type = 2 AND uid = {$info['uid']}")->count();
            }
            $support = $support ? 1 : 0;
            $v['reply'] = $reply;
            $v['support'] = $support;
        }
        $this->assign('article',$article);
        $this->assign('comments',$comments);
//        var_dump($comments[0]);
        //右侧热门推荐
        $time = time();
        $push1 = M("activity")->where("ispush = 1 AND status = 1 AND starttime > $time")->field("activityid,title,created")->limit(6)->order("created DESC")->select();
        $push2 =  M("article")->where("ispush = 0 AND status = 1")->field("articleid,title,created")->limit(6)->order("created DESC")->select();
        //广告
        $banner1 = M("banner")->where("type ='主题话题' AND status = 1")->order("created DESC")->find();
        $banner2 = M("banner")->where("type ='企业话题' AND status = 1")->order("created DESC")->find();
        $this->assign('push1',$push1);
        $this->assign('push2',$push2);
        $this->assign('banner1',$banner1);
        $this->assign('banner2',$banner2);
        $this->display();
    }

    /**
     *评论
     * 2017-2-24
     * djt
     */
    public function comment(){
        if(IS_POST){
            $info = session("userinfo");
            if(empty($info)){
                $this->error("你还没有登录，请先登录");
            }
            $uid = $info['uid'];
            $Comment = M('person_comment');
            $type = 5;
            $touid =  I("post.id");
            $content = I("post.content");
//            $browsenum = I("post.browsenum");
            if(empty($content)){
//               $this->error("内容不能为空");
                $this->ajaxReturn(array("msg"=>'内容不能为空','status'=>0));
            }
            if(empty($touid)){
//                $this->error("参数错误");
                $this->ajaxReturn(array("msg"=>'参数错误','status'=>0));
            }
            $data = array(
                'type' => $type,
                'uid' => $uid,
                'touid' => $touid,
                'content' => $content,
                'created' => time(),
                'updated' => time()
            );
            //查看任务维币
            $Task = M("vcoin_task");
            $TaskLog = M("task_log");
            $start = time() - 86400;
            $end = time();
            $isperson = $info['role']=2 ? 3 : 2;
            $task = $Task->where("action = '发帖' AND status = 1 AND isperson = {$isperson}")->find();
//        var_dump($task);exit;
            $taskid = $task['id'];
            $taskCoin = $task['value'];
            $limit = $task['limit'];
            $length = $task['length'];
            //用户当天完成的次数
            $count = $TaskLog->where("uid = {$uid} AND taskid = {$taskid} AND created>$start AND created<$end")->count();
            $model = new Model();
            $model->startTrans();
            $result = $Comment->add($data);
            if(!$result){
                $model->rollback();
//                $this->error('评论失败1');
                $this->ajaxReturn(array("msg"=>'评论失败1','status'=>0));
            }
            if(!empty($taskCoin)){
                if((strlen($content)>$length) && ($count>$limit)){
                    //增加用户维币
                    $addCoin = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$taskCoin);
                    if(empty($addCoin)){
                        $model->rollback();
//                        $this->error("评论失败2");
                        $this->ajaxReturn(array("msg"=>'评论失败2','status'=>0));
                    }
                    //添加任务记录
                    $Log = M("task_log");
                    $flogarray = array(
                        'uid' => $uid,
                        'taskid' => $taskid,
                        'created' => time()
                    );
                    $flog = $Log->add($flogarray);
                    if(!$flog){
                        $model->rollback();
//                        $this->error("评论失败21");
                        $this->ajaxReturn(array("msg"=>'评论失败21','status'=>0));
//                        $this->ajaxReturn(array('msg'=>'注册失败2','status'=>0));
                    }
                    //写入维币记录
                    $Vlog = M("vcoin_log");
                    $vlogAarry = array(
                        'uid' => $uid,
                        'action' => "发帖",
                        'organizer' => C("ORGANIZER"),
                        'intro' => "发增加维币",
                        'type' => 2,
                        'value' => $taskCoin,
                        'created' => time()
                    );
                    $vlog = $Vlog->add($vlogAarry);
                    if(!$vlog){
                        $model->rollback();
                        $this->ajaxReturn(array("msg"=>'回复失败3','status'=>0));
//                        $this->$this->error("回复失败3");
                    }
                }
            }
            $model->commit();
//            $this->success('添加成功');
            $this->ajaxReturn(array("msg"=>'评论成功','status'=>1));
        }else{
//            $this->error("异常操作");
            $this->ajaxReturn(array("msg"=>'评论成功','status'=>1));
        }
    }




    //结束

}
