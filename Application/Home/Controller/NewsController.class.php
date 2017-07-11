<?php
/**
 * @desc 课程管理
 */
namespace Home\Controller;
use Think\Controller;
use Think\Model;
use Org\Util;
use Home\Controller\CommonController;
class NewsController extends CommonController {

    /**
     * @desc 新闻首页
     *djt
     * 2017-2-27
     */
    public function index() {
        $this->display();
    }

    /**
     * 添加编辑新闻
     *
     * 2017-2-20
     */
    public function editNews() {
        $info = session("userinfo");
        if($info['role'] !=2){
            $this->error("当前身份状态不能操作");
//            $this->ajaxReturn(array('msg'=>'当前身份状态不能操作','status'=>0));
        }
        $uid = $info['uid'];
        $Company = M("company_info");
        $company = $Company->where(" uid = {$uid} AND status = 1  AND companystatus=1 AND temptype!=''")->find();
        if(empty($company)){
            $this->error("你还未成功完成入驻流程，不能添加新闻");
//            $this->ajaxReturn(array('msg'=>'你还未成功完成入驻流程，新闻','status'=>0));
        }
        $this->assign("company",$company);
        $companyid = $company['companyid'];
        if (IS_POST) {
            $title = I('post.title');
            $type = I('post.type');
            $intro = I("post.intro");
            $content = I('post.content');
            $id = I("post.id");
            $News = M("news");
            if(empty($id)){//添加
                $flag = true;
                $msg = '';
                if(empty($title)){
                    $flag = false;
                    $msg.="标题不能为空";
                }
                if(empty($intro)){
                    $flag = false;
                    $msg.="摘要不能为空";
                }
                if(empty($type)){
                    $flag = false;
                    $msg.="推荐类型不能为空";
                }
                if(empty($content)){
                    $flag = false;
                    $msg.="内容不能为空";
                }
                if(empty($_FILES['picture'])){
                    $flag = false;
                    $msg.="图片不能为空";
                }
                if(!$flag){
                    $this->error($msg);
//                    $this->ajaxReturn(array('msg'=>$msg,'status'=>0));
                }
                $picture = parent::_upload('news');
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
                    'row' => 2,
                    'intro' => $intro,
                    'content' => $content,
                    'picture' => $picture,
                    'created' => $time,
                    'updated' => $time
                );
                $news =  $News->add($data);
                if(empty($news)){
                    $this->error("添加失败");
//                    $this->ajaxReturn(array('msg'=>'添加失败','status'=>0));
                }
                $this->success('添加成功',U('Home/Company/newsManagement'));
//                $this->ajaxReturn(array("msg"=>'添加成功','status'=>1));
            }else{
                $data = array();
                if(!empty($title)){
                    $data['title'] = $title;
                }
                if(!empty($type)){
                    $data['type'] = $type;
                }
                if(!empty($intro)){
                    $data['intro'] = $intro;
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
                    $updated = $News->where("id = {$id}")->save($data);
                    if(empty($updated)){
                        $this->error("更新失败");
//                        $this->ajaxReturn(array("msg"=>'更新失败','status'=>0));
                    }
                    $this->success('更新成功',U('Home/Company/newsManagement'));
//                    $this->ajaxReturn(array("msg"=>'更新成功','status'=>1));
                }
            }
        } else {
            $id = _get('id');
            if(!empty($id)){
                $news = M("news")->where("id = {$id}")->find();
                $news['content'] = htmlspecialchars_decode($news['content']);
                $this->assign("news",$news);
            }
            $this->display();
        }
    }
    /**
     * 删除新闻
     *
     * 2017-2-20
     */
    public function delNews()
    {
        $info = session("userinfo");
        if ($info['role'] != 2) {
            $this->error("当前身份状态不能操作");
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
        $result = M("news")->where("companyid = {$companyid} AND id = {$id}")->limit(1)->delete();
        if($result===false){
//            $this->error("删除出错");
            $this->ajaxReturn(array('msg' => '删除出错', 'status' => 0));
        }
//        $this->error("删除成功");
        $this->ajaxReturn(array('msg' => '删除成功', 'status' => 1));
    }

    /**
     * 新闻详情
     * 2017-2-27
     * djt
     */
    public function newsDetails(){
        $jssdk = new JsSdkController;
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $info = session("userinfo");
        $this->assign('info',$info);
        $id = (int)_get("id");
        if(empty($id)){
            $this->error("参数错误1");
        }
        $News = M("news");
        //浏览数加1
        $result = $News->where("id = {$id}")->setInc("num",1);
        $news = M("news")->where("id = {$id}")->find();
        if(empty($news)){
            $this->error("暂无相关内容");
        }
        if(!empty($news['uid'])){
            $coin = M("person_info")->where("uid={$news['uid']}")->find();
            $this->assign('coin',$coin);
        }
//        if($news['row']==1){
            $news['content'] = htmlspecialchars_decode($news['content']);
        //获取需要替换的关键字
        $words = getKeyWords();
        foreach($words as $v){
            $url = "<a href='/index.php/Home/Index/index.html'>".$v."</a>";
            $news['content'] = str_replace($v,$url,$news['content'],$i);
        }
//        }else{
//            $news['content'] = nl2br( $news['content']);
//        }
        $row = $news['row'];
        $condition  = "id < {$id} AND status = 1 AND row = {$row}";
        $condition1  = "id > {$id} AND status = 1 AND row = {$row}";
        $condition2  = "status = 1 AND row = {$row} AND type=2";
        $companyid = $news['companyid'];
        if(!empty($companyid)){
            $condition.=" AND companyid={$companyid}";
            $condition1.=" AND companyid={$companyid}";
            $condition2.=" AND companyid={$companyid}";
        }
        //上一条
        $prev = $News->where($condition)->field("id,title,companyid")->find();
        //下一条
        $next = $News->where($condition1)->field("id,title,companyid")->find();
//        var_dump($prev);
//        var_dump($next);
//        var_dump($news);
        //热门资讯
        $articles = M("news")->where($condition2)->field("id,title,companyid")->order(array('num'=>'DESC','created'=>'DESC'))->limit(6)->select();
        $this->assign('articles', $articles);
        $this->assign('prev', $prev);
        $this->assign('next', $next);
        $this->assign('news',$news);
        $this->display();
    }

    /**
     * 新闻主页
     * 2017-2-27
     * djt
     */
    public function newsMore(){
        $News = M("news");
//        //浏览数加1
//        $result = $News->where("id = {$id}")->setInc("num",1);
        $News = M("news");
        //新闻条数查询
        $count= $News->where("status = 1")->count();
//        echo $count;
        $Page       = new \Think\Page($count, 20);
        $show       = $Page->show();
        $news = M("news")->where("status=1")->limit($Page->firstRow.','.$Page->listRows)->order("num DESC")->select();
        //热门文贴
        $articles = M("article")->where("status = 1 AND ispush = 1")->field("articleid,title")->limit(6)->select();
        $this->assign('articles', $articles);
//        $this->assign('prev', $prev);
//        $this->assign('next', $next);
        $this->assign('news',$news);
        $this->assign("page",$show);
        $this->display();
    }
    /**
     * 分享记录
     * djt
     * 2017-2-22
     */
    public function addshare(){
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
        $news= M("news as a")->join("LEFT join ww_company_info as b ON a.uid = b.uid")->where("a.id={$id}")->field("a.uid as uid,a.row as row,b.name as name")->find();
        $newsUid = $news['uid'];
        if(empty($newsUid)){
            $organizer = "上海维沃珂营销平台";
        }else{
            $organizer = $news['name'];
        }
        $data = array(
            'type' =>7,
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
        if(!empty($newsUid)){
            $newsVcoins = M("person_info")->where("uid={$newsUid}")->find();
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
            if(!empty($newsUid)){
                if(!empty($taskCoin)){
                    $delCoin = M("person_info")->where("uid={$newsUid}")->setDec("vcoin",$taskCoin);
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
            $newsUid = $newsUid ? $newsUid : 0;
            $vlogAarry1 = array(
                'uid' => $newsUid,
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
               $this->error("内容不能为空");
            }
            if(empty($touid)){
                $this->error("参数错误");
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
                $this->error('评论失败1');
            }
            if(!empty($taskCoin)){
                if((strlen($content)>$length) && ($count>$limit)){
                    //增加用户维币
                    $addCoin = M("person_info")->where("uid = {$uid}")->setInc('vcoin',$taskCoin);
                    if(empty($addCoin)){
                        $model->rollback();
                        $this->error("评论失败2");
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
                        $this->error("评论失败21");
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
                        $this->$this->error("回复失败3");
                    }
                }
            }
            $model->commit();
            $this->success('添加成功');
        }else{
            $this->error("异常操作");
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
                $this->error("你还没有登录，请先登录");
            }
            $uid = $info['uid'];
            $Reply = M('comment_reply');
            $type = 1;
            $commentid =  I("post.commentid");
            $content = I("post.content");
//            $browsenum = I("post.browsenum");
            if(empty($content)){
                $this->error("内容不能为空");
            }
            if(empty($commentid)){
                $this->error("参数错误");
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
                $this->error('回复失败');
            }
            $this->success('回复成功');
        }else{
            $this->error("异常操作");
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
                $this->error("你还没有登录，请先登录");
            }
            $uid = $info['uid'];
            $Reply = M('comment_reply');
            $type = 2;
            $commentid =  I("post.commentid");
            if(empty($commentid)){
                $this->error("参数错误");
            }
            $data = array(
                'type' => $type,
                'uid' => $uid,
                'commentid' => $commentid,
                'created' => time()
            );
            $result = $Reply->add($data);
            if(!$result){
                $this->error('操作失败');
            }
            $this->success('操作成功');
        }else{
            $this->error("异常操作");
        }
    }

    //结束位置
   
}
