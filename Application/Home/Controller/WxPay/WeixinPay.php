<?php
//线上支付流程说明见 https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=8_3
//微信支付相关接口
class WeixinPay
{
    public $Conf=array();//预付款订单必选字段
    public $key='';//商户平台KEY 需要跟商户平台同步修改

    public function __construct($c=null)
    {
        $trade_type= $c['trade_type']? $c['trade_type']:'APP';
        $this->Conf= array(
            'appid' => '',
            'mch_id' => '',
            'nonce_str' => md5(microtime() . rand(1, 999999999)),
            'body'=>'商品描述',
            'out_trade_no'=>'',
            'total_fee'=>'',
            'spbill_create_ip'=>'',
            'notify_url' => '',
            'trade_type'=> $trade_type
        );
    }
    public function __destruct()
    {

    }
    //抓取请求
    function _Curl($Url,$post){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 86400);
        //https请求
        /*curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);*/
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
    //获取微信预支付信息
    function GetPrePayMsg(){
        $url='https://api.mch.weixin.qq.com/pay/unifiedorder';
        $Conf= $this->Conf;
        $Conf['sign']= $this->Sign($Conf);
        $xml=$this->ArrToXml($Conf);
        $backXML=$this->_Curl($url, $xml) ;
        $backARR=$this->XmlToArray($backXML);
        if(!$backARR['prepay_id'])
            return false;
        //构造调其微信支付数据
        $PayConf=array(
            'appid'=> $this->Conf['appid'],
            'partnerid' => $this->Conf['mch_id'],
            'prepayid' => $backARR['prepay_id'],
            'noncestr' => md5(microtime() . rand(1, 999999999)),
            'timestamp' =>time(),
            'package' =>'Sign=WXPay'
        );
        $PayConf['sign']=$this->Sign($PayConf);
        return $PayConf;
    }

    //根据必选参数生成  统一下单 接口数据
    function ArrToXml($arr)
    {
        $xml = '<xml>';
        foreach ($arr as $k => $v) {
            $xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
        }
        $xml .= '</xml>';
        return $xml;
    }
    //加密字符
    function Sign($arr){
        ksort($arr);
        $TmpArr = array();
        foreach ($arr as $k => $v) {
            if ($v)
                $TmpArr[] = $k . '=' . $v;
        }
        //添加KEY
        $TmpArr[] = 'key=' . $this->key;
        return strtoupper(md5(join('&', $TmpArr)));
    }
    //编码XML值
    private function XmlEncode($tag)
    {
        $tag = str_replace("&", "&amp;", $tag);
        $tag = str_replace("<", "&lt;", $tag);
        $tag = str_replace(">", "&gt;", $tag);
        $tag = str_replace("'", "&apos;", $tag);
        $tag = str_replace("\"", '&quot;', $tag);
        return $tag;
    }
    //将XML转化为数组
    function XmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $this->values;
    }
}
