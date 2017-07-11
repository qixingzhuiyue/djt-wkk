<?php
/**
 * @desc 订单管理
 */
namespace Admin\Controller;
use Admin\Controller\CommonController;
class OrderController extends CommonController {


    /**
     * @desc 订单管理
     * 2017-2-14
     */
    public function index() {
        $condstr = 1;
        $ordernum = _get('ordernum');
        if ($ordernum) {
            $condstr .= " AND ordernum LIKE '%$ordernum%'";
        }
        $type = _get('type');
        if (is_numeric($type)) {
            $condstr .= " AND type = {$type}";
        }
        $orderstatus = _get('orderstatus');
        if(is_numeric($orderstatus)) {
            $condstr.= " AND orderstatus=$orderstatus";
        }
        $Order = M('order');
        $count      = $Order->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        $types = getOrderType();
        $orders = $Order->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($orders AS &$v) {
            $v['type'] = $types[$v['type']];
            if($v['orderstatus']==0){
                $v['orderstatus'] = "待付款";
            }elseif($v['orderstatus']==1){
                $v['orderstatus'] = "已支付，待发货";
            }else{
                $v['orderstatus'] = "已发货";
            }
        }
        $this->assign('orders', $orders);
        $this->assign('page', $show);
        $this->assign('types', $types);
        $this->display();
    }
    /**
     * @desc 删除订单
     * 2017-4-6
     */
    public function del(){
        $id = _get("id");
        if(empty($id)){
            $this->error("参数错误");
        }
        $result = M("Order")->where("id={$id}")->limit(1)->delete();
        if($result){
            $this->success("删除成功");
        }else{
            $this->success("删除失败");
        }
    }
    /**
     * @desc 订单详情查看
     * 2017-1-4
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.orderstatus');
            $time = time();
            $data = array(
                'orderstatus' => $status,
                'updated' => $time
            );
            $Order = M("order");
            $Order->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Order/index'));
        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Order = M('order');
            $order = $Order -> where('id='.$id) -> find();
            $types = getOrderType();
            $order['type'] = $types[$order['type']];
            $this->assign('order', $order);
            $this->display();
        }
    }

    /**
     * @desc 商品订单管理
     * 2017-1-6
     */
    public function goods() {
        $condstr = "type=1";
        $ordernum = _get('ordernum');
        if ($ordernum) {
            $condstr .= " AND ordernum LIKE '%$ordernum%'";
        }
        $name = _get('name');
        if ($name) {
            $condstr .= " AND name LIKE '%$name%'";
        }
        $orderstatus = _get('orderstatus');
        if(is_numeric($orderstatus)) {
            $condstr.= " AND orderstatus=$orderstatus";
        }
        $Order = M('order');
        $count      = $Order->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        
        $orders = $Order->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($orders AS &$v) {
            $Shop = M('shop_goods');
            $good = $Shop->where(['goodid'=>(int)$v['shopid']])->field('name')->find();
            $v['goodname'] = empty($good) ? '暂无' : $good['name'];
            $v['address'] = empty($v['prov'].$v['city'].$v['address']) ? '暂无' : $v['prov'].$v['city'].$v['address'];
        }
        $this->assign('orders', $orders);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 商品订单详情
     * 2017-1-6
     */
    public function goodView($id) {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Order = M('order');
            $order = $Order -> where('id='.$id.' AND type=1') -> find();
          $Shop = M('shop_goods');
        $good = $Shop->where(['goodid'=>(int)$order['shopid']])->field('name')->find();
        $order['goodname'] = empty($good) ? '暂无' : $good['name'];
        $v['address'] = empty($order['prov'].$order['city'].$order['address']) ? '暂无' : $order['prov'].$order['city'].$order['address'];
            $this->assign('order', $order);
            $this->display();
    }

    /**
     * @desc 活动订单管理
     * 2017-1-6
     */
    public function activity() {
        $condstr = "type=2";
        $ordernum = _get('ordernum');
        if ($ordernum) {
            $condstr .= " AND ordernum LIKE '%$ordernum%'";
        }
        $name = _get('name');
        if ($name) {
            $condstr .= " AND name LIKE '%$name%'";
        }
        $orderstatus = _get('orderstatus');
        if(is_numeric($orderstatus)) {
            $condstr.= " AND orderstatus=$orderstatus";
        }
        $Order = M('order');
        $count      = $Order->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $orders = $Order->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($orders AS &$v) {
            $Activity = M('activity');
            $activity = $Activity->where(['activityid'=>(int)$v['shopid']])->field('title')->find();
            $v['goodname'] = empty($activity) ? '暂无' : $activity['title'];
            $v['address'] = empty($v['prov'].$v['city'].$v['address']) ? '暂无' : $v['prov'].$v['city'].$v['address'];
        }
        $this->assign('orders', $orders);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 活动订单详情
     * 2017-1-6
     */
    public function activityView($id) {
        if (empty($id)) {
            $this->error('非法操作，参数错误');
        }
        $Order = M('order');
        $order = $Order -> where('id='.$id.' AND type=2') -> find();
        $Activity = M('activity');
        $activity = $Activity->where(['activityid'=>(int)$order['shopid']])->field('title')->find();
        $order['goodname'] = empty($activity) ? '暂无' : $activity['title'];
//        $v['address'] = empty($order['prov'].$order['city'].$order['address']) ? '暂无' : $order['prov'].$order['city'].$order['address'];
        $this->assign('order', $order);
        $this->display();
    }

    /**
     * @desc 培训订单管理
     * 2017-1-8
     */
    public function course() {
        $condstr = "type=3";
        $ordernum = _get('ordernum');
        if ($ordernum) {
            $condstr .= " AND ordernum LIKE '%$ordernum%'";
        }
        $name = _get('name');
        if ($name) {
            $condstr .= " AND name LIKE '%$name%'";
        }
        $orderstatus = _get('orderstatus');
        if(is_numeric($orderstatus)) {
            $condstr.= " AND orderstatus=$orderstatus";
        }
        $Order = M('order');
        $count      = $Order->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $orders = $Order->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($orders AS &$v) {
            $Course= M('course');
            $course = $Course->where(['courseid'=>(int)$v['shopid']])->field('title')->find();
            $v['goodname'] = empty($course) ? '暂无' : $course['title'];
            $v['address'] = empty($v['prov'].$v['city'].$v['address']) ? '暂无' : $v['prov'].$v['city'].$v['address'];
        }
        $this->assign('orders', $orders);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 课程订单详情
     * 2017-1-6
     */
    public function courseView($id) {
        if(IS_POST){
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.status');

            $data = array(
                'orderstatus' => $status,
                'updated'=>time()
            );

            $Apply = M("order");
            $Apply->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Order/course'));
        }else{
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Order = M('order');
            $order = $Order -> where('id='.$id.' AND type=3') -> find();
            $Course = M('apply_course');
            $course = $Course->where(['uid'=>(int)$order['uid'],'courseid'=>(int)$order['shopid']])->field('title,name,mobile,address')->find();
            $order['goodname'] = empty($course['title']) ? '暂无' : $course['title'];
            $order['name'] = empty($course['name'] && $order['name']) ? '暂无' : (!empty($order['name']) ? $order['name'] :$course['name']);
            $order['address'] = empty($course['address']) ? '暂无' : $course['address'];
//        $v['address'] = empty($order['prov'].$order['city'].$order['address']) ? '暂无' : $order['prov'].$order['city'].$order['address'];
            $this->assign('order', $order);
            $this->display();
        }
    }

}
