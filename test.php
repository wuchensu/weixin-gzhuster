<?php

define("SN","localhost");	//��������
define("UN","root");		//�û���
define("PW","gzhuster");	//����
define("DB","wxgzh");		//���ݿ���

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