<?php function registerScreen($formData, $errorMsg) {

	global $DBH, $tpl, $msgArray;
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	
	$query = "SELECT * FROM countries order by name";
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; 
	}

	$countries='';
	while ($row = mysql_fetch_assoc($result)) {
	
		$tpl->set('country', $row['name']);
		$tpl->set('id', $row['code']);
		$countries = $countries . $tpl->fetch('register_row.html');
	}
	$tpl->set('countryRow', $countries);
	
	$tpl->set('cmd', 'registration');
	$tpl->set('content', $tpl->fetch('register.html'));
	print $tpl->fetch('_main_template.html');

}

function registration(){
global $DBH, $tpl, $msgArray;
$username = $_POST['username'];
if ($username == ''){
	registerScreen('', 'invalid_username');
	exit;
}
$username = strip_tags($username); //strip tags are used to take plain text only, in case the register-er inserts dangours scripts.
$username = str_replace(' ', '', $username); // to remove blank spaces
if (!preg_match("/^[A-Za-z0-9_]{4,29}$/",$username)) {
  registerScreen('', 'invalid_username');
 exit;
}
$password = $_POST['password'];
$password2 = $_POST['password2'];
if ($password != $password2){
	registerScreen('', 'pass_not_match');
	exit;
}
if (strlen($password) < 6)
{
   registerScreen('', 'short_password');
   exit;
}
$password = strip_tags($password);
$password = md5($password); // md5 is used to encrypt your password to make it more secure.
 
$email = $_POST['email'];
if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email)) {
  registerScreen('', 'invalid_email');
  exit;
}
$id = $_POST['id'];
$id = strip_tags($id);

 
$sql1="SELECT username FROM users WHERE username='$username'"; // checking username already exists
$qry1=mysql_query($sql1);
$num_rows1 = mysql_num_rows($qry1);
$sql2="SELECT username FROM users WHERE email='$email'"; // checking username already exists
$qry2=mysql_query($sql2);
$num_rows2 = mysql_num_rows($qry2);
 
//alert if it already exists
if(($num_rows1 > 0) OR ($num_rows2 > 0))
{
	$tpl->set('cmd', '');
	registerScreen('', 'reg_failed');
}
 
else
{
// if username doesn't exist insert new records to database
$query = "INSERT INTO users(username, password, email, country, userGroup) VALUES ('$username', '$password', '$email','$id','2')";
 $success = mysql_query($query);

  
 //messages if the new record is inserted or not
if($success) {
	$tpl->set('cmd', '');
	$tpl->set('errorMsg', $msgArray['reg_success']);
	$tpl->set('content', $tpl->fetch('_login_screen.html'));
	print $tpl->fetch('_main_template.html');
}
 
else {
	$tpl->set('cmd', '');
	registerScreen('', 'reg_error');
    }
  }
}

?>