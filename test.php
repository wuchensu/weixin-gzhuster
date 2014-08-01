<?php

define("SN","localhost");	//服务器名
define("UN","root");		//用户名
define("PW","gzhuster");	//密码
define("DB","wxgzh");		//数据库名

	echo getmaxid("user");

	
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