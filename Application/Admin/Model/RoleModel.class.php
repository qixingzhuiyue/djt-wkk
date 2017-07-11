<?php
namespace Admin\Model;
use Think\Model;
use Think\Modle;

//角色模型类
class RoleModel extends Model{

    //表单自动验证
    protected $_validate = array(
        array('name','require','角色名称必填！',1,'regex',3),
        array('name','checkName','角色名称已存在，请换一个！',1,'callback',3),
        array('remark','require','角色描述必填',1,'regex',3),
    );

    //检验角色名称的唯一性
    function checkName(){
        $role = M('role');

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
}



?>