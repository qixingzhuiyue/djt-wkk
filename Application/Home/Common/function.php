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
//订单购买物品类型
function getOrderType() {
    return array(
        1 => '商品',
        2 => '活动',
        3 => '课程',
        4 => '维币',
        5 => '企业入驻'
    );
}
//关键字模板添加链接替换
function getKeyWords() {
    return array(
        '企业推广平台',
        '网络营销平台',
        '自助建站',
        '企业家社群',
        '企业生态圈',
        '上海首扬',
        '首扬信息',
//        '企业网站建设',
//        '企业网站推广',
        '上海网站建设',
        '企业建站',
        '落地为王',
        '维沃珂',
        '联合办公',
        '企业网站',
        '上海网站建设',
        '嘉定网站建设',
        '企业论坛',
        '活动发布',
        '活动召集',
        '企业交流',
        '微信代运营',
        '企业自助推广营销平台',
    );
}
function post_fsockopen($url,$post='',$flag = 0,$sync = 0,$header='' ,$cookie = ''){
    $ip = '';
    $timeout = 10;
    $limit = 500000;
    $matches = parse_url($url);

    !isset($matches['host']) && $matches['host'] = '';
    !isset($matches['path']) && $matches['path'] = '';
    !isset($matches['query']) && $matches['query'] = '';
    !isset($matches['port']) && $matches['port'] = '';
    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;

    if($flag == 0){
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: '.strlen($post)."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        if(empty($cookie)){
            $out .= "Cookie: ''\r\n\r\n";
        }else{
            $out .= "Cookie: $cookie";
        }
        $out .= $post;
    }else{
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: keep-alive\r\n";
        $out .= "Referer: http://www.kuaidi100.com/\r\n";
        $out .= "X-Requested-With: XMLHttpRequest\r\n";
        $out .= "Cookie: ''\r\n\r\n";
        $out .= $post;
    }

    $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
    if(!$fp) {
        return false;
    } else {
        stream_set_blocking($fp, TRUE);
        stream_set_timeout($fp, $timeout);
        @fwrite($fp, $out);
        if($sync != 0){
            @fclose($fp);
            return true;
        }
        $status = stream_get_meta_data($fp);
        if(!$status['timed_out']) {
            while (!feof($fp)) {
                if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
                    break;
                }
            }

            $stop = false;
            $return = '';
            while(!feof($fp) && !$stop) {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $return .= $data;
                if($limit) {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        @fclose($fp);
        return $return;
    }
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
 * @desc 获取图片应用模板类型：
 * @return boolean|array
 * 2017-2-8
 */
function getPictureType() {
    return array(
        1 => '企业模板1',
        2 => '企业模板2',
        3 => '企业模板3',
        4 => '店铺模板1',
        5 => '店铺模板2',
        6 => '店铺模板3',
        7 => '企业展示'
    );
}

/**
 * @desc 获取配置字典键值对
 * @param string $name
 * @return boolean|array
 * 2017-1-4
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
//获取地区名称
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

//获取配置项值
function getDisValue($name) {
    $Dis = M('dict_disposition');
    $dis = $Dis -> where("name ='{$name}' ") -> find();
    return $dis ? $dis : array();
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

//获取单个维币任务
function getTask($name) {
    $Task =  M('vcoin_task');
    $task = $Task->where("action = '{$name}' AND status = 1")->find();
    return !empty($task) ? $task : array();
}
//获取用户维币值，实时更新
function getVcoin() {
    $info = session("userinfo");
    if(empty($info)){
        return false;
    }
    $uid = $info['uid'];
    $vcoin = M("person_info")->where("uid = {$uid}")->field("vcoin")->find();
    $vcoin = (int)$vcoin['vcoin'];
    return $vcoin;
}
function imageEdit($url,$repalce,$width=1920,$height=550){
    if(empty($repalce)){
        return false;
    }
    if(file_exists('./Public'.$url)){
        $url1 = str_replace('.',$repalce,$url,$i);
        if(file_exists('./Public'.$url)){
          return $url1;
        }else{
            $image = new \Think\Image();
            $image->open('./Public'.$url);
            $image->thumb($width, $height,\Think\Image::IMAGE_THUMB_FIXED)->save('./Public'.$url1);
           return $url1;
        }
    }else{
        return false;
    }
}

?>