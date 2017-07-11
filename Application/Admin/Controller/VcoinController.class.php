<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class VcoinController extends CommonController {

//    //用户列表
//    public function index(){
//        $this->sysuser = D('Sysuser')->field('password',true)->relation(true)->select();
//        $this->display();
//    }
    /**
     * @desc 维币充值记录管理
     * 2017-1-4
     */
    public function vcoinLog() {
        $condstr = 'type=1';
//        $keyword = _get('keyword');
//        if ($keyword) {
//            $condstr .= " AND title LIKE '%$keyword%'";
//        }
//
//        $status = _get('status');
//        if (is_numeric($status)) {
//            $condstr .= " AND status=$status";
//        }
//
//        $type = _get('type');
//        if (is_numeric($type)) {
//            $condstr .= " AND type=$type";
//        }

        $Vcoin = M('vcoin_log');
        $count      = $Vcoin->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $vcoin = $Vcoin->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($vcoin AS &$v){
            $user = M('users');
            $userinfo = $user->where(['uid'=>(int)$v['uid']])->find();
            //获取充值用户名，空的时候给默认值
            $v['name'] = empty($userinfo['username']) ? '暂无' : $userinfo['username'];
        }
        $this->assign('vcoin', $vcoin);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 维币任务添加和编辑
     * 2017-2-15
     */
    public function taskView() {
        if (IS_POST) {
            $id = I('post.id');
            $action = I('post.action');
            $isperson = I('post.isperson');
            $value = I('post.value');
            $type = I('post.type');
            $limit = I('post.limit');
            $length = I('post.length');
            $Task = M('vcoin_task');
            if ($id) {
                //操作名不修改
                if (!empty($value)) {
                    $data['value'] = $value;
                }
                if (!empty($limit)) {
                    $data['limit'] = $limit;
                }
                if (!empty($length)) {
                    $data['length'] = $length;
                }
                $Task->where('id='.$id)->save($data);
            } else {
                if (empty($action)) {
                    $this->error('请填写操作名称');
                }
                if (empty($isperson)) {
                    $this->error('请填写适用类型');
                }
                if (empty($value)) {
                    $this->error('请填维币值');
                }
                if (empty($type)) {
                    $this->error('请填写任务类型');
                }
                $data = array(
                    'isperson' => $isperson,
                    'value' => $value,
                    'type' => $type,
                    'created' => time()
                );
                if($type==2){
                    if(empty($limit)){
                        $this->error("每日任务需要填写每日上限值");
                    }
                    $data['limit'] = $limit;
                }
                if(!empty($length)){
                    $data['length'] = $length;
                }
                $data['action'] = $action;
                $Task->add($data);
            }
            $this->success('操作成功',U('Admin/Vcoin/task'));


        } else {
            $Task = M('vcoin_task');
            $id = _get('id');
            if ($id) {
                $task = $Task -> where('id='.$id) -> find();
                $this->assign('task', $task);
            }
            $this->display();
        }
    }

    /**
     * @desc 维币任务表
     * 2017-2-15
     */
    public function task() {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND action LIKE '%$keyword%'";
        }

        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }

        $isperson = _get('isperson');
        if (is_numeric($isperson)) {
            $condstr .= " AND isperson=$isperson";
        }

        $type = _get('type');
        if (is_numeric($type)) {
            $condstr .= " AND type=$type";
        }

        $Task = M('vcoin_task');
        $count      = $Task->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $tasks = $Task->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('tasks', $tasks);
        $this->assign('page', $show);
        $this->display();
    }

}