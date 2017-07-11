<?php
namespace Home\Controller;
use Think\Model;
class CloudallyController extends CommonController {
    public function index(){
        //首页关键字
        $key = M("sys_keyword")->where("name='商家云盟首页'")->find();
        $this->assign("key",$key);
        //头部企业图广告
        $companyBanner = M("banner")->where("type ='商家云盟banner' AND status = 1")->order("weight DESC")->find();
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
        //优势图片取出六条
        $advs = M("sys_advantage")->where("status = 1")->order("created DESC")->limit(6)->select();
        $this->assign("advs",$advs);
        $this->display();
    }
}