<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    	$access_token  = $this->get_access_token("wxc05bf626f605c34a","2acccece9df8635e4dfe5dc5bf98ec92");
    	$jsapi=$this->get_jsapi_ticket($access_token);
    	//随即字符串
    	$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    	$url = dirname($url).$_SERVER["QUERY_STRING"].'/';
    	$noncestr =$this->getRandChar(16);
    	//时间戳
    	$timestamp = time();
    	$str = "jsapi_ticket=".$jsapi."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
    	$signature =sha1($str);
    	// dump($url);
    	//dump($url);exit;
    	$this->assign('jsapi',$jsapi);
    	$this->assign('noncestr',$noncestr);
    	$this->assign('timestamp',$timestamp);
    	$this->assign('signature',$signature);
       	$this->display();
    }
    public function jm(){ 
    	$data['lng']=$_POST['latitude'];
    	$data['lat']=$_POST['longitude'];
    	$data['distance']=1000;
    	$token=$this->desc($data);
    	$return=$this->curlGet("http://jkd2.shutung.com:81/App/v3/service/business_list?lng=".$data['lng']."&lat=".$data['lat']."&distance=".$data['distance']."&token=".$token);
    	//dump($return);die();
    	$this->ajaxReturn("http://jkd2.shutung.com:81/App/v3/service/business_list?lng=".$data['lng']."&lat=".$data['lat']."&distance=".$data['distance']."&token=".$token);
    }
    function getRandChar($length){
	   $str = null;
	   $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	   $max = strlen($strPol)-1;

	   for($i=0;$i<$length;$i++){
	    $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
	   }

	   return $str;
	  }
    function get_access_token($appid,$appscret){
    	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appscret;	
    	
    	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL,$url );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //相当关键，这句话是让curl_exec($ch)返回的结果可以进行赋值给其他的变量进行，json的数据操作，如果没有这句话，则curl返回的数据不可以进行人为的去操作（如json_decode等格式操作）
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
	$res = curl_exec($ch); 

	
	//$res = file_get_contents($token_access_url); //获取文件内容或获取网络请求的内容
	//echo $res;
	$result = json_decode($res, true); //接受一个 JSON 格式的字符串并且把它转换为 PHP 变量
	
	return $result['access_token'];
    }
    function get_jsapi_ticket($access_token){

    	$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";	
    	
    	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //相当关键，这句话是让curl_exec($ch)返回的结果可以进行赋值给其他的变量进行，json的数据操作，如果没有这句话，则curl返回的数据不可以进行人为的去操作（如json_decode等格式操作）
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
	$res = curl_exec($ch); 

	
	//$res = file_get_contents($token_access_url); //获取文件内容或获取网络请求的内容
	//echo $res;
	$result = json_decode($res, true); //接受一个 JSON 格式的字符串并且把它转换为 PHP 变量
	
	return $result['ticket'];
    }
    function curlPost($url, $data,$showError=1){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$tmpInfo = curl_exec($ch);
		$errorno=curl_errno($ch);
		if ($errorno) {
			return array('rt'=>false,'errorno'=>$errorno);
		}else{
			$js=json_decode($tmpInfo,1);
			if (intval($js['errcode']==0)){
				return array('rt'=>true,'errorno'=>0,'media_id'=>$js['media_id'],'msg_id'=>$js['msg_id']);
			}else {
				if ($showError){
					$this->error('发生了Post错误：错误代码'.$js['errcode'].',微信返回错误信息：'.$js['errmsg']);
				}
			}
		}
	}
	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}
	function desc($arr) {
		$str = "";
		if(empty($arr)){
			return 'GET参数出错（null）！';
		}
		sort($arr);
		foreach($arr as $val){
			$str.=$val;
		}
		$str =  md5(md5($str).TOKEN);
		return $str;
	}
}