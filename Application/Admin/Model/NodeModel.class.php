<?php
namespace Admin\Model;
use Think\Model;
use Think\Modle;

//角色模型类
class NodeModel extends Model{

    //表单自动验证
    protected $_validate = array(
        array('name','require','名称必填！',1,'regex',3),
        array('title','require','描述必填',1,'regex',3),
        array('sort','require','排序不能为空',1,'regex',3),
    );


}



?>