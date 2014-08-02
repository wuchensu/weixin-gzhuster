<?php

class STATISTICS 
{
	public static function statisticssend($sender)  //统计发送信息次数
	{
		$sql = "SELECT id,sendtimes FROM user WHERE userid='$sender'";
		$data = mysql_query($sql);
		$row = mysql_fetch_array($data);
		if($row[0] == "")
		{
			$newid=self::getmaxid("user")+1;
			$sql = "INSERT INTO user(id,userid,sendtimes) value ('$newid','$sender','1')";
			mysql_query($sql);
		}
		else 
		{
			$newid = $row[0];
			$st = $row[1] + 1;
			$sql = "UPDATE user SET sendtimes = '$st' WHERE id='$newid'"; 
			mysql_query($sql);
		}
	}
	
	public static function getmaxid($tablename)
	{
		$con = mysql_connect(SN,UN,PW);
		mysql_select_db(DB, $con);
		$sql = "SELECT max(id) FROM `" . $tablename . "`";
		$data = mysql_query( $sql );
		$row = mysql_fetch_array($data);
		return $row[0];
	}	

}

?>
