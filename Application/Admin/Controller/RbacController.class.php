<?php
namespace Admin\Controller;
use Admin\Controller\CommonController;
class RbacController extends CommonController {

    //用户列表
    public function index(){
        $this->sysuser = D('Sysuser')->field('password',true)->relation(true)->select();
        $this->display();
    }

    //改变用户状态
    public function changeStatus(){
        if(IS_GET){
            if(M('sysuser')->save($_GET)){
                $this->success('修改成功',U('Admin/Rbac/index'));
            }else{
                $this->error('修改失败');
            }
        }
    }
    //添加用户表单处理
    public function changePwd(){
        if(IS_POST){
            $sysuser = M('sysuser');
            $id = (int)I('post.id');
            $password = I('post.password');
            $newpwd = I('post.newpwd');
            $repwd = I('post.repwd');
            $uid = (int)$_SESSION[C('USER_AUTH_KEY')];
            $flag = true;
            $msg = '';
            if(empty($id)){
                $flag = false;
                $msg .= '用户ID,';
            }
            if(empty($password)){
                $flag = false;
                $msg .= '用户原密码,';
            }
            if(empty($newpwd)){
                $flag = false;
                $msg .= '用户新密码,';
            }
            if(!$flag){
                $msg = trim($msg,',');
                $msg.="不能为空";
                $this->error($msg);
            }
            if($id!==$uid){
                $this->error('参数错误');
            }
            if(strlen($newpwd)<6){
                $this->error('密码长度不能小于6位');
            }
            if($newpwd!=$repwd){
                $this->error('密码前后不一致');
            }
            $user = M("sysuser")->where("id={$id} AND status=1")->find();
            if(empty($user)) {
                $this->error("当前用户不可操作");
            }
            if(md5($password)!=$user['password']){
                $this->error('原密码错误');
            }
                $data = array(
                    'password' => md5($newpwd)
                );
                $result = $sysuser->where("id={$id}")->save($data);
                //修改密码
                if($result===false){
                    $this->error('修改失败');
                }else{
                    $this->success('修改成功',U('Admin/Rbac/index'));
                }

        }else{
            $id = (int)_get('id');
            $uid = (int)$_SESSION[C('USER_AUTH_KEY')];
            if(empty($id)||($id!==$uid)){
                $this->error('参数错误');
            }
            $user = M("sysuser")->where("id={$id} AND status=1")->find();
            if(empty($user)){
                $this->error("当前用户不可修改密码");
            }
            $this->assign('user',$user);
            $this->display();
        }
    }
    //删除用户
        public function delUser(){
            if(IS_GET){
                if(M('sysuser')->delete((int)$_GET['id'])){
                    $this->success('删除成功',U('Admin/Rbac/index'));
                }else{
                    $this->error('删除失败');
                }
            }
        }
    //角色列表
    public function role(){
        $this->role = M('role')->select();
        $this->display();
    }

    //节点列表
    public function node(){
        $field = array('id','name','title','pid');
        $node = M('node')->field($field)->order('sort')->select();
        $this->node =$node= node_merge($node);
        $this->display();
    }

    //添加用户
    public function addUser(){
        $this->role = M('role')->select();
        $this->display();
    }

    //添加用户表单处理
    public function addUserHandle(){
       if(IS_POST){
           $sysuser = D('Sysuser');
           if($sysuser->create()){
               $user = array(
                   'email'    => I('email'),
                   'name'     => I('name'),
                   'password' => I('password','','md5'),
                   'status'   => I('status'),
                   'created'  => time(),
                   'ip'       =>get_client_ip()
               );
               $role = array();

               //添加用户
               if($uid = M('sysuser')->add($user)){
                   foreach ($_POST['role_id'] as $v){
                       $role[]=array(
                           'role_id' =>$v,
                           'user_id' =>$uid
                       );
                       M('role_user')->addAll($role);
                       $this->success('添加成功',U('Admin/Rbac/index'));
                   }
               }else{
                   $this->error('添加失败');
               }
           }else{
               $this->error($sysuser->getError());
           }

       }
    }


    //添加角色
    public function addRole(){
        $this->display();
    }

    //添加角色表单处理
    public function addRoleHandle(){
        $role = D('Role');
        if($role->create()){
            if(M('role')->add($_POST)){
                $this->success('添加成功',U('Admin/Rbac/role'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($role->getError());
        }
    }

    //添加节点
    public function addNode(){
        $this->pid = I('pid',0,'intval');
        $this->level = I('level',1,'intval');

        switch ($this->level){
            case 1:
                $this->type = '应用';
                break;
            case 2:
                $this->type = '控制器';
                break;
            case 3:
                $this->type = '动作方法';
                break;
        }
        $this->display();
    }

    //添加节点表单处理
    public function addNodeHandle(){
        $role = D('Node');
        if($role->create()){
            if(M('node')->add($_POST)){
                $this->success('添加成功',U('Admin/Rbac/node'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error($role->getError());
        }
    }

    //配置权限
    public function access(){
        $rid = I('rid',0,'intval');
        $field = array('id','name','title','pid');
        $node = M('node')->order('sort')->field($field)->select();

        //原有权限
        $access = M('access')->where(array('role_id' => $rid))->getField('node_id',true);
        $this->node = node_merge($node,$access);
        $this->rid = $rid;
        $this->display();
    }

    //修改权限
    public function setAccess(){
        $rid = I('rid',0,'intval');
        $db = M('access');

        //清空原权限
        $db->where(array('role_id' => $rid))->delete();

        //组合新权限
        $data = array();
        foreach ($_POST['access'] as $v){
            $tmp = explode('_',$v);
            $data[] = array(
                'role_id'=>$rid,
                'node_id'=>$tmp[0],
                'level'=>$tmp[1]
            );
        }

        //插入新权限
        if($db->addAll($data)){
            $this->success('修改成功',U('Admin/Rbac/role'));
        }else{
            $this->error('修改失败');
        }
    }
}
