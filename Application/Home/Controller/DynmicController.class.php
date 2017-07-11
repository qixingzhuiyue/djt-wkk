<?php
namespace Home\Controller;
use Home\Controller\CommonController;
class DynmicController extends CommonController {
    /**
     * @desc 动态详情
     * 2017-2-27
     */
    public function view() {
            if (empty(_get('id'))) {
                $this->error("参数错误");
            }
                $id = _get('id');
                $Dyn= M('sys_dynmic');
                //点击量加1
                $result = $Dyn->where("id = {$id}")->setInc('num',1);
                $dynmic = $Dyn -> where('id='.$id) -> find();
                $dynmic['content'] = htmlspecialchars_decode( $dynmic['content']);
                //上一条
                $prev = $Dyn->where("id < {$id} AND status = 1")->field("id,title")->find();
                //下一条
                $next = $Dyn->where("id > {$id} AND status = 1")->field("id,title")->find();
                //热门文贴
                $articles = M("article")->where("status = 1 AND ispush = 1 AND type = 1")->field("articleid,title")->limit(6)->select();
                $this->assign('articles', $articles);
                $this->assign('dynmic', $dynmic);
                $this->assign('prev', $prev);
                $this->assign('next', $next);
            $this->display();

    }
}