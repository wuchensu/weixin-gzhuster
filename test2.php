<?php

define("SN","localhost");	//服务器名
define("UN","root");		//用户名
define("PW","gzhuster");	//密码
define("DB","wxgzh");		//数据库名

	
	
	//$sql = "INSERT  INTO `log` ( `id`, `type`, `content`) VALUES ('3','R','ASSCXD') ";
	
	$newid = getmaxid("log") + 1;
	$log_t = "T";
	$log_c = "XXXXXABBB";
	$sql = "INSERT  INTO `log` (`id`, `type`, `content`) VALUES ('{$newid}','{$log_t}','{$log_c}')";
	$con = mysql_connect(SN,UN,PW);	
	mysql_select_db(DB, $con);	
	mysql_query($sql,$con);
	mysql_close($con);
	
	echo $sql;
	
	
	
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