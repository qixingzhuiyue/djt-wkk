<?php
/**
 * @desc 企业及案例管理
 */
namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Org\Util;
use Home\Controller\CommonController;
class CompanyController extends CommonController {
    /**
     * djt
     * @desc 企业主页
     * 2017-2-20
     */
    public function index() {
        //首页关键字
        $key = M("sys_keyword")->where("name='企业展示首页'")->find();
        $this->assign("key",$key);
        $condition = "status = 1  AND companystatus=1 AND temptype!=''";
        $isgood = _get("isgood");
        if($isgood == 1){
            $condition.=" AND isgood = 1 ";
            $this->assign('isgood',1);
        }
        //用户注册判断
        $info = session("userinfo");
        $uid = $info['uid'];
        $status = 0;
        $Company = M("company_info");
        if($info['role']==2){
            $company = $Company->where("uid = {$uid} AND ispay = 1 AND status = 1 AND companystatus = 1")->find();
            if(!empty($company['temptype'])){
                $status = 1;
                $this->assign('company',$company);
                $this->assign('status',$status);
            }
        }
        //头部企业图广告
        $companyBanner = M("banner")->where("type ='企业index' AND status = 1")->order("weight DESC")->find();
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
        //企业查询
        $count= $Company->where($condition)->count();
//        echo $count;
        $Page       = new \Think\Page($count, 24);
        $show       = $Page->show();
        $companys = $Company->where($condition)->limit($Page->firstRow.','.$Page->listRows)->order(array('weight'=>'DESC','updated'=>'DESC','uid'=>'DESC'))->select();
        foreach($companys AS &$val){
            if(file_exists('./Public'.$val['logo'])){
                $url = str_replace('.','_mid.',$val['logo'],$i);
                if(file_exists('./Public'.$url)){
                    $val['logo'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$val['logo']);
                    $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $val['logo'] = $url;
                }
            }
        }
        $this->assign("companys",$companys);
        $this->assign("page",$show);
//        echo $page;exit;
//        var_dump($company);
//        echo $company;exit;
        $this->display();
    }

    /**djt
     * @desc 自助建站首页
     * 2017-2-17
     */
    public function company() {
        //获取微信凭证
        $jssdk = new JsSdkController;
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
//        $temptype= _get("temptype");
//        var_dump($_SERVER['REQUEST_URI']);
        $id = (int)_get("id");
        $fromid = (int)_get("fromid");
        if(!empty($fromid)){
            session("fromid",$fromid);
        }
        if(empty($id)){
            $this->error("参数错误");
        }
        $company = M("company_info")->where("companyid = {$id}")->find();
        if(file_exists('./Public'.$company['banner'])){
            $url = str_replace('.','_big.',$company['banner'],$i);
            if(file_exists('./Public'.$url)){
                $company['banner'] = $url;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$company['banner']);
                $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                $company['banner'] = $url;
            }
        }
        if(file_exists('./Public'.$company['companypic'])){
            $url = str_replace('.','_mid.',$company['companypic'],$i);
            if(file_exists('./Public'.$url)){
                $company['companypic'] = $url;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$company['companypic']);
                $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                $company['companypic'] = $url;
            }
        }
//        var_dump($company);
        if(empty($company)){
            $this->error("参数错误1");
        }
        if(empty($company['temptype'])){
            $this->error("该企业还未选择模板");
        }
        $temptype = $company['temptype'];
        if(!empty(_get("temptype"))){
            $temptype= _get("temptype");
        }
        //企业uid
        $comuid = $company['uid'];
        //企业维币数
        $Coin = M("person_info")->where("uid={$comuid}")->find();
        $company['vcoin'] = $Coin['vcoin'];
        //用户注册判断
        $info = session("userinfo");
        if(!empty($info)){
            $this->assign("info",$info);
        }
        $uid = $info['uid'];
        $status = 0;
        $Shop = M("company_shop");
        if($info['role']==2){
            $shop = $Shop->where("uid = {$uid} AND type = 1 AND status = 1")->find();

            if(!empty($shop['temptype'])){
                $status = 1;
                $this->assign('shop',$shop);
                $this->assign('status',$status);
            }
        }
//        var_dump($company);;
        $company['self'] = $company['uid'] == $uid ?  1 : 0;
        $this->assign('company',$company);
        //产品
        $products = M("company_product")->where("companyid = {$id} AND status = 1")->field("productid,title,picture")->limit(8)->select();
        foreach($products AS &$pv){
            if(file_exists('./Public'.$pv['picture'])){
                $url = str_replace('.','_small.',$pv['picture'],$i);
                if(file_exists("./Public".$url)){
                    $pv['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$pv['picture']);
                    $image->thumb(200,200,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $pv['picture'] = $url;
                }
            }

        }
        //热门产品4个
        $pushProducts = M("company_product")->where("companyid = {$id} AND status = 1 AND type = 2")->field("productid,title,picture,num")->limit(4)->select();
        foreach($pushProducts AS &$ppv){
            if(file_exists('./Public'.$ppv['picture'])){
                $url = str_replace('.','_little.',$ppv['picture'],$i);
                if(file_exists("./Public".$url)){
                    $ppv['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$ppv['picture']);
                    $image->thumb(100,100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $ppv['picture'] = $url;
                }
            }

        }
        $this->assign("products",$products);
        $this->assign("pushProducts",$pushProducts);
        //新闻资讯
        $news = M("news")->where("companyid = {$id} AND status = 1 AND row = 2 ")->field("id,title,created,picture,intro")->limit(10)->select();
        //热门资讯4条
        $pushNews = M("news")->where("companyid = {$id} AND status = 1 AND row = 2 AND type = 2 ")->field("id,title,created,picture,intro,num")->limit(4)->select();
        $this->assign("news",$news);
        $this->assign("pushNews",$pushNews);
//        var_dump($pushNews);
        //企业信息
        $this->display($temptype);
    }
    /**
     * 关于我们
     * 2017-4-18
     * djt
     */
    public function aboutUs(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误1");
        }
        $Company = M("company_info");
        $intro = $Company->where("companyid = {$id}")->find();
        if(empty($intro)){
            $this->error("暂无相关内容");
        }
        $this->assign('intro',$intro);
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
        $company = M("company_info as a")->join("LEFT join ww_person_info as b ON a.uid = b.uid")->where("a.companyid={$id}")->field("a.*,b.vcoin as vcoin")->find();
        $comUid = $company['uid'];
//        if(empty($arcUid)){
//            $organizer = "上海维沃珂营销平台";
//        }else{
//            $organizer = $article['name'];
//        }
        $data = array(
            'type' =>5,
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
            $arcVcoins = M("person_info")->where("uid={$comUid}")->find();
            $arcvcoinStatus = (int)$arcVcoins['vcoin'];
            $arcvcoinStatus = $arcvcoinStatus>=$taskCoin ? true : false;
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
            //减少发帖人维币:$arcUid 不为空
            $delCoin = true;
            if(!empty($comUid)){
                $delCoin = M("person_info")->where("uid={$comUid}")->setDec("vcoin",$taskCoin);
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
                'organizer' => $company['name'],
                'intro' => "分享企业站增加维币",
                'type' => 2,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog = $Vlog->add($vlogAarry);
            //发帖人维币扣除
            $comUid = $comUid ? $comUid : 0;
            $vlogAarry1 = array(
                'uid' => $comUid,
                'action' => "支付用户分享企业站",
                'organizer' => $info['name'],
                'intro' => "支付用户分享企业站扣除维币",
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
     * 小站产品分享
     * djt
     * 2017-2-22
     */
    public function addProShare(){
//        $id = 31;
//        $news= M("news as a")->join("LEFT join ww_company_info as b ON a.uid = b.uid")->where("a.id={$id}")->field("a.uid as uid,a.row as row,b.name as name")->find();
//        var_dump($news);exit;
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
        //获取发帖人uid
        $product= M("company_product as a")->join("LEFT join ww_company_info as b ON a.uid = b.uid")->where("a.productid={$id}")->field("a.uid as uid,b.name as name")->find();
        $productUid = $product['uid'];
        if(empty($productUid)){
            $organizer = "上海维沃珂营销平台";
        }else{
            $organizer = $productUid['name'];
        }
        $data = array(
            'type' =>8,
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
        //发帖人维币数
        $arcvcoinStatus = true;
        if(!empty($productUid)){
            $newsVcoins = M("person_info")->where("uid={$productUid}")->find();
            $newsvcoinStatus = (int)$newsVcoins['vcoin'];
            $newsvcoinStatus = $newsvcoinStatus>=$taskCoin ? true : false;
        }
        //任务金额为0，用户维币不足，或者完成次数已经超过限定值
        if(empty($taskCoin)||empty($newsvcoinStatus)){
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
            if(!empty($taskCoin)){
                $addCoin = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$taskCoin);
            }
            //减少发帖人维币:$arcUid 不为空
            $delCoin = true;
            if(!empty($productUid)){
                if(!empty($taskCoin)){
                    $delCoin = M("person_info")->where("uid={$productUid}")->setDec("vcoin",$taskCoin);
                }
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
                'intro' => "分享文贴增加维币",
                'type' => 2,
                'value' => $taskCoin,
                'created' => time()
            );
            $vlog = $Vlog->add($vlogAarry);
            //发帖人维币扣除
            $productUid = $productUid ? $productUid : 0;
            $vlogAarry1 = array(
                'uid' => $productUid,
                'action' => "支付用户分享文贴",
                'organizer' => $info['name'],
                'intro' => "支付用户分享文贴扣除维币",
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
    /**djt
     * @desc 更多产品
     * 2017-2-17
     */
    public function productMore() {
//        $temptype= _get("temptype");
        $id = (int)_get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $company = M("company_info")->where("companyid = {$id}")->find();
        if(empty($company)){
            $this->error("参数错误1");
        }
        if(empty($company['temptype'])){
            $this->error("该企业还未选择模板");
        }
//        $temptype = $company['temptype'];
//        if(!empty(_get("temptype"))){
//            $temptype= _get("temptype");
//        }
        //用户注册判断
        $info = session("userinfo");
        $uid = $info['uid'];
        $status = 0;
        $Shop = M("company_shop");
        if($info['role']==2){
            $shop = $Shop->where("uid = {$uid} AND type = 1 AND status = 1")->find();

            if(!empty($shop['temptype'])){
                $status = 1;
                $this->assign('shop',$shop);
                $this->assign('status',$status);
            }
        }
//        var_dump($company);;
        $company['self'] = $company['uid'] == $uid ?  1 : 0;
        $this->assign('company',$company);
        $count =  M("company_product")->where("companyid = {$id} AND status = 1")->count();
//        echo $count;
        $Page       = new \Think\Page($count,20);
        $show       = $Page->show();
        $products = M("company_product")->where("companyid = {$id} AND status = 1")->limit($Page->firstRow.','.$Page->listRows)->order("num DESC")->select();
//        //热门产品4个
//        $pushProducts = M("company_product")->where("companyid = {$id} AND status = 1 AND type = 2")->field("productid,title,picture,num")->limit(4)->select();
        $this->assign("products",$products);
        $this->assign("page",$show);
//        $this->assign("pushProducts",$pushProducts);
//        //新闻资讯
//        $news = M("news")->where("companyid = {$id} AND status = 1 AND row = 2 ")->field("id,title,created,picture,intro")->limit(10)->select();
//        //热门资讯4条
//        $pushNews = M("news")->where("companyid = {$id} AND status = 1 AND row = 2 AND type = 2 ")->field("id,title,created,picture,intro,num")->limit(4)->select();
//        $this->assign("news",$news);
//        $this->assign("pushNews",$pushNews);
//        var_dump($pushNews);
        //企业信息
        $this->display();
    }

    /**djt
     * @desc 更多新闻
     * 2017-2-17
     */
    public function newsMore() {
//        $temptype= _get("temptype");
        $id = (int)_get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $company = M("company_info")->where("companyid = {$id}")->find();
        if(empty($company)){
            $this->error("参数错误1");
        }
        if(empty($company['temptype'])){
            $this->error("该企业还未选择模板");
        }
//        $temptype = $company['temptype'];
//        if(!empty(_get("temptype"))){
//            $temptype= _get("temptype");
//        }
        //用户注册判断
        $info = session("userinfo");
        $uid = $info['uid'];
        $status = 0;
        $Shop = M("company_shop");
        if($info['role']==2){
            $shop = $Shop->where("uid = {$uid} AND type = 1 AND status = 1")->find();

            if(!empty($shop['temptype'])){
                $status = 1;
                $this->assign('shop',$shop);
                $this->assign('status',$status);
            }
        }
//        var_dump($company);;
        $company['self'] = $company['uid'] == $uid ?  1 : 0;
        $this->assign('company',$company);
        $count =  M("news")->where("companyid = {$id} AND status = 1")->count();
//        echo $count;
        $Page       = new \Think\Page($count,20);
        $show       = $Page->show();
        $news = M("news")->where("companyid = {$id} AND status = 1")->limit($Page->firstRow.','.$Page->listRows)->order("num DESC")->select();
//        //热门产品4个
//        $pushProducts = M("company_product")->where("companyid = {$id} AND status = 1 AND type = 2")->field("productid,title,picture,num")->limit(4)->select();
        $this->assign("news",$news);
        $this->assign("page",$show);
//        $this->assign("pushProducts",$pushProducts);
//        //新闻资讯
//        $news = M("news")->where("companyid = {$id} AND status = 1 AND row = 2 ")->field("id,title,created,picture,intro")->limit(10)->select();
//        //热门资讯4条
//        $pushNews = M("news")->where("companyid = {$id} AND status = 1 AND row = 2 AND type = 2 ")->field("id,title,created,picture,intro,num")->limit(4)->select();
//        $this->assign("news",$news);
//        $this->assign("pushNews",$pushNews);
//        var_dump($pushNews);
        //企业信息
        $this->display();
    }
    /**
     * 收藏企业站
     * djt
     * 2017-2-21
     */
    public function addCollect(){
        $info = session("userinfo");
        if(empty($info)){
//            $this->error("请先登录");
            $this->ajaxReturn(array("msg"=>"请先登录","status"=>0));
        }
        $uid = $info['uid'];
        $id = _get('id');
        if(empty($id)){
//            $this->error("参数错误");
            $this->ajaxReturn(array("msg"=>"参数错误","status"=>0));
        }
        $data = array(
            'type' => 5,
            'uid' => $uid,
            'collectid' => $id,
            'created' => time()
        );
        $result = M("collect")->add($data);
        if(!$result){
            $this->error("收藏失败");
        }
        $this->success("收藏成功");
    }

    /**
     * 取消企业
     * djt
     * 2017-2-22
     */
    public function delCollect(){
        $info = session("userinfo");
        if(empty($info)){
            $this->error("请先登录");
        }
        $uid = $info['uid'];
        $id = _get('id');
        if(empty($id)){
            $this->error("参数错误");
        }
        //取消收藏
            $collect = M("collect")->where("id ={$id} AND uid = {$uid} AND type = 5")->delete();
            if(empty($collect)){
                $this->error("取消收藏失败1");
            }
        $this->success("取消收藏成功");
    }

    /**2017-2-15
     * @desc 企业入驻申请
     * djt
     */
    public function apply() {
        $info = session('userinfo');
        if(empty($info)){
            $this->error('你还没有登录，请前往登录',U('Home/User/login'));
        }
        $fromcid = _get('fromcid');
        if(!empty($fromcid)){
            session('fromcid',$fromcid);
        }
        $role = $info['role'];
        if($role!=2){
            $this->error("你当前的身份不是企业用户，不能申请");
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where("uid = {$uid}")->find();
        if(!empty($company)){
            if($company['ispay']==0){
                $this->error("你已经提交申请，请前往支付",U("Home/Company/applyPay"));
            }else if($company['temptype']==''){
                $this->error("你已入驻，请选择模板",U("Home/Company/addCompanyTemplate"));
            }else if($company['status']!=1){
                $this->error("你的企业还未通过审核");
            }else{
                $this->success("你已经入驻,即将进入你的主页",U("Home/Company/company",array('id'=>$company['companyid'])));
            }
        }
        if(IS_POST){
            $name = I("post.name");
//            $name = "七星国1";
            //公司全名不能重复
            $companyname = $Company->where("name = '{$name}'")->field("companyid")->find();
            if(!empty($companyname)){
                $this->error("该公司名称已经使用不能继续申请");
            }
            $type = I("post.type");
            $licensenum = I("post.licensenum");
            $business = I("post.business");
            $intro = I("post.intro");
            $link = I("post.link");
            $prov = I("post.prov");
            $city = I("post.city");
            $address = I("post.address");
            $contact = I("post.contact");
            $mobile = I("post.mobile");
//            $type = 1;
//            $licensenum = '1221321ssssss';
//            $business = "只为守护";
//            $intro = "一直永远不倒的队伍";
//            $link = "http://www.baidu.com";
//            $prov = "北京";
//            $city = "东城区";
//            $address = "185号，21栋21楼";
//            $contact = "宁雨";
//            $mobile = "13166292960";
            $flag = true;
            $msg = '';
            if(empty($name)){
                $flag = false;
                $msg .= '公司名称不能为空，';
            }
            if(empty($type)){
                $flag = false;
                $msg .= '公司类型不能为空，';
            }
//            if(empty($licensenum)){
//                $flag = false;
//                $msg .= '公司类型不能为空，';
//            }
            if(empty($contact)){
                $flag = false;
                $msg .= '联系人不能为空，';
            }
            if(empty($mobile)){
                $flag = false;
                $msg .= '联系人电话不能为空，';
            }
            if(empty($_FILES['picture']['name'][0])&&empty($_FILES['picture']['name'][0])){
                $licensepic = '';
                $logo ='';
            }else{
                $pictures = $this->_uploads('company');
                $licensepic = $pictures[0];
                $logo = $pictures[1];
            }
//            $licensepic = "/Uploads/news/20170209/589c46848503e.jpg";
//            $logo = "/Uploads/news/20170209/589c46848503e.jpg";
//            if(empty($licensepic)){
//                $flag = false;
//                $msg .= '营业执照图片不能为空，';
//            }
//            if(empty($logo)){
//                $flag = false;
//                $msg .= 'logo不能为空，';
//            }
            if(!$flag){
                $msg = trim($msg,'，');
                $this->error($msg);
            }
            $data = array(
                "uid" => $uid,
                "name" => $name,
                "type" => $type,
                "licensenum" => $licensenum,
                "licensepic" => $licensepic,
                "business" => $business,
                "logo" => $logo,
                "intro" => $intro,
                "link" => $link,
                "prov" => $prov,
                "city" => $city,
                "address" => $address,
                "contact" => $contact,
                "mobile" => $mobile,
                "created" => time(),
                "updated" => time()
            );
            $moneyRow= M("dict_disposition")->where("name = '企业入驻(元)' AND status = 1")->field("value")->find();
            $money = round($moneyRow['value'],2);
                $model = new Model();
            $model->startTrans();
            $shopid = $Company->add($data);
//            var_dump($shopid);exit;
                if($shopid){
                    //生成订单
                    //获取入驻花费金钱数，单位元
                    if(empty($money)){
                        $model->rollback();
                        $this->error("订单价格异常");
                    }
                    $order['amount'] = $money;
                    $order['shopid'] = $shopid;
                    $order['uid'] = $uid;
                    $order['ordernum'] = date("YmdHis").rand(1000,9999);
                    $order['orderdate'] = time();
                    $order['descp'] = "企业入驻维沃珂平台";
                    $order['type'] = 5;//与function中getCompanyType的id对应
                    $order['seller'] = "上海维沃珂营销平台";
                    $order['created'] = time();
                    $order['updated'] = time();
                    $Order = M("order");
                    $addResult = $Order->add($order);
                    if(!$addResult){
                        $model->rollback();
                        $this->error("生成订单失败");
                    }
                    $model->commit();
                    $this->success('添加成功',U("Home/Company/applyPay"));
                }else{
                    $model->rollback();
                    $this->error('添加失败');
                }
        }
        $areas = getAreas();
        $types = getDictTypes("companyType");
        $dis = getDisValue("企业入驻");
        //入驻支付现金数
        $value = (int)$dis['value'];
        $num = C("VCOIN_NUM");
        //入驻获得维币数
        $vvalue = (int)round($value*$num);
        $this->assign("vvalue",$vvalue);
        $this->assign("value",$value);
        $this->assign("areas",$areas);
        $this->assign("types",$types);
        $this->display();
    }

    /**2017-2-15
     * @desc 编辑企业资料
     * djt
     */
    public function editCompany() {
        $info = session('userinfo');
        if(empty($info)){
//            echo "<script>window.location.href='/index.php/Home/User/login'</script>";
            $this->error('你还没有登录，请先登录',U('Home/User/login'));
        }
        $role = $info['role'];
        if($role!=2){
            $this->error("你当前的身份不是企业用户，编辑企业资料");
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where("uid = {$uid} AND status = 1 AND companystatus = 1")->find();
        if(empty($company)){
            $this->error("你暂时无法编辑企业资料");
        }
        if(IS_POST){
//            $id = I('post.id');
//            if(empty($id)){
//                $this->error("参数错误");
//            }
            $name = I("post.name");
            $type = I("post.type");
            $licensenum = I("post.licensenum");
            $business = I("post.business");
            $intro = I("post.intro");
            $keywords = I("post.keywords");
            $link = I("post.link");
            $prov = I("post.prov");
            $city = I("post.city");
            $address = I("post.address");
            $contact = I("post.contact");
            $mobile = I("post.mobile");
//            $type = 2;
//            $business = "2只为守护";
//            $intro = "2一直永远不倒的队伍";
//            $link = "2http://www.baidu.com";
//            $prov = "2北京";
//            $city = "2东城区";
//            $address = "2185号，21栋21楼";
//            $contact = "2宁雨";
//            $mobile = "213166292960";
           $data = array();
            $flag = true;
            $msg = '';
            if(empty($company['name'])){
                if(empty($name)){
                    $flag = false;
                    $msg .="公司全称不能为空";
                }
            }
            if(!empty($name)){
                $data['name'] = $name;
            }
            if(!empty($type)){
               $data['type'] = $type;
            }
            if(!empty($licensenum)){
                $data['licensenum'] = $licensenum;
            }
            if(empty($company['business'])){
                if(empty($business)){
                    $flag = false;
                    $msg .="公司业务不能为空";
                }
            }
            if(!empty($business)){
                $data['business'] = $business;
            }
            if(!empty($intro)){
                $data['intro'] = $intro;
            }
            if(!empty($keywords)){
                $data['keywords'] = $keywords;
            }
            if(!empty($link)){
                $data['link'] = $link;
            }
            $prov = getArea($prov);
            if(empty($company['prov'])){
                if(empty($prov)){
                    $flag = false;
                    $msg .="公司省份不能为空";
                }
            }
            if(!empty($prov)){
                $data['prov'] = $prov;
            }
            if(empty($company['city'])){
                if(empty($city)){
                    $flag = false;
                    $msg .="公司市区不能为空";
                }
            }
            if(!empty($city)){
                $data['city'] = $city;
            }
            if(empty($company['address'])){
                if(empty($address)){
                    $flag = false;
                    $msg .="公司详细地址不能为空";
                }
            }
            if(!empty($address)){
                $data['address'] = $address;
            }
            if(empty($company['contact'])){
                if(empty($contact)){
                    $flag = false;
                    $msg .="公司联系人不能为空";
                }
            }
            if(!empty($contact)){
                $data['contact'] = $contact;
            }
            if(empty($company['mobile'])){
                if(empty($mobile)){
                    $flag = false;
                    $msg .="公司联系人电话不能为空";
                }
            }
            if(!empty($mobile)){
                $data['mobile'] = $mobile;
            }
            if(!empty($_FILES['banner']['name'])||!empty($_FILES['lic']['name'])||!empty($_FILES['logo']['name']||!empty($_FILES['companypic']['name']))){
                $pictures = $this->_uploads1('company');
                $licensepic = $pictures['lic'] ?  $pictures['lic'] : '';
                $logo = $pictures["logo"] ?  $pictures["logo"] : '';
                $banner = $pictures["banner"] ?  $pictures["banner"] : '';
                $companypic = $pictures["companypic"] ?  $pictures["companypic"] : '';
            }
//            $licensepic = "/Uploads/news/20170209/589c46848503e.jpg";
//            $logo = "/Uploads/news/20170209/589c46848503e.jpg";
            if(!empty($licensepic)){
                $data['licensepic'] = $licensepic;
            }
            if(!empty($logo)){
                $data['logo'] = $logo;
            }
            if(!empty($banner)){
                $data['banner'] = $banner;
            }
            if(!empty($companypic)){
                $data['companypic'] = $companypic;
            }
            $companyid = $company['companyid'];
           if(!empty($data)){
               $data['updated'] = time();
               $cResult = $Company->where("companyid = {$companyid}")->save($data);
               if($cResult){
                   $this->success("编辑成功",U("Home/Company/company",array("id"=>$companyid)));
               }else{
                   $this->error("编辑失败");
               }
           }else{
               $this->success("编辑成功",U("Home/Company/company",array("id"=>$companyid)));
           }
        }
//        $areas = getAreas();
//        $types = getDictTypes("companyType");
//        $this->assign("areas",$areas);
//        $this->assign("types",$types);
//        $this->assign("company",$company);
//        $this->display();
    }

    /**2017-2-15
     * @desc 编辑企业资料
     * djt
     */
    public function editComInfo() {
        $info = session('userinfo');
        if(empty($info)){
//            echo "<script>window.location.href='/index.php/Home/User/login'</script>";
            $this->error('你还没有登录，请先登录',U('Home/User/login'));
        }
        $role = $info['role'];
        if($role!=2){
            $this->error("你当前的身份不是企业用户，编辑企业资料");
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where("uid = {$uid} AND companystatus = 1")->find();
        if(empty($company)){
            $this->error("你暂时无法编辑企业资料");
        }
        if(IS_POST){
//            $id = I('post.id');
//            if(empty($id)){
//                $this->error("参数错误");
//            }
            $name = I("post.name");
            $type = I("post.type");
            $licensenum = I("post.licensenum");
            $business = I("post.business");
            $intro = I("post.intro");
            $keywords = I("post.keywords");
            $link = I("post.link");
            $prov = I("post.prov");
            $city = I("post.city");
            $address = I("post.address");
            $contact = I("post.contact");
            $mobile = I("post.mobile");
//            $type = 2;
//            $business = "2只为守护";
//            $intro = "2一直永远不倒的队伍";
//            $link = "2http://www.baidu.com";
//            $prov = "2北京";
//            $city = "2东城区";
//            $address = "2185号，21栋21楼";
//            $contact = "2宁雨";
//            $mobile = "213166292960";
            $data = array();
            if(!empty($name)){
                $data['name'] = $name;
            }
            if(!empty($type)){
                $data['type'] = $type;
            }
            if(!empty($licensenum)){
                $data['licensenum'] = $licensenum;
            }
            if(!empty($business)){
                $data['business'] = $business;
            }
            if(!empty($intro)){
                $data['intro'] = $intro;
            }
            if(!empty($keywords)){
                $data['keywords'] = $keywords;
            }
            if(!empty($link)){
                $data['link'] = $link;
            }
            $prov = getArea($prov);
            if(!empty($prov)){
                $data['prov'] = $prov;
            }
            if(!empty($city)){
                $data['city'] = $city;
            }
            if(!empty($address)){
                $data['address'] = $address;
            }
            if(!empty($contact)){
                $data['contact'] = $contact;
            }
            if(!empty($mobile)){
                $data['mobile'] = $mobile;
            }
            if(!empty($_FILES['banner']['name'])||!empty($_FILES['lic']['name'])||!empty($_FILES['logo']['name']||!empty($_FILES['companypic']['name']))){
                $pictures = $this->_uploads1('company');
                $licensepic = $pictures['lic'] ?  $pictures['lic'] : '';
                $logo = $pictures["logo"] ?  $pictures["logo"] : '';
                $banner = $pictures["banner"] ?  $pictures["banner"] : '';
                $companypic = $pictures["companypic"] ?  $pictures["companypic"] : '';
            }
//            $licensepic = "/Uploads/news/20170209/589c46848503e.jpg";
//            $logo = "/Uploads/news/20170209/589c46848503e.jpg";
            if(!empty($licensepic)){
                $data['licensepic'] = $licensepic;
            }
            if(!empty($logo)){
                $data['logo'] = $logo;
            }
            if(!empty($banner)){
                $data['banner'] = $banner;
            }
            if(!empty($companypic)){
                $data['companypic'] = $companypic;
            }
            $companyid = $company['companyid'];
            if(!empty($data)){
                $data['updated'] = time();
                $cResult = $Company->where("companyid = {$companyid}")->save($data);
                if($cResult){
                    $this->success("编辑成功",U("Home/Company/editComInfo"));
                }else{
                    $this->error("编辑失败");
                }
            }else{
                $this->success("编辑成功",U("Home/Company/editComInfo"));
            }
        }
        //获取省市
        $provs = getAreas();
        $this->assign('provs',$provs);
        $areas = getAreas();
        $types = getDictTypes("companyType");
        $this->assign("areas",$areas);
        $this->assign("types",$types);
        $this->assign("company",$company);
        $this->display();
    }

    /**
     * 企业入驻支付页面
     * djt
     * 2017-2-14
     */
    public function applyPay(){
        $info = session('userinfo');
        if(empty($info)){
            $this->error('你还没有登录，请先登录',U('Home/User/login'));
        }
//        $this->error("正在开发中,请耐心等待",U('Home/Index/index'));
        $Order = M("order");
        $uid = $info['uid'];
        $time = time() - 30*60;
        $order = $Order->where("uid ={$uid} AND type = 5 AND orderstatus = 0")->find();
        if(empty($order)){
            $this->error("订单不存在",U('Home/Company/index'));
        }
//        //刷新更新微信支付订单号并记录与平台订单的关系
//        $wxordernum = date("YmdHis").rand(1000,9000);
        $ordernum = $order['ordernum'];
        if(empty($ordernum)){
            $this->error("订单号不存在");
        }
//        $data = array(
//            "wxordernum"=>$wxordernum,
//            "ordernum"=>$ordernum,
//            "created"=>time(),
//            "updated"=>time()
//        );
//        $reult = M("wxordernum")->add($data);
//        if(empty($reult)){
//            $this->error("微信订单生成失败");
//        }
        $num = (int)C('VCOIN_NUM');
        //当前用户维币
        $coin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
        $order['vcoin']= $coin['vcoin'];
        $order['vnum']= (int)round($order['amount']*$num);
        $this->assign("order",$order);
//        $this->assign("wxordernum",$wxordernum);
        $this->display();
    }

    /**
     * 企业入驻维币支付
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
        $Order = M("order");
        $uid = $info['uid'];
//        var_dump($info);
        $order = $Order->where("uid ={$uid} AND type = 5 AND orderstatus = 0")->find();
//        echo $Order->getLastSql();exit;
        if(empty($order)){
            $this->ajaxReturn(array('msg'=>'当前用户不存在入驻企业订单','status'=>0));
//            $this->error("当前用户不存在入驻企业订单");
        }
        $orderid = $order['id'];
        $money = round($order['amount']*$num);
        $pay = intval($money);
        $Person = M("person_info");
        $personInfo = $Person->where("uid = {$uid}")->find();
        $vcions = intval($personInfo['vcoin']);
        $vcoin = $vcions - $pay;
        if($vcoin>=0){
            //入驻成功加维币
            $Task =M("vcoin_task");
            $task = $Task->where("action = '企业入驻' AND status = 1")->find();
            $taskCoin = $task['value'];
            $taskid = $task['id'];
            $log = M("task_log")->where("uid = {$uid} AND taskid = {$taskid}")->find();
            $model = new Model();
            $model->startTrans();
            //单次任务添加维币
            if(empty($log)){
                $vcoin =$vcoin + $taskCoin;
                $pUpdated = array(
                    'vcoin' => $vcoin,
                    'updated' => time()
                );
                $pResult = $Person->where("uid = {$uid}")->save($pUpdated);
                if(!$pResult){
                    $model->rollback();
                    $this->ajaxReturn(array('msg'=>'更新失败','status'=>0));
//                    $this->error("更新失败");
                }
            }
            //邀请入驻加维币
            $fromcid = session("fromcid");
            if(!empty($fromcid)){
                $ctask = $Task->where("action ='邀请商家入驻' AND status = 1")->find();
                if(!empty($ctask)){
                    $rid = $ctask['id'];
                    $rvcoin = $ctask['value'];
                    //邀请用户维币增加
                    $rinfo = $Person->where("uid = {$fromcid}")->setInc('vcoin',$rvcoin);
                    if(!$rinfo){
                        $model->rollback();
                        $this->ajaxReturn(array('msg'=>'注册失败1','status'=>0));
                    }
                    //添加任务记录
                    $Log = M("task_log");
                    $flogarray = array(
                        'uid' => $fromcid,
                        'taskid' => $rid,
                        'created' => time()
                    );
                    $flog = $Log->add($flogarray);
                    if(!$flog){
                        $model->rollback();
                        $this->ajaxReturn(array('msg'=>'支付失败2','status'=>0));
//                        $this->ajaxReturn(array('msg'=>'注册失败2','status'=>0));
                    }
                    //写入维币记录
                    $Vlog = M("vcoin_log");
                    $vlogAarry = array(
                        'uid' => $fromcid,
                        'action' => "邀请商家入驻",
                        'organizer' => "维沃珂",
                        'intro' => "邀请商家入驻成功",
                        'type' => 2,
                        'value' => $rvcoin,
                        'created' => time()
                    );
                    $vlog = $Vlog->add($vlogAarry);
                    if(!$vlog){
                        $model->rollback();
                        $this->ajaxReturn(array('msg'=>'支付失败3','status'=>0));
                    }
                }
            }
                //更新企业状态：审核通过
                $comUpDatated = array(
                    'ispay' => 1,
                    'status' => 1,
                    'updated' =>time()
                );
                $cResult = M("company_info")->where("uid = {$uid}")->save($comUpDatated);
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
                //维币使用记录
                $log = array(
                   'uid' => $uid,
                    'action' => "入驻企业支付",
                    'organizer' => "上海维沃珂营销平台",
                    'intro' => "企业入驻使用维币",
                    'type' => 3,
                    'value' => $pay,
                    'created' => time()
                );
                $log = M("vcoin_log")->add($log);
                $model->commit();
                $this->ajaxReturn(array('msg'=>'支付成功','status'=>1));
//                $this->success("支付成功",U("Home/Comapny/businessLocated"));//后续跳转到选择企业模板页面

        }else{
            $this->ajaxReturn(array('msg'=>'你的维币数不足，请充值','status'=>0));
//            $this->error("你的维币数不足，请充值");
        }
    }
    /**
     * @desc 选择企业模板
     * djt
     * 2017-2-14
     */
    public function addCompanyTemplate(){
        if(IS_POST){
            $info = session("userinfo");
            if(empty($info)){
                $this->ajaxReturn(array('msg'=>'你还未登陆，请先登录','status'=>0));
//                $this->error("你还未登陆，请先登录");
            }
            $temptype = I("post.temptype");
            if(empty($temptype)){
                $this->ajaxReturn(array('msg'=>'你还未选择模板','status'=>0));
//                $this->error("你还未选择模板");
            }
            $uid = $info['uid'];
            $Company = M("company_info");
            $company = $Company->where("uid = {$uid} AND status = 1 AND companystatus = 1")->find();
            if(empty($company)){
                $this->ajaxReturn(array('msg'=>'你得企业还未入驻成功,不能选择企业模板','status'=>0));
//                $this->error("你得企业还未入驻成功,不能选择企业模板");
            }
            if(!empty($company['temptype'])){
                $this->ajaxReturn(array('msg'=>'你已经选择了模板，不能更改','status'=>0));
//                $this->error("你已经选择了模板，不能更改");
            }
            $model = new Model();
            $model->startTrans();
            //更新模板
            $data = array(
                'temptype' =>$temptype,
                'updated' => time()
            );
            $cResult = $Company->where("uid = {$uid}")->save($data);
            if(!$cResult){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'添加模板失败','status'=>0));
//                $this->error("添加模板失败");
            }
            //增加维币
            $task = getTask("自助建站");
            $taskid = $task['id'];
            $taskVcoin = $task['value'];
            $Person = M("person_info");
            $coin = $Person->where("uid = {$uid}")->find();
            $vcoin = $coin['vcoin'];
            $vcoin = $vcoin + $taskVcoin;
            $vdata = array(
                'vcoin' => $vcoin,
                'updated' => time()
            );
            $vUpdated = $Person->where("uid = {$uid}")->save($vdata);
            if(!$vUpdated){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'更新失败1','status'=>0));
//                $this->error("更新失败1");
            }
            //添加任务记录
            $logArray = array(
                'uid' => $uid,
                'taskid' => $taskid,
                'created' => time()
            );
            $log = M("task_log")->add($logArray);
            if(!$log){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'更新失败2','status'=>0));
//                $this->error("更新失败2");
            }
            //添加维币记录
            $vArray = array(
               'uid' => $uid,
               'action' => "自助建站任务",
               'organizer' => "上海维沃珂营销平台",
               'intro' => "企业自助建站获取维币",
               'type' => 2,
                'value' => $taskVcoin,
                'created' => time()
            );
            $vlog = M("vcoin_log")->add($vArray);
            if(!$vlog){
                $model->rollback();
                $this->ajaxReturn(array('msg'=>'更新失败3','status'=>0));
//                $this->error("更新失败3");
            }
            $model->commit();
            $this->ajaxReturn(array('msg'=>'更新成功','companyid'=>$company['companyid'],'status'=>1));
//            $this->success("更新成功");
        }
        //获取模板
        $this->display();
//        $Picture = M("picture");
//        $pids = $Picture->where("name ='企业模板' AND pid = 0 AND status = 1")->find();
//        $pid = $pids['id'];
//        $pictures = $Picture->where("pid = {$pid} AND status =1")->field("id,name,picture")->select();
//        $this->assign("pictures",$pictures);
    }

    /**
     * djt
     * 添加产品编辑
     * 2017-2-20
     */
    public function addProduct() {
        $info = session("userinfo");
        if($info['role'] !=2){
            $this->error("当前身份状态不能操作");
//            $this->ajaxReturn(array('msg'=>'当前身份状态不能操作','status'=>0));
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
        if(empty($company)){
            $this->error("你还未成功完成入驻流程，不能添加产品");
//            $this->ajaxReturn(array('msg'=>'你还未成功完成入驻流程，不能添加产品','status'=>0));
        }
        $this->assign('company',$company);
        $companyid = $company['companyid'];
        if (IS_POST) {
            $title = I('post.title');
            $type = I('post.type');
            $content = I('post.content');
            $id = I("post.id");
            $Product = M("company_product");
            if(empty($id)){//添加
                $flag = true;
                $msg = '';
                if(empty($title)){
                    $flag = false;
                    $msg.="标题不能为空";
                }
                if(empty($type)){
                    $flag = false;
                    $msg.="推荐类型不能为空";
                }
                if(empty($content)){
                    $flag = false;
                    $msg.="内容不能为空";
                }
                if(empty($_FILES['picture']['name'])){
                    $flag = false;
                    $msg.="图片不能为空";
                }
                if(!$flag){
                    $this->error($msg);
//                    $this->ajaxReturn(array('msg'=>$msg,'status'=>0));
                }
                $picture = parent::_upload('product');
                if(empty($picture)){
                    $this->error("上传图片失败");
//                    $this->ajaxReturn(array('msg'=>'上传图片失败','status'=>0));
                }
                $time = time();
                $data = array(
                    'companyid' => $companyid,
                    'uid' => $uid,
                    'title' => $title,
                    'type' => $type,
                    'content' => $content,
                    'picture' => $picture,
                    'created' => $time,
                    'updated' => $time
                );
                $product =  $Product->add($data);
                if(empty($product)){
                    $this->error("添加失败");
//                    $this->ajaxReturn(array('msg'=>'添加失败','status'=>0));
                  }
                $this->success('添加成功',U('Home/Company/productManagement'));
//                $this->ajaxReturn(array("msg"=>'添加成功','status'=>1));
            }else{
                $data = array();
                if(!empty($title)){
                    $data['title'] = $title;
                }
                if(!empty($type)){
                    $data['type'] = $type;
                }
                if(!empty($content)){
                    $data['content'] = $content;
                }
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload("picture");
                }
                if(!empty($picture)){
                    $data['picture'] = $picture;
                }
                if(!empty($data)){
                    $data['updated'] = time();
                    $updated = $Product->where("productid = {$id}")->save($data);
                    if(empty($updated)){
                        $this->error("更新失败");
//                        $this->ajaxReturn(array("msg"=>'更新失败','status'=>0));
                    }
                    $this->success('更新成功',U('Home/Company/productManagement'));
//                    $this->ajaxReturn(array("msg"=>'更新成功','status'=>1));
                }
            }
        } else {
            $id = _get("id");
            if(!empty($id)){
                $product = M("company_product")->where("productid = {$id}")->find();
                $product['content'] = htmlspecialchars_decode($product['content']);
                $this->assign('product',$product);
            }
            $this->display();
        }
    }
    /**
     * 产品详情
     * 2017-3-9
     * djt
     */
    public function productDetail(){
        $info = session("userinfo");
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误1");
        }
        $Product = M("company_product");
        //浏览数加1
        $result = $Product->where("productid = {$id}")->setInc("num",1);
        $product = $Product->where("productid = {$id}")->find();
        if(empty($product)){
            $this->error("暂无相关内容");
        }
        $companyid = $product['companyid'];
//        if($product['row']==1){
//            $news['content'] = htmlspecialchars_decode($news['content']);
//        }else{
        $product['content'] = htmlspecialchars_decode( $product['content']);
//        }
        //上一条
        $prev = $Product->where("productid < {$id} AND companyid={$companyid} AND status = 1")->field("productid,title")->find();
        //下一条
        $next = $Product->where("productid > {$id} AND companyid={$companyid} AND status = 1")->field("productid,title")->find();
//        //热门文贴
//        $articles = M("article")->where("status = 1 AND ispush = 1 AND type = {$row}")->field("articleid,title")->limit(6)->select();
//        $this->assign('articles', $articles);
        $this->assign('prev', $prev);
        $this->assign('next', $next);
        $this->assign('product',$product);
        $this->display();
    }
    /**
     * djt
     * 删除产品
     * 2017-2-20
     */
    public function delProduct()
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
        $companyid = $company['companyid'];
        $result = M("company_product")->where("companyid = {$companyid} AND productid = {$id}")->limit(1)->delete();
        if($result===false){
//            $this->error("删除出错");
            $this->ajaxReturn(array('msg' => '删除出错', 'status' => 0));
        }
//        $this->error("删除成功");
        $this->ajaxReturn(array('msg' => '删除成功', 'status' => 1));
    }

    /**
     * 管理企业
     * djt
     * 2017-2-20
     */
     public function businessManagement(){
         $info = session("userinfo");
         if(empty($info)){
             $this->error('你还没有登录，请前往登录',U('Home/User/login'));
         }
         if($info['role'] !=2){
             $this->error("当前不是企业身份不能操作");
         }
         $uid = $info['uid'];
         $Company = M("company_info");
         $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
         if(empty($company)){
             $this->error("你还未成功完成入驻流程，不能编辑企业信息");
//             $this->ajaxReturn(array('msg'=>'你还未成功完成入驻流程，不能编辑企业信息','status'=>0));
         }
         //企业资料
         $this->assign('company',$company);
         //获取省市
         $provs = getAreas();
         $this->assign('provs',$provs);
         //企业类型
         $types = getDictTypes("companyType");
//         var_dump($types);
         $this->assign('types',$types);
         //企业新闻资讯
         $News = M("news");
         $companyid = $company['companyid'];
         $news = $News->where("companyid = {$companyid} AND row = 2 AND status = 1")->order("created DESC")->select();
         $this->assign("news",$news);
         //产品
         $Product = M("company_product");
         $products = $Product->where("companyid = {$companyid} AND status = 1")->order("created DESC")->select();
         $this->assign("products",$products);
         $this->display();
     }
    /**
     * 管理企业新闻
     * djt
     * 2017-2-20
     */
    public function newsManagement(){
        $info = session("userinfo");
        if($info['role'] !=2){
            $this->error("当前身份状态不能操作");
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
        if(empty($company)){
            $this->error("你还未成功完成入驻流程，不能编辑企业信息");
//             $this->ajaxReturn(array('msg'=>'你还未成功完成入驻流程，不能编辑企业信息','status'=>0));
        }
        //企业资料
        $this->assign('company',$company);
        //企业新闻资讯
        $News = M("news");
        $companyid = $company['companyid'];
        $count = $News->where("companyid = {$companyid} AND row = 2 AND status = 1")->count();
        $Page       = new \Think\Page($count, 11);
        $show       = $Page->show();
        $news = $News->where("companyid = {$companyid} AND row = 2 AND status = 1")->limit($Page->firstRow.','.$Page->listRows)->order("created DESC")->select();
        $this->assign("news",$news);
        $this->assign("page",$show);
//        //产品
//        $Product = M("company_product");
//        $products = $Product->where("companyid = {$companyid} AND status = 1")->order("created DESC")->select();
//        $this->assign("products",$products);
        $this->display();
    }
    /**
     * 管理企业产品
     * djt
     * 2017-2-20
     */
    public function productManagement(){
        $info = session("userinfo");
        if($info['role'] !=2){
            $this->error("当前身份状态不能操作");
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
        if(empty($company)){
            $this->error("你还未成功完成入驻流程，不能编辑企业信息");
//             $this->ajaxReturn(array('msg'=>'你还未成功完成入驻流程，不能编辑企业信息','status'=>0));
        }
        //企业资料
        $this->assign('company',$company);
        //企业新闻资讯
        $Product = M("company_product");;
        $companyid = $company['companyid'];
        $count = $Product->where("companyid = {$companyid} AND status = 1")->count();
        $Page       = new \Think\Page($count, 20);
        $show       = $Page->show();
        $products = $Product->where("companyid = {$companyid} AND status = 1")->limit($Page->firstRow.','.$Page->listRows)->order("created DESC")->select();
        $this->assign("products",$products);
        $this->assign("page",$show);
//        //产品
//        $Product = M("company_product");
//        $products = $Product->where("companyid = {$companyid} AND status = 1")->order("created DESC")->select();
//        $this->assign("products",$products);
        $this->display();
    }
    /**
     * 管理企业产品
     * djt
     * 2017-2-20
     */
    public function articleManagement(){
        $info = session("userinfo");
        if($info['role'] !=2){
            $this->error("当前身份状态不能操作");
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
        if(empty($company)){
            $this->error("你还未成功完成入驻流程，不能编辑企业信息");
//             $this->ajaxReturn(array('msg'=>'你还未成功完成入驻流程，不能编辑企业信息','status'=>0));
        }
        //企业资料
        $this->assign('company',$company);
        //企业新闻资讯
        $Article = M("article");;
        $companyid = $company['companyid'];
        $count = $Article->where("companyid = {$companyid} AND status = 1")->count();
        $Page       = new \Think\Page($count, 20);
        $show       = $Page->show();
        $articles = $Article->where("companyid = {$companyid} AND status = 1")->limit($Page->firstRow.','.$Page->listRows)->order("created DESC")->select();
//        echo $Article->getLastSql();
        $this->assign("articles",$articles);
//        var_dump($articles);
        $this->assign("page",$show);
//        //产品
//        $Product = M("company_product");
//        $products = $Product->where("companyid = {$companyid} AND status = 1")->order("created DESC")->select();
//        $this->assign("products",$products);
        $this->display();
    }


}
