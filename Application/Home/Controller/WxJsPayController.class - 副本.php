<?php
namespace Home\Controller;
use Think\Controller;
use Org\Qrcode;
use Think\Crypt\Driver\Think;
use Think\Model;
use Org\Util;
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
/**
 * 
 * JSAPI支付实现类
 * 该类实现了从微信公众平台获取code、通过code获取openid和access_token、
 * 生成jsapi支付js接口所需的参数、生成获取共享收货地址所需的参数
 * 
 * 该类是微信支付提供的样例程序，商户可根据自己的需求修改，或者使用lib中的api自行开发
 * 
 * @author widy
 *
 */
class WxJsPayController extends CommonController{
	/**
	 * 
	 * 网页授权接口微信服务器返回的数据，返回样例如下
	 * {
	 *  "access_token":"TDOw-fwRyUJTJGkqO2PW9wYPJyDTK6p_9JK1LRcTKQE-ehxvkTkdQXonnvvUwFsYml6gsen1tLKGeIeZ2hmGs6tzPIK_F38w4PR0b-HtD7k",
	 *  "expires_in":7200,
	 *  "refresh_token":"E2os3gzqCI1vgPD1ygmAmTItT6I91OhaRDIe1wiGnUxEctP33Lf4EMwYGH2NF2WuHPtafACj9vY_CHc_4RBPJH3Gc7DGB-4kd24j8cj4tZg",
	 *  "openid":"oHYU902mg61nA0thYMyUUAvWV_Zc",
	 *  "scope":"snsapi_base",
	 *  "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
	 * }
	 * 其中access_token可用于获取共享收货地址
	 * openid是微信支付jsapi支付接口必须的参数
	 * @var array
	 */
	public $data = null;

	/*微信jssdk支付开始*/
	public function GetOpenid()
	{
		//通过code获得openid
		if (!isset($_GET['code'])){
			//触发微信返回code码
			$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
//			echo urldecode($baseUrl);exit;
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
	
	/**
	 * 
	 * 获取jsapi支付的参数
	 * @param array $UnifiedOrderResult 统一支付接口返回的数据
	 * @throws WxPayException
	 * 
	 * @return json数据，可直接填入js函数作为参数
	 */
	public function GetJsApiParameters($UnifiedOrderResult)
	{
		if(!array_key_exists("appid", $UnifiedOrderResult)
		|| !array_key_exists("prepay_id", $UnifiedOrderResult)
		|| $UnifiedOrderResult['prepay_id'] == "")
		{
			throw new \WxPayException("参数错误");
		}
		$jsapi = new \WxPayJsApiPay();
		$jsapi->SetAppid($UnifiedOrderResult["appid"]);
		$timeStamp = time();
		$jsapi->SetTimeStamp("$timeStamp");
		$jsapi->SetNonceStr(\WxPayApi::getNonceStr());
		$jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
		$jsapi->SetSignType("MD5");
		$jsapi->SetPaySign($jsapi->MakeSign());
		$parameters = json_encode($jsapi->GetValues());
		return $parameters;
	}
	/**
	 * 
	 * 获取地址js参数
	 * 
	 * @return 获取共享收货地址js函数需要的参数，json格式可以直接做参数使用
	 */
	public function GetEditAddressParameters()
	{	
		$getData = $this->data;
		$data = array();
		$data["appid"] = \WxPayConfig::APPID;
		$data["url"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$time = time();
		$data["timestamp"] = "$time";
		$data["noncestr"] = "1234568";
		$data["accesstoken"] = $getData["access_token"];
		ksort($data);
		$params = $this->ToUrlParams($data);
		$addrSign = sha1($params);
		
		$afterData = array(
			"addrSign" => $addrSign,
			"signType" => "sha1",
			"scope" => "jsapi_address",
			"appId" => \WxPayConfig::APPID,
			"timeStamp" => $data["timestamp"],
			"nonceStr" => $data["noncestr"]
		);
		$parameters = json_encode($afterData);
		return $parameters;
	}
	//打印输出数组信息
	public function printf_info($data)
	{
		foreach($data as $key=>$value){
			echo "<font color='#00ff55;'>$key</font> : $value <br/>";
		}
	}
	public function UnifiedOrder(){
		$info = session("userinfo");
		if(empty($info)){
			echo "<script>alert('你还没有登录，敬请期待！');history.go('www.91wework.com');</script>";
			/*$this->error("您还未登录，请先登录");*/
		}
		$uid = $info['uid'];
		$jssdk = new JsSdkController;
		$signPackage = $jssdk->GetSignPackage();
		$this->assign('signPackage',$signPackage);
		$ordernum = _get('ordernum');
		if(empty($ordernum)){
			$this->error("参数不能为空");
		}
		$order = M('order')->where("ordernum='{$ordernum}'")->order('created DESC')->find();
		if(empty($order)){
			$this->error("该订单不存在");
		}
		if($uid!=$order['uid']){
			$this->error("你无权操作此订单");
		}
		if($uid!=30){
			echo "<script>alert('该功能暂未开放，敬请期待！';history.go(-1);</script>";
		}
		if($order['orderstatus']!=0){
			$this->error("该订单状态不能进行支付");
		}
		//查看微信订单是否支付
		$wresult =  M("wxordernum")->where("ordernum='{$ordernum}' AND status=1")->order('updated DESC')->find();
		if(!empty($wresult)){
			$this->error("该订单已经微信支付，如有疑问请联络管理员");
		}
		//生成订单对应的微信订单号
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
		$data = $this->data;
		if(empty($data)){
			$openId = $this->GetOpenid();
			$data = $this->data;
		}
		$openId = $data['openid'];
//		echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
//		dump($order);
//		exit;
		$input = new \WxPayUnifiedOrder();
		$input->SetBody("test");
		$input->SetAttach("test");
		$input->SetOut_trade_no($wxordernum);
		$input->SetTotal_fee("1");
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("http://www.91wework.com/index.php/Home/WxJsPay/callback_orders");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$worder = \WxPayApi::unifiedOrder($input);
		$this->printf_info($worder);
		$jsApiParameters = $this->GetJsApiParameters($worder);
//获取共享收货地址js函数参数
		$editAddress = $this->GetEditAddressParameters();
		$this->assign('jsApiParameters',$jsApiParameters);
		$jsApiParametersArray = json_decode($jsApiParameters,true);
		$this->assign("jsApiParametersArray",$jsApiParametersArray);
		$this->assign('editAddress',$editAddress);
		$this->display('Wxpay/jsapi');
//		dump($jsApiParameters);
//		dump($editAddress);exit;
		//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
		/**
		 * 注意：
		 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
		 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
		 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
		 */
	}
	/**
	 * 微信jssdk支付维币回调地址
	 * 2017-5-10
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
		$notify->Handle(true);
	}
}