<?php
/**
 * @desc 设计师及案例管理
 */
namespace Admin\Controller;
use Admin\Controller\CommonController;
class DesignerController extends CommonController {

    /**
     * @desc 设计师管理
     */
    public function index() {      
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND realname LIKE '%$keyword%'";
        }
        
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
        
        $recommend = _get('recommend');
        if (is_numeric($recommend)) {
            $condstr .= " AND recommend=$recommend";
        }
        
        $Designer = M('Designer');
        $count      = $Designer->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        
        $designers = $Designer->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('designers', $designers);
        $this->assign('page', $show);
        $this->display();
    }
    
    /**
     * @desc 设计师详情查看
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $workexp = I('post.workexp');
            $goodat = I('post.goodat');
            $url = I('post.url');
            $tel = I('post.tel');
            $email = I('post.email');
            $qq = I('post.qq');
            $wx = I('post.wx');            
            $status = I('post.status');
            $recommend = I('post.recommend');
            $weight = I('post.weight');
            
            $data = array(
                'workexp' => $workexp,
                'goodat' => $goodat,
                'url' => $url,
                'tel' => $tel,
                'email' => $email,
                'qq' => $qq,
                'wx' => $wx,
                'status' => $status,
                'recommend' => $recommend,
                'weight' => $weight
            );
            
            $Designer = M("Designer");
            $Designer->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Designer/index'));
            
            
        } else {        
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }     
            $Designer = M('Designer');
            $designer = $Designer -> where('id='.$id) -> find();
            $this->assign('designer', $designer);
            $this->display();
        }              
    }
    
    /**
     * @desc 设计师案例
     */
    public function work($id=0) {
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
        
        if ($id) {
            $condstr .= " AND designerid=$id";
        }
        
        $Work = M('designer_works');
        $count      = $Work->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        
        $works = $Work->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('works', $works);
        $this->assign('page', $show);
        $this->display();        
    }
    
    /**
     * @desc 设计师案例编辑
     */
    public function workView($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误');
            }        
            $status = I('post.status');
            $recommend = I('post.recommend');
            $weight = I('post.weight');
        
            $data = array(                
                'status' => $status,
                'recommend' => $recommend,
                'weight' => $weight
            );
        
            $Work = M("designer_works");
            $Work->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Designer/work'));
        
        
        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Work = M('designer_works');
            $work = $Work -> join('jc_designer ON jc_designer.id=jc_designer_works.designerid') ->field('jc_designer_works.*,jc_designer.realname')-> where('jc_designer.id='.$id) -> find();
            $this->assign('work', $work);
            $this->display();
        }  
    }
    
    /**
     * @desc 设计师入驻申请审核
     */
    public function apply($id=0) {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND realname LIKE '%$keyword%'";
        }
    
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }          
    
        $Apply = M('designer_apply');
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
            $remark = I('post.remark');
    
            $data = array(
                'status' => $status,
                'remark' => $remark
            );
    
            $Apply = M("designer_apply");
            $Apply->where('id='.$id)->save($data);            
            
            if ($status == 1) {
                $apply = $Apply -> where('id='.$id) -> find();
                
                $Designer = M("designer");
                $designer = $Designer -> where("realname='{$apply['realname']}'") -> find();
                if (empty($designer)) {
                    $time = time();
                    $data = array(
                        'uid' => $apply['uid'],
                        'applyid' => $id,
                        'realname' => $apply['realname'],
                        'picture' => $apply['picture'],
                        'tel' => $apply['phone'],
                        'created' => $time,
                        'changed' => $time
                    );                     
                    $Designer->add($data);
                }                
            }            
            $this->success('更新成功',U('Admin/Designer/apply'));
    
        } else {
            if (empty($id)) {
                $this->error('非法操作，参数错误');
            }
            $Apply = M('designer_apply');
            $apply = $Apply -> where('id='.$id) -> find();
            $this->assign('apply', $apply);
            $this->display();
        }
    }
    
    /**
     * @desc 评价管理
     */
    public function comment() {
        if (isset($_GET['status'])) {
    
            $id = I('get.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('get.status');
    
            $time = time();
            $data['status'] = $status;
    
    
            $Topic = M("designer_works_comment");
            $Topic->where('id='.$id)->save($data);
            $this->success('更新成功',U('Admin/Designer/comment'));
    
        } else {
    
            $condstr = 1;
            $keyword = _get('keyword');
            if ($keyword) {
                $condstr .= " AND content LIKE '%$keyword%'";
            }
    
            $status = _get('status');
            if (is_numeric($status)) {
                $condstr .= " AND status=$status";
            }
    
            $id = _get('id');
            if (is_numeric($id)) {
                $condstr .= " AND topicid=$id";
            }
    
            $Comment = M('designer_works_comment');
            $count      = $Comment->where($condstr)->count();
            $Page       = new \Think\Page($count, 15);
            $show       = $Page->show();
    
            $comments = $Comment->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
            $this->assign('comments', $comments);
            $this->assign('page', $show);
            $this->display();
        }
    }

   
}
