<?php
namespace Home\Controller;
use Think\Controller;

define("TOKEN", "weixin");

class WechatController extends CommonController{
	//wx6e144be882e1e0d0
	//7b94f80642c8c78e9cfd5b51a6dc2b79
	//public $appid = 'wxfa5a6521224f3471';
	//public $secret = 'efa35b1a7cd6c35002d945a048dc04c7';
	public $appid = 'wx604acb859c405539';
    //public $secret = '1ce51548af788822c50fefa89e94d22a';
	public $secret = '7056ef47d91f948e04bbf6c52e02043b';
//	public $redirect_uri = 'http://qifuzhiye.51ruanron.com/index.php/Home/Wechat/webAuth';

	public function index(){
       
		$this -> createMenu();
		$this -> responseMsg();
    }
	public function valid(){
		$echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
	}
	public function responseMsg(){
		  //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
				libxml_disable_entity_loader(true);
              	
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);

                switch($RX_TYPE)
                {
                    case "text":
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    default:
                        $resultStr = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "";
            exit;
        }
	}
	public function handleEvent($postObj){
		 $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "大众招商让您轻松赚取朋友圈红利<br />通过悬赏，让您的房源快速消化；<br />通过分享，让您和您的朋友在不经意间获得一大笔的零花钱；<br />通过委托，让您快速找到理想的房源；<br />通过共投，让您搭上资产增值的快车；<br />通过推客，让您帮助客户解决困扰。";
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
		
		
	}
	  public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->ToUserName, $object->FromUserName, time(), $content, $flag);
        return $resultStr;
    }
    public function responseMsgs(){
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";             
				if(!empty( $keyword )){
              		$msgType = "text";
                	$contentStr = "Welcome to wechat world!";
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;
                }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
    }
	//检查token	
	private function checkSignature(){
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	//获取access_token 这个方法是根据jsapi_ticket 的有效期关联的，在文件里读取全局变量jsapi_ticket 当jsapi_ticket过期时才会请求access-ticket，当这两个值在微信端有效期时间一致是，可以把jsapi_ticket写到文件里，供所有人使用，当它过期后也就表示access_ticket也过期了，这样在请求getAccessToken，就不存在频繁刷新的问题了，但是这是在两者有效期保证一致的情况下，如果两者有效期相差很远，有可能还会出现频繁属性触动频率限制的，最好的办法是，将获取的access_token 也写到文件里去
	public function getAccessToken(){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->secret}";
		$access =file_get_contents($url);
		$_SESSION['accessToken'] = json_decode($access,true);
		$_SESSION['accessToken']['lifeTime'] = time()+$_SESSION['accessToken']['expires_in']-1;
		return $_SESSION['accessToken']['access_token'];
	}
	
	//检查access_token是否过期
	public function checkAccessToken(){
		//$_SESSION = array();
		//session_destroy();
		if($_SESSION['accessToken'] and $_SESSION['accessToken']['lifeTime'] > time()){
			$accessToken = $_SESSION['accessToken']['access_token'];
		}else{
			$accessToken = $this -> getAccessToken();
		}
		
		return $accessToken;
	}
	
	
	//创建菜单
	private function createMenu(){
		$data = '{
				 "button":[{	
					  "type":"view",
					  "name":"惊喜从这里开始",
					  "url":"http://www.91wework.com"
				  }]
		}';
		/* {	
					  "type":"view",
					  "name":"委托找房",
					  "url":"http://shouzhuo.ruanron.cn/index.php/Home/entrust"
				  },{	
					  "type":"view",
					  "name":"个人中心",
					  "url":"http://shouzhuo.ruanron.cn/index.php/Home/about"
				  } */
		$token = $this -> checkAccessToken(); 
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$token;
		echo $this ->  vpost($url,$data);
		/* $ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch,CURLOPT_POST,$data);
		$contents = curl_exec($ch);
		curl_close($ch);
		echo $contents; */
	}

	
	//引导关注者打开如下页面
	public function openHtml(){
		redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri={$this->redirect_uri}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect");
	}
	//网页授权
	public function webAuth(){
		$code = I('get.code');
		//拉取openid
		//echo $code;exit;
		if(empty($code)){
			$this -> error('请授权登录……');
		}else{
			$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->secret}&code={$code}&grant_type=authorization_code";
			$content = file_get_contents($url);
			$data = json_decode($content,true);
			if($data['errcode'] == '40029'){
				$this -> openHtml();
			}else{
				//拉取用户信息
				$url = "https://api.weixin.qq.com/sns/userinfo?access_token={$data['access_token']}&openid={$data['openid']}&lang=zh_CN";
				$content = file_get_contents($url);
				$data = json_decode($content,true);
				$this -> member($data);
				$urls = $_SESSION['url'];
				if(!empty($urls)){
					unset($_SESSION['url']);
					echo "<script>location.href='".$urls."'</script>";
				}else{
					$this ->redirect("About/index");
				}
				
			}
		}
	}
	
	//用户信息
	public function member($data){
		$member = M('member');
		$where['openid'] = array('eq',$data['openid']);
		$info = $member -> where($where) ->find();
		if($info){
			session('identifying',$info['id']);
		}else{
			$map = array(
				'openid' => $data['openid'],
				'nickname' => $data['nickname'],
				'sex' => $data['sex'] == 2 ? "女" : "男",
				'headimg' => $data['headimgurl'],
				'province'=>$data['province'],
				'city'=>$data['city'],
				'country'=>$data['country'],
				'createtime' => time(),
				'logintime' => time(),
				'loginip' => get_client_ip(),
			);
			$res = $member -> add($map);
			session('name',$data['nickname']);
			session('identifying',$res);
		}
	}	
	function vpost($url,$data){ // 模拟提交数据函数 
		$curl = curl_init(); // 启动一个CURL会话 
		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址 
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查 
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在 
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)'); // 模拟用户使用的浏览器 
		// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转 
		// curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer 
		curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求 
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包 
		curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环 
		curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回 
		$tmpInfo = curl_exec($curl); // 执行操作 
		if (curl_errno($curl)) { 
			echo 'Errno'.curl_error($curl);//捕抓异常 
		} 
		curl_close($curl); // 关闭CURL会话 
		return $tmpInfo; // 返回数据 
	}

	public function getUser(){
		$token = $this->getAccessToken();
		$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$token;
		$data = file_get_contents($url);
		$arr_data = json_decode($data,true);
		$openids = $arr_data['data']['openid'];
		return $openids;
	}
	
}



?>