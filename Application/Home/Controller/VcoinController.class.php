<?php
namespace Home\Controller;
use Home\Controller\CommonController;
use Think\Model;

class VcoinController extends CommonController {

    /**
     * @desc 维币管理
     * 2017-2-25
     * djt
     */
    public function myWallet() {
        //维币记录
        $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录");
        }
        $uid = $info['uid'];
        $log = M("vcoin_log")->where("uid = {$uid}")->select();
        $this->assign("log",$log);
//        var_dump($log[0]);
        //用户维币数
        $vcoin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
        $vcoin = $vcoin['vcoin'];
        $this->assign("vcoin",$vcoin);
        $this->display();
    }
    /**
     * 维币支付
     * djt
     * 3-5
     */
    public function buyVcoin(){
//        $this->ajaxReturn(array("msg"=>'该功能正在开发中','status'=>1));
        $info = session("userinfo");
        $uid = $info['uid'];
        if(empty($uid)){
//            $this->ajaxReturn(array("msg"=>'你还没有登录','status'=>0));
            $this->error("您还未登录，请先登录");
        }
        $num = I("post.num");
        $num = round($num,2);
//        var_dump($num);
        if(empty($num)){
            $this->error("参数错误1");
        }
        if((int)$num<10){
            $this->error("最低10元起充");
        }
        //一元兑换维币数
//        $vnum = C("VCOIN_NUM");
        //支付金额
        $amount = $num;
        $ordernum = date("YmdHis").rand(1000,10000);
        $data = array(
            'uid' => $uid,
            'ordernum' => $ordernum,
            'orderdate' => time(),
            'payment' => 'wxpay',
            'descp' => '微信购买维币',
            'amount' => $amount,
            'type' => 4,
            'seller' => C("ORGANIZER"),
            'created' => time(),
            'updated' => time()
        );
        //生成订单
        $db_order = M("order");
//        $model = new Model();
//        $model->startTrans();
        $result = $db_order->add($data);
        if(empty($result)){
//            $model->rollback();
            $this->error("生成订单失败");
        }
//        $wxordernum = date("YmdHis").rand(1000,9000);
//        $wxdata = array(
//            "wxordernum"=>$wxordernum,
//            "ordernum"=>$ordernum,
//            "created"=>time(),
//            "updated"=>time()
//        );
//        $wxreult = M("wxordernum")->add($wxdata);
//        if(empty($wxreult)){
//            $model->rollback();
//            $this->error("微信订单生成失败");
//        }
        if($result){
//            $model->commit();
            $this->success("订单生成成功，前往支付",U('Home/Wxpay/payVcoin',array('type'=>1,'ordernum'=>$ordernum)));
        }
//        $signNum = C("VCOIN_NUM");
//        $num = (int)round($num);
//        $pay = round($num/$signNum,2);
//        if($num==0||$pay==0){
//            $this->ajaxReturn(array("msg"=>'你输入的金额有误','status'=>0));
//        }
//        $this->ajaxReturn(array("msg"=>'该功能正在开发中','status'=>1));
    }

    /**
     * 微信支付轮询
     * 2017-3-29
     * djt
     */
    public function nativeOrders(){
        $order_num=trim($_POST['oid']);
        $status=M('order')->where(array('ordernum'=>$order_num))->field('orderstatus')->find();
        $this->ajaxReturn(array('msg'=>"支付",'status'=>$status['orderstatus']));
    }




    //结束
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
            $Task = M('vcoin_task');
            if ($id) {
                //操作名不修改
                $Task->where('id='.$id)->save($data);
            } else {
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