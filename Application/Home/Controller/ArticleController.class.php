<?php
/**
 * @desc 课程管理
 */
namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Org\Util;
use Home\Controller\CommonController;
class ArticleController extends CommonController {

    /**
     * @desc 经验交流首页
     *djt
     * 2017-2-24
     */
    public function index() {
        //首页关键字
        $key = M("sys_keyword")->where("name='经验交流首页'")->find();
        $this->assign("key",$key);
        //头部广告
        $companyBanner = M("banner")->where("type ='经验交流banner' AND status = 1")->order("weight DESC")->find();
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
        $condition = 1;
        //类型
        $Article = M("article");
        $type = _get("type");
        $type = $type ? $type : 1;
        $this->assign('type',$type);
        $condition.=" AND type = {$type} AND status = 1";
        //热门  最新 排行推荐 1热门 2 最新 3 排行
        $type2 = _get("type2");
        $type2 = $type2 ? $type2 : 1;
        $this->assign('type2', $type2);
            if($type2 == 1){
                //热门条件
                $condition = $condition." AND ispush = 1";
                //分页
                $count = $Article->where($condition)->count();
//        echo $count;
                $Page       = new \Think\Page($count,26);
                $show       = $Page->show();
                $this->assign('page',$show);
                $artiles = $Article->where($condition)->field("articleid,author,title,picture,intro,created,browsenum")->order(array('weight'=>'DESC','created'=>'DESC'))->limit($Page->firstRow.','.$Page->listRows)->select();
            }elseif($type2 == 3){
                //分页
                $count = $Article->where($condition)->count();
//        echo $count;
                $Page       = new \Think\Page($count,26);
                $show       = $Page->show();
                $this->assign('page',$show);
                $artiles = $Article->where($condition)->field("articleid,author,title,picture,intro,created,browsenum")->order(array('weight'=>'DESC','browsenum'=>'DESC'))->limit($Page->firstRow.','.$Page->listRows)->select();
            }elseif($type2 == 2){
                //分页
                $count = $Article->where($condition)->count();
//        echo $count;
                $Page       = new \Think\Page($count,26);
                $show       = $Page->show();
                $this->assign('page',$show);
                $artiles = $Article->where($condition)->field("articleid,author,title,picture,intro,created,browsenum")->limit($Page->firstRow.','.$Page->listRows)->order(array('weight'=>'DESC','created'=>'DESC'))->select();
            }
        foreach($artiles AS &$v){
            $v['commentNum'] = M("person_comment")->where("touid = {$v['articleid']} AND type =4 AND status = 1")->count();
            if(file_exists('./Public'.$v['picture'])){
                $url = str_replace(".",'_mid.',$v['picture'],$i);
                if(file_exists('./Public'.$url)){
                    $v['picture'] = $url;
                }else{
                    $image = new \Think\Image();
                    $image->open('./Public'.$v['picture']);
                    $image->thumb(400,400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    $v['picture'] = $url;
                }
            }
        }
        //右侧热门推荐
        $push1 = $Article->where("ispush = 1 AND status = 1")->field("articleid,title")->order(array('weight'=>'DESC','created'=>'DESC'))->limit(6)->order("created DESC")->select();
        $push2 = $Article->where("ispush = 0 AND status = 1")->field("articleid,title")->order(array('weight'=>'DESC','created'=>'DESC'))->limit(6)->order("created DESC")->select();
        //广告
        $banner1 = M("banner")->where("type ='主题话题' AND status = 1")->order(array('weight'=>'DESC','created'=>'DESC'))->find();
        $banner2 = M("banner")->where("type ='企业话题' AND status = 1")->order(array('weight'=>'DESC','created'=>'DESC'))->find();
        $this->assign("articles",$artiles);
        $this->assign("push1",$push1);
        $this->assign("push2",$push2);
        $this->assign("banner1",$banner1);
        $this->assign("banner2",$banner2);
        $this->display();
    }

    /**
     *添加文贴
     * 2017-2-24
     * djt
     */
    public function posted(){
        $info = session("userinfo");
        if($info['role'] !=2){
            $this->error("当前身份状态不能操作");
//            $this->ajaxReturn(array('msg'=>'当前身份状态不能操作','status'=>0));
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
        if(empty($company)){
            $this->error("你还未成功完成入驻流程，不能发布文贴");
//            $this->ajaxReturn(array('msg'=>'你还未成功完成入驻流程，不能添加产品','status'=>0));
        }
        $this->assign('company',$company);
        $companyid = $company['companyid'];
        if(IS_POST){
            $Article = M('article');
            $type = 2;
            $author = $info['name'];
            $title = I("post.title");
            $ispush = I("post.ispush");
            $intro = I("post.intro");
            $id= I("post.id");
            $content = I("post.content");
            //编辑
            if(!empty($id)){
                $art = M("article")->where("articleid={$id} AND uid={$uid} AND status=1")->find();
                if(empty($art)){
                    $this->error("当前文章不可编辑");
                }
                $data = array();
                if(!empty($_FILES['picture']['name'])){
                    $picture = parent::_upload('article');
                }
                if(!empty($title)){
                    $data['title'] = $title;
                }
                if(!empty($intro)){
                    $data['intro'] = $intro;
                }
                if(!empty($picture)){
                    $data['picture'] = $picture;
                }
                if(!empty($content)){
                    $data['content'] = $content;
                }
                $data['ispush'] = $ispush;
                if(!empty($data)){
                  $result = M("article")->where("articleid={$id}")->save($data);
                    if(!$result){
                        $this->error("编辑失败");
                    }
                }
                $this->success("编辑成功",U('Home/Company/articleManagement'));
            }else{
                $msg = '';
                $flag = true;
                if(empty($title)){
                    $flag = false;
                    $msg.="标题,";
                }
                if(empty($_FILES['picture']['name'])){
                    $flag = false;
                    $msg.="图片,";
                }else{
                    $picture = parent::_upload('article');
                }
                if(empty($intro)){
                    $flag = false;
                    $msg.="简介,";
                }
                if(empty($content)){
                    $flag = false;
                    $msg.="内容,";
                }
                if(!$flag){
                    $msg = trim($msg,',');
                    $this->error($msg);
                }
                $data = array(
                    'type' => $type,
                    'author' => $author,
                    'title' => $title,
                    'companyid'=>$companyid,
                    'uid' => $uid,
                    'intro' => $intro,
                    'picture' => $picture,
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
                $task = $Task->where("action = '回帖' AND status = 1 AND isperson = {$isperson}")->find();
//        var_dump($task);exit;
                $taskid = $task['id'];
                $taskCoin = $task['value'];
                $limit = $task['limit'];
                $length = $task['length'];
                //用户当天完成的次数
                $count = $TaskLog->where("uid = {$uid} AND taskid = {$taskid} AND created>$start AND created<$end")->count();
                $model = new Model();
                $model->startTrans();
                $result = $Article->add($data);
                if(!$result){
                    $model->rollback();
                    $this->error('添加失败1');
                }
                if(!empty($taskCoin)){
                    if((strlen($content)>$length) && ($count>$limit)){
                        //增加用户维币
                        $addCoin = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$taskCoin);
                        if(empty($addCoin)){
                            $model->rollback();
                            $this->error("回复失败1");
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
                            $this->error("回复失败2");
//                        $this->ajaxReturn(array('msg'=>'注册失败2','status'=>0));
                        }
                        //写入维币记录
                        $Vlog = M("vcoin_log");
                        $vlogAarry = array(
                            'uid' => $uid,
                            'action' => "回帖",
                            'organizer' => C("ORGANIZER"),
                            'intro' => "回帖增加维币",
                            'type' => 2,
                            'value' => $taskCoin,
                            'created' => time()
                        );
                        $vlog = $Vlog->add($vlogAarry);
                        if(!$vlog){
                            $model->rollback();
                            $this->error("回复失败3");
                        }
                    }
                }
                $model->commit();
                $this->success('添加成功',U('Home/Company/articleManagement'));
            }
//            $browsenum = I("post.browsenum");
        }else{
            $id = _get('id');
            if(!empty($id)){
                $article = M("article")->where("companyid={$companyid} AND articleid={$id}")->find();
                $article['content'] = htmlspecialchars_decode($article['content']);
                $this->assign("article",$article);
            }
            $this->display();
        }
    }

    /**
     * 文贴详情
     * 2017-2-24
     * djt
     */
    public function articleDetails(){
        $jssdk = new JsSdkController;
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $info = session("userinfo");
//        var_dump($_GET);
        $id = (int)_get("id");
        if(empty($id)){
            $this->error("参数错误1");
        }
        //有分享用户session存储
        $fromid = (int)_get("fromid");
        if(!empty($fromid)){
            session("fromid",$fromid);
        }
        $Article = M("article");
        //浏览数加1
        $result = $Article->where("articleid = {$id}")->setInc("browsenum",1);
        $article = M("article")->where("articleid = {$id}")->find();
        if(empty($article)){
            $this->error("暂无相关内容");
        }
        //企业发帖显示V币
        $arcUid = $article['uid'];
        if(!empty($arcUid)){
            $Coin = M("person_info")->where("uid={$arcUid}")->find();
            $article['vcoin'] = $Coin['vcoin'];
        }
        //是否收藏
        if(!empty($info['uid'])){
            $avatar = M("person_info")->where("uid = {$info['uid']}")->field('avatar')->find();
            $this->assign('avatar',$avatar);
            $this->assign('info',$info);
            $collect = M("collect")->where("collectid = {$id} AND type=3 AND uid ={$info['uid']}")->count();
        }
        $article['collect'] = $collect ? 1 : 0;
//        var_dump($article['collect']);
//        if($article['type']!=2){
            $article['content'] = htmlspecialchars_decode($article['content']);
        //获取需要替换的关键字
        $words = getKeyWords();
        foreach($words as $v){
            //给关键字加链接
            $url = "<a href='/index.php/Home/Index/index.html'>".$v."</a>";
            $article['content'] = str_replace($v,$url,$article['content'],$i);
        }
        //将title 和 alt的链接去掉
//        $article['content'] = preg_replace('/title=\"(.*?)<a href=(.*?)>(.*?)<\/a>(.*?)\".*?alt=\"(.*?)<a href=(.*?)>(.*?)<\/a>(.*?)\"/', "title=$1$3 alt=$5$7",$article['content']);
//        }else{
//            $article['content'] = nl2br($article['content']);
//        }
        //评论数
        $article['commentNum'] = M("person_comment")->where("touid = {$id} AND type = 4 AND status =1")->count();
        //评论
        $comments =  M("person_comment")->where("touid = {$id} AND type = 4 AND status =1")->order("created DESC")->limit(10)->select();
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
            $this->assign('replyStatus',$replyStatus);
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
        $this->assign('article',$article);
//        var_dump($article);
        $this->assign('comments',$comments);
//        var_dump($comments[0]);
        //右侧热门推荐
        $push1 = $Article->where("ispush = 1 AND status = 1")->field("articleid,title,created")->limit(6)->order("created DESC")->select();
        $push2 = $Article->where("ispush = 0 AND status = 1")->field("articleid,title,created")->limit(6)->order("created DESC")->select();
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
     * 删除文贴
     * 2017-2-20
     */
    public function delArticle()
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
        $result = M("article")->where("companyid = {$companyid} AND articleid = {$id}")->limit(1)->delete();
        if($result===false){
//            $this->error("删除出错");
            $this->ajaxReturn(array('msg' => '删除出错', 'status' => 0));
        }
//        $this->error("删除成功");
        $this->ajaxReturn(array('msg' => '删除成功', 'status' => 1));
    }
    /**
     *评论回帖
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
            $type = 4;
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
     * 2017-2-24
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
        $collect = M("collect")->where("id ={$id} AND uid = {$uid}")->find();
        if(!empty($collect)){
            $this->ajaxReturn(array("msg"=>'你已经收藏过该文贴','status'=>0));
        }
        $data = array(
            'type' => 3,
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
     * 分享记录
     * djt
     * 2017-2-22
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
        $article = M("article as a")->join("LEFT join ww_company_info as b ON a.uid = b.uid")->where("a.articleid={$id}")->field("a.uid as uid,b.name as name")->find();
        $arcUid = $article['uid'];
        if(empty($arcUid)){
            $organizer = "上海维沃珂营销平台";
        }else{
            $organizer = $article['name'];
        }
        $data = array(
            'type' =>3,
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
        if(!empty($arcUid)){
            $arcVcoins = M("person_info")->where("uid={$arcUid}")->find();
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
            //减少发帖人维币:$arcUid 不为空
            $delCoin = true;
            if(!empty($arcUid)){
                $delCoin = M("person_info")->where("uid={$arcUid}")->setDec("vcoin",$taskCoin);
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
            $arcUid = $arcUid ? $arcUid : 0;
            $vlogAarry1 = array(
                'uid' => $arcUid,
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
    //结束位置
}
