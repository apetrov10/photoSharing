<?php function loginScreen() {

	global $DBH, $tpl, $msgArray;
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	$tpl->set('cmd', 'login');
	$tpl->set('content', $tpl->fetch('_login_screen.html'));
	print $tpl->fetch('_main_template.html');

}

function login($initUser, $initPass) {
		
	global $DBH, $tpl, $msgArray;
	
	$username = $_POST['username'];
$password = md5($_POST['password']);
 
// To protect MySQL injection
$username = stripslashes($username);
$password = stripslashes($password);
$username = mysql_real_escape_string($username);
$password = mysql_real_escape_string($password);
$sql="SELECT * FROM users WHERE username='$username' and password='$password'";
$result=mysql_query($sql);
 
// Mysql_num_row is to count number of row from the above query
$count=mysql_num_rows($result);
 
// count is 1 if the above username and password matches
if($count==1){
 
// now redirect to dashboard page, we also store the username in session for further use in dashboard
//session_register("username"); // session checker for pages
$_SESSION['username']= $username; // storing username in session
 
	$tpl->set('cmd', '');
	$tpl->set('content', '<h1><center><b>:o)</b></center</h1>');
	print $tpl->fetch('_main_template.html');
}
 
//if the username and password doesn't match redirect to homepage with message=1
else {
    echo '
    <script language="javascript" type="text/javascript">
window.location.href="index.php?message=1";
</script>';
 
}
} // end of login();
	

	
// void logout()
function logout() {
	
	unset($_SESSION['sessusr']);
	unset($_SESSION['userGroup']);
	unset($_SESSION['lastActivity']);
}

?>