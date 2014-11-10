<?php

require_once('./config.php');
require_once('./upload.php');
require_once('./errorMsgs.php');
require_once('./modules/template.php');
require_once('./modules/categories.php');
require_once('./modules/images.php');
require_once('./modules/authMan.php');
require_once('./modules/commentsAndRatings.php');
require_once('./modules/register.php');
require_once('./modules/profile.php');
require_once('./modules/messages.php');
require_once('./modules/admin.php');
require_once('./modules/users.php');
require_once('./class.phpmailer.php');
require_once('./class.smtp.php');
require_once('./modules/resetPassword.php');

session_start();
// zadavame pytia do firektoriata sys HTML shabloni
$path = './templates/';

// syazdawame instakcia na klasa
$tpl = new Template($path);
$tpl->set('charEncoding', 'Windows-1251');
$tpl->set('JavaScript', '');

// tuk pravim vryzkata s bazata ot danni
if (!$DBH = @mysql_connect($host, $user, $password)) {
	$tpl->set('content', $msgArray['DB_unable_to_connect']);
	print $tpl->fetch('_main_template.html');
	exit;
}
mysql_query("SET NAMES cp1251");

if (!mysql_select_db($db_name)) {
	$tpl->set('content', $msgArray['DB_no_db_selected']);
	print $tpl->fetch('_main_template.html');
	exit;
}

if (!empty($_REQUEST['cmd'])) {
	$cmd = $_REQUEST['cmd'];
} else {
	$cmd = 'mainMenu';
}
preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
if (count($matches)>1){
  $tpl->set('IE', 'Този сайт се вижда най-добре с Mozilla Firefox и Google Chorme!');
  } else {
	$tpl->set('IE', '');
}
if(isUserLogged()){
	$query = "SELECT usergroup FROM users WHERE username = '".$_SESSION['sessusr']."'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	countMessages();
	if ($row['usergroup'] == '4'){
		$tpl->set('admin', '<tr><td class = "navigation" align="left"><a class="menu" href="index.php?cmd=adminPanel">Администриране</a></td></tr>');
	} else {
		$tpl->set('admin', '');
	}
	$tpl->set('log_options', $tpl->fetch('_logged_options.html'));
	$tpl->set('user',$_SESSION['sessusr']);
	$tpl->set('logged',$tpl->fetch('_logged_in_as.html'));
	if ($row['usergroup'] == '3'){
		if($cmd == 'logout'){	
				logout();
				header('Location: index.php?cmd=loginScreen');
				exit;
			}
		$tpl->set('msg', 'banned');
		$tpl->set('content', '<h1><font color="red"><b>Потребителят е наказан!</b></font></h1>');
		print $tpl->fetch('_main_template.html');
		exit;
	} else {
		if ($cmd == 'addCategory') {
			// izvikvane na funkcia ot niakoi modul
				addCategoryScreen('','');
			} elseif ($cmd == 'addCat') {
				addEditCat($_POST['name']);
			} elseif ($cmd == 'deleteMsg') {
				deleteMessage($_GET['mid']);
			} elseif ($cmd == 'home') {
				home();
			} elseif ($cmd == 'changePasswordScr') {
				changePasswordScreen();
			} elseif ($cmd == 'deleteImg') {
				deletePic($_GET['imgid'], $_GET['lnk']);
			} elseif ($cmd == 'gallery') {
				viewImages($_GET['catid'], $_GET['pg'], 1);
			} elseif ($cmd == 'upr') {
				browseRes();
			} elseif ($cmd == 'ownPics') {
				browsePictures($_GET['autor'],$_GET['pg']);
			} elseif ($cmd == 'bestPics') {
				viewImages('', $_GET['pg'], 2);
			} elseif ($cmd == 'selectedImages') {
				viewImages('', $_GET['pg'], 3);
			} elseif ($cmd == 'changePassword') {
				changePassword($_POST['newPass1'], $_POST['newPass2']);
			} elseif ($cmd == 'topAutors') {
				topAutors(1);
			}elseif ($cmd == 'ownComments') {
				browseComments($_GET['autor'],$_GET['pg']);
			} elseif ($cmd == 'rate') {
				addRating($_POST['imgid'],$_POST['rating']);
			} elseif ($cmd == 'sendMsgScr') {
				sendMsgScreen($_GET['user']);
			} elseif ($cmd == 'controlUsers') {
				controlUsers($_GET['pg']);
			} elseif ($cmd == 'searchControlUsers') {
				controlUsers($_GET['pg'], $_GET['search']);
			} elseif ($cmd == 'banUser') {
				banUser(1, $_GET['username']);
			} elseif ($cmd == 'unbanUser') {
				banUser(2, $_GET['username']);
			} elseif ($cmd == 'makeMod') {
				moderatorRights(1, $_GET['username']);
			} elseif ($cmd == 'removeMod') {
				moderatorRights(2, $_GET['username']);
			} elseif ($cmd == 'deleteUsr') {
				deleteUser( $_GET['username']);
			} elseif ($cmd == 'adminPanel') {
				adminPanel();	
			} elseif ($cmd == 'viewMsg') {
				viewMessage($_GET['mid']);
			} elseif ($cmd == 'browseMsgs') {
				browseMessages('');
			} elseif ($cmd == 'viewProfile') {
				profile($_GET['autor']);
			} elseif ($cmd == 'autors') {
				browseUsers($_GET['pg'], '');
			} elseif ($cmd == 'searchAutors') {
				browseUsers($_GET['pg'], $_GET['search']);
			} elseif ($cmd == 'profile') {
				profile($_SESSION['sessusr']);
			} elseif ($cmd == 'editProfileScreen') {
				editProfileScreen($_SESSION['sessusr']);
			} elseif ($cmd == 'editProfile') {
				editProfile($_SESSION['sessusr'], $_POST['firstName'], $_POST['lastName'],
				$_POST['camera'],$_POST['lenses'], $_POST['code']);
			} elseif ($cmd == 'searchRes') {
				browseRes($_GET['search']);
			} elseif ($cmd == 'browseCat') {
				browseCats();
			} elseif ($cmd == 'deleteComment') {
				delComment($_GET['id'], $_GET['imgid']);
			} elseif ($cmd == 'browseImages') {
				viewCategories();
			} elseif ($cmd == 'editCat') {
				addEditCat($_POST['name'],$_REQUEST['sid']);
			} elseif ($cmd == 'editCatScreen') {
				editCatScreen($_REQUEST['sid']);
			} elseif ($cmd == 'addImage') {
				addImage();
			} elseif ($cmd == 'upload') {
				upload();
			} elseif ($cmd == 'select') {
				select($_GET['id']);
			} elseif ($cmd == 'deleteCat'){
				deleteCat($_GET['sid']);
			} elseif ($cmd == 'viewImgScreen'){
				viewImgScr($_GET['imgid'], $_SESSION['sessusr']);
			} elseif ($cmd == 'comment'){
				addComment($_POST['imgid']);
			} elseif ($cmd == 'sendMessage'){
				sendMessage($_SESSION['sessusr'], $_POST['user2']);
			} elseif ($cmd == 'editImgScreen'){
				EditImageScr($_GET['imgid']);
			} elseif ($cmd == 'editImage'){
				EditImage($_POST['imgid'], $_POST['name'], $_POST['descr'], $_POST['camera'], $_POST['lense'], 
				$_POST['aperture'], $_POST['iso'], $_POST['speed'], $_POST['cat']);
			}elseif($cmd == 'logout'){	
				logout();
				header('Location: index.php?cmd=loginScreen');
			}elseif ($cmd == 'countImages') {
			// izvikvane na funkcia ot niakoi modul
				countImages();
			
			} else {
				// izvejdane na glavnoto menu
				home();
				}
				
		}
	}
 else {
	$tpl->set('log_options', $tpl->fetch('_login_options.html'));
	$tpl->set('logged','<tr height="20" bgcolor="F1F1F1">
	<td width="200">&nbsp;</td>
	<td align="right"></td>
	</tr>');
	$tpl->set('admin','');
	if($cmd == 'loginScreen'){
		getLoginScreen();
	} elseif($cmd == 'login'){	
		if(login($_POST['username'],$_POST['password']) > 0){
			header('Location: index.php?cmd=home');
		}else {
			getLoginScreen('login_invalid');
		}
	} elseif ($cmd == 'mainMenu'){
			home();
	} elseif ($cmd == 'viewImgScreen'){
			ViewImgScr($_GET['imgid'], '');
	} elseif ($cmd == 'gallery') {
			viewImages($_GET['catid'], $_GET['pg'], 1);		
	} elseif ($cmd == 'register') {
			registerScreen('','');
	} elseif ($cmd == 'registration') {
			registration();
	} elseif ($cmd == 'frgtPswd') {
			resetPswdScrn('');	
	} elseif ($cmd == 'frgPasswordChange') {
			changeFrgtnPassScr($_GET['token']);			
	} elseif ($cmd == 'changeFrgtnPswrd') {
			changeFrgtnPassword($_POST['newPass'], $_POST['username']);
	} elseif ($cmd == 'checkPass') {
			checkMail($_POST['email']);
	} elseif ($cmd == 'bestPics') {
			viewImages('', $_GET['pg'], 2);
	} elseif ($cmd == 'selectedImages') {
			viewImages('', $_GET['pg'], 3);
	} elseif ($cmd == 'home') {
				home();
	} elseif ($cmd == 'login_screen') {
		// izvikvane na funkcia ot niakoi modul
		getLoginScreen();
	} elseif ($cmd == 'browseImages') {
		// izvikvane na funkcia ot niakoi modul
		viewCategories();
	} elseif ($cmd == 'autors') {
				browseUsers($_GET['pg'], '');
	}
	else {
		getLoginScreen();
	}
	}

?>