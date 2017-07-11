<?php
namespace Admin\Controller;
use Org\Util\Rbac;
use Think\Controller;
class LoginController extends Controller {

    //后台登录页面
    public function index(){
        if(empty($_SESSION[C('USER_AUTH_KEY')])) {
            $this->display();
        }else{
            $this->redirect('Admin/Index/index');
        }
    }

    //登录表单操作
    public function login(){
        //判断不能为空
        if(!IS_POST) E('页面不存在');
        if(empty($_POST['email']) || empty($_POST['password'])){
            $this->error('账号或密码不能为空');
        }

        //账号密码检测
        $db = M('sysuser');
        $msg = $db->where(array('email'=>I('email')))->find();
        if(!$msg || $msg['password'] != I('password','','md5')){
            $this->error('账号或密码错误');
        }

        //管理员账号检测
        if ($msg['status'] == 0) $this->error('账号被锁定');

        //更新登录时间和ip并存入session
        $data = array(
            'id' => $msg['id'],
            'logined' => time(),
            'ip' => get_client_ip()
        );
        $db->save($data);
        session(C('USER_AUTH_KEY'),$msg['id']);
        session('email',$msg['email']);
        session('name',$msg['name']);
        session('logined',date('Y-m-d H:i:s',$msg['logined']));
        session('ip',$msg['ip']);

        //超级管理员
        if($msg['email'] == C('RBAC_SUPERADMIN')){
            session(C('ADMIN_AUTH_KEY'),true);
        }

        //读取用户权限
        Rbac::saveAccessList();

        $this->success('登录成功',U('Admin/Index/index'));
    }
}