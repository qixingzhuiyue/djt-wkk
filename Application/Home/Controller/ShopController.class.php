<?php
/**
 * @desc 店铺及商品管理
 */
namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Org\Util;
use Home\Controller\CommonController;
class ShopController extends CommonController {

    /**
     * @desc 店铺首页
     *
     */
    public function index() {
        //首页关键字
        $key = M("sys_keyword")->where("name='企业店铺首页'")->find();
        //头部企业图广告
        $companyBanner = M("banner")->where("type ='店铺banner' AND status = 1")->order("weight DESC")->find();
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
        $this->assign("key",$key);
        $info = session("userinfo");
        $uid = $info['uid'];
        $status = 0;
        if($info['role']==2){
            $shop = M("company_shop")->where("uid = {$uid} AND type = 1 AND status = 1")->find();
            if(!empty($shop['temptype'])){
                $status = 1;
                $this->assign('shop',$shop);
                $this->assign('status',$status);
            }
        }
        //推荐商品
        $goods = M("shop_goods as a")->join("left join ww_company_shop as b ON a.shopid = b.shopid")->field("a.*,b.temptype")->where("a.status = 1 AND a.ispush = 1 AND b.temptype!=''")->order("a.updated DESC")->limit(3)->select();
        $num = C("VCOIN_NUM");
        foreach($goods AS &$v){
            $v['nowprice'] = (int)round($v['nowprice']*$num);
            $v['price'] = (int)round($v['price']*$num);
            $v['vcoin'] = $v['nowprice'] ? $v['nowprice'] : $v['price'];
        }
        $this->assign("goods",$goods);
//        var_dump($goods);exit;
        //店铺页
        $ispush = I("get.ispush");
        $condition = "type =1 AND status = 1 AND temptype!=''";
        if($ispush==1){
            $condition .=" AND ispush = 1";
        }
        $count = M("company_shop")->where($condition)->count();
        $Page = new Util\Page($count,12);
        $show = $Page->show();
        $shops = M("company_shop")->where($condition)->order("updated DESC")->limit("$Page->firstRow".','.$Page->listRows)->select();
        $this->assign("shops",$shops);
        $this->assign("page",$show);
        //推荐活动店铺
        $pushs = M("company_shop")->where("type = 1 AND ispush = 1 AND status = 1  AND temptype!=''")->order("updated DESC")->limit(6)->select();
//        var_dump($pushs[0]);
        $this->assign('pushs',$pushs);
        $this->display();
    }

    /**djt
     * @desc 店铺页
     * 2017-2-17
     */
    public function shop() {
        $temptype= _get("temptype");
        $id = (int)_get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $status = 0;
        //获取shopid
        $Shop = M("company_shop");
        $shop= $Shop ->where("shopid = {$id} AND type = 1")->find();
        if(empty($temptype)){
            $temptype= $shop['temptype'];
        }
        $shopid = $shop['shopid'];
        $this->assign("shop",$shop);
        $info = session("userinfo");
        if(!empty($info)){
            $this->assign('info',$info);
        }
        $uid = $info['uid'];
        $status = 0;
        if($info['role']==2){
            if($uid == $shop['uid']){
                $status = 1;
                $this->assign('status',$status);
            }
        }
        //商品
        $goods = M("shop_goods")->where("shopid = {$shopid} AND status= 1")->order("created DESC")->select();
        $num = C("VCOIN_NUM");
        foreach($goods AS &$v){
            $price = (int)round($v['price']*$num);
            $nowprice = (int)round($v['nowprice']*$num);
            $v['vcoin'] = empty($nowprice) ? $price :$nowprice;
            //热门
            if($v['type'] == 2){
                $hots[] = $v;
            }
            //推荐
            if($v['type'] == 1){
                $pushs[] = $v;
            }
            //折扣和优惠:最新折扣，最新活动
            if(!empty($v['nowprice'])){
                $discount[] = $v;
            }
        }
        $this->assign('goods',$goods);
        //最新的8个
        $newShops = array_slice($goods,0,8);
//        var_dump($discount);exit;
        $this->assign("newShops",$newShops);
        $this->assign("pushs",$pushs);
        $this->assign("hots",$hots);
        $this->assign("discount",$discount);
        $this->display($temptype);
    }
    /**
     * 商品详情
     * 2017-4-12
     * djt
     */
    public function proDetails(){
        $jssdk = new JsSdkController;
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $info = session("userinfo");
        $id = (int)_get("id");
        if(empty($id)){
            $this->error("参数错误1");
        }
        $Good = M("shop_goods");
        $good = $Good->where("goodid = {$id}")->find();
        if(empty($good)){
            $this->error('参数异常');
        }
        $shopid = $good['shopid'];
        //店铺信息
        $shop = M("company_shop")->where("shopid={$shopid}")->find();
        $this->assign('shop',$shop);
//        if($product['row']==1){
//            $news['content'] = htmlspecialchars_decode($news['content']);
//        }else{
        $good['content'] = htmlspecialchars_decode( $good['content']);
//        }
        //是否收藏
        if(!empty($info['uid'])){
//            $avatar = M("person_info")->where("uid = {$info['uid']}")->field('avatar')->find();
            //获取头像，三表联查以user这个基本表为主表，左关联就能查出另外两表中含有的数据
            $avatars = M("users as a")->join(array("LEFT join ww_person_info as b ON a.uid=b.uid","LEFT join ww_company_info as c ON a.uid=c.uid"))->where("a.uid={$info['uid']}")->field('b.avatar,c.logo')->find();
            $avatar = empty($avatars['avatar']) ? $avatars['logo'] : $avatars['avatar'];
            $this->assign('avatar',$avatar);
            $this->assign('info',$info);
            $collect = M("collect")->where("collectid = {$id} AND type=6 AND uid ={$info['uid']}")->count();
        }
        $good['collect'] = $collect ? 1 : 0;
        //评论数
        $good['commentNum'] = M("person_comment")->where("touid = {$id} AND type = 2 AND status =1")->count();
        //评论
        $comments =  M("person_comment")->where("touid = {$id} AND type = 2 AND status =1")->order("created DESC")->limit(10)->select();
        foreach($comments AS &$v){
            //用户信息
            $infos = M("users as a")->join("LEFT join ww_person_info as b ON a.uid = b.uid")->where("a.uid ={$v['uid']}")->field("a.*,b.avatar")->find();
            $v['name'] = $infos['name'];
            $v['avatar'] = $infos['avatar'];
            if($infos['role']==2){
                $companyAvatar = M("company_info")->where("uid={$v['uid']}")->find();
                $v['avatar'] = $companyAvatar['logo'];
            }
            if($info['uid']==$v['uid']){
                $replyStatus = 0;
            }else{
                $replyStatus = 1;
            }
//            $this->assign('replyStatus',$replyStatus);
            $v['replyStatus'] = $replyStatus;
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
            //评论回复
            $reply = M("comment_reply")->where("commentid = {$v['commentid']} AND type = 1")->select();
            foreach($reply AS &$val){
                $replyer = M("users as a ")->join("LEFT join ww_person_info as b ON a.uid = b.uid")->field("a.*,b.avatar")->where("a.uid = {$val['uid']} ")->find();
                $val['name'] = $replyer['name'];
                $val['avatar'] = $replyer['avatar'];
                if($replyer['role']==2){
                    $companyAvatar = M("company_info")->where("uid={$val['uid']}")->find();
                    $val['avatar'] = $companyAvatar['logo'];
                }
                if(file_exists('./Public'.$val['avatar'])){
                    $url = str_replace('.','_little.',$val['avatar'],$i);
                    if(file_exists('./Public'.$url)){
                        $val['avatar'] = $url;
                    }else{
                        $image = new \Think\Image();
                        $image->open('./Public'.$v['avatar']);
                        $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                        $val['avatar'] = $url;
                    }
                }
            }
            //评论支持
            if(!empty($info['uid'])){
                $support = M("comment_reply")->where("commentid = {$v['commentid']} AND type = 2 AND uid = {$info['uid']}")->count();
            }
            $support = $support ? 1 : 0;
            $v['reply'] = $reply;
            $v['support'] = $support;
        }
        $this->assign('comments',$comments);
        //商品价格
        $good['money'] = empty($good['nowprice']) ? $good['price'] : $good['nowprice'];
        //推荐店铺
        $shops = M("company_shop")->where("ispush=1 AND type=1 AND status = 1")->order(array('updated'=>'DESC','created'=>'DESC'))->limit(5)->field("shopid,name,logo")->select();
        //推荐热门商品
        $goods = $Good->where("type = 2 AND shopid={$shopid} AND status = 1")->order(array('updated'=>'DESC','created'=>'DESC'))->limit(3)->field("goodid,price,nowprice,name,picture")->select();
        foreach($goods as &$val){
            $val['money'] = empty($val['nowprice']) ? $val['price'] : $val['nowprice'];
        }
//        //热门文贴
//        $articles = M("article")->where("status = 1 AND ispush = 1 AND type = {$row}")->field("articleid,title")->limit(6)->select();
//        $this->assign('articles', $articles);
        $this->assign('shops', $shops);
        $this->assign('goods', $goods);
        $this->assign('good',$good);
        $this->display();
    }
    /**
     * 收藏店铺
     * djt
     * 2017-2-21
     */
    public function addCollect(){
//        $id = 4;
//        $shopInfo = M("company_shop as a")->join("INNER join ww_person_info as b ON a.uid = b.uid")->where("a.shopid = {$id}")->field("a.*,b.vcoin")->find();
        $info = session("userinfo");
//        $info = array(
//           'uid'=>7,
//            'name'=>"七星国1"
//        );
        if(empty($info)){
//            $this->error("请先登录");
            $this->ajaxReturn(array('msg'=>'你还未登录','status'=>2));
        }
        $uid = $info['uid'];
        $id = _post('id');
//        $id = 4;
        if(empty($id)){
//            $this->error("参数错误");
            $this->ajaxReturn(array('msg'=>'参数错误','status'=>0));
        }
        $data = array(
            'type' => 6,
            'uid' => $uid,
            'collectid' => $id,
            'created' => time()
        );
        //只能收藏一次
        $collect = M("collect")->where("uid = {$uid} AND type = 6 AND collectid ={$id}")->find();
//        var_dump($collect);exit;
        if(!empty($collect)){
//            $this->error("已经收藏此店铺");
            $this->ajaxReturn(array('msg'=>'已经收藏此店铺','status'=>0));
        }
        //查看任务维币
        $Task = M("vcoin_task");
        $TaskLog = M("task_log");
        $start = time() - 86400;
        $end = time();
        $task = $Task->where("action = '收藏店铺' AND status = 1")->find();
//        var_dump($task);exit;
        $taskid = $task['id'];
        $taskCoin = $task['value'];
        $limit = $task['limit'];
        //用户当天完成的次数
        $count = $TaskLog->where("uid = {$uid} AND taskid = {$taskid} AND created>$start AND created<$end")->count();
        $shopInfo = M("company_shop as a")->join("INNER join ww_person_info as b ON a.uid = b.uid")->where("a.shopid = {$id}")->field("a.*,b.vcoin")->find();
        $coin = $shopInfo['vcoin'];
        $shopUid = $shopInfo['uid'];
        if(empty($taskCoin)){
            $result = M("collect")->add($data);
           if(empty($result)){
//               $this->error("添加失败");
               $this->ajaxReturn(array('msg'=>'收藏失败1','status'=>0));
           }
        }else{
            $model = new Model();
            $model->startTrans();
            $result = M("collect")->add($data);
            if(!$result){
                $model->rollback();
//                $this->error("添加失败");
                $this->ajaxReturn(array('msg'=>'收藏失败2','status'=>0));
            }
            if($count >= $limit){
                $model->commit();
//                $this->error("收藏成功");
                $this->ajaxReturn(array('msg'=>'收藏成功','status'=>1));
            }
            if($coin<$taskCoin){
                $model->rollback();
//                $this->error("收藏失败1");
                $this->ajaxReturn(array('msg'=>'收藏失败1','status'=>0));
            }
            //增加用户维币
            $addCoin = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$taskCoin);
            $delCoin = M("person_info")->where("uid = {$shopUid} ")->setDec('vcoin',$taskCoin);
            if(empty($addCoin)||empty($delCoin)){
                $model->rollback();
                $this->error("收藏失败2");
                $this->ajaxReturn(array('msg'=>'收藏失败2','status'=>0));
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
                $this->ajaxReturn(array('msg'=>'收藏失败3','status'=>0));
//                        $this->ajaxReturn(array('msg'=>'注册失败2','status'=>0));
            }
            //写入维币记录
            $Vlog = M("vcoin_log");
            $vlogAarry = array(
                'uid' => $uid,
                'action' => "收藏店铺",
                'organizer' => $shopInfo['name'],
                'intro' => "收藏店铺增加维币",
                'type' => 2,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog = $Vlog->add($vlogAarry);
            if(!$vlog){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'收藏失败4','status'=>0));
            }
            //店家写入维币记录
            $vlogAarry1 = array(
                'uid' => $shopUid,
                'action' => "支付收藏店铺",
                'organizer' => $info['name'],
                'intro' => "用户收藏店铺支付的维币",
                'type' => 3,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog1 = $Vlog->add($vlogAarry1);
            if(!$vlog1){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'收藏失败5','status'=>0));
            }
            $model->commit();
        }
//        $this->success("添加成功");
        $this->ajaxReturn(array('msg'=>'收藏成功1','status'=>1));
    }

    /**
     * 取消店铺
     * djt
     * 2017-2-21
     */
    public function delCollect(){
        $info = session("userinfo");
//        $info = array(
//           'uid'=>7,
//            'name'=>"七星国1"
//        );
        if(empty($info)){
            $this->error("请先登录");
        }
        $uid = $info['uid'];
        $id = _get('id');
//        $id = 37;
        if(empty($id)){
            $this->error("参数错误");
        }
        //店铺信息
        $shopInfo = M("company_shop as b")->join("INNER join ww_collect as a ON a.collectid = b.shopid")->where("a.id = {$id}")->field("b.*")->find();
        $shopUid = $shopInfo['uid'];
        if(empty($shopUid)){
            $this->error("参数错误1");
        }
        //查看任务维币
        $Task = M("vcoin_task");
        $task = $Task->where("action = '收藏店铺' AND status = 1")->find();
//        var_dump($task);exit;
        $taskCoin = $task['value'];
        //用户维币
        $coin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
        $coin = $coin['vcoin'];
        if($coin<$taskCoin){
            $this->error("当前维币不足无法取消收藏");
        }
        //取消收藏
        if(empty($taskCoin)){
            $collect = M("collect")->where("id ={$id} AND uid = {$uid} AND type = 6")->delete();
            if(empty($collect)){
                $this->error("取消收藏失败1");
            }
        }else{
            $model = new Model();
            $model->startTrans();
            $collect = M("collect")->where("id ={$id} uid = {$uid} AND type = 6")->delete();
            if(empty($collect)){
                $model->rollback();
                $this->error("取消收藏失败2");
            }
            //增加店铺用户维币
            $addCoin = M("person_info")->where("uid = {$shopUid}")->setInc('vcoin',$taskCoin);
            $delCoin = M("person_info")->where("uid = {$uid} ")->setDec('vcoin',$taskCoin);
            if(empty($addCoin)||empty($delCoin)){
                $model->rollback();
                $this->error("取消失败2");
            }
//        //添加任务记录
//        $Log = M("task_log");
//        $flogarray = array(
//            'uid' => $uid,
//            'taskid' => $taskid,
//            'created' => time()
//        );
//        $flog = $Log->add($flogarray);
//        if(!$flog){
//            $model->rollback();
//            $this->ajaxReturn(array('msg'=>'收藏失败3','status'=>0));
////                        $this->ajaxReturn(array('msg'=>'注册失败2','status'=>0));
//        }
            //写入维币记录
            $Vlog = M("vcoin_log");
            $vlogAarry = array(
                'uid' => $uid,
                'action' => "取消收藏店铺",
                'organizer' => $info['name'],
                'intro' => "取消收藏店铺减少维币",
                'type' => 3,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog = $Vlog->add($vlogAarry);
            if(!$vlog){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'取消收藏失败4','status'=>0));
            }
//        //店家写入维币记录
//        $vlogAarry1 = array(
//            'uid' => $shopUid,
//            'action' => "用户取消收藏店铺",
//            'organizer' => $info['name'],
//            'intro' => "用户收藏店铺支付的维币",
//            'type' => 3,
//            'value' => $taskCoin,
//            'created' => time()
//        );
//        $vlog1 = $Vlog->add($vlogAarry1);
//        if(!$vlog1){
//            $model->rollback();
//            $this->ajaxReturn(array('msg'=>'收藏失败5','status'=>0));
//        }
            $model->commit();
        }
        $this->success("取消收藏成功");
    }
    /**
     * 收藏商品
     * djt
     * 2017-2-24
     */
    public function addGoodCollect(){
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
        $collect = M("collect")->where("collectid ={$id} AND type=1 AND uid = {$uid}")->find();
        if(!empty($collect)){
            $this->ajaxReturn(array("msg"=>'你已经收藏该商品','status'=>0));
        }
        $data = array(
            'type' => 1,
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
     * 取消商品收藏
     * djt
     * 2017-2-24
     */
    public function delGoodCollect(){
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
        $collect = M("collect")->where("collectid ={$id} AND type=1 AND uid = {$uid}")->delete();
        if(empty($collect)){
//            $this->error("取消收藏失败1");
            $this->ajaxReturn(array("msg"=>"取消收藏失败1",'status'=>0));
        }
//        $this->success("取消收藏成功");
        $this->ajaxReturn(array("msg"=>"取消收藏成功",'status'=>1));
    }
    /**
     * 分享记录
     * djt
     * 2017-4-21
     */
    public function addshare(){
        //用户当天完成的次数
//        $id = 4;
//        $shopInfo = M("company_shop as a")->join("INNER join ww_person_info as b ON a.uid = b.uid")->where("a.shopid = {$id}")->field("a.*,b.vcoin")->find();
        $info = session("userinfo");
//        $info = array(
//           'uid'=>7,
//            'name'=>"七星国1"
//        );
//        if(empty($info)){
//            $this->ajaxReturn(array("msg"=>'登录分享可赢金币','status'=>1));
//        }
        $uid = $info['uid'];
        $id = _post('id');
//        $id = 3;
        if(empty($id)){
            $this->ajaxReturn(array("msg"=>'参数错误','status'=>0));
        }
//        获取店铺uid 三表联查
//        $sql = "select c.uid,c.vcoin FROM ww_shop_goods a,ww_company_shop b,ww_person_info c where a.goodid={$id} AND a.shopid=b.shopid AND b.uid = c.uid";
//        $result = M()->query($sql);
//        echo M()->getLastSql();
//        var_dump($result);exit;
        $goods = M("company_shop as b")->join(array('RIGHT join ww_shop_goods as a ON a.shopid=b.shopid','LEFT join ww_person_info as c ON b.uid=c.uid'))->where("a.goodid={$id}")->field("b.name,c.uid,c.vcoin")->find();
//        echo M()->getLastSql();
//        var_dump($goods);exit;
        $goodUid = $goods['uid'];
        if(empty($goodUid)){
            $organizer = "上海维沃珂营销平台";
        }else{
            $organizer = $goodUid['name'];
        }
        $data = array(
            'type' =>1,
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
        //店铺主人维币数减少
        $arcvcoinStatus = true;
        if(!empty($goodUid)){
            $arcVcoins = M("person_info")->where("uid={$goodUid}")->find();
            $arcvcoinStatus = (int)$arcVcoins['vcoin'];
            $arcvcoinStatus = $arcvcoinStatus>=$taskCoin ? true : false;
        }
        if(empty($taskCoin)||empty($arcvcoinStatus)){
            $result = M("share")->add($data);
            if(empty($result)){
//                $this->error("分享记录失败");
                $this->ajaxReturn(array("msg"=>'分享记录失败','status'=>0));
            }
            $this->ajaxReturn(array("msg"=>'分享成功','status'=>1));
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
                $this->ajaxReturn(array("msg"=>'分享记录成功','status'=>1));
            }
            //增加用户维币
            $addCoin = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$taskCoin);
            //减少店铺所有人维币:$goodUid 不为空
            $delCoin = true;
            if(!empty($goodUid)){
                $delCoin = M("person_info")->where("uid={$goodUid}")->setDec("vcoin",$taskCoin);
            }
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
                'action' => "分享",
                'organizer' => $organizer,
                'intro' => "分享商品增加维币",
                'type' => 2,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog = $Vlog->add($vlogAarry);
            //发帖人维币扣除
            $goodUid = $goodUid ? $goodUid : 0;
            $vlogAarry1 = array(
                'uid' => $goodUid,
                'action' => "支付用户分享商品",
                'organizer' => $info['name'],
                'intro' => "支付用户分享商品扣除维币",
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
            $this->ajaxReturn(array('msg'=>'分享成功','status'=>1));
        }
//        $this->success("分享成功");
    }
    /**
     *评论商品
     * 2017-4-21
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
            $type = 2;
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

    /**
     *回复评论
     * 2017-2-24
     * djt
     */
    public function replyComment(){
        if(IS_POST){
            $info = session("userinfo");
            if(empty($info)){
//                $this->error("你还没有登录，请先登录");
                $this->ajaxReturn(array("msg"=>'你还没有登录','status'=>0));
            }
            $uid = $info['uid'];
            $Reply = M('comment_reply');
            $type = 1;
            $commentid =  I("post.commentid");
            $content = I("post.content");
//            $browsenum = I("post.browsenum");
            if(empty($content)){
//                $this->error("内容不能为空");
                $this->ajaxReturn(array("msg"=>'内容不能为空','status'=>0));
            }
            if(empty($commentid)){
//                $this->error("参数错误");
                $this->ajaxReturn(array("msg"=>'参数错误','status'=>0));
            }
            $data = array(
                'type' => $type,
                'uid' => $uid,
                'commentid' => $commentid,
                'content' => $content,
                'created' => time()
            );
            $result = $Reply->add($data);
            if(!$result){
//                $this->error('回复失败');
                $this->ajaxReturn(array("msg"=>'回复失败','status'=>0));
            }
//            $this->success('回复成功');
            $this->ajaxReturn(array("msg"=>'回复成功','status'=>1));
        }else{
//            $this->error("异常操作");
            $this->ajaxReturn(array("msg"=>'异常操作','status'=>0));
        }
    }

    /**
     *支持评论
     * 2017-4-24
     * djt
     */
    public function support(){
        if(IS_POST){
            $info = session("userinfo");
            if(empty($info)){
//                $this->error("你还没有登录，请先登录");
                $this->ajaxReturn(array('msg'=>'你还没有登录，请先登录','status'=>0));
            }
            $uid = $info['uid'];
            $Reply = M('comment_reply');
            $type = 2;
            $commentid =  I("post.commentid");
            if(empty($commentid)){
//                $this->error("参数错误");
                $this->ajaxReturn(array('msg'=>'参数错误','status'=>0));
            }
            //是否已经支持
            $num = $Reply->where("type=2 AND uid ={$uid} AND commentid={$commentid}")->find();
            if(!empty($num)){
                $this->ajaxReturn(array('msg'=>'你已经支持过','status'=>0));
            }
            $data = array(
                'type' => $type,
                'uid' => $uid,
                'commentid' => $commentid,
                'created' => time()
            );
            $result = $Reply->add($data);
            if(!$result){
//                $this->error('操作失败');
                $this->ajaxReturn(array('msg'=>'操作失败','status'=>0));
            }
//            $this->success('操作成功');
            $this->ajaxReturn(array('msg'=>'操作成功','status'=>1));
        }else{
//            $this->error("异常操作");
            $this->ajaxReturn(array('msg'=>'异常操作','status'=>0));
        }
    }
    /**djt
     * @desc 店铺管理
     * 2017-2-17
     */
    public function shopsManagement() {
        $info = session("userinfo");
        if(empty($info)){
            $this->error("你还没有登录");
        }
        if($info['role']!=2){
            $this->error("你不是企业用户，无法操作");
        }
        $uid = $info['uid'];
        $shop = M("company_shop")->where("uid = {$uid} AND type = 1 AND status =1")->find();
        if(empty($shop)){
            $this->error("申请店铺，后台审核通过方可进入");
        }
        if (IS_POST) {
            $type = I("post.type");
            $name = I("post.name");
            $business = I("post.business");
            $intro = I("post.intro");
            $contact = I("post.contact");
            $mobile = I("post.mobile");
            if(strlen($mobile)>12){
                $this->error("联系电话填写有误");
            }
            $data = array();
            if (!empty($type)) {
                $data['shoptype'] = $type;
            }
            if (!empty($name)) {
                $data['name'] = $name;
            }
            if (!empty($business)) {
                $data['business'] = $business;
            }
            if (!empty($intro)) {
                $data['intro'] = $intro;
            }
            if (!empty($contact)) {
                $data['contact'] = $contact;
            }
            if(!empty($_FILES['banner']['name'])||!empty($_FILES['logo']['name'])){
                $pictures = $this->_uploads1('shop');
                $logo = $pictures["logo"] ?  $pictures["logo"] : '';
                $banner = $pictures["banner"] ?  $pictures["banner"] : '';
            }
            if(!empty($logo)){
                $data['logo'] = $logo;
            }
            if(!empty($banner)){
                $data['banner'] = $banner;
            }
//            $picture = "/Uploads/news/20170209/589c46848503e.jpg";
            if (!empty($mobile)) {
                $data['mobile'] = $mobile;
            }
//            var_dump($data);exit;
//            $licensepic = "/Uploads/news/20170209/589c46848503e.jpg";
//            $logo = "/Uploads/news/20170209/589c46848503e.jpg";
            if (!empty($data)) {
                $data['updated'] = time();
                $Shop = M("company_shop");
                $sResult = $Shop->where("uid = {$uid}")->save($data);
                if (!$sResult) {
                    $this->error("编辑失败");
                }
            }
            $this->success("编辑成功");
        }else{
            //店铺类型
            $types = getDictTypes("店铺类型");
            $this->assign("shop",$shop);
            $this->assign("types",$types);
            $Company = M("company_info");
            $company = $Company->where("uid = {$uid} AND status = 1 AND companystatus = 1")->find();
            $this->assign("company", $company);
            $this->display();
        }
//        //商品
//        $shopid = $shop['shopid'];
//        $goods = M("shop_goods")->where("shopid =  {$shopid} AND status = 1 ")->order("created DESC")->select();
//        //1元兑换维币数
//        $num = C("VCOIN_NUM");
//        foreach($goods AS &$v){
//            $price = (int)round($v['price']*$num);
//            $nowprice = (int)round($v['nowprice']*$num);
//            $v['vcoin'] = $nowprice ? $nowprice : $price;
//        }
//        $this->assign('goods',$goods);
//        var_dump($types);exit;
    }
    /**djt
     * @desc 商品管理
     * 2017-2-17
     */
    public function goodsManagement() {
        $info = session("userinfo");
        if(empty($info)){
            $this->error("你还没有登录");
        }
        if($info['role']!=2){
            $this->error("你不是企业用户，无法操作");
        }
        $uid = $info['uid'];
        $shop = M("company_shop")->where("uid = {$uid} AND type = 1 AND status =1")->find();
        if(empty($shop)){
            $this->error("申请店铺，后台审核通过方可进入");
        }
            //店铺类
            $types = getDictTypes("店铺类型");
            $this->assign("shop",$shop);
            $this->assign("types",$types);
            $Company = M("company_info");
            $company = $Company->where("uid = {$uid} AND status = 1 AND companystatus = 1")->find();
            $this->assign("company", $company);
        $shopid = $shop['shopid'];
        $goods = M("shop_goods")->where("shopid =  {$shopid} AND status = 1 ")->order("created DESC")->select();
        //1元兑换维币数
        $num = C("VCOIN_NUM");
        foreach($goods AS &$v){
            $price = (int)round($v['price']*$num);
            $nowprice = (int)round($v['nowprice']*$num);
            $v['vcoin'] = $nowprice ? $nowprice : $price;
            if(file_exists('./Public'.$v['picture'])){
                $url = str_replace('.','_mid.',$v['picture'],$i);
                if(file_exists('./Public'.$url)){
                    $v['picture'] = $url;
                }
            }
        }
        $this->assign('goods',$goods);
        $this->display();
    }
    /**
     * 商品订单管理
     * 2017-4-27
     */
    public function orderManagement(){
        $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录，无法查看订单");
        }
        $uid = $info['uid'];
        $shop = M("company_shop")->where("uid = {$uid} AND type = 1 AND status =1")->find();
        if(empty($shop)){
            $this->error("申请店铺，后台审核通过方可进入");
        }
        $this->assign("shop",$shop);
        $condition = "b.uid = {$uid} AND c.type=1";
        $orderstatus = _get('orderstatus');
        if(is_numeric($orderstatus)&&(int)$orderstatus<5){
            $orderstatus = (int)$orderstatus;
            $condition.=" AND c.orderstatus={$orderstatus}";
        }
        //商品订单 四表联查
        $orders = M("shop_goods as a")->join(array("RIGHT join ww_company_shop as b ON a.shopid = b.shopid","LEFT join ww_order as c  ON c.shopid=a.goodid","RIGHT join ww_users as d ON c.uid=d.uid"))->where($condition)->field("c.*,b.shopid,d.name,a.picture,a.name as goodname")->order("c.orderdate DESC")->select();
//        echo M()->getLastSql();exit;
        $this->assign("orders",$orders);
//        dump($orders[0]);
        $this->display();
    }
    /**
     * 商家或者买家取消订单
     * 2017-4-27
     */
    public function closeOrder(){
        $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录，无法操作订单");
        }
        $orderId = (int)_get('orderId');
        if(empty($orderId)){
            $this->error("参数错误");
        }
        $order = M("order")->where("id={$orderId} AND type=1")->find();
        if(empty($order)){
            $this->error("该商品订单不存在");
        }
        if($order['orderstatus']!=0){
            $this->error("该商品订单不可关闭");
        }
        $goodid = (int)$order['shopid'];
        $shop = M("shop_goods as a")->join("LEFT join ww_company_shop as b ON a.shopid=b.shopid")->where("a.goodid={$goodid}")->field("b.uid")->find();
        $uid = $info['uid'];
        if(($uid!=$shop['uid'])||($uid!=$order['uid'])){
            $this->error("你无权操作此订单");
        }
        $descp = $order==$shop['uid'] ? $order['descp']."/该订单由卖家关闭" : $order['descp']."/该订单由买家关闭";
        $data = array('orderstatus'=>3,'descp'=>$descp,'updated'=>time());
        $result = M("order")->where("id={$orderId} AND type=1 AND orderstatus=0")->save($data);
        if($result){
            $this->success("关闭订单成功");
        }else{
            $this->error("关闭订单失败");
        }
    }
    /**
     * 商家确认发货
     * 2017-4-27
     */
    public function confirmOrder(){
//        $invoice_sn = '100873872018';
//        $url = 'http://www.kuaidi100.com/query?type=shunfeng&postid='.$invoice_sn;
//        $res = post_fsockopen($url,'',1);
//        $res = json_decode($res,true);
//        var_dump($res);exit;
//        if(!empty($res) && $res['status'] == "200"){
//            $out['response'] = array_reverse($res['data']);
//            $out['delivery_sn'] = $invoice_sn;
//            return $out;
//        }

        $info = session("userinfo");
        if(empty($info)){
//            $this->error("您还未登录，操作订单");
            $this->ajaxReturn(array('msg'=>'您还未登录，无法操作','status'=>0));
        }
        $orderId = (int)I('post.orderId');
        $expressNum = I('post.expressNum');
        $expressName = I("post.expressName");
//        $orderId = 129;
//        $expressNum='42242142141241';
//        $expressName = '顺丰';
        if(empty($orderId)){
//            $this->error("订单号不能为空");
            $this->ajaxReturn(array('msg'=>'订单号不能为空','status'=>0));
        }
        if(empty($expressNum)){
//            $this->error("运单号不能为空");
            $this->ajaxReturn(array('msg'=>'运单号不能为空','status'=>0));
        }
        if(empty($expressName)){
//            $this->error("快递名称不能为空");
            $this->ajaxReturn(array('msg'=>'快递名称不能为空','status'=>0));
        }
        $order = M("order")->where("id={$orderId} AND type=1")->find();
        if(empty($order)){
//            $this->error("该商品订单不存在");
            $this->ajaxReturn(array('msg'=>'该商品订单不存在','status'=>0));
        }
        if($order['orderstatus']!=1){
//            $this->error("该商品订单不可确认收货，无法确认发货");
            $this->ajaxReturn(array('msg'=>'该商品订单不处在已支付状态，无法确认发货','status'=>0));
        }
        $goodid = (int)$order['shopid'];
        $shop = M("shop_goods as a")->join("LEFT join ww_company_shop as b ON a.shopid=b.shopid")->where("a.goodid={$goodid}")->field("b.uid")->find();
        $uid = $info['uid'];
        if($uid!=$shop['uid']){
//            $this->error("你无权操作此订单");
            $this->ajaxReturn(array('msg'=>'你无权操作此订单','status'=>0));
        }
        $data = array(
            'orderstatus'=>2,
            'expressnum' => $expressNum,
            'expressname' => $expressName,
            'updated' => time()
        );
        $result = M("order")->where("id={$orderId} AND type=1 AND orderstatus=1")->save($data);
        if($result){
//            $this->success("确认发货成功");
            $this->ajaxReturn(array('msg'=>'确认发货成功','status'=>1));
        }else{
//            $this->error("确认发货失败");
            $this->ajaxReturn(array('msg'=>'确认发货失败','status'=>0));
        }
    }

    /**djt移到店铺管理shopsManagement，暂时不用此接口
     * @desc 店铺资料编辑
     * 2017-2-16
     */
    public function editInfo()
    {
        $info = session('userinfo');
        if (empty($info)) {
            $this->error('你还没有登录，请先登录');
        }
        $uid = $info['uid'];
        if (IS_POST) {
            $type = I("post.type");
            $name = I("post.name");
            $business = I("post.business");
            $intro = I("post.intro");
            $contact = I("post.contact");
            $mobile = I("post.mobile");
//            $type = 42;
//            $business = "2只为守护";
//            $intro = "2一直永远不倒的队伍";
//            $name = "七星小店";
//            $contact = "2宁雨";
//            $mobile = "213166292960";
            if(strlen($mobile)>12){
                $this->error("联系电话填写有误");
            }
            $data = array();
            if (!empty($type)) {
                $data['shoptype'] = $type;
            }
            if (!empty($name)) {
                $data['name'] = $name;
            }
            if (!empty($business)) {
                $data['business'] = $business;
            }
            if (!empty($intro)) {
                $data['intro'] = $intro;
            }
            if (!empty($contact)) {
                $data['contact'] = $contact;
            }
            if(!empty($_FILES['picture']['name'])){
                $pictures = parent::_uploads("shop");
                $banner = $pictures[0];
                $picture = $pictures[1];
            }
//            $picture = "/Uploads/news/20170209/589c46848503e.jpg";
            if (!empty($picture)) {
                $data['logo'] = $picture;
            }
            if (!empty($banner)) {
                $data['banner'] = $banner;
            }
            if (!empty($mobile)) {
                $data['mobile'] = $mobile;
            }
//            var_dump($data);exit;
//            $licensepic = "/Uploads/news/20170209/589c46848503e.jpg";
//            $logo = "/Uploads/news/20170209/589c46848503e.jpg";
            if (!empty($data)) {
                $data['updated'] = time();
                $Shop = M("company_shop");
                $sResult = $Shop->where("uid = {$uid}")->save($data);
                if ($sResult) {
                    $this->success("编辑成功");
                } else {
                    $this->error("编辑失败");
                }
            }
        }
            $Company = M("company_info");
            $company = $Company->where("uid = {$uid} AND status = 1 AND companystatus = 1")->find();
            $shopType = getDictTypes("店铺类型");
            $this->assign("shopType", $shopType);
            $this->assign("company", $company);
            $this->display();
    }

    /**djt
     * @desc 店铺入驻申请
     * 2017-2-15
     */
    public function apply() {
//        $this->error("开发中，敬请期待");
        $info = session("userinfo");
        if(empty($info)){
            $this->error("你还未登陆");
        }
        if($info['role']!=2){
            $this->error("你不是企业用户无法添加商铺");
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $companyStatus = $Company->where("uid = {$uid} AND status = 1 AND companystatus =1")->find();
        if(empty($companyStatus)){
            $this->error("你的企业还未审核通过");
        }
        $this->assign('company',$companyStatus);
        $companyid = $companyStatus['companyid'];
        $Shop = M("company_shop");
        $shop = $Shop->where("uid = {$uid}")->find();
        if(!empty($shop)){
            if($shop['temptype']==''){
                $this->error("你已申请店铺，请选择模板",U("Home/Shop/shopslocated"));
            }
            $this->error("你已经存在店铺,即将前往店铺",U('Home/Shop/shop',array('id'=>$shop['shopid'],'temptype'=>$shop['temptype'])));
        }
        if(IS_POST){
            $name = I("post.name");
            $contact = I("post.contact");
            $intro = I("post.intro");
//            $name = "剑冢";
//            $contact = "藏剑";
//            $intro = "收藏，找寻，呵护";
//            if(empty($_FILES['picture'])){
//                $picture = '';
//            }else{
//                $picture = parent::_upload("shop");
//            }
////            $picture = "/Uploads/news/20170209/589c46848503e.jpg";
//            if(empty($picture)){
//                $this->error("店铺logo不能为空");
//            }
            if(empty($name)){
                $this->error("店铺名不能为空");
            }
            if(empty($contact)){
                $this->error("联系人不能为空");
            }
            if(empty($intro)){
                $this->error("店铺介绍不能为空");
            }
            $shopname = $Shop->where("name ='{$name}'")->find();
            if(!empty($shopname)){
                $this->error("该店铺名已被使用");
            }
            $data = array(
                'name' => $name,
                'companyid' => $companyid,
                'uid' => $uid,
                'type' => 0,//默认未审核
//                'logo' => $picture,
                'contact' => $contact,
                'intro' => $intro,
                'created' => time(),
                'updated' => time()
            );
            $shopid = $Shop->add($data);
            if(empty($shopid)){
                $this->error("申请失败");
            }
            $this->success("申请成功",U('Home/Shop/shopsLocated'));
        }
        $this->assign("company",$companyStatus);
        $this->display();
    }

    /**
     * @desc 选择店铺模板
     * djt
     * 2017-2-15
     */
    public function shopsLocated(){
        if(IS_POST){
            $info = session("userinfo");
            if(empty($info)){
                $this->ajaxReturn(array('msg'=>"你还未登陆，请先登录",'status'=>0));
//                $this->error("你还未登陆，请先登录");
            }
            $temptype = I("post.temptype");
//        $temptype = 1;
            if(empty($temptype)){
                $this->ajaxReturn(array('msg'=>"你还未选择模板",'status'=>0));
//                $this->error("你还未选择模板");
            }
            $uid = $info['uid'];
            $Shop = M("company_shop");
            $shop = $Shop->where("uid = {$uid}  AND status = 1")->find();
            if(empty($shop)){
                $this->ajaxReturn(array('msg'=>"你的店铺还未申请,不能选择模板",'status'=>0));
//                $this->error("你的店铺还未申请成功,不能选择模板");
            }
            if(!empty($shop['temptype'])){
                $this->ajaxReturn(array('msg'=>"你已经选择了模板，不能更改",'status'=>0));
//                $this->error("你已经选择了模板，不能更改");
            }
            $data = array(
                'temptype' =>$temptype,
                'updated' => time()
            );
            $sResult = $Shop->where("uid = {$uid}")->save($data);
            if($sResult){
                $this->ajaxReturn(array('msg'=>"添加模板成功",'shopid'=>$shop['shopid'],'status'=>1));
//                $this->success("添加模板成功");
            }else{
                $this->ajaxReturn(array('msg'=>"添加模板失败",'status'=>0));
//                $this->error("添加模板失败");
            }
        }
        //获取模板
//        $Picture = M("picture");
//        $pids = $Picture->where("name ='店铺模板' AND pid = 0 AND status = 1")->find();
//        $pid = $pids['id'];
//        $pictures = $Picture->where("pid = {$pid} AND status =1")->field("id,name,picture")->select();
//        $this->assign("pictures",$pictures);
        $this->display();
    }
    /**
     * 添加编辑商品
     *2017-2-16
     */

    public function add() {
        $info = session("userinfo");
        if(empty($info)){
            $this->error("你还未登陆,请先登录");
//            $this->ajaxReturn(array('msg'=>'你还未登陆,请先登录','status'=>0));
    }
        $uid = $info['uid'];
        if (IS_POST) {
            $name = I('post.name');
            $shopid = I('post.shopid');
            $goodid = I('post.goodid');
            $price = I('post.price');
            $price = round($price,2);
            $nowprice = I('post.nowprice');
            $nowprice = round($nowprice,2);
            $intro = I('post.intro');
            $content = I('post.content');
            $type = I('post.type');
            $status = I('post.status');
            $Good = M("shop_goods");
            $flag = true;
            $msg = "";
            if(empty($shopid)){
                $this->error("参数错误");
            }
            $shop = M("company_shop")->where("shopid = {$shopid} AND uid = {$uid} AND type = 1")->field("shopid")->find();
            if(empty($shop)){
                $this->error("当前店铺不可添加编辑商品");
//                $this->ajaxReturn(array('msg'=>'当前店铺不可添加编辑商品','status'=>0));
            }
            if(empty($goodid)){
                if(empty($shopid)){
                    $this->error("参数错误");
//                    $this->ajaxReturn(array('msg'=>'参数错误','status'=>0));
                }
                if(empty($name)){
                    $flag = false;
                    $msg .= "商品名称不能为空,";
                }
                if(empty($price)){
                    $flag = false;
                    $msg .= "商品价格不能为空,";
                }
                if(empty($intro)){
                    $flag = false;
                    $msg .= "商品介绍不能为空,";
                }
                if(empty($content)){
                    $flag = false;
                    $msg .= "商品详情不能为空,";
                }
                if(empty($type)){
                    $flag = false;
                    $msg .= "商品推荐类型不能为空,";
                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload("shop");
                }
                if(empty($picture)){
                    $flag = false;
                    $msg .= "商品图片不能为空";
                }
                if(!$flag){
                    $msg = trim($msg,',');
                    $this->error($msg);
//                    $this->ajaxReturn(array('msg'=>'$msg','status'=>0));

                }
                $data = array(
                    'shopid' => $shopid,
                    'name' => $name,
                    'picture' => $picture,
                    'price' => $price,
                    'nowprice' => $nowprice,
                    'intro' => $intro,
                    'content' => $content,
                    'type' => $type,
                    'created' => time(),
                    'updated' => time()
                );
                if(!empty($status)){
                    $data['status'] = $status;
                }
                //添加商品
                $model = new Model();
                $model->startTrans();
                $good = $Good->add($data);
                if(!$good){
                    $model->rollback();
                    $this->error("添加失败1");
                }
                //维币任务
                $task = getTask("发布产品");
                $flag = false;
                $Log = M("task_log");
                if(!empty($task)){
                    $taskid = $task['id'];
                    $value = $taskid['value'];
                    if($task['type'] ==1){
                        $log = $Log->where("uid = {$uid} AND taskid = {$taskid}")->find();
                        if(empty($log)){
                            $flag = true;
                        }
                    }
                    if($task['type'] ==2){
                        $limit = (int)$task['limit'] ? (int)$task['limit']:1;
                        $start = strtotime(date('Y-m-d'))-86400;
                        $end = time();
                        $log = $Log->where("uid = {$uid} AND taskid = {$taskid} AND created>=$start AND created<$end")->count();
                        if($limit>$log){
                            $flag = true;
                        }
                    }
                    if($task['type'] ==4){
                       $flag = true;
                    }
                    if($flag){
                        //任务有效添加用户维币
                        $person = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$value);
                        if(!$person){
                            $model->rollback();
                            $this->error("添加失败1");
                        }
                        //任务记录添加
                        $taskArray = array(
                            'uid' => $uid,
                            'taskid' => $taskid,
                            'created' => time()
                        );
                        $tLog = M("task_log")->add($taskArray);
                        if(!$tLog){
                            $model->rollback();
                            $this->error("添加失败2");
                        }
                        //维币记录
                        $vArray = array(
                            'uid' => $uid,
                            'action' => "添加商品",
                            'organizer' => "上海维沃珂营销平台",
                            'intro' => "任务完成获得的维币",
                            'type' => 2,
                            'value' => $value,
                            'created' => time()
                        );
                        $vlog = M("vcoin_log")->add($vArray);
                        if(empty($vlog)){
                            $model->rollback();
                            $this->error("添加失败3");
                        }
                    }
                }
                $model->commit();
                $this->success("添加成功",U('Home/Shop/goodsManagement'));
            }else{//编辑
                $data = array();
                if(!empty($name)){
                    $data['name'] = $name;
                }
                if(!empty($price)){
                    $data['price'] = $price;
                }
                if(!empty($nowprice)){
                    $data['nowprice'] = $nowprice;
                }
                if(!empty($intro)){
                    $data['intro'] = $intro;
                }
                if(!empty($content)){
                    $data['content'] = $content;
                }
                if(!empty($type)){
                    $data['type'] = $type;
                }
                if(!empty($status)){
                    $data['status'] = $status;
                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload("good");
                }
                if(!empty($picture)){
                    $data['picture'] = $picture;
                }
                if(!empty($data)){
                    $gUpdated = $Good->where("goodid = {$goodid}")->save($data);
                    if(!$gUpdated){
                        $this->error("更新失败");
                    }
                }
                $this->success("更新成功",U('Home/Shop/goodsManagement'));
            }
        } else {
            $goodid = _get('id');
            $shop = M("company_shop")->where("uid = {$uid} AND type = 1")->find();
            if(empty($shop)){
                $this->error("当前用户店铺不可用");
//                $this->ajaxReturn(array('msg'=>'当前店铺不可添加编辑商品','status'=>0));
            }
            $this->assign("shop",$shop);
            if(!empty($goodid)){
                $good = M("shop_goods")->where("goodid = {$goodid} AND status = 1")->find();
                $good['content'] = htmlspecialchars_decode($good['content']);
                $this->assign("good",$good);

            }
            $this->display();
        }
    }

    /*
     * 删除商品
     * 2017-2-20
     */
    public function delGoods()
    {
        $info = session("userinfo");
        if ($info['role'] != 2) {
//            $this->error("当前身份状态不能操作");
            $this->ajaxReturn(array('msg' => '当前身份状态不能操作', 'status' => 0));
        }
        $id = I("post.id");
        if(empty($id)){
//            $this->error("参数错误");
            $this->ajaxReturn(array('msg' => '参数错误', 'status' => 0));
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
        if (empty($company)) {
//            $this->error("非法请求");
            $this->ajaxReturn(array('msg' => '非法请求', 'status' => 0));
        }
        //该商品有订单存在不可删除
        $order = M("order")->where("shopid={$id} AND type=1")->count();
        if(!empty($order)){
            $this->ajaxReturn(array('msg' => '改商品已经存在订单不可删除', 'status' => 0));
        }
        $companyid = $company['companyid'];
        $result = M("shop_goods")->where("goodid = {$id}")->limit(1)->delete();
        if($result===false){
//            $this->error("删除出错");
            $this->ajaxReturn(array('msg' => '删除出错', 'status' => 0));
        }
//        $this->error("删除成功");
        $this->ajaxReturn(array('msg' => '删除成功', 'status' => 1));
    }


    /**
     * 商品支付展示页面
     * djt
     * 2017-2-25
     */
    public function shopPay(){
        $info = session("userinfo");
        $uid = $info['uid'];
//        if($uid!=29){
//            $this->error("商品购买暂不支持，敬请期待");
//        }
        $Shop = M("shop_goods");
        $num = (int)C('VCOIN_NUM');
        $id = _get("id");
        $orderid = _get("orderid");
        if(empty($id)&&empty($orderid)){
            $this->error("参数错误");
        }
        if(!empty($id)){//订单不存在时
            $good = $Shop->where("goodid = {$id} AND status = 1")->find();
            if(empty($good)){
                $this->error("该商品不存在或者已下架");
            }
            //商品价格
//            $pay = empty($good['nowprice']) ? (int)round($good['price']*$num) : (int)round($good['nowprice']*$num);
            $pay = empty($good['nowprice']) ? round($good['price'],2) : round($good['nowprice'],2);
            if(empty($pay)){
                $this->error("商品价格异常");
            }
            $good['money'] = $pay;
        }elseif(!empty($orderid)){
            //订单已经存在
            if(empty($uid)){
                $this->error("你还没有登录请先登录");
            }
            $order = M("order")->where("id = {$orderid} AND type = 1 AND orderstatus = 0")->find();
            //获取商品相关信息
            $good = $Shop->where("goodid = {$order['shopid']} AND status = 1")->find();
            if(empty($order)){
                $this->error("该订单不需要处理");
            }
            $good['vcoin'] = (int)round($order['amount']*$num);
            if(empty($good['vcoin'])){
                $this->error("价格异常");
            }
            $this->assign('order',$order);
        }
        //当前用户维币
        if(!empty($uid)){
            $coin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
        }
        $coin = empty($coin) ? 0 : $coin;
        //需支付维币
        $this->assign("good",$good);
        $this->assign("coin",$coin);
        $this->display();
    }

    /**
     * 商品支付
     * djt
     * 2017-2-25
     */
    public function vPay(){
        //获取一元兑换维币数
        $num = (int)C('VCOIN_NUM');
        $info = session('userinfo');
        if(empty($info)){
            $this->error("你还没有登录，请先登录");
//            $this->ajaxReturn(array('msg'=>'你还没有登录，请先登录','status'=>0));
//            $this->error('你还没有登录，请先登录');
        }
        $uid = $info['uid'];
//        if($uid!=29){
//            $this->error("商品购买暂不支持，敬请期待");
//        }
        if(IS_POST){
            $goodid = I("post.goodid");
//            $orderid = I("post.orderid");
            $name = I("post.name");
            $phone = I("post.phone");
            $prov = I("post.prov");
            $city = I("post.city");
            $area = I("post.area");
            $address = I("post.address");
            if(empty($goodid) && empty($orderid)){
                $this->error("参数错误");
//                $this->ajaxReturn(array('msg'=>'参数错误','status'=>0));
            }
            $coin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
            //订单已经存在直接支付
            if(!empty($orderid)) {
                $order = M("order")->where("id = {$orderid} AND type = 1 AND orderstatus = 0")->find();
                if (empty($order)) {
                    $this->error("该订单已经处理");
                }
                //获取商品相关信息
                $good = M("shop_goods as a")->join("LEFT join ww_company_shop as b ON a.shopid = b.shopid ")->field("a.name as goodname,b.name as shopname,b.uid as shopuid")->where("a.goodid = {$order['shopid']}")->find();
                $pay = (int)round($order['amount'] * $num);
                if (empty($pay)) {
                    $this->error("价格异常");
                }
                if ($pay > $coin) {
                    $this->error("你当前维币余额不足，请先充值");
                }
                $data = array();
                empty($name) ? '' : $data['name'] = $name;
                empty($address) ? '' : $data['address'] = $address;
                empty($phone) ? '' : $data['phone'] = $phone;
                empty($prov) ? '' : $data['prov'] = $prov;
                empty($city) ? '' : $data['city'] = $city;
                empty($area) ? '' : $data['area'] = $area;
                $model = new Model();
                $model->startTrans();
                //扣除维币
                $infoResult = M("person_info")->where("uid = {$uid}")->setDec("vcoin", $pay);
                //商户维币增加
                $infoResult1 = M("person_info")->where("uid = {$good['shopuid']}")->setInc("vcoin", $pay);
                if (empty($infoResult)||empty($infoResult1)) {
                    $model->rollback();
                    $this->error("支付失败");
                }
                //更新订单状态
                $data['paydate'] = time();
                $data['orderstatus'] = 1;
                $data['updated'] = time();
                $orderResult = M("order")->where("id = {$orderid}")->save($data);
                if (empty($orderResult)) {
                    $model->rollback();
                    $this->error("支付失败1");
                }
                //写入维币记录
                $Vlog = M("vcoin_log");
                $vlogAarry = array(
                    'uid' => $uid,
                    'action' => "购买了" . $good['goodname'],
                    'organizer' => $good['shopname'],
                    'intro' => "支付商品",
                    'type' => 3,
                    'value' => $pay,
                    'created' => time()
                );
                $vlog = $Vlog->add($vlogAarry);
                if (!$vlog) {
                    $model->rollback();
                    $this->ajaxReturn(array('msg' => '支付失败3', 'status' => 0));
                }
                $model->commit();
                $this->success("支付成功");
            }elseif(!empty($goodid)){
                $msg = '';
                $flag = true;
                if(empty($name)){
                    $flag = false;
                    $msg .="姓名，";
                }
                if(empty($prov)||empty($city)||empty($address)){
                    $flag = false;
                    $msg .="地址，";
                }
                $preg = "/^1\d{10}$/";
                if(empty($phone)){
                    $flag = false;
                    $msg .="手机号，";
                }elseif(empty(preg_match($preg,$phone))){
                    $flag = false;
                    $msg = empty($msg) ? $msg : trim($msg,',')."不能为空";
                    $msg .="手机号格式不正确";
                }
                if(!$flag){
                    $this->error($msg);
                }
                //获取商品相关信息
                $good = M("shop_goods as a")->join("LEFT join ww_company_shop as b ON a.shopid = b.shopid ")->field("a.*,b.name as shopname,b.uid as shopuid")->where("a.goodid = {$goodid} and a.status = 1")->find();
                if(empty($good)){
                    $this->error("该商品不存在或者已下架");
                }
                //商品维币价格
                $pay = empty($good['nowprice']) ? (int)round($good['price']*$num) : (int)round($good['nowprice']*$num);
                //订单价格
                $amount =empty($good['nowprice']) ? round($good['price'],2) : round($good['nowprice'],2);

                $data = array(
                    'uid' => $uid,
                    'orderdate' => time(),
                    'payment' => 'wxkpay',
                    'descp' => "购买商品",
                    'shopid' => $goodid,
                    'type' => 1,
                    'amount' => $amount,
                    'name' => $name,
                    'phone' => $phone,
                    'prov' => $prov,
                    'city' => $city,
                    'area' => $area,
                    'address'=> $address,
                    'created' => time(),
                    'updated' => time()
                );
                //生成订单号
                $data['ordernum'] = date("YmdHis").rand(1000,9999).$uid;
                //生成订单
                $orderAdd = M("order")->add($data);
                if(!$orderAdd){
                    $this->error("订单生成失败");
                }else{
                    $this->success("订单生成成功，前往支付",U('Home/Wxpay/payVcoin',array('type'=>4,'ordernum'=>$data['ordernum'])));
                }
            }
                //维币支付功能
                /*if($pay > $coin){
                    $this->error("维币不足，请充值后前往订单管理页面支付");
                }
                $model = new Model();
                $model->startTrans();
                //扣除维币
                $infoResult = M("person_info")->where("uid = {$uid}")->setDec("vcoin", $pay);
                //商户维币增加
                $infoResult1 = M("person_info")->where("uid = {$good['shopuid']}")->setInc("vcoin", $pay);
                if (empty($infoResult)||empty($infoResult1)) {
                    $model->rollback();
                    $this->error("支付失败");
                }
                //更新订单状态
                $data['paydate'] = time();
                $data['orderstatus'] = 1;
                $data['updated'] = time();
                $ordernum =  $data['ordernum'];
                $orderResult = M("order")->where("ordernum = '{$ordernum}' ")->save($data);
                if (empty($orderResult)) {
                    $model->rollback();
                    $this->error("支付失败1");
                }
                //写入维币记录
                $Vlog = M("vcoin_log");
                $vlogAarry = array(
                    'uid' => $uid,
                    'action' => "购买了" . $good['name'],
                    'organizer' => $good['shopname'],
                    'intro' => "支付商品",
                    'type' => 3,
                    'value' => $pay,
                    'created' => time()
                );
                $vlog = $Vlog->add($vlogAarry);
                if (!$vlog) {
                    $model->rollback();
                    $this->error("记录失败");
//                    $this->ajaxReturn(array('msg' => '支付失败3', 'status' => 0));
                }
                $model->commit();
                $this->success("支付成功",U('Home/Shop/paySuccess'));
            }*/
        }else{
            $this->error("非法请求");
        }
    }




    //结束

}
