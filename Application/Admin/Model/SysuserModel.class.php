<?php
namespace Admin\Model;

//角色模型类
use Think\Model\RelationModel;

class SysuserModel extends RelationModel{


    //表单自动验证
    protected $_validate = array(
        array('email','require','登录账号必填！',1,'regex',3),
        array('email','checkEmail','登录账号已存在，请换一个！',1,'callback',3),

        array('name','require','昵称必填！',1,'regex',3),
        array('name','checkName','昵称已存在，请换一个！',1,'callback',3),

        array('password','require','密码必填！',1,'regex',3),
        //array('role_id[0]','checkRole','所属角色必选一个！',1,'callback',3),
    );

    //检验角色名称的唯一性
    function checkEmail(){
        $role = M('sysuser');

        if(empty($_POST['id'])){//添加角色
            if ($role->getByEmail($_POST['email'])){//存在角色
                return false;
            }else{
                return true;
            }
        }else{//修改角色-判断与他人的角色名是否相同
            if($role->where("id != {$_POST['id']} and email ='{$_POST['email']}'")->find()){
                return false;
            }else{
                return true;
            }


        }
    }

    //检验角色名称的唯一性
    function checkName(){
        $role = M('sysuser');

        if(empty($_POST['id'])){//添加角色
            if ($role->getByName($_POST['name'])){//存在角色
                return false;
            }else{
                return true;
            }
        }else{//修改角色-判断与他人的角色名是否相同
            if($role->where("id != {$_POST['id']} and name ='{$_POST['name']}'")->find()){
                return false;
            }else{
                return true;
            }


        }
    }

    //所属角色必选一个
    function checkRole(){
        if($_POST['role_id'][0] == ''){
            return false;
        }else{
            return true;
        }
    }
}



?>