<?php

// Module Authentication manager

function getLoginScreen($errorMsg = '') {
		
	global $tpl, $msgArray;
	
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	
	$tpl->set('content', $tpl->fetch('_login_screen.html'));
	print $tpl->fetch('_main_template.html');
	
			
}

// login
function login($initUser, $initPass) {
		
	global $DBH, $secretKey;
	
	$procUser = "'" . mysql_real_escape_string(stripslashes($initUser)). "'";
	$initPass = md5(stripslashes($initPass));
	$initPass = "'" . mysql_real_escape_string($initPass) . "'";
	//$initPass = "'" . $initPass . "'";	
	$query = 'SELECT username, userGroup, lastLogin  FROM users 
					where password='.$initPass.' AND username='.$procUser;
	
	// izpylniavame zaiavkata
	if (!$result = mysql_query($query)) {
		// sintaktichna greshka
		return -1;
	}
	if ($row = mysql_fetch_assoc($result)) {
		// ima potrebitel s takiva user i pass
		// devai sys sledvashtite proverki
		// obnoviane se last login atributa
		
		$currentTime = time();
		$llUpdateQuery = 'UPDATE users SET lastLogin='.$currentTime.' WHERE username='.$procUser;
		if (!$result = mysql_query($llUpdateQuery)) {
			// sintaktichna greshka
			return -1;
		}
		
		session_regenerate_id(true);

		$_SESSION['sessusr'] = $row['username'];
		$_SESSION['userGroup'] = $row['userGroup'];
		$_SESSION['lastActivity'] = $currentTime;
		$_SESSION['idhash'] = md5($_SERVER['HTTP_USER_AGENT']);
		
		return $row['userGroup'];
	} else {
		// nevalidni potrebitelsko ime ili parola
		return 0;

	}
	
	
} // end of login();
	
	
function isUserLogged() {
	
	global $session_timeout;
	
	// proverka dali ima lognat potrebitel
	if (empty($_SESSION['sessusr'])) {
		return 0;
	}
	
	// proverka dali ne e iztekla sesiata
	if (($_SESSION['lastActivity'] + $session_timeout) < time()) {
		return 0;
	}
	
	// proverka za otkradnata sesia (na baza identifikacia na browsera)
	if (md5($_SERVER['HTTP_USER_AGENT']) != $_SESSION['idhash']) {
		return 0;
	}
		
	return 1;
}
	
// void logout()
function logout() {
	
	unset($_SESSION['sessusr']);
	unset($_SESSION['userGroup']);
	unset($_SESSION['lastActivity']);
}
	
	


?>