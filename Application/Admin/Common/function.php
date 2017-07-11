<?php

/**
 * 递归重组节点信息为多维数组
 * @param $node 【要处理的节点】
 * @param int $pid 【父级id】
 * @return [tyoe] [description]
 */
function node_merge($node,$access=null,$pid = 0){
    $arr = array();
    foreach ($node as $v){

        if(is_array($access)){
            $v['access'] = in_array_case($v['id'],$access)? 1:0;
        }

        if($v['pid'] == $pid){
            $v['child'] = node_merge($node,$access,$v['id']);
            $arr[] = $v;
        }
    }
    return $arr;
}

function _post($key) {
    return addslashes(trim($_POST[$key]));
}

function _get($key) {
    return addslashes(trim($_GET[$key]));
}

/**
 * @desc 获取广告类型
 * @return boolean|array
 * 2017-2-8
 */
function getBannerType() {
    return array(
       1 => '首页',
       2 => '店铺',
       3 => '人才对接',
       4 => '商家云盟'
    );
}
/**
 * @desc 获取广告位置
 * @return boolean|array
 * 2017-2-8
 */
function getBannerLocation() {
    return array(
        1 => '头部',
        2 => '中部',
        3 => '底部'
    );
}
/**
 * @desc 获取订单购买物类型
 * @return boolean|array
 * 2017-2-14
 */
function getOrderType() {
    return array(
        1 => '商品',
        2 => '活动',
        3 => '课程',
        4 => '维币',
        5 => '企业入驻'
    );
}

/**未使用
 * @desc 获取配置字典键值对
 * @param string $name
 * @return boolean|array
 * 2017-1-
 */
function getDictDisposition($name) {
    if (empty($name)) { return false; }
    $dict = M('dict_disposition');
    $condstr = "`status`=1 AND `name`='$name'";
    $arr = $dict->where($condstr)->field('id')->find();
    if (empty($arr['id'])) { return false; }
    $condstrs = "`pid`={$arr['id']} AND `status`=1";
    $result = $dict->where($condstrs)->order('id ASC')->field('id,name,value')->select();
    if (empty($result)) { return false; }
    $resultArr  = array();
    foreach($result AS $v){
        $resultArr[$v['id']]  =$v['name'];
    }
    if (empty($resultArr)) { return false; }
    return $resultArr;
}


///**
// * @desc 获取配置字典键值对
// * @param string $name
// * @return boolean|array
// * 2017-1-4
// */
//function getDictDispositions($name) {
//    if (empty($name)) { return false; }
//    $dict = M('dict_disposition');
//    $condstr = "`status`=1 AND `name`='$name'";
//    $arr = $dict->where($condstr)->field('id')->find();
//    if (empty($arr['id'])) { return false; }
//    $condstrs = "`upid`={$arr['id']} AND `status`=1";
//    $result = $dict->where($condstrs)->order('id ASC')->field('name,value')->select();
//    if (empty($result)) { return false; }
//}

function getUsername($uid) {
    $User = M('user');
    $user = $User -> where('id='.$uid) -> find();
    return $user['name'];
}
function getTopic($topicid) {
    $Topic = M('topic');
    $topic = $Topic -> where('id='.$topicid) -> find();
    return $topic['title'];
}
function getArea($id) {
    $Area = M('dict_area');
    $area = $Area -> where('id='.$id) -> find();
    return $area['name'] ? $area['name'] : '无';
}
//获取配置项名称
function getDis($id) {
    $Dis = M('dict_disposition');
    $dis = $Dis -> where('id='.$id) -> find();
    return $dis['name'] ? $dis['name'] : '无';
}

//获取配置项目
function getDictTypes($name) {
    $Dis = M('dict_disposition');
    $dis = $Dis -> where("name ='{$name}' ") -> find();
    $id = $dis['id'];
    $dispostion = $Dis->where("pid = {$id} AND status=1")->select();

    return $dispostion ? $dispostion:array();
}
//获取父级地区元素
function getAreas() {
    $Area= M('dict_area');
    $areas= $Area -> where('pid=0') -> select();
    return !empty($areas) ? $areas : array();
}
?>