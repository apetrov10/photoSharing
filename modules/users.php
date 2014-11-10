<?php
function home(){
	$dir = "gallery/";
	global $DBH, $tpl, $msgArray;
	$result1 = mysql_query("select * from gallery where selected = 1 ORDER BY picdate LIMIT 0,1");
$dir = "gallery/";
	global $DBH, $tpl;

	$result2 = mysql_query("select * from gallery ORDER BY picdate DESC LIMIT 0,3");

	$images='';
$i=0;
while ($row=mysql_fetch_array($result2)) {
	$i++;
	$mini = $dir . "small_" . $row['fname'];
	$full = $dir . $row['fname'];
	if (file_exists($mini)){
		$tpl->set('full', $full);
		$tpl->set('mini', $mini);
		$tpl->set('imgid', $row['id']);
		$tpl->set('rating', $row['rating']);
		$tpl->set('autor', $row['user']);
		if ($i%4==0){
			$newLine = "<br>";
		} 
		else{
			$newLine = "";
		}
		$tpl->set('newLine', $newLine);
		$images=$images.$tpl->fetch('gallery_row.html');
	}
}

	$tpl->set('last_ratings', $images);
while ($row=mysql_fetch_array($result1)) {
	$mini = $dir . "small_" . $row['fname'];
	$full = $dir . $row['fname'];
	if (file_exists($mini)){
		$tpl->set('full', $full);
		$tpl->set('mini', $mini);
		$tpl->set('imgid', $row['id']);
	}
}

$query = 'SELECT gallery.id as picid, comments.user as usr, name, comments.date as comdate, text FROM comments, gallery 
WHERE comments.picture = gallery.id ORDER BY date DESC LIMIT 0,10';

if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}

	$comments = '';
	while ($row = mysql_fetch_assoc($result)) {
		$tpl->set('picid', $row['picid']);
		$tpl->set('autor', $row['usr']);
		$date = $row['comdate'];
		$tpl->set('date', date('d-m-Y', strtotime($date)));
		$tpl->set('text', $row['text']);
		if ($row['name']){
			$tpl->set('name', $row['name']);
		} else{
			$tpl->set('name', 'Без име');
		}

		$comments = $comments . $tpl->fetch('_profile_comments_row.html');
	}

	$tpl->set('last_comments', $comments);
	$tpl->set('content', $tpl->fetch('_home.html'));
	print $tpl->fetch('_main_template.html');
}


function browseUsers($pg, $search = '') {
	
	global $DBH, $tpl;
	
	if (!empty($search)) {
		$WHERE_SEARCH = "(username LIKE '%$search%')";
		$tpl->set('search', $search);
	} else {
		// a ako e prazna ne se tyrsi po nishto i zatova sled where ima (1), koeto vse edno oznachava che niama where
		$WHERE_SEARCH = '(1)';
		$tpl->set('search', '');
	}
	
	
	$pg = ($pg - 1) * 20;
	$query = 'SELECT username, userGroup FROM users WHERE '.$WHERE_SEARCH.' ORDER BY username LIMIT '.$pg.',20';
	
	// izpylniavame zaiavkata
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	
	$tpl->set('numberOfUsers', mysql_num_rows($result));
	$users = '';
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
		
		$tpl->set('username', $row['username']);
		// i tuk moje vmesto da se pishe set() 2 pyti da se polzva cikyl - ama za dva etiketa ...
		$users = $users . $tpl->fetch('_view_users_row.html');
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
		$tpl->set('link', 'autors');
		$page = $page.$tpl->fetch('_pages.html');
	}

	$tpl->set('pages', $page);
	$tpl->set('users', $users);
	
	$tpl->set('content', $tpl->fetch('_view_users.html'));
	print $tpl->fetch('_main_template.html');
	
}

function topAutors($pg) {
	
	global $DBH, $tpl;
	
	
	$pg = ($pg - 1) * 20;
	$query = 'SELECT username, rating, numvotes FROM users ORDER BY rating/numvotes DESC LIMIT '.$pg.',20';
	
	// izpylniavame zaiavkata
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	
	$tpl->set('numberOfUsers', mysql_num_rows($result));
	$users = '';
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
		
		$tpl->set('username', $row['username']);
		if ($row['numvotes'] == 0){
			$tpl->set('rating', '0');
		} else {
			$tpl->set('rating', $row['rating']/$row['numvotes']);
		}
		// i tuk moje vmesto da se pishe set() 2 pyti da se polzva cikyl - ama za dva etiketa ...
		$users = $users . $tpl->fetch('_view_top_users_row.html');
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
		$tpl->set('link', 'topAutors');
		$page = $page.$tpl->fetch('_pages.html');
	}

	$tpl->set('pages', $page);
	$tpl->set('users', $users);
	
	$tpl->set('content', $tpl->fetch('_view_top_users.html'));
	print $tpl->fetch('_main_template.html');
	
}
?>