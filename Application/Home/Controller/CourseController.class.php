<?php
/**
 * @desc 课程管理
 */
namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Org\Util;
use Home\Controller\CommonController;
class CourseController extends CommonController {

    /**
     * @desc 培训首页
     *djt
     * 2017-2-22
     */
    public function index() {
        //首页关键字
        $key = M("sys_keyword")->where("name='企业培训首页'")->find();
        $this->assign("key",$key);
        //头部企业图广告
        $companyBanner = M("banner")->where("type ='企业培训banner' AND status = 1")->order("weight DESC")->find();
        if(file_exists('./Public'.$companyBanner['picture'])){
            $url = str_replace('.','_big.',$companyBanner['picture'],$i);
            if(file_exists('./Public'.$url)){
                $companyBanner['picture'] = $url;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$companyBanner['picture']);
                $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                $companyBanner['picture'] = $url;
            }
        }
        $this->assign("banner",$companyBanner);
//        var_dump($companyBanner);
        //一般课程
        $Course = M("course");
        $time = time();
        $course = $Course->where("status = 1 AND starttime > $time")->order("courseid DESC")->select();
        $num = C("VCOIN_NUM");
        foreach($course AS $key=>&$v){
            $v['nowprice'] = (int)round($v['nowprice']*$num);
            $v['price'] = (int)round($v['price']*$num);
            $v['vcoin'] = $v['nowprice'] ? $v['nowprice'] : $v['price'];
//            $v['content'] = htmlspecialchars_decode($v['content']);
            if(file_exists('./Public'.$v['picture'])){
                $url = str_replace('.','_mid.',$v['picture'],$i);
                if(file_exists('./Public'.$url)){
                    $v['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$v['picture']);
                    $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $v['picture'] = $url;
                }
            }
            if($key>2){
                $touid = $v['courseid'];
                $v['score'] = M("person_comment")->where("touid = $touid AND status = 1 AND type = 3")->avg("score");
                $v['score']  = (int)round($v['score']);
                $v['star'] = (int)round($v['score']/2);
            }
        }
        $this->assign("course",$course);
        //成功案例
        $goodCourse = M("review_course")->where("status = 1 AND ispush = 1")->limit(7)->field("id,created,title,picture")->order("updated DESC")->select();
        $this->assign("goodCourse",$goodCourse);
        //精彩瞬间
        $review = M("review_course")->where("status = 1")->field("id,title,picture")->order("id DESC")->select();
        $num = count($review);
        $this->assign('num',$num);
        foreach($review as &$rval){
            if(file_exists('./Public'.$rval['picture'])){
                $url = str_replace('.','_mid.',$rval['picture'],$i);
                if(file_exists('./Public'.$url)){
                    $rval['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$rval['picture']);
                    $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $rval['picture'] = $url;
                }
            }
        }
        $this->assign("review",$review);
        //培训咨询
        $courseArticle = M("article")->where("status = 1 AND type = 3")->field("articleid,title,created")->order("articleid DESC")->select();
        $this->assign("courseArticle",$courseArticle);
        $this->display();
    }

    /**
     * 定制课程
     * djt
     * 2017-2-22
     */
    public function addNeed(){
        $info = session("userinfo");
        if(empty($info)){
            $this->error("请先登录");
        }
        $uid = $info['uid'];
        $name = I("post.name");
        $mobile = I("post.mobile");
        $company = I("company");
        $address = I("post.address");
        $intro = I("post.intro");
//        $uid =6;
//        $name = "哪里走";
//        $mobile = "13166292960";
//        $company = "窝游";
//        $address = "dsfdfdsfdsds";
//        $intro = "zheddsfdsfdsdsfdsf";
        $msg = '';
        $flag = true;
        $preg = "/^1\d{10}$/";
        if(empty($name)){
            $flag = false;
            $msg.="姓名不能为空";
        }
        if(empty($mobile)){
            $flag = false;
            $msg.="手机号不能为空";
        }elseif(!preg_match($preg,$mobile)){
            $flag = false;
            $msg.="手机号格式不正确";
        }
        if(empty($company)){
            $flag = false;
            $msg.="公司名称不能为空";
        }
        if(empty($address)){
            $flag = false;
            $msg.="地址不能为空";
        }
        if(empty($intro)){
            $flag = false;
            $msg.="需求不能为空";
        }
        if(!$flag){
            $this->error($msg);
        }
        $data = array(
            'uid' => $uid,
            'name' => $name,
            'company' => $company,
            'mobile' => $mobile,
            'address' => $address,
            'intro' => $intro,
            'created' => time(),
            'updated' => time()
        );
        $result = M("need_course")->add($data);
        if(!$result){
            $this->error("添加失败");
        }
        $this->success("添加成功",U("Home/Course/index"));
    }

    /**
     * 课程详情
     * @desc
     * 2017-2-22
     * djt
     */
    public function trainedDetails() {
        //获取微信凭证
        $jssdk = new JsSdkController;
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        //获取用户登录信息
        $info = session("userinfo");
        $num = C("VCOIN_NUM");
            $id = (int)_get("id");
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Course = M('course');
            $course = $Course ->where('courseid='.$id) -> find();
            $course['content'] = htmlspecialchars_decode($course['content']);
            //获取需要替换的关键字
            $words = getKeyWords();
            foreach($words as $v){
                $url = "<a href='/index.php/Home/Index/index.html'>".$v."</a>";
                $course['content'] = str_replace($v,$url,$course['content'],$i);
            }
            $price = (int)round($course['price']*$num);
            $nowprice = (int)round($course['nowprice']*$num);
            $course['vcoin'] = $nowprice ? $nowprice:  $price;
            //评价综合分
            $scoreAvg = M("person_comment")->where("type = 3 AND status = 1 AND touid = {$id}")->avg('score');
            $course['scorseAvg'] = round($scoreAvg);
            $course['star'] = (int)round($scoreAvg/2);
            //学习人数
            $applyCount = M("apply_course")->where("status = 1 AND ispay = 1 AND courseid = {$id}")->count();
            $course['applyNum'] = (int)$applyCount;
//            var_dump($course);exit;
           //评价
            //用户登录时评论给出用户信息
            if(!empty($info['uid'])){
                $this->assign('info',$info);
                //头像
                if($info['role']==1){
                    $avatar = M("person_info")->where("uid={$info['uid']}")->find();
                    $this->assign("avatar",$avatar['avatar']);
                    $address = $avatar['prov'].$avatar['city'].$avatar['area'];
                    $this->assign("address",$address);
                }
                //公司信息
                if($info['role']==2){
                    $company = M("company_info")->where("uid={$info['uid']}")->find();
                    $this->assign("avatar",$company['logo']);
                    $this->assign("company",$company);
                    $address = $company['prov'].$company['city'].$company['address'];
                    $this->assign("address",$address);
                }
                //是否收藏
                $collect = M("collect")->where("type = 2 AND uid={$info['uid']} AND collectid = {$id}")->count();
                $collectStatus = $collect ? 1 : 0;
                $this->assign("collectStatus",$collectStatus);
                //是否报名
                $applyResult = M("apply_course")->where("uid={$info['uid']} AND courseid={$id} AND status=1")->find();
                $order = M('order')->where("uid={$info['uid']} AND type=3 AND shopid={$id}")->order('id DESC')->find();
                $apply = 0;
                if(empty($applyResult)){
//                    未报名
                    $apply = 0;
                }elseif($applyResult['ispay']==0){
                    //已报名未支付
                    $apply = 1;
                    $orderId = $order['id'];
                    $this->assign('orderId',$orderId);

                }elseif($applyResult['ispay']==1){
                    //已支付
                    $apply = 2;
                }
                $this->assign('apply',$apply);
            }
//            $count = M("person_comment")->where("type = 3 AND status = 1 AND touid = {$id}")->limit(10)->count();
//            $Page = new \Think\Page($count,20);
//            $show = $Page->show();
            $comment = M("person_comment as a")->join("LEFT join ww_person_info as b ON a.uid = b.uid")->field("a.*,b.avatar")->where("a.type = 3 AND a.status = 1 AND a.touid = {$id}")->limit(15)->order(array('created'=>'DESC','score'=>'DESC'))->select();
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
            //推荐课程
            $time = time();
            $pushs = $Course->where("status = 1 AND ispush = 1 AND starttime > $time")->field("courseid,title,picture,price,nowprice")->limit(3)->select();
            foreach($pushs AS &$v){
               $v['showprice'] = empty($v['nowprice']) ? (int)round($v['price']*$num) : (int)round($v['nowprice']*$num);
            }
//        var_dump($pushs[0]);
//        var_dump($pushs);exit;
        $this->assign('pushs', $pushs);
        $this->assign('course', $course);
            $this->display();
    }

    /**
     * 报名课程
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
        $address = I("post.address");
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
        if(empty($address)){
//            $this->ajaxReturn(array("msg"=>'地址不能为空','status'=>0));
            $this->error("地址不能为空");
        }
        $coure = M("course")->where("courseid = {$id} AND status=1")->find();
        if(empty($coure['title'])){
//            $this->ajaxReturn(array("msg"=>'参数异常，该课程不可报名','status'=>0));
            $this->error("参数异常，该课程不可报名");
        }
        if(empty($coure['price'])&&empty($coure['nowprice'])){
//            $this->ajaxReturn(array("msg"=>'参数异常1，该课程不可报名','status'=>0));
            $this->error("参数异常，该课程不可报名");
        }
        $amount = empty($coure['nowprice']) ? $coure['price'] : $coure['nowprice'];
        $Apply= M("apply_course");
        $apply = $Apply->where("uid = {$uid} AND courseid = {$id}")->find();
        if(!empty($apply)){
//            $this->ajaxReturn(array("msg"=>'你已经报名，可前往订单管理查看进度','status'=>0));
            $this->error("你已经报名，可前往订单管理查看进度");
        }
        //申请数据
        $data = array(
            'courseid' => $id,
            'title' => $coure['title'],
            'uid' => $uid,
            'name' => $name,
            'mobile' => $mobile,
            'company' => $company,
            'address' => $address,
            'created'=>time(),
            'updated' => time()
        );
        //订单数据
        $order['amount'] = $amount;
        $order['shopid'] = $id;
        $order['uid'] = $uid;
        $order['ordernum'] = date("YmdHis").rand(100,999).md5("wwk".$uid);
        $order['orderdate'] = time();
        $order['descp'] = "报名参加课程";
        $order['type'] = 3;
        $order['seller'] = "上海维沃珂营销平台";
        $order['created'] = time();
        $order['updated'] = time();
        $Order = M("order");
        $model = new Model();
        $model->startTrans();
        $result = M("apply_course")->add($data);
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
        $model->commit();
        $this->success("报名成功,前往支付",U('Home/Course/applyPay',array('id'=>$addResult)));
//        $this->ajaxReturn(array("msg"=>'报名成功','id'=>$addResult,'status'=>1));
    }

    /**
     * 课程支付页面
     * djt
     * 2017-2-23
     */
    public function applyPay(){
        $info = session('userinfo');
        if(empty($info)){
            $this->error('你还没有登录，请先登录');
        }
        $id = _get("id");
        $Order = M("order");
        $uid = $info['uid'];
        $order = $Order->where("uid ={$uid} AND id = {$id} AND orderstatus = 0")->find();
        if(empty($order)){
            $this->error("订单不存在");
        }
        //课程详情
        $course = M("course")->where("courseid = {$order['shopid']} AND status = 1")->find();
        if(empty($course)){
            $this->error("报名课程处于异常状态，无法支付");
        }
        $this->assign("course",$course);
        $num = (int)C('VCOIN_NUM');
        //当前用户维币
        $coin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
        //用户维币
        $order['vcoin']= $coin['vcoin'];
        //需支付维币
        $order['vnum']= (int)round($order['amount']*$num);
        $this->assign("order",$order);
        $this->display();
    }

    /**
     * 报名课程维币支付
     * djt
     * 2017-2-14
     */
    public function vPay(){
        //获取一元兑换维币数
        $num = (int)C('VCOIN_NUM');
        $info = session('userinfo');
        if(empty($info)){
            $this->ajaxReturn(array('msg'=>'你还没有登录，请先登录','status'=>0));
//            $this->error('你还没有登录，请先登录');
        }
        $id = _post("id");
        if(empty($id)){
            $this->ajaxReturn(array('msg'=>'参数错误','status'=>0));
        }
        $Order = M("order");
        $uid = $info['uid'];
//        var_dump($info);
        $order = $Order->where("uid ={$uid} AND id = {$id} AND orderstatus = 0")->find();
//        echo $Order->getLastSql();exit;
        if(empty($order)){
            $this->ajaxReturn(array('msg'=>'当前用户不存在该订单','status'=>0));
//            $this->error("当前用户不存在入驻企业订单");
        }
        $orderid = $order['id'];
        $shopid = $order['shopid'];
        $money = round($order['amount']*$num);
        $pay = intval($money);
        $Person = M("person_info");
        $personInfo = $Person->where("uid = {$uid}")->find();
        $vcions = intval($personInfo['vcoin']);
        $vcoin = $vcions - $pay;
        if($vcoin>=0){
            $model = new Model();
            $model->startTrans();
            //用户维币减少
            if(!empty($pay)){
                $infoResult = $Person->where("uid = {$uid}")->setDec('vcoin',$pay);
                if(!$infoResult){
                    $model->rollback();
                    $this->ajaxReturn(array('msg'=>'支付失败1','status'=>0));
                }
            }
            //写入维币记录
            $Vlog = M("vcoin_log");
            $vlogAarry = array(
                'uid' => $uid,
                'action' => "购买课程",
                'organizer' => "维沃珂",
                'intro' => "支付课程",
                'type' => 3,
                'value' => $pay,
                'created' => time()
            );
            $vlog = $Vlog->add($vlogAarry);
            if(!$vlog){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'支付失败3','status'=>0));
            }

            //更新报名状态
            $applyUpDatated = array(
                'ispay' => 1,
                'status' => 1,
                'updated' =>time()
            );
            $cResult = M("apply_course")->where("courseid = {$shopid}")->save($applyUpDatated);
            if(!$cResult){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'支付失败','status'=>0));
//                    $this->error("支付失败");
            }
            //更新订单状态
            $orderUpdated = array(
                'paydate' => time(),
                "orderstatus" => 2,
                'payment' => 'wwkpay',
                'updated' => time()
            );
            $oResult = $Order->where("id = {$orderid}")->save($orderUpdated);
            if(!$oResult){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'订单变更失败','status'=>0));
//                    $this->error("订单变更失败");
            }
            $model->commit();
            $this->ajaxReturn(array('msg'=>'支付成功','id'=>$shopid,'status'=>1));
//                $this->success("支付成功",U("Home/Comapny/businessLocated"));//后续跳转到选择企业模板页面

        }else{
            $this->ajaxReturn(array('msg'=>'你的维币数不足，请充值','status'=>2));
//            $this->error("你的维币数不足，请充值");
        }
    }

    /**
     * 培训资讯（来源于文贴表）
     * @desc
     * 2017-2-23
     * djt
     */
    public function news() {
        $id = _get("id");
        if (empty($id)) {
            $this->error('非法操作，参数错误');
        }
        $Article = M('article');
        $article = $Article ->where('articleid='.$id) -> find();
        $article['content'] = htmlspecialchars_decode($article['content']);
        //推荐热门文贴
        $hots = $Article->where("status = 1 AND ispush = 1")->field("id,title,created")->limit(5)->select();
        //推荐精品文贴
        $goods = $Article->where("status = 1 AND ispush = 0")->field("id,title,created")->limit(5)->select();
//        var_dump($pushs);exit;
        //评论
        $comments = M("person_comments")->where("touid = {$id} AND status = 1 AND type = 4 ")->limit(5)->select();
        foreach($comments AS &$v){
            $commentid = $v['commentid'];
            //获取他的回复
            $reply = M("comment_reply")->where("commentid = {$commentid}")->order("replyid DESC")->select();
            $v['reply'] = $reply;
        }
        $this->assign('hots', $hots);
        $this->assign('goods', $goods);
        $this->assign('article', $article);
        $this->display();
    }

    /**
     *添加培训咨询评论
     * 2017-2-23
     * djt
     */
    public function commentNews(){
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
        }elseif(strlen($content)>200){
            $this->ajaxReturn(array("msg"=>"内容字数不能小于200",'status'=>0));
        }
        $data = array(
            'uid' => $info['uid'],
            'touid' => $id,
            'type' => 4,
            'content' => $content,
            'created' => time(),
            'updated' => time()
        );
        $result = M("person_comment")->add($data);
        if($result){
            $this->ajaxReturn(array("msg"=>"评论成功",'status'=>1));
        }else{
            $this->ajaxReturn(array("msg"=>"评论失败",'status'=>0));
        }
    }

    /**
     *回复培训咨询评论
     * 2017-2-23
     * djt
     */
    public function commentReply(){
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
        }elseif(strlen($content)>200){
            $this->ajaxReturn(array("msg"=>"内容字数不能小于200",'status'=>0));
        }
        $data = array(
            'uid' => $info['uid'],
            'commentid' => $id,
            'content' => $content,
            'created' => time(),
            'updated' => time()
        );
        $result = M("comment_reply")->add($data);
        if($result){
            $this->ajaxReturn(array("msg"=>"评论成功",'status'=>1));
        }else{
            $this->ajaxReturn(array("msg"=>"评论失败",'status'=>0));
        }
    }

    /**
     * 培训精彩瞬间
     * @desc
     * 2017-2-23
     * djt
     */
    public function moments() {
        $id = _get("id");
        if (empty($id)) {
            $this->error('非法操作，参数错误');
        }
        $Course = M('review_course');
        $course = $Course ->where('id='.$id) -> find();
        $course['content'] = htmlspecialchars_decode($course['content']);
        //推荐精彩瞬间
        $pushs = $Course->where("status = 1 AND ispush = 1")->field("id,title,picture")->select();
        $num = count($pushs);
        $this->assign('num',$num);
//        var_dump($pushs);exit;
        $this->assign('pushs', $pushs);
        $this->assign('course', $course);
//        var_dump(count($pushs));
        $this->display();
    }

    /**
     * 收藏课程
     * djt
     * 2017-2-22
     */
    public function addCollect(){
        $info = session("userinfo");
        if(empty($info)){
//            $this->error("请先登录");
            $this->ajaxReturn(array("msg"=>'请先登录','status'=>0));
        }
        $uid = $info['uid'];
        $id = _post('id');
        if(empty($id)){
//            $this->error("参数错误");
            $this->ajaxReturn(array("msg"=>'参数错误','status'=>0));
        }
        $data = array(
            'type' => 2,
            'uid' => $uid,
            'collectid' => $id,
            'created' => time()
        );
        $result = M("collect")->add($data);
        if(!$result){
//            $this->error("添加失败");
            $this->ajaxReturn(array("msg"=>'收藏失败','status'=>0));
        }
//        $this->success("添加成功");
        $this->ajaxReturn(array("msg"=>'收藏成功','status'=>1));
    }

    /**
     * 取消收藏课程
     * djt
     * 2017-2-22
     */
    public function delCollect(){
        $info = session("userinfo");
        if(empty($info)){
//            $this->error("请先登录");
            $this->ajaxReturn(array("msg"=>'请先登录','status'=>0));
        }
        $uid = $info['uid'];
        $id = _post('id');
        if(empty($id)){
//            $this->error("参数错误");
            $this->ajaxReturn(array("msg"=>'参数错误','status'=>0));
        }
        //取消收藏
        $collect = M("collect")->where("collectid ={$id} AND uid = {$uid} AND type = 2")->delete();
        if(empty($collect)){
//            $this->error("取消收藏失败1");
            $this->ajaxReturn(array("msg"=>'取消收藏失败1','status'=>0));
        }
//        $this->success("取消收藏成功");
        $this->ajaxReturn(array("msg"=>'取消收藏成功','status'=>1));
    }

    /**
     * 分享记录
     * djt
     * 2017-2-22
     */
    public function addshare(){
//        $id = 4;
//        $shopInfo = M("company_shop as a")->join("INNER join ww_person_info as b ON a.uid = b.uid")->where("a.shopid = {$id}")->field("a.*,b.vcoin")->find();
        $info = session("userinfo");
        $info = array(
           'uid'=>7,
            'name'=>"七星国1"
        );
        if(empty($info)){
           $this->ajaxReturn(array("msg"=>'登录分享可赢金币','status'=>1));
        }
        $uid = $info['uid'];
        $id = _post('id');
        $id = 4;
        if(empty($id)){
            $this->ajaxReturn(array("msg"=>'参数错误','status'=>0));
        }
        $data = array(
            'type' =>2,
            'uid' => $uid,
            'shareid' => $id,
            'created' => time()
        );
        //查看任务维币
        $Task = M("vcoin_task");
        $TaskLog = M("task_log");
        $start = time() - 86400;
        $end = time();
        $task = $Task->where("action = '分享' AND status = 1")->find();
//        var_dump($task);exit;
        $taskid = $task['id'];
        $taskCoin = $task['value'];
        $limit = $task['limit'];
        //用户当天完成的次数
        $count = $TaskLog->where("uid = {$uid} AND taskid = {$taskid} AND created>$start AND created<$end")->count();
        if(empty($taskCoin)){
            $result = M("share")->add($data);
            if(empty($result)){
                $this->error("分享记录失败");
                $this->ajaxReturn(array("msg"=>'分享记录失败','status'=>0));
            }
        }else{
            $model = new Model();
            $model->startTrans();
            $result = M("share")->add($data);
            if(!$result){
                $model->rollback();
                $this->error("分享记录失败1");
                $this->ajaxReturn(array("msg"=>'分享记录失败','status'=>0));
//                $this->error("添加失败");
            }
            if($count >= $limit){
                $model->commit();
                $this->error("分享记录成功1");
                $this->ajaxReturn(array("msg"=>'分享记录成功','status'=>1));
            }
            //增加用户维币
            $addCoin = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$taskCoin);
            if(empty($addCoin)){
                $model->rollback();
                $this->error("维币更新失败");
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
                $this->error("任务记录失败");
                $this->ajaxReturn(array('msg'=>'任务记录失败','status'=>0));
//                        $this->ajaxReturn(array('msg'=>'注册失败2','status'=>0));
            }
            //写入维币记录
            $Vlog = M("vcoin_log");
            $vlogAarry = array(
                'uid' => $uid,
                'action' => "分享",
                'organizer' => "上海维沃珂营销平台",
                'intro' => "分享课程增加维币",
                'type' => 2,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog = $Vlog->add($vlogAarry);
            if(!$vlog){
                $model->rollback();
                $this->error("维币记录失败");
                $this->ajaxReturn(array('msg'=>'维币记录失败','status'=>0));
            }
            $model->commit();
        }
        $this->success("分享成功");
        $this->ajaxReturn(array('msg'=>'分享成功','status'=>1));
    }

    /**
     *添加课程评价
     * 2017-2-23
     * djt
     */
    public function comment(){
        $info = session("userinfo");
        if(empty($info['uid'])){
            $this->ajaxReturn(array("msg"=>"你还没有登录",'status'=>0));
        }
        $id = I("post.id");
        $score = (int)I("post.score");
        $content = I("post.content");
        $score = $score ? $score : 10;
        if(empty($id)){
            $this->ajaxReturn(array("msg"=>"参数错误",'status'=>0));
        }
        if(empty($content)){
            $this->ajaxReturn(array("msg"=>"内容不能为空",'status'=>0));
        }elseif(strlen($content)>200){
            $this->ajaxReturn(array("msg"=>"内容字数不能小于200",'status'=>0));
        }
        $data = array(
            'uid' => $info['uid'],
            'touid' => $id,
            'score' => $score,
            'type' => 3,
            'content' => $content,
            'created' => time(),
            'updated' => time()
        );
        $result = M("person_comment")->add($data);
        if($result){
            $this->ajaxReturn(array("msg"=>"评论成功",'status'=>1));
        }else{
            $this->ajaxReturn(array("msg"=>"评论失败",'status'=>0));
        }
    }


//结束页
}
