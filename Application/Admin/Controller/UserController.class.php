<?php
/**
 * @desc 用户管理
 */
namespace Admin\Controller;
use Admin\Controller\CommonController;
class UserController extends CommonController {

    /**
     * @desc 用户首页
     */
    public function index() {
        $condstr = 1;
        $keyword = _get('keyword');
        if ($keyword) {
            $condstr .= " AND (phone LIKE '%$keyword%' OR username LIKE '%$keyword%')";
        }
        $status = _get('status');
        if (is_numeric($status)) {
            $condstr .= " AND status=$status";
        }
        $type = _get('type');
        if (is_numeric($type)) {
            $condstr .= " AND type=$type";
        }
        
        $role = _get('role');
        if (is_numeric($role)) {
            $condstr .= " AND role=$role";
        }
        //echo $condstr;exit;
        $User = M('Users');
        $count      = $User->where($condstr)->count();
        $Page       = new \Think\Page($count, 15);
        $show       = $Page->show();
        $users = $User->where($condstr)->order('created')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($users AS &$v) {
            $v['name'] = empty($v['name']) ? '暂无昵称' : $v['name'];
        }
//        var_dump($condstr);EXIT;
        $this->assign('users', $users);
        $this->assign('page', $show);
        $this->display();    
    }
    
    /**
     * @desc 用户查看
     */
    public function view($id) {
        if (IS_POST) {
            $id = I('post.id');
            if (empty($id)) {
                $this->error('参数错误1');
            }
            $status = I('post.status');        
            if (!is_numeric($status)) {
                $this->error('参数错误2');
            }

            $User = M("Users");
            $data['status'] = $status;
            $User->where('uid='.$id)->save($data);
            $this->success('更新成功',U('Admin/User/index'));
        } else {
            $User = M('Users');
            $user = $User -> where('uid='.$id) -> find();
            $this->assign('user', $user);
            $this->display();
        }        
    }

   
}
