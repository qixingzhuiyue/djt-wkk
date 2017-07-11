<?php
namespace Org\Sms;
/* *
 * 类名：ChuanglanSmsApi
 * 功能：创蓝接口请求类
 * 详细：构造创蓝短信接口请求，获取远程HTTP数据
 * 版本：1.3
 * 日期：2014-07-16
 * 说明：
 * 以下代码只是为了方便客户测试而提供的样例代码，客户可以根据自己网站的需要，按照技术文档自行编写,并非一定要使用该代码。
 * 该代码仅供学习和研究创蓝接口使用，只是提供一个参考。
 * http://code.253.com/返回状态码网址
 * http://222.73.117.158/msg/index.jsp  后台查看创蓝是否发送短信统计成功失败量
 * 在本地接口中请求创蓝会得到一个经过处理的数组
 */
class ChuanglanSmsApi {

//		//创蓝发送短信接口URL, 如无必要，该参数可不用修改
//		private  $api_send_url = 'http://sms.253.com/msg/send?';
//
//		//创蓝短信余额查询接口URL, 如无必要，该参数可不用修改
//		private $api_balance_query_url = 'http://sms.253.com/msg/balance?';
//
//		//创蓝账号 替换成你自己的账号
//		private $api_account	= 'N132857_N7128168';
//
//		//创蓝密码 替换成你自己的密码
//		private $api_password	= 'ZtFJ8p0gRN3238';
		
	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $needstatus 是否需要状态报告
	 * @param string $product 产品id，可选
	 * @param string $extno   扩展码，可选
	 */
	//发送短信的接口地址
	const API_SEND_URL='http://sms.253.com/msg/send?';

	//查询余额的接口地址
	const API_BALANCE_QUERY_URL='http://sms.253.com/msg/balance?';

	const API_ACCOUNT='N132857_N7128168';//短信账号从 https://zz.253.com/site/login.html 里面获取。

	const API_PASSWORD='ZtFJ8p0gRN3238';//短信密码从 from https://zz.253.com/site/login.html 里面获取。

	/**
	 * 发送短信需要的接口参数
	 *
	 * @param string $mobile 		手机号码
	 * @param string $msg 			想要发送的短信内容
	 * @param string $needstatus 	是否需要状态报告 '1'为需要 '0'位不需要。
	 */
	public function sendSMS( $mobile, $msg, $needstatus = 1) {

		//发送短信的接口参数
		$postArr = array (
			'un' => self::API_ACCOUNT,
			'pw' => self::API_PASSWORD,
			'msg' => $msg,
			'phone' => $mobile,
			'rd' => $needstatus
		);

		$result = $this->curlPost( self::API_SEND_URL , $postArr);
		return $result;
	}

	/**
	 *
	 *
	 *  查询余额
	 */
	public function queryBalance() {

		// 查询接口参数
		$postArr = array (
			'un' => self::API_ACCOUNT,
			'pw' => self::API_PASSWORD,
		);
		$result = $this->curlPost(self::API_BALANCE_QUERY_URL, $postArr);
		return $result;
	}

	/**
	 * 处理接口返回值
	 *
	 */
	public function execResult($result){
		$result=preg_split("/[,\r\n]/",$result);
		return $result;
	}

	/**
	 * @param string $url
	 * @param array $postFields
	 * @return mixed
	 */
	private function curlPost($url,$postFields){
		$postFields = http_build_query($postFields);
		if(function_exists('curl_init')){

			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
			$result = curl_exec ( $ch );
			if(curl_errno($ch))
			{
				return 'Curl error: ' . curl_error($ch);
			}
			curl_close ( $ch );
		}elseif(function_exists('file_get_contents')){

			$result=file_get_contents($url.$postFields);

		}
		return $result;
	}

	//魔术获取
	public function __get($name){
		return $this->$name;
	}

	//魔术设置
	public function __set($name,$value){
		$this->$name=$value;
	}
}
?>