<?php
function adminPanel() {
	global $tpl;
	$tpl->set('content', $tpl->fetch('_admin_panel.html'));
	print $tpl->fetch('_main_template.html');
}
function controlUsers($pg, $search = '') {
	
	global $DBH, $tpl;
	$pg = ($pg - 1) * 20;
	
		if (!empty($search)) {
		$WHERE_SEARCH = "(username LIKE '%$search%')";
		$tpl->set('search', $search);
	} else {
		$WHERE_SEARCH = '(1)';
		$tpl->set('search', '');
	}
	
	$query = 'SELECT username, userGroup FROM users WHERE '.$WHERE_SEARCH.' ORDER BY username LIMIT '.$pg.',20';

	// izpylniavame zaiavkata
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit;
	}
	
	$tpl->set('numberOfUsers', mysql_num_rows($result));
	$users = '';
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
			
			$tpl->set('username', $row['username']);
			if ($row['userGroup'] == 4){
				$tpl->set('delete', '');
				$tpl->set('modrights', '');
				$tpl->set('ban', '');
				$tpl->set('modLink', '');
				$tpl->set('banLink', '');
			} else if ($row['userGroup'] == 1){
				$tpl->set('delete', 'Изтрий');
				$tpl->set('modrights', 'Премахни модератор');
				$tpl->set('ban', 'Накажи');
				$tpl->set('modLink', 'removeMod');
				$tpl->set('banLink', 'banUser');
			} else if ($row['userGroup'] == 3){
				$tpl->set('delete', 'Изтрий');
				$tpl->set('modrights', 'Направи модератор');
				$tpl->set('ban', 'Премахни наказание');
				$tpl->set('modLink', 'makeMod');
				$tpl->set('banLink', 'unbanUser');
			} else {
				$tpl->set('delete', 'Изтрий');
				$tpl->set('modrights', 'Направи модератор');
				$tpl->set('ban', 'Накажи');
				$tpl->set('modLink', 'makeMod');
				$tpl->set('banLink', 'banUser');
			}
			$users = $users . $tpl->fetch('_control_users_row.html');
		
		
	}
	$numUsers = mysql_query('SELECT Count(*) as num FROM users') or die(mysql_error());	
	$row = mysql_fetch_assoc($numUsers);
	$numUsers = $row['num'];
	$numPages = (int)($numUsers/20 + 1);
	$pg = '';
	$page = '';
	for ($i = 1; $i <= $numPages; $i++){
		$tpl->set('pg', $i);
		$tpl->set('pg1', $i);
		$tpl->set('link', 'controlUsers');
		$page = $page.$tpl->fetch('_pages.html');
	}

	$tpl->set('pages', $page);
	$tpl->set('users', $users);

	$tpl->set('JavaScript', $tpl->fetch('JavaScript.js'));
	
	$tpl->set('content', $tpl->fetch('_control_users.html'));
	print $tpl->fetch('_main_template.html');
	
}

function banUser($set, $user) {
	global $DBH, $tpl;
	if ($set == 1){
		$usrGroup = 3;
	} else {
		$usrGroup = 2;
	}
	
	$query = "UPDATE users SET userGroup = ".$usrGroup." WHERE username = '".$user."'";
	
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit;
	}
	controlUsers(1,'');
}

function moderatorRights($set, $user) {
	global $DBH, $tpl;
	if ($set == 1){
		$usrGroup = 1;
	} else {
		$usrGroup = 2;
	}
	
	$query = "UPDATE users SET userGroup = ".$usrGroup." WHERE username = '".$user."'";
	
	// izpylniavame zaiavkata
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	controlUsers(1,'');
}

function deleteUser($user) {
	global $DBH, $tpl;
	
	$queryUsers = "DELETE FROM `users` WHERE username = '".$user."'";
	$queryImages = "DELETE FROM `gallery` WHERE user = '".$user."'";
	$queryRatings = "DELETE FROM `ratings` WHERE user = '".$user."'";
	$queryComments = "DELETE FROM `comments` WHERE user = '".$user."'";
	$queryMessages = "DELETE FROM `messages` WHERE user1 = '".$user."' OR user2 = '".$user."'";
	
	if (!$result = mysql_query($queryUsers)) {
		print mysql_error();
		exit;
	}
	if (!$result = mysql_query($queryImages)) {
		print mysql_error();
		exit;
	}
	if (!$result = mysql_query($queryRatings)) {
		print mysql_error();
		exit;
	}
	if (!$result = mysql_query($queryComments)) {
		print mysql_error();
		exit;
	}
	if (!$result = mysql_query($queryMessages)) {
		print mysql_error();
		exit;
	}
	controlUsers(1);
}
?>