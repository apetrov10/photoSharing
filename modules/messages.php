<?php
function sendMessage($user1, $user2){
	global $DBH, $tpl;
	$query = "INSERT INTO `messages`(`id`, `user1`, `user2`, `title`, `text`, `read_m`, date) 
	VALUES (0, '".$user1."','".$user2."', '". addslashes($_POST['title']) ."','". addslashes($_POST['text']) ."',0, NOW())";
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	$msg = 'msg_sent';
	browseMessages($msg);
}

function sendMsgScreen($user){
	global $DBH, $tpl;
	$tpl->set('user2', $user);
	$tpl->set('content', $tpl->fetch('_send_message_screen.html'));
	print $tpl->fetch('_main_template.html');
}

function browseMessages($msg) {
	
	global $DBH, $tpl, $msgArray;
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	$query = "SELECT * FROM messages WHERE user2='".$_SESSION['sessusr']."' ORDER BY date DESC";
	$messages='';
	// izpylniavame zaiavkata
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	
	if (mysql_num_rows($result) < 1){
			$msg='no_messages';
			$tpl->set('messages', '');
		} else {
	
			while ($row = mysql_fetch_assoc($result)) {
				if ($row['read_m']== 0){
					$tpl->set('bold', '<b>');
				} else {
					$tpl->set('bold', '');
				}
				$tpl->set('mid', $row['id']);
				$tpl->set('autor', $row['user1']);
				$tpl->set('title', $row['title']);
				
				$messages = $messages . $tpl->fetch('_messages_row.html');
			}
			$tpl->set('messages', $messages);
		}
	if (!empty($msg)) {
		$tpl->set('msg', $msgArray[$msg]);
	} else {
		$tpl->set('msg', '');
	}
	
	$tpl->set('content', $tpl->fetch('_browse_messages.html'));
	print $tpl->fetch('_main_template.html');
}

function viewMessage($mid) {
	
	global $DBH, $tpl, $msgArray;
	
	$query = "SELECT * FROM messages WHERE id='".$mid."'";
	$update = "UPDATE `messages` SET `read_m`= 1 WHERE id='".$mid."'";
	if (!$result = mysql_query($update)) {
		print mysql_error();
		exit; 
	}
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; 
	}
	while ($row = mysql_fetch_assoc($result)) {
	
		$tpl->set('autor', $row['user1']);
		$tpl->set('date', $row['date']);
		$tpl->set('title', $row['title']);
		$tpl->set('text', $row['text']);
	}
	$tpl->set('content', $tpl->fetch('_view_message_screen.html'));
	print $tpl->fetch('_main_template.html');
	
}

function deleteMessage($mid) {
	
	global $DBH, $tpl, $msgArray;
	
	$query = "DELETE FROM messages WHERE id='".$mid."'";
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; 
	}
	browseMessages('msg_deleted');
	
}

function countMessages() {
	
	global $DBH, $tpl, $msgArray;
	
	$query = "Select COUNT(*) cn FROM messages WHERE read_m = 0 AND user2='".$_SESSION['sessusr']."'";
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; 
	}
	while ($row = mysql_fetch_assoc($result)) {
		$tpl->set('num', $row['cn']);
	}
}
?>