<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class SysuserRelationModel extends RelationModel{

    //定义主表名称
    protected $tableName = 'sysuser';

    //定义关联关系
    protected $_link = array(
        'role' => array(
        'mapping_type'      =>  self::MANY_TO_MANY, //关联方式
        //'class_name'        =>  'Group',
        //'mapping_name'      =>  'groups',
        'foreign_key'       =>  'user_id',  //外键
        'relation_foreign_key'  =>  'role_id', //关联键
        'mapping_fields' => 'id,name,remark', //读取role中的字段
        'relation_table'    =>  'ww_role_user' //此处应显式定义中间表名称，且不能使用C函数读取表前缀
        )
    );
}


?>