<?php
namespace Home\Controller;
use Think\Model;
use Org\Util;
class IndexController extends CommonController {
    public function index(){
//        phpinfo();exit;
       $jssdk = new JsSdkController;
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        //分享首页fromid
        $fromid = (int)_get('fromid');
        if(!empty($fromid)){
            session('fromid',$fromid);
        }
        //首页关键字
        $this->assign("info",session('userinfo'));
//        //缓存
//        S(
//            array(
//                'type' => 'memcache',
//                'host' => '127.0.0.1',
//                'port' => '3306',
//                'prefix' => 'index_',
//                'expire' => 60
//            )
//        );
//        if(empty(S('key'))){
            $key = M("sys_keyword")->where("name='首页'")->find();
//            S('key',$key);
//        }else{
//            $key = S('key');
//        }
//        dump($key);
        $this->assign("key",$key);
        $this->assign("keys",$key);
        //首页头部轮播图广告
        $Banner = M("banner");
        $topBanner = $Banner->where("type ='首页banner' AND status = 1")->limit(3)->select();
        foreach($topBanner AS &$val3){
            if(file_exists('./Public'.$val3['picture'])){
                $url = str_replace('.','_big.',$val3['picture'],$i);
                if(file_exists('./Public'.$url)){
                    $val3['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$val3['picture']);
                    $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $val3['picture'] = $url;
                }
            }
        }
        $this->assign("topsBanner",$topBanner);
        //中部企业图广告
        $companyBanner = $Banner->where("type ='首页企业展示' AND status = 1")->order("weight DESC")->find();
        if(file_exists('./Public'.$companyBanner['picture'])){
            $url = str_replace('.','_big.',$companyBanner['picture'],$i);
            if(file_exists('./Public'.$url)){
                $image = new \Think\Image();
                $image->open('./Public'.$companyBanner['picture']);
                $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                $companyBanner['picture'] = $url;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$companyBanner['picture']);
                $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                $companyBanner['picture'] = $url;
            }
        }
        $this->assign("companyBanner",$companyBanner);
        //底部人才轮播图广告
        $talentBanner = $Banner->where("type ='首页人才对接' AND status = 1")->order("weight DESC")->find();
        if(file_exists('./Public'.$talentBanner['picture'])){
            $url = str_replace('.','_big.',$talentBanner['picture'],$i);
            if(file_exists('./Public'.$url)){
                $image = new \Think\Image();
                $image->open('./Public'.$talentBanner['picture']);
                $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                $talentBanner['picture'] = $url;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$talentBanner['picture']);
                $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_FIXED)->save('./Public'.$url);
                $talentBanner['picture'] = $url;
            }
        }
        $this->assign("talentBanner",$talentBanner);
        //底部合作企业广告
        $contactCompany= $Banner->where("type ='合作企业' AND status = 1")->limit(10)->select();
        foreach($contactCompany AS &$coval){
            if(file_exists('./Public'.$coval['picture'])){
                $url = str_replace('.','_small.',$coval['picture'],$i);
                if(file_exists('./Public'.$url)){
                    $coval['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$coval['picture']);
                    $image->thumb(200, 200,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $coval['picture'] = $url;
                }
            }
        }
        $this->assign("contactCompany",$contactCompany);
        //底部友情链接广告
        $link = $Banner->where("type ='友情链接' AND status = 1")->limit(10)->order(array('weight'=>"DESC",'created'=>'DESC'))->select();
        $this->assign("link",$link);
        //
        //服务企业数
        $Company = M("company_info");
//        $companyNum = $Company->where("status=1 AND companystatus=1")->count();
        $companyNum = M("users")->where("status=1 AND role=2")->count();
        //前期数据显示数字增大31
        $companyNum +=31;
        $this->assign("companyNum",$companyNum);
        //优秀企业展示 优秀企业标准字段是什么？
        $goodCompany = $Company->where("status = 1 AND isgood = 1 AND companystatus=1 AND temptype!='' ")->limit(12)->order(array("weight"=>"DESC","created"=>"DESC"))->select();
        foreach($goodCompany AS &$val2){
            if(file_exists('./Public'.$val2['logo'])){
                $url = str_replace('.','_mid.',$val2['logo'],$i);
                if(file_exists('./Public'.$url)){
                    $val2['logo'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$val2['logo']);
                    $image->thumb(184, 126,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $val2['logo'] = $url;
                }
            }
        }
        $this->assign("goodCompany",$goodCompany);
        //热门活动,热门活动标准
        $time = time();
        $Activity = M("activity");
        $num = C("VCOIN_NUM");
        $hotActivitys = $Activity->where("status = 1 AND ispush = 1 AND endtime>{$time} ")->order(array('updated'=>'DESC'))->limit(3)->select();
//        var_dump($hotActivitys);exit;
        foreach($hotActivitys AS &$v){
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
            $pic = M("person_info")->where("uid = {$v['uid']}")->field("avatar")->find();
            $pic1 = M('company_info')->where("uid = {$v['uid']}")->field('logo')->find();
            $v['avatar'] = !empty($pic1['logo']) ? $pic1['logo']:$pic['avatar'];
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
            if($v['isfree']==0){
                $v['vcoin'] = '免费';
                $v['price'] ='免费';
            }else{
                $price = (int)round($v['price']*$num);
                $v['price'] = $v['price'].'元';
                $v['vcoin'] = $price.' V币';
            }
        }
//        var_dump($hotActivitys);
        $this->assign("hotActivitys",$hotActivitys);
        //往期活动
        $overActivitys = $Activity->where("status = 1 AND endtime<{$time}")->order(array('ispush'=>'DESC','updated'=>'DESC'))->limit(3)->select();
        foreach($overActivitys AS &$val){
            if(file_exists('./Public'.$val['picture'])){
                $url = str_replace('.','_mid.',$val['picture'],$i);
                if(file_exists('./Public'.$url)){
                    $val['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$val['picture']);
                    $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $val['picture'] = $url;
                }
            }
            $pic = M("person_info")->where("uid = {$val['uid']}")->field("avatar")->find();
            $pic1 = M('company_info')->where("uid = {$val['uid']}")->field('logo')->find();
            $val['avatar'] = !empty($pic1['logo']) ? $pic1['logo']:$pic['avatar'];
            $val['avatar'] = $pic['avatar'];
            if(file_exists('./Public'.$val['avatar'])){
                $url1 = str_replace('.','_little.',$val['avatar'],$i);
                if(file_exists('./Public'.$url1)){
                    $val['avatar'] = $url1;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$val['avatar']);
                    $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url1);
                    $val['avatar'] = $url1;
                }
            }
            if($val['isfree']==0){
                $val['vcoin'] = '免费';
                $val['price'] = '免费';
            }else{
                $price = (int)round($val['price']*$num);
                $val['price'].='元';
                $val['vcoin'] = $price."维币";
            }
        }
        $this->assign('overActivitys',$overActivitys);
        //精彩课程推荐
        $Course = M("course");
        $course = $Course->where("ispush = 1 and status = 1")->limit(4)->select();
        foreach($course AS &$cval){
            //无精选封面图选择一般图片代替
            $cval['picture1'] = empty($cval['picture1']) ? $cval['picture'] : $cval['picture1'];
            //选取缩略图显示
            if(file_exists('./Public'.$cval['picture1'])){
                $url = str_replace('.','_mid.',$cval['picture1'],$i);
                if(file_exists('./Public'.$url)){
                    $cval['picture']= $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$cval['picture1']);
                    $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $cval['picture'] = $url;
                }
            }
        }
        $this->assign("course",$course);
        //人才对接，推荐职位
        $Job = M("sys_job");
        $jobs = $Job->where("status = 1")->order("created DESC")->limit(8)->select();
        foreach($jobs AS $key=>&$jval){
            if(file_exists('./Public'.$jval['picture'])){
                if($key==0){
                    $url = str_replace('.','_mid.',$jval['picture'],$i);
                }else{
                    $url = str_replace('.','_small.',$jval['picture'],$i);
                }
                if(file_exists('./Public'.$url)){
                    $jval['picture']= $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$jval['picture']);
                    if($key==0){
                        $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    }else{
                        $image->thumb(200, 200,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    }
                    $jval['picture'] = $url;
                }
            }
        }
        $this->assign('jobs',$jobs);
        //人才对接，推荐人才
        $talents = M("sys_talent")->where("status = 1")->order("created DESC")->limit(8)->select();
        foreach($talents AS $k=>&$tval){
            if(file_exists('./Public'.$tval['picture'])){
                if($k==0){
                    $url = str_replace('.','_mid.',$tval['picture'],$i);
                }else{
                    $url = str_replace('.','_small.',$tval['picture'],$i);
                }
                if(file_exists('./Public'.$url)){
                    $tval['picture']= $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$tval['picture']);
                    if($k==0){
                        $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    }else{
                        $image->thumb(200, 200,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    }
                    $tval['picture'] = $url;
                }
            }
        }
        $this->assign('talents',$talents);
        //平台简介
        $intro = M("sys_intro")->where("status = 1 AND type=1")->order("updated DESC")->find();
        if(file_exists('./Public'.$intro['picture'])){
            $url = str_replace('.','_mid.',$intro['picture'],$i);
            if(file_exists('./Public'.$url)){
                $intro['picture'] = $url;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$intro['picture']);
                $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                $intro['picture'] = $url;
            }
        }
        $this->assign("intro",$intro);
        //平台动态
        $dynmic = M("sys_dynmic")->where("status = 1")->order("num DESC")->limit(4)->select();
        foreach($dynmic AS &$dval){
            if(file_exists('./Public'.$dval['picture'])){
                $url = str_replace('.','_little.',$dval['picture'],$i);
                if(file_exists('./Public'.$url)){
                    $dval['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$dval['picture']);
                    $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $dval['picture'] = $url;
                }
            }
        }
        $this->assign("dynmic",$dynmic);
        //按企业类型展示
        $Dict = M("dict_disposition");
        $types = $Dict->where("pid = 1 AND status =1")->field("name,id")->order("weight DESC")->select();
        foreach($types AS $value){
            $name = $value['name'];
            $company = $Company->where("type = '{$name}' AND status = 1 AND companystatus=1 AND temptype!='' ")->limit(12)->select();
            foreach($company AS &$value1){
                if(file_exists('./Public'.$value1['logo'])){
                    $url = str_replace('.','_mid.',$value1['logo'],$i);
                    if(file_exists('./Public'.$url)){
                        $value1['logo'] = $url;
                    }else{
                        $image = new \Think\Image();
                        $image->open('./Public'.$value1['logo']);
                        $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                        $value1['logo'] = $url;
                    }
                }
            }
            $tcompanys[] =$company;
        }
        $this->assign("types",$types);
        $this->assign("tcompanys",$tcompanys);
//        var_dump($tcompanys[0]);
        //店铺推荐
        $Shop = M("company_shop");
        $shops = $Shop->where("status = 1 AND type = 1 AND temptype!=''")->order("created DESC")->select();
        foreach($shops AS &$sval){
            if(file_exists('./Public'.$sval['logo'])){
                $url = str_replace('.','_small.',$sval['logo'],$i);
                if(file_exists('./Public'.$url)){
                    $sval['logo'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$sval['logo']);
                    $image->thumb(200, 200,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $sval['logo'] = $url;
                }
            }
        }
        $this->assign('shops',$shops);
        //平台资讯
        $news = M("news")->where("status = 1 AND type=2 AND row =1")->limit(6)->select();
        if(file_exists('./Public'.$news[0]['picture'])){
            $url = str_replace('.','_little.',$news[0]['picture'],$i);
            if(file_exists('./Public'.$url)){
                $news[0]['picture'] = $url;
            }else{
                $image = new \Think\Image();
                $image->open('./Public'.$news[0]['picture']);
                $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                $news[0]['picture'] = $url;
            }
        }
        $this->assign("news",$news);
        //企业资讯
        $companyNews = M("news")->where("status = 1  AND type=2 AND row = 2")->order("num DESC")->limit(8)->select();
        $this->assign("companyNews",$companyNews);
//        //
//        echo M("news")->getLastSql();
//        var_dump($companyNews);exit;
        $this->display();
    }
    /**
     * djt
     * 联系客服留言表
     * 2017-2-20
     */
    public function note(){
//        $info = session("userinfo");
//        if($info){
//            $this->ajaxReturn(array("msg"=>"你还没有登录","status"=>0));
//        }
//        $uid = $info['uid'];
        $name = I("post.name");
        $phone = I("post.phone");
        $address = I("post.address");
        $content = I("post.content");
//        $name = "党惊涛我";
//        $phone = "13166292960";
//        $address = "王企鹅无热无若";
//        $content = "饭都是似懂非懂是多少的范德萨";
        $msg = '';
        $flag = true;
        if(empty($name)){
            $flag = false;
            $msg .="姓名不能为空";
        }
        $preg = "/^1\d{10}$/";
        $matchResult = preg_match($preg,$phone);
        if(empty($phone)){
            $flag = false;
            $msg .="手机号不能为空";
        }elseif(empty($matchResult)){
            $flag = false;
            $msg .="手机号格式不正确";
        }
        if(empty($address)){
            $flag = false;
            $msg .="地址不能为空";
        }
        if(empty($content)){
            $flag = false;
            $msg .="留言内容不能为空";
        }elseif(strlen($content)>1000){
            $flag = false;
            $msg .="留言内容不能超过1000字";
        }
        if(!$flag){
//            echo $msg;exit;
            $this->ajaxReturn(array('msg'=>$msg,'status'=>0));
        }
        $data = array(
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'content' => $content,
            'created' => time(),
            'updated' => time()
        );
        //添加
        $result = M("note")->add($data);
        if(!$result){
            $this->ajaxReturn(array('msg'=>'添加失败','status'=>0));
        }
//        echo "success";exit;
        $this->ajaxReturn(array('msg'=>"留言成功",'status'=>1));
    }
    /**
     * 关于我们
     * djt
     * 2017-3-8
     */
    public function aboutUs(){
        //banner
        $jobBanner = M("banner")->where("type ='平台简介banner' AND status = 1")->order("weight DESC")->find();
        $this->assign("banner",$jobBanner);
        $about = M("sys_intro")->order("created DESC")->find();
        $about['content'] = htmlspecialchars_decode( $about['content']);
        $this->assign("about",$about);
        $this->display();
    }
    /**
     * 站内地图
     * djt
     * 2017-3-8
     */
    public function nav(){
        $about = M("sys_intro")->where("status=1 AND type=2")->order("created DESC")->find();
        $about['content'] = htmlspecialchars_decode( $about['content']);
        $this->assign("about",$about);
        $this->display();
    }
    /**
     * 站内搜索
     * djt
     * 2017-3-8
     */
    public function search(){
        if(IS_POST){
            $name = I("post.name");
            if(empty($name)){
                $this->display();
            }else{
                //企业搜索
                $count[] = M("company_info")->where("status = 1 AND companystatus = 1 AND name like '%{$name}%'")->count();
                $count[] = M("activity")->where("status = 1 AND title like '%{$name}%'")->count();
//                $count[] = M("company_shop")->where("status = 1 AND type=1 AND temptype!='' AND name like '%{$name}%'")->count();
                $count[] = M("course")->where("status = 1 AND title like '%{$name}%'")->count();
                $count[] = M("article")->where("status = 1 AND title like '%{$name}%'")->count();
                $count[] = M("news")->where("status = 1 AND title like '%{$name}%'")->count();
//                $count[] = M("sys_job")->where("status = 1 AND (name like '%{$name}%' OR job like '%{$name}%')")->count();
//                $count[] = M("company_info")->where("status = 1 AND companystatus = 1 AND name=%{$name}%")->count();
                $countNum = max($count);
                $Page       = new \Think\Page($countNum, 15);
                $show       = $Page->show();
                //字符串替换
                $replace = "<i class='red'>".$name."</i>";
                //企业
                $companys = M("company_info")->where("status = 1 AND companystatus = 1 AND name like '%{$name}%'")->limit($Page->firstRow.','.$Page->listRows)->order("updated DESC")->select();
                foreach($companys AS &$v){
                    $v['name'] = str_replace($name,$replace,$v['name'],$i);
                    $v['intro'] = str_replace($name,$replace,$v['intro'],$i);
                }
                $this->assign('companys',$companys);
                //活动
                $activitys = M("activity")->where("status = 1 AND title like '%{$name}%'")->limit($Page->firstRow.','.$Page->listRows)->order("updated DESC")->select();
                foreach($activitys AS &$av){
                    $av['title'] = str_replace($name,$replace,$av['title'],$i);
                }
                $this->assign('activitys',$activitys);
                //店铺
                $shops = M("company_shop")->where("status = 1 AND type=1 AND temptype!='' AND name like '%{$name}%'")->limit($Page->firstRow.','.$Page->listRows)->order("updated DESC")->select();
                foreach($shops AS &$sv){
                    $sv['name'] = str_replace($name,$replace,$sv['name'],$i);
                    $sv['intro'] = str_replace($name,$replace,$sv['intro'],$i);
                }
                $this->assign('shops',$shops);
                //课程
                $courses = M("course")->where("status = 1 AND title like '%{$name}%'")->limit($Page->firstRow.','.$Page->listRows)->order("updated DESC")->select();
                foreach($courses AS &$cov){
                    $cov['title'] = str_replace($name,$replace,$cov['title'],$i);
                    $cov['intro'] = str_replace($name,$replace,$cov['intro'],$i);
                }
                $this->assign('courses',$courses);
                //帖子
                $articles = M("article")->where("status = 1 AND title like '%{$name}%'")->limit($Page->firstRow.','.$Page->listRows)->order("updated DESC")->select();
                foreach($articles AS &$arv){
                    $arv['title'] = str_replace($name,$replace,$arv['title'],$i);
                    $arv['intro'] = str_replace($name,$replace,$arv['intro'],$i);
                }
                $this->assign('articles',$articles);
                //新闻
                $news = M("news")->where("status = 1 AND title like '%{$name}%'")->limit($Page->firstRow.','.$Page->listRows)->order("updated DESC")->select();
                foreach($news AS &$nv){
                    $nv['title'] = str_replace($name,$replace,$nv['title'],$i);
                    $nv['intro'] = str_replace($name,$replace,$nv['intro'],$i);
                }
                $this->assign('news',$news);
//                //人才对接，推荐职位
//                $Job = M("sys_job");
//                $jobs = $Job->where("status = 1")->order("created DESC")->limit(8)->select();
//                $this->assign('jobs',$jobs);
//                //人才对接，推荐人才
//                $talents = M("sys_talent")->where("status = 1")->order("created DESC")->limit(8)->select();
//                $this->assign('talents',$talents);
                //右侧热门推荐
                $push = M("article")->where("status = 1")->field("articleid,title")->limit(15)->order(array("browsenum"=>"DESC","created"=>"DESC"))->select();
                $this->assign("push",$push);
                $this->assign("page",$show);
            }
        }
        //右侧热门推荐
        $push = M("article")->where("status = 1")->field("articleid,title")->limit(15)->order(array("browsenum"=>"DESC","created"=>"DESC"))->select();
        $this->assign("push",$push);
       $this->display();
    }
    /*
     * 第三方请求测试
     * djt
     * 2017-6-23
     */
    public function test(){
        $array = array(
            'a' => 'ceshi',
            'b' => '测试'
        );
        $str = serialize($array);
        var_dump($str);
        var_dump(unserialize($str));
        EXIT;
        $a = "/home/web/lib/img/a.php";
        $b = '/home/web/api/img/b.php';
        $arr = explode("/",$a);
        $arr1 = explode("/",$b);
        $sameDir = array_intersect_assoc($arr,$arr1);
        $deth = 0;
        //相同部分的键出现跳跃说明之后的路径就不同了找出这个跳跃点
        for($i=0,$len = count($sameDir);$i<$len;$i++){
            $deth = $i;
            if(!isset($sameDir[$i])){
                break;
            }
        }
        //for 循环最多到$i = $len 不再循环即 $deth最大是 $len-1;当$i == $len 时要给$deth+1
        if($i==count($sameDir)){
            $deth++;
        }
        //将path2的/转换为../，然后和path1的后边部分合并
        //计算前缀
        if(count($arr1)-$deth-1 > 0){
            $prefix = array_fill(0,count($arr1)-$deth-1,'..');
        }else{
            $prefix = array('.');
        }
        //合并数组
        $tmp = array_merge($prefix,array_slice($arr,$deth));
        //将数组拼接为字符串
        $relativePath = implode("/",$tmp);
        echo $relativePath;exit;
        var_dump($deth,$sameDir);exit;
        $inf =INF;
        IF(INF>100000){
            ECHO 1;
        }ELSE{
            ECHO 2;
        }
        EXIT();
    if(IS_POST){
        $data['url'] = I('post.url');
        $data['test'] = I("post.test");
        $this->ajaxReturn(array('data'=>$data,'status'=>1));
    }else{
        $this->ajaxReturn(array('msg'=>'postshibai','status'=>0));
    }
}
}