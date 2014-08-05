<!DOCTYPE HTML> 
<html>
<head>
  <meta content="text/html; charset=utf-8" http-equiv="content-type" />
<style>
.error {color: #FF0000;}
</style>
</head>
<body> 

<?php
// 定义变量并设置为空值
$nameErr = $emailErr = $genderErr = $telErr = "";
$name = $email = $gender = $comment = $tel = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   if (empty($_POST["name"])) {
     $nameErr = "姓名是必填的";
   } else {
     $name = test_input($_POST["name"]);
     // 检查姓名是否包含字母和空白字符
     if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
       $nameErr = "只允许字母和空格"; 
     }
   }
   
   if (empty($_POST["email"])) {
     $emailErr = "电邮是必填的";
   } else {
     $email = test_input($_POST["email"]);
     // 检查电子邮件地址语法是否有效
     if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email)) {
       $emailErr = "无效的 email 格式"; 
     }
   }
     
   if (empty($_POST["tel"])) {
     $tel = "";
   } else {
     $tel = test_input($_POST["tel"]);
     // 检查 URL 地址语法是否有效（正则表达式也允许 URL 中的斜杠）
     if (!preg_match("/1[3578]{1}[0-9]{9}$/",$tel)) {
       $telErr = "手机号错误"; 
     }
   }

   if (empty($_POST["comment"])) {
     $comment = "";
   } else {
     $comment = test_input($_POST["comment"]);
   }

   if (empty($_POST["gender"])) {
     $genderErr = "性别是必选的";
   } else {
     $gender = test_input($_POST["gender"]);
   }
}

function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
?>

<h2>登记页面</h2>
<p><span class="error">* 必需的字段</span></p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
   姓名：<input type="text" name="name">
   <span class="error">* <?php echo $nameErr;?></span>
   <br><br>
   电邮：<input type="text" name="email">
   <span class="error">* <?php echo $emailErr;?></span>
   <br><br>
   手机：<input type="text" name="tel">
   <span class="error"><?php echo $telErr;?></span>
   <br><br>
   个性签名：<textarea name="comment" rows="5" cols="40"></textarea>
   <br><br>
   性别：
   <input type="radio" name="gender" value="female">女性
   <input type="radio" name="gender" value="male">男性
   <span class="error">* <?php echo $genderErr;?></span>
   <br><br>
   <input type="submit" name="submit" value="提交"> 
</form>

<?php
if($nameErr == "" && $emailErr == "" && $genderErr == "" && $telErr == "") {
  echo "<h2>您的输入：</h2>";
  echo $name;
  echo "<br>";
  echo $email;
  echo "<br>";
  echo $tel;
  echo "<br>";
  echo $comment;
  echo "<br>";
  echo $gender;
  if ($name != "") {
    $con = mysql_connect("localhost","cs","444444");
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }
    mysql_select_db("test", $con);
    $sql="insert into users (name, tel, gender, email)
    VALUES
    ('$name', '$tel', '$gender', '$email')";
    if (!mysql_query($sql,$con)) {
      die('Error: ' . mysql_error());
    }
    echo "1 record added";
    mysql_close($con);
  }
}

?>

</body>
</html>
