<?php
/**
 * @desc 店铺及商品管理
 */
namespace Admin\Controller;
use Admin\Controller\CommonController;
class ShopController extends CommonController {

    /**
     * @desc 店铺管理
     * 2017-1-4
     */
    public function index() {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND name LIKE '%$keyword%'";
        }

        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }

        $recommend = _get('recommend');
        if (is_numeric($recommend)) {
            $condstr .= " AND recommend=$recommend";
        }
        $condstr .= " AND type=1";
        $Shop = M('company_shop');
        $count      = $Shop->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $shops = $Shop->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('shops', $shops);
        $this->assign('page', $show);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $name = I('post.name');
            $url = I('post.url');
            $status = I('post.status');
            $recommend = I('post.recommend');
            $weight = I('post.weight');
            
            $logo = parent::_upload('shop');
            $time = time();
            
            //print_r($_POST);
            //exit;
            
            $data = array(
                'name' => $name,          
                'logo' => $logo,
                'url' => $url,
                'status' => $status,
                'recommend' => $recommend,
                'weight' => $weight,
                'created' => $time,
                'changed' => $time
            );
            
            $Shop = M("Shop");
            $Shop->add($data);
            $this->success('添加成功',U('Admin/Shop/index'));
        } else {        
            $this->display('view');
        }
    }
    
    /**
     * @desc 店铺详情查看
     * 2017-1-4
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.status');
            $ispush = I("post.ispush");
            $time = time();
            $data = array(
                'status' => $status,
                'ispush' => $ispush,
                'updated' => $time
            );
            $Shop = M("company_shop");
            $Shop->where('shopid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Shop/index'));
        } else {        
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }     
            $Shop = M('company_shop');
            $shop = $Shop -> where('shopid='.$id) -> find();
            $this->assign('shop', $shop);
            $this->display();
        }              
    }
    
    /**
     * @desc 店铺商品
     * 2017-1-4
     */
    public function goods($id=0) {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND name LIKE '%$keyword%'";
        }
        
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
        
        $recommend = _get('type');
        if (is_numeric($recommend)) {
            $condstr .= " AND type=$recommend";
        }
        
        if ($id) {
            $condstr .= " AND shopid=$id";
        }
        
        $Goods = M('shop_goods');
        $count      = $Goods->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        
        $goods = $Goods->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('goods', $goods);
        $this->assign('page', $show);
        $this->display();        
    }
    /**
     * @desc 改变商品状态
     * 2017-1-4
     */
    public function changeStatus(){
        if(IS_GET){
            if(M('shop_goods')->save($_GET)){
                $this->success('修改成功',U('Admin/Shop/goods'));
            }else{
                $this->error('修改失败');
            }
        }
    }
    /**
     * @desc 店铺商品编辑
     */
    public function goodsView($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }        
            $status = I('post.status');
            $recommend = I('post.recommend');
            $weight = I('post.weight');
        
            $data = array(                
                'status' => $status,
                'recommend' => $recommend,
                'weight' => $weight
            );
        
            $Goods = M("shop_goods");
            $Goods->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Shop/goods'));
        
        
        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Goods = M('shop_goods');
            $goods = $Goods -> join('ww_company_shop ON ww_company_shop.shopid=ww_shop_goods.shopid') ->field('ww_shop_goods.*,ww_company_shop.name AS shopname')-> where('ww_shop_goods.goodid='.$id) -> find();
            $this->assign('goods', $goods);
            $this->display();
        }  
    }
    
    /**
     * @desc 店铺入驻申请审核
     */
    public function apply($id=0) {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND name LIKE '%$keyword%'";
        }
    
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }          

        $Apply = M('company_shop');
        $count      = $Apply->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        $applys = $Apply->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($applys AS &$v) {
            $company = M('company_info');
            $pic = $company->where(['companyid'=>(int)$v['companyid']])->find();
            $v['license'] = empty($pic['licensepic']) ? 0 : $pic['licensepic'];
        }
        $this->assign('applys', $applys);
        $this->assign('page', $show);
        $this->display();
    }
    
    /**
     * @desc 入驻审核处理
     * 2017-1-4
     */
    public function applyView($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $type = I('post.type');
            $data = array(
                'type' => $type
            );
    
            $Apply = M("company_shop");
            $Apply->where('shopid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Shop/apply'));
    
        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Apply = M('company_shop');
            $apply = $Apply -> where('shopid='.$id) -> find();
            $company = M('company_info');
            $pic = $company->where(['companyid'=>(int)$apply['companyid']])->find();
            $apply['license'] = empty($pic['licensepic']) ? 0 : $pic['licensepic'];
            $this->assign('apply', $apply);
            $this->display();
        }
    }

   
}
