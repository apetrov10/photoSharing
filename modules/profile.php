<?php
function profile($user = '', $errorMsg = ''){
	global $DBH, $tpl, $msgArray;
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	$query = 'SELECT * FROM users, countries WHERE username = "'.$user.'" AND country = code';
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	while ($row = mysql_fetch_assoc($result)) {
		
		$tpl->set('username', $row['username']);
		$tpl->set('firstName', $row['firstName']);
		$tpl->set('lastName', $row['lastName']);
		$tpl->set('country', $row['name']);
		$tpl->set('camera', $row['camera']);
		$tpl->set('lenses', $row['lenses']);
		$user = $row['username'];
		if ($row['numvotes'] == 0){
			$tpl->set('rating', '0');
		} else {
			$tpl->set('rating', $row['rating']/$row['numvotes']);
		}
	}
	if ($_SESSION['sessusr'] == $user){
		$tpl->set('edit', $tpl->fetch('_profile_edit_options.html'));
	} else {
		$tpl->set('user2', $user);
		$tpl->set('edit', $tpl->fetch('_send_msg_from_profile.html'));
	}
	$tpl->set('content', $tpl->fetch('_profile.html'));
	print $tpl->fetch('_main_template.html');
}

function browsePictures($user = '', $pg = '', $errorMsg = ''){
global $DBH, $tpl, $msgArray;
if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
$pg = ($pg - 1) * 20;
$query = 'SELECT * FROM gallery WHERE user = "'.$user.'" ORDER BY picdate DESC LIMIT '.$pg.',20';

if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	
	$tpl->set('numberOfPictures', mysql_num_rows($result));
	$pictures = '';
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
		
		$tpl->set('id', $row['id']);
				$tpl->set('link', 'ownPics');
				$date = $row['picdate'];
				$tpl->set('date', date('d-m-Y', strtotime($date)));
		if ($row['name']){
			$tpl->set('name', $row['name']);
		} else{
			$tpl->set('name', 'Без име');
		}
		if ($user == $_SESSION['sessusr']){
			$tpl->set('edit', '<td><a class="inner" href="index.php?cmd=editImgScreen&imgid='.$row['id'].'">Редактирай</a></td>');
		} else {
			$tpl->set('edit', '');
		}
		
		if ($i % 2) {
			$tpl->set('bgColor', '#F2F2F2');
		} else {
			$tpl->set('bgColor', '#E0E0E0');
		}
		$i++;

		$pictures = $pictures . $tpl->fetch('_profile_pictures_row.html');
	}

	$tpl->set('pictures', $pictures);

	if ($i == 0) {
		$tpl->set('pictures', $msgArray['br_no_such_pics']);
	}
	
	
	if (!empty($msg)) {
		$tpl->set('msg', $msgArray[$msg]);
	} else {
		$tpl->set('msg', '');
	}
	
	$numImages = mysql_query('SELECT Count(*) as num FROM gallery WHERE user = "'.$user.'"') or die(mysql_error());	
	$row = mysql_fetch_assoc($numImages);
	$numImages = $row['num'];
	$numPages = (int)($numImages/20 + 1);
	$pg = '';
	$page = '';
	for ($i = 1; $i <= $numPages; $i++){
		$tpl->set('pg', $i);
		$page = $page.$tpl->fetch('_pages.html');
	}
	$tpl->set('link', 'browse');
	$tpl->set('pages', $page);
	$tpl->set('JavaScript', $tpl->fetch('JavaScript.js'));
	$tpl->set('content', $tpl->fetch('_profile_pictures.html'));
	print $tpl->fetch('_main_template.html');

}

function browseComments($user = '', $pg = ''){
global $DBH, $tpl, $msgArray;
$pg = ($pg - 1) * 20;
$query = 'SELECT gallery.id as picid, date, name, text FROM comments, gallery 
WHERE comments.picture = gallery.id AND comments.user = "'.$user.'" ORDER BY date DESC LIMIT '.$pg.',20';

if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	
	$tpl->set('numberOfComments', mysql_num_rows($result));
	$comments = '';
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$tpl->set('picid', $row['picid']);
		$date = $row['date'];
		$tpl->set('date', date('d-m-Y', strtotime($date)));
		$tpl->set('text', $row['text']);
		$tpl->set('autor', '');
		if ($row['name']){
			$tpl->set('name', $row['name']);
		} else{
			$tpl->set('name', 'Без име');
		}
		
		if ($i % 2) {
			$tpl->set('bgColor', '#F2F2F2');
		} else {
			$tpl->set('bgColor', '#E0E0E0');
		}
		$i++;

		$comments = $comments . $tpl->fetch('_profile_comments_row.html');
	}

	$tpl->set('comments', $comments);

	if ($i == 0) {
		$tpl->set('comments', $msgArray['br_no_such_comments']);
	}
	
	
	if (!empty($msg)) {
		$tpl->set('msg', $msgArray[$msg]);
	} else {
		$tpl->set('msg', '');
	}
	
	$numComments = mysql_query('SELECT Count(*) as num FROM comments WHERE user = "'.$user.'"') or die(mysql_error());	
	$row = mysql_fetch_assoc($numComments);
	$numComments = $row['num'];
	$numPages = (int)($numComments/20 + 1);
	$pg = '';
	$page = '';
	for ($i = 1; $i <= $numPages; $i++){
		$tpl->set('pg', $i);
		$page = $page.$tpl->fetch('_comments_pages.html');
	}
	
	$tpl->set('pages', $page);
	$tpl->set('JavaScript', $tpl->fetch('JavaScript.js'));
	$tpl->set('content', $tpl->fetch('_profile_comments.html'));
	print $tpl->fetch('_main_template.html');

}

function editProfileScreen($user, $errorMsg=''){
	global $DBH, $tpl, $msgArray;
	
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	
	$query = 'select * from users where username = "'.$user.'"';
	if (!$result = mysql_query($query)) {
			print mysql_error();
			exit; 
		}
		while ($row1 = mysql_fetch_assoc($result)){
			$tpl->set('username', $row1['username']);
			$tpl->set('firstName', $row1['firstName']);
			$tpl->set('lastName', $row1['lastName']);
			$tpl->set('code', $row1['country']);
			$tpl->set('camera', $row1['camera']);
			$tpl->set('lenses', $row1['lenses']);
			$country =  $row1['country'];
			$code = $row1['country'];
		}
		
	$query = 'SELECT * FROM countries';
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; 
	}
	$countries='';
	while ($row2 = mysql_fetch_assoc($result)) {
		$tpl->set('opTitle', $row2['name']);
		$tpl->set('opValue', $row2['code']);

		if(($country == $row2['code'])) {
			$tpl->set('opSelected', 'selected');
		}else {
			$tpl->set('opSelected', '');
		}

		$countries = $countries.$tpl->fetch('_select_box.html');
	}				
	$tpl->set('countryRow', $countries);
	
	$tpl->set('content', $tpl->fetch('_edit_profile.html'));
	print $tpl->fetch('_main_template.html');
}

function editProfile($user, $firstName, $lastName, $camera, $lenses, $code){
	global $DBH, $tpl, $msgArray;
	
if (!preg_match("/^[a-zA-Zа-яА-Я ]*$/",$firstName)) {
  profile($user, 'invalid_first_name');
  exit;
}
if (!preg_match("/^[a-zA-Zа-яА-Я ]*$/",$lastName)) {
  profile($user, 'invalid_last_name');
 exit;
}
	$query = 'UPDATE users
						SET firstName='. "'" . addslashes($firstName) . "'".',
						lastName='. "'" . addslashes($lastName) . "'".',
						camera='. "'" . addslashes($camera) . "'".',
						lenses='. "'" . addslashes($lenses) . "'".',
						country = '."'".addslashes($code)."'".'
						WHERE username="'.$user.'"';
	$result = mysql_query($query);
	profile($user,'');
}

function changePasswordScreen($msg){
	global $DBH, $tpl, $msgArray;
	
	if (!empty($msg)) {
		$tpl->set('errorMsg', $msgArray[$msg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	
	$tpl->set('content', $tpl->fetch('_change_password_screen.html'));
	print $tpl->fetch('_main_template.html');
}

function changePassword($newPass1, $newPass2){
	global $DBH, $tpl;
	$msg='';
	if ($newPass1 != $newPass2){
		$msg = 'old_pass_error';
	} else {
		$query = "SELECT * FROM users WHERE username = '".$_SESSION['sessusr']."'";
		if (!$result = mysql_query($query)) {
			print mysql_error();
			exit; 
		}
		$row = mysql_fetch_assoc($result);
		$row = mysql_fetch_assoc($result);
		$password = $_POST['oldPass'];
		$password = strip_tags($password);
		$password = md5($password);
		if ($row['password'] != $password){
			$msg = 'no_match';
		} else {
			$password = md5($_POST['newPass1']);
			$query = "UPDATE users SET password='".$password."' WHERE username = '".$_SESSION['sessusr']."'";
			$msg = 'change_succesfull';
			if (!$result = mysql_query($query)) {
				print mysql_error();
				exit; 
			}

		}
	}
	changePasswordScreen($msg);
}
?>