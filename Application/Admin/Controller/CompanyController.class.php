<?php
/**
 * @desc 企业及案例管理
 */
namespace Admin\Controller;
use Admin\Controller\CommonController;
class CompanyController extends CommonController {

    /**
     * @desc 企业管理
     */
    public function index() {
        $condstr = "status = 1";
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND name LIKE '%$keyword%'";
        }
        
        $companystatus= _get('companystatus');
        if (is_numeric($companystatus)) {
            $condstr .= " AND companystatus=$companystatus";
        }
        $isgood= _get('isgood');
        if (is_numeric($isgood)) {
            $condstr .= " AND isgood=$isgood";
        }
        $type = _get('type');
        if (!empty($type)) {
            $condstr .= " AND type='{$type}'";
        }
        
        $Company = M('company_info');
        $count      = $Company->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        $types = getDictTypes("companyType");
        $companys = $Company->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->order(array('weight'=>"DESC",'created'=>"DESC"))->select();
        $this->assign('companys', $companys);
        $this->assign('types', $types);
        $this->assign('page', $show);
        $this->display();
    }
    /**
     * @desc 改变状态 企业正常或者屏蔽，是否是优秀企业 暂时不用 使用view修改
     * 2017-1-3
     */
    public function changeStatus(){
        if(IS_GET){
            if(M('company_info')->save($_GET)){
                $this->success('修改成功',U('Admin/company/index'));
            }else{
                $this->error('修改失败');
            }
        }
    }

    /**
     * @desc 企业产品管理
     * 2017-1-4
     */
    public function product() {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND title LIKE '%$keyword%'";
        }

        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }

        $type = _get('type');
        if (is_numeric($type)) {
            $condstr .= " AND type=$type";
        }

        $Product = M('company_product');
        $count      = $Product->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $products = $Product->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($products AS &$v){
            $company = M('company_info');
            $companyinfo = $company->where(['companyid'=>(int)$v['companyid']])->find();
            //获取该产品对应的公司名称，首选公司全称，如果全称为空取简称ename，如果全为空给默认值：公司名称暂无
            $v['companyname'] = (empty($companyinfo['name'])&&empty($companyinfo['ename'])) ? '公司名称暂无' :(empty($companyinfo['name'])?$companyinfo['ename']:$companyinfo['name']);
            $v['picture'] = empty($v['picture']) ? 0 :  $v['picture'];
        }
        $this->assign('products', $products);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 改变产品状态
     * 2017-1-4
     */
    public function changeProductStatus(){
        if(IS_GET){
            if(M('company_product')->save($_GET)){
                $this->success('修改成功',U('Admin/company/product'));
            }else{
                $this->error('修改失败');
            }
        }
    }

    /**
     * @desc 企业新闻管理
     * 2017-1-4
     */
    public function news() {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND title LIKE '%$keyword%'";
        }

        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }

        $type = _get('type');
        if (is_numeric($type)) {
            $condstr .= " AND type=$type";
        }

        $News = M('news');
        $count      = $News->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();

        $news = $News->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($news AS &$v){
            $company = M('company_info');
            $companyinfo = $company->where(['companyid'=>(int)$v['companyid']])->find();
            //获取该产品对应的公司名称，首选公司全称，如果全称为空取简称ename，如果全为空给默认值：公司名称暂无
            $v['companyname'] = (empty($companyinfo['name'])&&empty($companyinfo['ename'])) ? '公司名称暂无' :(empty($companyinfo['name'])?$companyinfo['ename']:$companyinfo['name']);
            $v['picture'] = empty($v['picture']) ? 0 :  $v['picture'];
        }
        $this->assign('news', $news);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * @desc 改变新闻状态
     * 2017-1-4
     */
    public function changeNewsStatus(){
        if(IS_GET){
            if(M('company_news')->save($_GET)){
                $this->success('修改成功',U('Admin/company/news'));
            }else{
                $this->error('修改失败');
            }
        }
    }

    /**
     * @desc 企业详情查看和优秀推荐
     * djt
     * 2017-3-15
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
//            $url = I('post.url');
//            $tel = I('post.tel');
//            $contact = I('post.contact');
//            $email = I('post.email');
//            $qq = I('post.qq');
//            $wx = I('post.wx');
            $companystatus = I('post.companystatus');
            $isgood = I('post.isgood');
            $weight = I('post.weight');
            $time = time();
            $data = array(
                'companystatus' => $companystatus,
                'isgood' => $isgood,
                'weight' => $weight,
                'updated' => $time
            );
            $Company = M("company_info");
            $Company->where('companyid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Company/index'));


        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Company = M('company_info');
            $company = $Company -> where('companyid='.$id) -> find();
            $this->assign('company', $company);
//            var_dump($company);
            $this->display();
        }
    }

    public function add() {
        if (IS_POST) {
            $name = I('post.name');
            $url = I('post.url');
            $status = I('post.status');
            $recommend = I('post.recommend');
            $weight = I('post.weight');
            
            $logo = parent::_upload('company');
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
            
            $Company = M("company_info");
            $Company->add($data);
            $this->success('添加成功',U('Admin/Company/index'));
        } else {        
            $this->display('view');
        }
    }
    /**
     * @desc 企业入驻申请表
     * 2017-2-13
     */
    public function apply() {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND name LIKE '%$keyword%'";
        }
    
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
        $ispay = _get('ispay');
        if (is_numeric($ispay)) {
            $condstr .= " AND ispay=$ispay";
        }
        $Apply = M('company_info');
        $count      = $Apply->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
    
        $applys = $Apply->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('applys', $applys);
        $this->assign('page', $show);
        $this->display();
    }
    
    /**
     * @desc 入驻审核处理
     */
    public function applyView($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.status');
    
            $data = array(
                'status' => $status
            );
    
            $Apply = M("company_info");
            $Apply->where('companyid='.$id)->save($data);
            $this->success('更新成功',U('Admin/Company/apply'));

        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Apply = M('company_info');
            $apply = $Apply -> where('companyid='.$id) -> find();
            $this->assign('apply', $apply);
            $this->display();
        }
    }

   
}
