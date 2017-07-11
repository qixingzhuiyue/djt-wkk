<?php
namespace Home\Controller;
use Think\Controller;
use Org\Qrcode;

use Think\Crypt\Driver\Think;
use Think\Model;
use Org\Util;
use Home\Controller\WxJsPayController;
use Home\Controller\CommonController;
//定义时间
ini_set('date.timezone','Asia/Shanghai');
//定义引导路径
//ini_set('include_path','/var/www/html');

//require_once APP_PATH."WxPay/lib/Wxpay.Api.php";
//include __ROOT__."/Application/Home/Controller/WxPay/lib/WxPay.Api.php";
require_once "WxPay/lib/WxPay.Api.php";
require_once "WxPay/lib/WxPay.Notify.php";
//require_once "/ruanron/WxPay/lib/WxPay.Data.php";
require_once "WxPay/lib/WxPay.NativePay.php";

//
require_once "WxPay/WeixinPay.php";

class WxpayController extends CommonController {

    public function index(){
        echo __ROOT__;
        echo "111111111";
    }

   /*微信jssdk支付开始*/
    public function UnifiedOrder(){
        $jssdk = new JsSdkController;
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $ordernum = _get('ordernum');
        $Wxjp = new WxJsPayController();
        $Wxjp->UnifiedOrder($ordernum);
    }
    public function GetOpenid()
    {
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }
    /**
     *
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     *
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = \WxPayConfig::APPID;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }
    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     *
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     *
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(\WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
            && \WxPayConfig::CURL_PROXY_PORT != 0){
            curl_setopt($ch,CURLOPT_PROXY, \WxPayConfig::CURL_PROXY_HOST);
            curl_setopt($ch,CURLOPT_PROXYPORT, \WxPayConfig::CURL_PROXY_PORT);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;
    }
    /**
     *
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     *
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = \WxPayConfig::APPID;
        $urlObj["secret"] = \WxPayConfig::APPSECRET;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }
//    public function jsapi(){
////打印输出数组信息
//        function printf_info($data)
//        {
//            foreach($data as $key=>$value){
//                echo "<font color='#00ff55;'>$key</font> : $value <br/>";
//            }
//        }
//
////①、获取用户openid
//        $tools = new JsApiPay();
//        $openId = $tools->GetOpenid();
//
////②、统一下单
//        $input = new WxPayUnifiedOrder();
//        $input->SetBody("test");
//        $input->SetAttach("test");
//        $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
//        $input->SetTotal_fee("1");
//        $input->SetTime_start(date("YmdHis"));
//        $input->SetTime_expire(date("YmdHis", time() + 600));
//        $input->SetGoods_tag("test");
//        $input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
//        $input->SetTrade_type("JSAPI");
//        $input->SetOpenid($openId);
//        $order = WxPayApi::unifiedOrder($input);
//        echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
//        printf_info($order);
//        $jsApiParameters = $tools->GetJsApiParameters($order);
//
////获取共享收货地址js函数参数
//        $editAddress = $tools->GetEditAddressParameters();
//
////③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
//        /**
//         * 注意：
//         * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
//         * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
//         * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
//         */
//    }



    /**
     * 微信支付
     * 2017-2-25
     * djt
     */
    public function payVcoin(){
       $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录，请先登录");
        }
        $uid = $info['uid'];
        //不同支付用途分类 1购买维币 2企业入驻 3,活动支付，4商品支付
        $type = I("get.type");
        if(empty($type)){
            $this->error("类型参数错误");
        }
        if(!in_array($type,array(1,2,3,4))){
            $this->error("支付商品类型出错");
        }
        if($type==1){//购买维币
            //一元兑换维币数
            $vnum = C("VCOIN_NUM");
            $ordernum = I("get.ordernum");
            if(empty($ordernum)){
                $this->error("参数错误");
            }
            $order = M("order")->where("ordernum ='{$ordernum}' AND type=4 AND orderstatus = 0")->find();
//            echo M('order')->getLastSql();
            if(empty($order)){
                $this->error('订单不存在',U('Home/Vcoin/myWallet'));
            }
            //支付金额
            $amount = $order['amount'];
            if(empty($amount)){
                $this->error('金额不能为0');
            }
//            $amount = 0.01;
//            $ordernum = date("YmdHis").rand(1000,10000);
//            $data = array(
//                'uid' => $uid,
//                'ordernum' => $ordernum,
//                'orderdate' => time(),
//                'payment' => 'wxpay',
//                'descp' => '微信购买维币',
//                'amount' => $amount,
//                'type' => 4,
//                'seller' => C("ORGANIZER"),
//                'created' => time(),
//                'updated' => time()
//            );
//            //生成订单
//            $db_order = M("order");
//            $result = $db_order->add($data);
//            if(empty($result)){
//                $this->error("生成订单失败");
//            }
            //生成微信订单
            $wxordernum = date("YmdHis").rand(1000,9000);
            $wxdata = array(
                "wxordernum"=>$wxordernum,
                "ordernum"=>$ordernum,
                "created"=>time(),
                "updated"=>time()
            );
            $wxreult = M("wxordernum")->add($wxdata);
            if(empty($wxreult)){
                $this->error("微信订单生成失败");
            }
            //实例化
            $input = new \WxPayUnifiedOrder();
            $input->SetBody("企业自助推广营销平台维币");
            $input->SetAttach("企业自助推广营销平台维币");
            //old订单号,生成随机数，回调匹配使用，写入到数据库中
//        $Out_trade_no=\WxPayConfig::MCHID.date("YmdHis").substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 6, 8);
            $Out_trade_no = $wxordernum;
            //存入微信支付订单号
//        $db_order->where("order_num='".$find_order['order_num']."'")->save(array("out_trade_no"=>$Out_trade_no));
            //$f['ali_trade_no']=$Out_trade_no;
            $input->SetOut_trade_no($Out_trade_no);
            $input->SetTotal_fee($amount*100);
            //$input->SetTotal_fee(1);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            //$input->SetGoods_tag($data['username']);
            $input->SetGoods_tag('myname');
            $input->SetNotify_url("http://www.91wework.com/index.php/Home/Wxpay/callback_orders");
            $input->SetTrade_type("NATIVE");
            //$input->SetProduct_id($data['order_num']);
            $input->SetProduct_id($wxordernum);
            $notify = new \NativePay();

            $result = $notify->GetPayUrl($input);
            $url_img = $result["code_url"];
            $QRcode = new \Org\Util\QRcode();
            // var_dump(C('W_DOMAIN')."/Login/register?fromuid=".$this->name);exit;
            $str = $wxordernum.rand(1000,9999);
            $QRcode->png($url_img,"./Public/qrcode/".$str."qrcode.png", 4,8);
            $code_img = "/Public/qrcode/".$str."qrcode.png";
//            $code_img = C('Wxreturn_url')."/WxPay/example/qrcode.php?data=".urlencode($url_img);

            //ajax 调用回传
            //$this->ajaxReturn(array("status"=>1,"info"=>$code_img));
            //echo "<img src='".$code_img."'>";

            $this->assign('wxpay_code',$code_img);
            $this->assign('order',$order);

//        $this->header_bottom();
//        var_dump($data);
        }
        if($type==2){
            $uid = $info['uid'];
            $ordernum = I("get.ordernum");
//            $wxordernum = I("post.wxordernum");
            if(empty($ordernum)){
                $this->error("参数错误");
            }
            $order = M("order")->where("ordernum ='{$ordernum}' AND type=5 AND orderstatus = 0")->find();
//            echo M('order')->getLastSql();
            if(empty($order)){
                $this->error('订单不存在',U('Home/Index/index'));
            }
            //刷新更新微信支付订单号并记录与平台订单的关系
            $wxordernum = date("YmdHis").rand(1000,9000);
            $data = array(
                "wxordernum"=>$wxordernum,
                "ordernum"=>$ordernum,
                "created"=>time(),
                "updated"=>time()
            );
            $reult = M("wxordernum")->add($data);
            if(empty($reult)){
                $this->error("微信订单生成失败");
            }
            //支付金额
            $amount = round($order['amount'],2);
            if(empty($amount)){
                $this->error('金额有误');
            }
            //实例化
            $input = new \WxPayUnifiedOrder();
            $input->SetBody("平台入驻支付");
            $input->SetAttach("平台入驻支付");
            //old订单号,生成随机数，回调匹配使用，写入到数据库中
//        $Out_trade_no=\WxPayConfig::MCHID.date("YmdHis").substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 6, 8);
            $Out_trade_no = $wxordernum;
            //存入微信支付订单号
//        $db_order->where("order_num='".$find_order['order_num']."'")->save(array("out_trade_no"=>$Out_trade_no));
            //$f['ali_trade_no']=$Out_trade_no;
            $input->SetOut_trade_no($Out_trade_no);
            $input->SetTotal_fee($amount*100);
            //$input->SetTotal_fee(1);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            //$input->SetGoods_tag($data['username']);
            $input->SetGoods_tag('myname');
            $input->SetNotify_url("http://www.91wework.com/index.php/Home/Wxpay/callback_orders");
            $input->SetTrade_type("NATIVE");
            //$input->SetProduct_id($data['order_num']);
            $input->SetProduct_id($wxordernum);
            $notify = new \NativePay();

            $result = $notify->GetPayUrl($input);
//        var_dump($result);
            $url_img = $result["code_url"];
//        $Qr = new \Org\Qrcode\QRcode();
//        $code_img =  $Qr::png($url_img);
            $code_img = C('Wxreturn_url')."/WxPay/example/qrcode.php?data=".urlencode($url_img);

            //ajax 调用回传
            //$this->ajaxReturn(array("status"=>1,"info"=>$code_img));
            //echo "<img src='".$code_img."'>";

            $this->assign('wxpay_code',$code_img);
            $this->assign('order',$order);

//        $this->header_bottom();
//        var_dump($data);
        }
        if($type==3){
            $uid = $info['uid'];
            $ordernum = I("post.ordernum");
            if(empty($ordernum)){
                $this->error("参数错误");
            }
            //对应关系是否正确
            $wx = M("wxordernum")->where("ordernum={$ordernum} AND wxordernum={$wxordernum}")->find();
            if(empty($wx)){
                $this->error("参数出错1");
            }
            //查询微信生成订单号查询
            $order=M("order")->where("ordernum={$ordernum}")->find();
            if(empty($order)){
                $this->error("该订单不存在");
            }
            //支付金额
            $amount = $order['amount'];
            //实例化
            $input = new \WxPayUnifiedOrder();
            $input->SetBody("维沃珂平台消费支付");
            $input->SetAttach("维沃珂平台消费支付");
            //old订单号,生成随机数，回调匹配使用，写入到数据库中
//        $Out_trade_no=\WxPayConfig::MCHID.date("YmdHis").substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 6, 8);
            $Out_trade_no = $wxordernum;
            //存入微信支付订单号
//        $db_order->where("order_num='".$find_order['order_num']."'")->save(array("out_trade_no"=>$Out_trade_no));
            //$f['ali_trade_no']=$Out_trade_no;
            $input->SetOut_trade_no($Out_trade_no);
            $input->SetTotal_fee($amount*100);
            //$input->SetTotal_fee(1);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            //$input->SetGoods_tag($data['username']);
            $input->SetGoods_tag('myname');
            $input->SetNotify_url("http://www.91wework.com/index.php/Home/Wxpay/callback_orders");
            $input->SetTrade_type("NATIVE");
            //$input->SetProduct_id($data['order_num']);
            $input->SetProduct_id($wxordernum);
            $notify = new \NativePay();

            $result = $notify->GetPayUrl($input);
//        var_dump($result);
            $url_img = $result["code_url"];
//        $Qr = new \Org\Qrcode\QRcode();
//        $code_img =  $Qr::png($url_img);
            $code_img = C('Wxreturn_url')."/WxPay/example/qrcode.php?data=".urlencode($url_img);

            //ajax 调用回传
            //$this->ajaxReturn(array("status"=>1,"info"=>$code_img));
            //echo "<img src='".$code_img."'>";

            $this->assign('wxpay_code',$code_img);
            $this->assign('order',$order);

//        $this->header_bottom();
//        var_dump($data);
        }
        if($type==4){//购买商品
            $ordernum = I("get.ordernum");
            if(empty($ordernum)){
                $this->error("参数错误");
            }
            $order = M("order")->where("ordernum ='{$ordernum}' AND type=1 AND orderstatus = 0")->find();
//            echo M('order')->getLastSql();
            if(empty($order)){
                $this->error('订单不存在',U('Home/Order/orderMange'));
            }
            //支付金额
            $amount = $order['amount'];
            if(empty($amount)){
                $this->error('金额不能为0');
            }
            //生成微信订单
            $wxordernum = date("YmdHis").rand(1000,9000);
            $wxdata = array(
                "wxordernum"=>$wxordernum,
                "ordernum"=>$ordernum,
                "created"=>time(),
                "updated"=>time()
            );
            $wxreult = M("wxordernum")->add($wxdata);
            if(empty($wxreult)){
                $this->error("微信订单生成失败");
            }
            //实例化
            $input = new \WxPayUnifiedOrder();
            $input->SetBody("企业自助推广营销平台商品");
            $input->SetAttach("企业自助推广营销平台商品");
            //old订单号,生成随机数，回调匹配使用，写入到数据库中
//        $Out_trade_no=\WxPayConfig::MCHID.date("YmdHis").substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 6, 8);
            $Out_trade_no = $wxordernum;
            //存入微信支付订单号
//        $db_order->where("order_num='".$find_order['order_num']."'")->save(array("out_trade_no"=>$Out_trade_no));
            //$f['ali_trade_no']=$Out_trade_no;
            $input->SetOut_trade_no($Out_trade_no);
            $input->SetTotal_fee($amount*100);
            //$input->SetTotal_fee(1);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            //$input->SetGoods_tag($data['username']);
            $input->SetGoods_tag('myname');
            $input->SetNotify_url("http://www.91wework.com/index.php/Home/Wxpay/callback_orders");
            $input->SetTrade_type("NATIVE");
            //$input->SetProduct_id($data['order_num']);
            $input->SetProduct_id($wxordernum);
            $notify = new \NativePay();

            $result = $notify->GetPayUrl($input);
            $url_img = $result["code_url"];
            $QRcode = new \Org\Util\QRcode();
            // var_dump(C('W_DOMAIN')."/Login/register?fromuid=".$this->name);exit;
            $str = $wxordernum.rand(1000,9999);
            $QRcode->png($url_img,"./Public/qrcode/".$str."qrcode.png", 4,8);
            $code_img = "/Public/qrcode/".$str."qrcode.png";
//            $code_img = C('Wxreturn_url')."/WxPay/example/qrcode.php?data=".urlencode($url_img);

            //ajax 调用回传
            //$this->ajaxReturn(array("status"=>1,"info"=>$code_img));
            //echo "<img src='".$code_img."'>";

            $this->assign('wxpay_code',$code_img);
            $this->assign('order',$order);

//        $this->header_bottom();
//        var_dump($data);
        }
        $this->display('wxcode');
    }

    /**
     * 微信支付入驻 弃用
     * 2017-2-25
     * djt
     * 暂不用已经加到支付维币里
     */
    public function payCompany(){
       $info = session("userinfo");
        if(empty($info)){
            $this->error("您还未登录，请先登录");
        }
        $uid = $info['uid'];
        $ordernum = I("post.ordernum");
        $wxordernum = I("post.wxordernum");
        if(empty($ordernum)||empty($wxordernum)){
            $this->error("参数错误");
        }
        //对应关系是否正确
        $wx = M("wxordernum")->where("ordernum={$ordernum} AND wxordernum={$wxordernum}")->find();
        if(empty($wx)){
            $this->error("参数出错1");
        }
        //查询微信生成订单号查询
        $order=M("order")->where("ordernum={$ordernum}")->find();
        if(empty($order)){
            $this->error("该订单不存在");
        }
        //支付金额
        $amount = $order['amount'];
        //实例化
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("维沃珂入驻支付");
        $input->SetAttach("维沃珂入驻支付");
        //old订单号,生成随机数，回调匹配使用，写入到数据库中
//        $Out_trade_no=\WxPayConfig::MCHID.date("YmdHis").substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 6, 8);
        $Out_trade_no = $wxordernum;
        //存入微信支付订单号
//        $db_order->where("order_num='".$find_order['order_num']."'")->save(array("out_trade_no"=>$Out_trade_no));
        //$f['ali_trade_no']=$Out_trade_no;
        $input->SetOut_trade_no($Out_trade_no);
        $input->SetTotal_fee($amount*100);
        //$input->SetTotal_fee(1);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        //$input->SetGoods_tag($data['username']);
        $input->SetGoods_tag('myname');
        $input->SetNotify_url("http://www.91wework.com/index.php/Home/Wxpay/callback_orders");
        $input->SetTrade_type("NATIVE");
        //$input->SetProduct_id($data['order_num']);
        $input->SetProduct_id($wxordernum);
        $notify = new \NativePay();

        $result = $notify->GetPayUrl($input);
//        var_dump($result);
        $url_img = $result["code_url"];
//        $Qr = new \Org\Qrcode\QRcode();
//        $code_img =  $Qr::png($url_img);
        $code_img = C('Wxreturn_url')."/WxPay/example/qrcode.php?data=".urlencode($url_img);

        //ajax 调用回传
        //$this->ajaxReturn(array("status"=>1,"info"=>$code_img));
        //echo "<img src='".$code_img."'>";

        $this->assign('wxpay_code',$code_img);
        $this->assign('order',$order);

//        $this->header_bottom();
//        var_dump($data);
        $this->display('wxcode');
    }
    /**
     * 微信扫码支付维币回调地址
     * 2017-2-25
     * djt
     */
    public function callback_orders(){
        //获取微信回调数据
        $notify = new \WxPayNotify();
        $xml = file_get_contents('php://input', 'r');
//        $xml ="<xml><appid><![CDATA[wx34e1042319d0b7b8]]></appid>
//<attach><![CDATA[维沃珂入驻支付]]></attach>
//<bank_type><![CDATA[CFT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[Y]]></is_subscribe>
//<mch_id><![CDATA[1325955201]]></mch_id>
//<nonce_str><![CDATA[92ync6v65c4lr16109lpadydvhga4iz7]]></nonce_str>
//<openid><![CDATA[oDGx3xJ9V9Z5xZ1gniU_BvbhhjO0]]></openid>
//<out_trade_no><![CDATA[20170306205345999]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[A2578240265DDA2E949428F9629BB663]]></sign>
//<time_end><![CDATA[20170306210953]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[NATIVE]]></trade_type>
//<transaction_id><![CDATA[4007542001201703062473530648]]></transaction_id>
//</xml>";

        //xml数据转数组
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        $data['xmld'] = $xml;
        $data['postd'] = json_encode($_POST);
        $data['getd'] = json_encode($_GET);
        $data['status'] = json_encode($_SERVER);
        $data['created'] = time();
        $data['updated'] = time();
        //保存订单处理结果，处理成功为1,处理失败为2
        $data['status'] = 2;
        //保存处理结果，默认为fail,执行成功改为success
        $data['result'] = 'fail';
        //签名+状态验证
        //if($notify->checksign() && $result->return_code == "SUCCESS" && $result->result_code == "SUCCESS"){
        if($result['return_code'] == "SUCCESS" && $result['result_code'] == "SUCCESS"){
            //在微信订单号和系统订单号表wxordernum中更新状态为已支付
           //测试订单号设置
//            $result['out_trade_no'] = '201704261649147985';
            M("wxordernum")->where("wxordernum={$result['out_trade_no']}")->save(array('status'=>1));
            //保存验证结果
            $data['result'] = 'success';
            //更改订单支付状态
            //查询微信订单对应的系统订单
            $wx = M("wxordernum")->where("wxordernum={$result['out_trade_no']}")->find();
            $db_order=M('order');
            //查询微信生成订单号查询
            $order=$db_order->where("ordernum='".$wx['ordernum']."'")->find();
            //保存订单号 订单id
            $data['orderid'] = $order['id'];
            $data['ordernum'] = $result['out_trade_no'];
            //一元维币购买数
            $num = C("VCOIN_NUM");
            if($order['orderstatus'] == 0){
                //购买维币或者企业入驻
                if($order['type']==4||$order['type']==5){
                    $amount = $order['amount'];
                    $coin = (int)round($amount*$num);
                    //增加用户维币数
                    $uid = $order['uid'];
                    $coinResult = M("person_info")->where("uid = {$uid}")->setInc("vcoin",$coin);
                    //如果订单为入驻订单修改入驻企业的支付状态
                    $comstatus = true;
                    if($order['type']==5){
                        $companyData = array("ispay"=>1,'status'=>1);
                        $companyResult = M("company_info")->where("uid={$uid}")->save($companyData);
                        if(!$companyResult){
                            $comstatus = false;
                        }
                        //邀请入驻加维币
                        $fromcid = session("fromcid");
                        if(!empty($fromcid)){
                            $Task =M("vcoin_task");
                            $Person = M("person_info");
                            $ctask = $Task->where("action ='邀请商家入驻' AND status = 1")->find();
                            if(!empty($ctask)){
                                $rid = $ctask['id'];
                                $rvcoin = $ctask['value'];
                                //邀请用户维币增加
                                $rinfo = $Person->where("uid = {$fromcid}")->setInc('vcoin',$rvcoin);
                                //添加任务记录
                                $Log = M("task_log");
                                $flogarray = array(
                                    'uid' => $fromcid,
                                    'taskid' => $rid,
                                    'created' => time()
                                );
                                $flog = $Log->add($flogarray);
                                //写入维币记录
                                $Vlog1 = M("vcoin_log");
                                $vlogAarry1 = array(
                                    'uid' => $fromcid,
                                    'action' => "邀请商家入驻",
                                    'organizer' => "维沃珂",
                                    'intro' => "邀请商家入驻成功",
                                    'type' => 2,
                                    'value' => $rvcoin,
                                    'created' => time()
                                );
                                $vlog1 = $Vlog1->add($vlogAarry1);
                            }
                        }
                    }
                    //写入维币记录
                    $Vlog = M("vcoin_log");
                    $vlogAarry = array(
                        'uid' => $uid,
                        'action' => "购买维币",
                        'organizer' => C("ORGANIZER"),
                        'intro' => "微信购买".$coin."维币",
                        'type' => 1,
                        'value' => $coin,
                        'created' => time()
                    );
                    $vlog = $Vlog->add($vlogAarry);
                    if($coinResult&&$comstatus){
                        //更新订单状态
                        $orderData['paydate'] = time();
                        $orderData['orderstatus'] = 1;
                        $orderData['updated'] = time();
                        $orderResult = M("order")->where("ordernum = '{$wx['ordernum']}'")->save($orderData);
                        //订单为购买维币
                    }
                }
                //商品微信支付
                if($order['type']==1){
                    $amount = $order['amount'];
                    //查询消费任务
                    $Task =M("vcoin_task");
                    $Person = M("person_info");
                    $gtask = $Task->where("action ='消费' AND status = 1")->find();
                    //获取商家拥有的维币和卖家ID
                    $shopCoin = M("company_shop as b")->join(array("RIGHT join ww_shop_goods as a ON a.shopid=b.shopid","LEFT join ww_person_info as c ON b.uid=c.uid"))->where("a.goodid={$order['shopid']}")->field('b.uid as shopuid,b.name,c.vcoin as vcoin')->find();
                    $shopuid = (int)$shopCoin['shopuid'];
                    //买家id和名字
                    $uid = (int)$order['uid'];
                    $buy = M('users')->where("uid = {$uid}")->find();
                    $name = $buy['name'];
                    //事务开始
                    $model = new Model();
                    $model->startTrans();
                    if((int)$gtask['value']>=1){
                        $rid = $gtask['id'];
                        //给用户添加的维币
                        $gcoin= (int)round($amount/$gtask['value']);
                        if($shopCoin['vcoin']>=$gcoin&&($gcoin>0)&&(!empty($uid))&&(!empty($shopuid))){
                            //用户维币增加
                            $ginfo = $Person->where("uid = {$uid}")->setInc('vcoin',$gcoin);
                            //添加任务记录
                            $Log = M("task_log");
                            $gflogarray = array(
                                'uid' => $uid,
                                'taskid' => $rid,
                                'created' => time()
                            );
                            $flog = $Log->add($gflogarray);
                            //写入维币记录
                            $Vlog1 = M("vcoin_log");
                            $gvlogAarry1 = array(
                                'uid' => $uid,
                                'action' => "消费获得维币",
                                'organizer' =>$shopCoin['name'],
                                'intro' => "购买商品获得维币",
                                'type' => 2,
                                'value' => $gcoin,
                                'created' => time()
                            );
                            $gvlog1 = $Vlog1->add($gvlogAarry1);
                            //商家金币减少
                            $sinfo = $Person->where("uid = {$shopCoin['shopuid']}")->setDec('vcoin',$gcoin);
                            //写入维币记录
                            $gvlogAarry2 = array(
                                'uid' => $shopCoin['shopuid'],
                                'action' => "买家消费扣除维币",
                                'organizer' =>$name,
                                'intro' => "卖出商品送出维币",
                                'type' => 3,
                                'value' => $gcoin,
                                'created' => time()
                            );
                            $gvlog2 = $Vlog1->add($gvlogAarry2);
                            if(!$gvlog2 && !$sinfo && !$flog && !$ginfo){
                                $model->rollback();
                            }
                        }
                    }
                    //更新订单状态
                        $orderData = array();
                        $orderData['paydate'] = time();
                        $orderData['orderstatus'] = 1;
                        $orderData['updated'] = time();
                        $orderResult = M("order")->where("ordernum = '{$wx['ordernum']}'")->save($orderData);
                    if($orderResult){
                        $model->commit();
                    }else{
                        $model->rollback();
                    }
               }
                //活动微信支付
                if($order['type']==2){

                }
                if($orderResult){
                    $data['status'] = 1;
                }
            }
        }
//        $data['F'] = '微信支付';
        M('order_wxpays')->add($data);
        //返回状态
        $notify->Handle(false);
    }
    /**
     * 轮询看支付状态:由于引入微信第三方的文件倒是ajax请求返回 数据出错，将轮询放到Vcoin类里实现
     * djt
     *
     */
    public function test() {
        $this->ajaxReturn(array('msg'=>"支付",'status'=>1));
    }
    public function nativeOrders(){
        $order_num=trim($_POST['oid']);
        $status=M('order')->where(array('ordernum'=>$order_num))->field('orderstatus')->find();
        $this->ajaxReturn(array('msg'=>"支付",'status'=>$status['orderstatus']));
    }




    //结束
    //微信会员扫码支付续费回调,众筹回调
    public function callback_vcoin(){
        //echo "111111111";
        //exit;

        //获取微信回调数据
        $notify = new \WxPayNotify();
        //$rsv_data = $GLOBALS['HTTP_RAW_POST_DATA'];
        //$raw_data = file_get_contents('php://input', 'r');
        $xml = file_get_contents('php://input', 'r');

        //$rsv_data = M('Log_pay_notice')->where(array('id'=>16327))->find();
        //dump($rsv_data);
        //dump($rsv_data['d']);
        //dump(xml_to_array($rsv_data['d']));
        //$result = simplexml_load_string($rsv_data['d']);


        //$xml = $rsv_data['d'];
        //xml数据转数组
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        //var_dump($result);

        $data['D'] = $xml;
        $data['D_Post'] = json_encode($_POST);
        $data['D_Get'] = json_encode($_GET);
        $data['D_Server'] = json_encode($_SERVER);
        $data['add_time'] = time();
        //保存订单处理结果，处理成功为1,处理失败为2
        $data['status'] = 2;
        //保存处理结果，默认为fail,执行成功改为success
        $data['result'] = 'fail';
        //签名+状态验证
        //if($notify->checksign() && $result->return_code == "SUCCESS" && $result->result_code == "SUCCESS"){
        if($result['return_code'] == "SUCCESS" && $result['result_code'] == "SUCCESS"){
            //保存验证结果
            $data['result'] = 'success';
            //更改订单支付状态
            $db_order=M('Crowdf_order');
            //查询微信生成订单号查询
            $order=$db_order->where("out_trade_no='".$result['out_trade_no']."'")->find();
            //保存订单号 订单id
            $data['order_id'] = $order['id'];
            $data['trade_on'] = $result['out_trade_no'];
            if($order['status'] == 0){
                //更改订单状态为1
                $dis_status = $db_order->where("out_trade_no='".$result['out_trade_no']."'")->save(array('status'=>1));
                //写入新的价格
                $raise_money = M('Crowdfunding')->where(array('id'=>$order['corwdf_id']))->find();
                $money = $raise_money['rasie_money'] + $order['money'];
                M('Crowdfunding')->where(array('id'=>$order['corwdf_id']))->save(array('raise_money'=>$money));
                if($dis_status){
                    $data['status'] = 1;
                }
            }
        }
        $data['F'] = '微信支付';
        M('Log_pay_notice')->add($data);
        echo M('Log_pay_notice')->getLastsql();
        //返回状态
        $notify->Handle(false);
    }
    //微信会员扫码支付续费回调,应用订单回调 
    public function callback_orderscopy(){

        //获取微信回调数据
        $notify = new \WxPayNotify();
        $xml = file_get_contents('php://input', 'r');

        //xml数据转数组
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        $data['D'] = $xml;
        $data['D_Post'] = json_encode($_POST);
        $data['D_Get'] = json_encode($_GET);
        $data['D_Server'] = json_encode($_SERVER);
        $data['add_time'] = time();
        //保存订单处理结果，处理成功为1,处理失败为2
        $data['status'] = 2;
        //保存处理结果，默认为fail,执行成功改为success
        $data['result'] = 'fail';
        //签名+状态验证
        //if($notify->checksign() && $result->return_code == "SUCCESS" && $result->result_code == "SUCCESS"){
        if($result['return_code'] == "SUCCESS" && $result['result_code'] == "SUCCESS"){
            //保存验证结果
            $data['result'] = 'success';
            //更改订单支付状态
            $db_order=M('order');
            //查询微信生成订单号查询
            $order=$db_order->where("out_trade_no='".$result['out_trade_no']."'")->find();
            //保存订单号 订单id
            $data['order_id'] = $order['id'];
            $data['trade_on'] = $result['out_trade_no'];
            if($order['status'] == 0){
                //更改订单状态为1
                $dis_status = $db_order->where("out_trade_no='".$result['out_trade_no']."'")->save(array('status'=>1));
                if($dis_status){
                    $data['status'] = 1;
                }
            }
        }
        $data['F'] = '微信支付';

        M('Log_pay_notice')->add($data);
        //返回状态
        $notify->Handle(false);
    }

    public function native_orders(){
        $order_num=trim($_POST['oid']);
        $status=M('Orders')->where(array('order_num'=>$order_num))->getField('status');

        //后期修改只读一个数据库
        if(!$status){
            $status=M('Crowdf_order')->where(array('order_num'=>$order_num))->getField('status');
        }
        $this->ajaxReturn(array("status"=>$status));
    }

}

?>
