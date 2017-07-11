<?php
/**
 * @desc 订单管理
 */
namespace Home\Controller;
use Home\Controller\CommonController;
use Think\Model;

class OrderController extends CommonController {


    public function index() {
        $this->display();
    }

    /**
     * 订单管理
     * 2017-2-25
     */
    public function orderMange(){
        $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录，无法查看订单");
        }
        $uid = $info['uid'];
        //商品订单
        $goods = M("order as a")->join("LEFT join ww_shop_goods as b ON a.shopid = b.goodid")->where("a.uid = {$uid} AND a.type = 1")->field("a.*,b.picture,b.name as goodname")->order("a.orderdate ASC")->select();
        //活动订单
        $activitys =  M("order as a")->join("LEFT join  ww_activity as b ON a.shopid = b.activityid")->where("a.uid = {$uid} AND a.type = 2")->field("a.*,b.prov,b.title")->order("a.orderdate ASC")->select();
        //课程订单
        $coures = M("order as a")->join("LEFT join  ww_course as b ON a.shopid = b.courseid")->where("a.uid = {$uid} AND a.type = 3")->field("a.*,b.teacher,b.title")->order("a.orderdate ASC")->select();
//        var_dump($coures[0]);
        $this->assign("goods",$goods);
        $this->assign("activitys",$activitys);
        $this->assign("coures",$coures);
        $this->display();
    }

    /**
     * 买家确认收货
     * 2017-2-25
     * djt
     */
    public function confirmOrder(){
//        $order = M("order")->where("uid = 1 AND orderstatus in(1,2)")->buildsql();
//        echo $order;exit;
        $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录，请先登录");
        }
        $uid = $info['uid'];
        $id = (int)_get("id");
        $type = (int)_get("type");
        if(empty($id)){
            $this->error("参数错误1");
        }
        if(empty($type)){
            $this->error("参数错误2");
        }
        $order = M("order")->where("uid = {$uid} AND type={$type} AND id = {$id} AND orderstatus=2")->find();
        if(empty($order)){
            $this->error("你无法操作当前订单");
        }
        //获取商家信息
        $goodId = $order['shopid'];
        $otype = $order['type'];
        if(in_array($otype,array(1,2,3))){
            if(empty($goodId)){
                $this->error("该商品订单异常");
            }
        }
        //商品确认收货要记录该用户增加的钱
        if($otype==1){
            //获取卖家uid
            $shop = M('company_shop as a')->join("LEFT join ww_shop_goods as b ON a.shopid=b.shopid")->where("b.goodid={$goodId}")->field("a.uid,b.name")->find();
            if(empty($shop['uid'])){
                $this->error("该商品卖家不存在");
            }
            $suid = (int)$shop['uid'];
            $amount = round($order['amount'],2);
            $name = $shop['name'];
            //开启事务
            $model = new Model();
            $model->startTrans();
            //更新买家的金钱money
            if(!empty($suid)&&!empty($amount)){
                //增加用户money
                $mresult = M("person_info")->where("uid={$suid}")->setInc('money',$amount);
                //添加用户记录
                $mlog = array(
                    'uid' => $suid,
                    'type' => 1,
                    'sztype' => 1,
                    'amount' => $amount,
                    'status' => 1,
                    'descp' => '购买了商品'.$name,
                    'created' => time()
                );
                $mlog = M("money_log")->add($mlog);
                if(!$mresult || !$mlog){
                    $model->rollback();
                    $this->error('卖家信息更新失败');
                }
            }
        }
        $data = array(
            'orderstatus'=>4
        );
        $result = M("order")->where("id = {$id}")->save($data);
        if($result){
            $model->commit();
            $this->success("确认成功");
        }else{
            $model->rollback();
            $this->error("确认失败");
        }
    }

    /**
     * 未使用取消订单:针对商品订单
     * 2017-2-25
     * djt
     */
    public function delOrder(){
//        $order = M("order")->where("uid = 1 AND orderstatus in(1,2)")->buildsql();
//        echo $order;exit;
        $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录，请先登录");
        }
        $uid = $info['uid'];
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $order = M("order")->where("uid = {$uid} AND type = 1 AND id = {$id} AND orderstatus =0")->count();
        if(empty($order)){
            $this->error("你无法操作当前订单");
        }
        $result = M("order")->where("id = {$id}")->delete();
        if(empty($result)){
            $this->error("取消失败");
        }
        $this->success("取消成功");
    }







    //结束



}
