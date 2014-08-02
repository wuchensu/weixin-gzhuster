<?php
/*
    广州Huster
    CopyRight 2014 LZP All Rights Reserved
*/

define("TOKEN", "gzhuster");
define("MENU","欢迎关注广州Hust订阅号，功能目录如下，回复序号使用对应功能:
【1】加入校友QQ群
【2】最新校友活动
【3】往期校友活动
【4】查看广州天气
【5】查看广州空气质量
【6】查看一则笑话
【7】查看本日星座"); 

define("SN","localhost");	//服务器名
define("UN","root");		//用户名
define("PW","gzhuster");	//密码
define("DB","wxgzh");		//数据库名

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if($tmpStr == $signature){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
    	require 'classes/statistics.php';
    	$con = mysql_connect(SN,UN,PW);
		mysql_select_db(DB, $con);
 		
    	
 		require 'plog/classes/plog.php';
		Plog::set_config(include 'plog/config.php');
		$log = Plog::factory(__FILE__);
		

		
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){

            $log->R($postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
            }

            $log->T($result);
            STATISTICS::statisticssend($postObj->FromUserName);
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

	private function transmitText($object, $content)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $newsTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[]]></Content>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

	private function logger($log_type,$log_content)
    {
		$newid=getmaxid("log")+1;
		$sql = "INSERT  INTO `log` ( `id`, `type`, `content`) VALUES ('$newid','$log_type','$log_content') ";
		$con = mysql_connect(SN,UN,PW);
		mysql_select_db(DB, $con);
		mysql_query($sql);
		mysql_close($con);    

	}
	
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = MENU;
                break;
        }
        $result = $this->transmitText($object, $content);
        return $result;
    }

    private function receiveText($object)
    {
		$keyword = trim($object->Content);
		
		switch($keyword)
		{
			case "0":
			case "目录":
				$content = MENU;
				$result = $this->transmitText($object, $content);
				break;

			case "1":
				$output = "[{\"Title\":\"广州Hust校友群\",\"Description\":\"欢迎加入广州Hust校友群关注最新活动动态，群号：31053864\",\"PicUrl\":\"http://gzhust-gzhust.stor.vipsinaapp.com/pic/1406598799219.png\",\"Url\":\"http://mp.weixin.qq.com/s?__biz=MjM5NjE0MzU4Mw==&mid=200572224&idx=1&sn=f3a712a2369ff6bda6e0f61c570cb9f1#rd\"}]";
				$content = json_decode($output, true);
				$result = $this->transmitNews($object, $content);
				break;	
		
			case "2":
				$content = "暂无活动，请加QQ群31053864关注最新活动动态！";
				$result = $this->transmitText($object, $content);
				break;
				
			case "3":
				$content = "待加列表";
				$result = $this->transmitText($object, $content);
				break;
			
			case "4":
				$url = "http://apix.vipsinaapp.com/weather/?appkey=".$object->ToUserName."&city=".urlencode("广州"); 
				$output = file_get_contents($url);
				$content = json_decode($output, true);
				$result = $this->transmitNews($object, $content);
				break;
				
			case "5":
				$url = "http://apix.vipsinaapp.com/airquality/?appkey=".$object->ToUserName."&city=".urlencode("广州"); 
				$output = file_get_contents($url);
				$content = json_decode($output, true);
				$result = $this->transmitNews($object, $content);
				break;
				
			case "6":
				$url = "http://apix.vipsinaapp.com/joke/?appkey=".$object->ToUserName; 
				$output = file_get_contents($url);
				$content = json_decode($output, true);
				$result = $this->transmitText($object, $content);
				break;
				
			case "7":
				$content = "输入括号内的序号或星座名查看相应星座运程：
【MJ】查看摩羯座运程
【SP】查看水瓶座运程
【SY】查看双鱼座运程
【BY】查看白羊座运程
【JN】查看金牛座运程
【SZ】查看双子座运程
【JX】查看巨蟹座运程
【SZ2】查看狮子座运程
【CN】查看处女座运程
【TC】查看天秤座运程
【TX】查看天蝎座运程
【SS】查看射手座运程";
				$result = $this->transmitText($object, $content);
				break;

			case "mj":
			case "MJ":
			case "Mj":
			case "摩羯":
			case "摩羯座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("摩羯座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;



			case "sp":
			case "SP":
			case "Sp":
			case "水瓶":
			case "水瓶座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("水瓶座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;
				
			case "sy":
			case "SY":
			case "Sy":
			case "双鱼":
			case "双鱼座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("双鱼座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;			

			case "by":
			case "BY":
			case "By":
			case "白羊":
			case "白羊座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("白羊座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

			case "jn":
			case "JN":
			case "Jn":
			case "金牛":
			case "金牛座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("金牛座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

			case "sz":
			case "SZ":
			case "Sz":
			case "双子":
			case "双子座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("双子座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

			case "jx":
			case "JX":
			case "Jx":
			case "巨蟹":
			case "巨蟹座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("巨蟹座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

			case "sz2":
			case "SZ2":
			case "Sz2":
			case "狮子":
			case "狮子座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("狮子座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

			case "cn":
			case "CN":
			case "Cn":
			case "处女":
			case "处女座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("处女座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

			case "tc":
			case "TC":
			case "Tc":
			case "天秤":
			case "天秤座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("天秤座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

			case "tx":
			case "TX":
			case "Tx":
			case "天蝎":
			case "天蝎座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("天蝎座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

			case "ss":
			case "SS":
			case "Ss":
			case "射手":
			case "射手座":
				$url = "http://apix.vipsinaapp.com/astrology/?appkey=".$object->ToUserName."&name=".urlencode("射手座"); 
				$output = file_get_contents($url);
				$content = str_replace("\\n","\r",$output);
				$content = str_replace("\"","",$content);
				$result = $this->transmitText($object, $content);
				break;

				
			case "777":	
				$con = mysql_connect(SN,UN,PW);
				mysql_select_db(DB, $con);	
				$sql = "SELECT * FROM `user`";
				$data = mysql_query( $sql );
				$arr_str = "";
				while($row = mysql_fetch_array($data))
					{
					$arr_str .= $row['id'] . " " . $row['userid'] . " " . $row['name'] ;
					$arr_str .= "\r";
					}
				$content = $arr_str;
				$result = $this->transmitText($object, $content);
				mysql_close($con);
				break;
			
			case "888":
				
				
				//$sql = "INSERT  INTO `log` ( `id`, `type`, `content`) VALUES ('2','T','ASSCXD') ";
				
				$newid = getmaxid("log") + 1;
				$log_t = "T";
				$log_c = "XXXXXAAA";
				$sql = "INSERT  INTO `log` (`id`, `type`, `content`) VALUES ('$newid','$log_t','$log_c')";
				
				$con = mysql_connect(SN,UN,PW);
				mysql_select_db(DB, $con);	
				mysql_query($sql);
				mysql_close($con);
				$content = $sql;
				$result = $this->transmitText($object, $content);
				break;
			
			case "999":

				$content = getmaxid("user");
				$result = $this->transmitText($object, $content);
				break;
				
				
				
				
			default:
				$content = MENU;
				$result = $this->transmitText($object, $content);
				break;
			
		}
		
		return $result;		

		/*$url = "http://apix.vipsinaapp.com/airquality/?appkey=".$object->ToUserName."&city=".urlencode($keyword);  */ 
        /*$keyword = trim($object->Content);$url = "http://apix.vipsinaapp.com/stockanalysis/?appkey=trialuser&code=000063";  */

        
    }



}

function getmaxid($tablename)
{
	$con = mysql_connect(SN,UN,PW);
	mysql_select_db(DB, $con);
	$sql = "SELECT max(id) FROM `" . $tablename . "`";
	$data = mysql_query( $sql );
	$row = mysql_fetch_array($data);
	mysql_close($con);
	return $row[0];

}


?>