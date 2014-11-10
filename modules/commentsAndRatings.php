<?php

function addComment($imgid) {
	
	global $DBH, $tpl, $msgArray;
	$text  = addslashes($_POST['text']);
	$user = $_SESSION['sessusr'];
	$SQLQuery = "INSERT INTO `comments`(`id`, `user`, `text`, `picture`) VALUES ('0','$user','$text',$imgid)";
	if (!mysql_query($SQLQuery, $DBH)) {
		print 'Ne moje da se izpylni slednata SQL zaiavka: '.$SQLQuery.'<br>';
		print mysql_error();
	}
	viewImgScr($imgid, $user);
}

function addRating($imgid, $grade) {
	
	global $DBH, $tpl, $msgArray;
	$user = $_SESSION['sessusr'];
	$SQLQueryRatings = "INSERT INTO `ratings`(`id`, `user`, `image`, `rating`) VALUES ('0','$user','$imgid','$grade')";
	if (!mysql_query($SQLQueryRatings, $DBH)) {
		print 'Ne moje da se izpylni slednata SQL zaiavka: '.$SQLQueryRatings.'<br>';
		print mysql_error();
	}
	$SQLQueryGallery = "UPDATE gallery		
						SET rating= rating + '$grade', numvotes = numvotes + 1
						WHERE id='$imgid'";
	if (!mysql_query($SQLQueryGallery, $DBH)) {
		print 'Ne moje da se izpylni slednata SQL zaiavka: '.$SQLQueryGallery.'<br>';
		print mysql_error();
	}
	$queryForAutor = "SELECT user FROM gallery WHERE id = '".$imgid."'";
	
	// izpylniavame zaiavkata
	if (!$result = mysql_query($queryForAutor)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	while ($row = mysql_fetch_assoc($result)) {	
		$user = $row['user'];
	}
	$SQLQueryUser = "UPDATE users	
						SET rating= rating + '$grade', numvotes = numvotes + 1
						WHERE username='".$user."'";
	if (!mysql_query($SQLQueryUser, $DBH)) {
		print 'Ne moje da se izpylni slednata SQL zaiavka: '.$SQLQueryUsers.'<br>';
		print mysql_error();
	}
	viewImgScr($imgid, $_SESSION['sessusr']);
}

function delComment($id, $imgid) {
	
	global $DBH, $tpl, $msgArray;
	$user = $_SESSION['sessusr'];
	$SQLQuery = "DELETE FROM `comments` WHERE id=".$id;
		$msg = 'comment_deleted';
	if (!mysql_query($SQLQuery, $DBH)) {
		print 'Ne moje da se izpylni slednata SQL zaiavka: '.$SQLQuery.'<br>';
		print mysql_error();
	}
	viewImgScr($imgid, $user);
}
?>