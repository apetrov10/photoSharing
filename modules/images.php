<?php

function addImage(){
	global $DBH, $tpl;
	
	$query = "SELECT * FROM categories";
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; 
	}
	
	if (mysql_num_rows($result) < 1) {
		print 'Opitvate se da dobavite nevalidno izobrajenie';
		exit;
	}
	$imgs='';
	while ($row = mysql_fetch_assoc($result)) {
	
		$tpl->set('catName', $row['name']);
		$tpl->set('id', $row['id']);
		$imgs = $imgs . $tpl->fetch('_upload_form_row.html');
	}
	//$tpl->set('imgRow', $tpl->fetch('_upload_form_row.html'));
	$tpl->set('imgRow', $imgs);
	$tpl->set('content', $tpl->fetch('_upload_form.html'));
	print $tpl->fetch('_main_template.html');
	}
	
function viewImages($category, $pg, $function){
	$dir = "gallery/";
	global $DBH, $tpl, $msgArray;
	$pg = ($pg - 1) * 20;
	if ($function == 1){
		$result1 = mysql_query("select * from gallery where category = '".$category."' ORDER BY picdate DESC LIMIT ".$pg.",20");
		$result2 = mysql_query("select * from categories where id = '".$category."'");
		while ($row=mysql_fetch_array($result2)) {
			$tpl->set('info', "Категория: ".$row['name']);
		}
		$numImages = mysql_query("SELECT Count(*) as num FROM gallery where category = '".$category."'") or die(mysql_error());	
	} else if ($function == 2){
		$tpl->set('info', 'Снимки с рейтинг над 8 от последната седмица');
		$result1 = mysql_query("select * from gallery WHERE `picdate` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND rating > 8.00 ORDER BY picdate DESC LIMIT ".$pg.",20");
		$numImages = mysql_query("SELECT Count(*) as num FROM gallery WHERE `picdate` >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY rating") or die(mysql_error());
	} else {
		$tpl->set('info', 'Галерия от избрани снимки');
		$result1 = mysql_query("select * from gallery WHERE `selected` = 1 ORDER BY picdate LIMIT ".$pg.",20");
		$numImages = mysql_query("SELECT Count(*) as num FROM gallery WHERE `selected` = 1") or die(mysql_error());	
	}
	$images='';
$i=0;
while ($row=mysql_fetch_array($result1)) {
	$i++;
	$mini = $dir . "small_" . $row['fname'];
	$full = $dir . $row['fname'];
	if (file_exists($mini)){
		$tpl->set('full', $full);
		$tpl->set('mini', $mini);
		$tpl->set('imgid', $row['id']);
		$tpl->set('catid', $category);
		$tpl->set('function', $function);
		if ($row['numvotes']>0){
				$grade = $row['rating']/$row['numvotes'];
				$tpl->set('rating', $grade);
			} else {
				$tpl->set('rating', "N/A");
			}
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
	if ($i == 0){
			$tpl->set('errorMsg', $msgArray['no_images']);
		} else {
			$tpl->set('errorMsg', '');
		}
	$row = mysql_fetch_assoc($numImages);
	$numImages = $row['num'];
	$numPages = (int)($numImages/20 + 1);
	$pg = '';
	$page = '';
	if ($numPages == 1){
		$tpl->set('pg', '');
		$tpl->set('pg1', '');
	} else{
		for ($i = 1; $i <= $numPages; $i++){
			$tpl->set('pg', $i);
			if ($function == 1){
				$tpl->set('pg1', $i.'&catid='.$category);
				$tpl->set('link', 'gallery');
			} else{
				$tpl->set('pg1', $i);
				$tpl->set('link', 'bestPics');
			}
			
			$page = $page.$tpl->fetch('_pages.html');
		}
	}
	$tpl->set('pages', $page);
	$tpl->set('images', $images);

	$tpl->set('content', $tpl->fetch('_galleries_by_cat.html'));
	print $tpl->fetch('_main_template.html');
}

function viewCategories(){
	global $DBH, $tpl;
	
	$query = "SELECT * FROM categories ORDER BY name";
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; 
	}
	$catImg = '';
	while ($row=mysql_fetch_array($result)) {
			$tpl->set('catid', $row['id']);
			$tpl->set('category', $row['name']);
			$category = $row['name'];
			$catImg = $catImg . $tpl->fetch('_gallery_cat.html');
		}
	
	$tpl->set('cats', $catImg);
	$tpl->set('content', $tpl->fetch('_galleries.html'));
	print $tpl->fetch('_main_template.html');
}	
function viewImgScr($id,  $user = ''){
		$dir = "gallery/";
		global $DBH, $tpl;
		$comments='';
		$admin_rights = '';
		$delete_rights = '';
		$result = mysql_query("select * from gallery where id=$id");
		if ($user){

		// izpylniavame zaiavkata
			$query_for_edit = "SELECT username, userGroup FROM users WHERE username ='".$_SESSION['sessusr']."'";
			if (!$result2 = mysql_query($query_for_edit)) {
				print mysql_error();
				exit; // s tozi operator prekratiavame ispylnenieto na programata
			}
		}
		while ($row=mysql_fetch_array($result)) {
			$full = $dir . $row['fname'];
			$tpl->set('full', $full);
			$tpl->set('name', $row['name']);
			$tpl->set('autor', $row['user']);
			$autor = $row['user'];
			$tpl->set('descr', $row['descr']);
			$tpl->set('camera', $row['camera']);
			$tpl->set('lense', $row['lense']);
			$tpl->set('speed', $row['speed']);
			$tpl->set('aperture', $row['aperture']);
			if ($row['iso'] == 0){
				$tpl->set('iso', '');
			} else {
				$tpl->set('iso', $row['iso']);
				$tpl->set('iso', $row['iso']);
			}
			$tpl->set('imgid', $row['id']);
			$imgid = $row['id'];
			$selected = $row['selected'];
			if ($row['numvotes']>0){
				$grade = $row['rating']/$row['numvotes'];
				$tpl->set('rating', $grade);
			} else {
				$tpl->set('rating', "N/A");
			}
		}
			if ($user == ''){
				$tpl->set('edit','');
			} else {
				while ($row_for_edit=mysql_fetch_array($result2)) {
				if (($row_for_edit['userGroup'] == 1) OR $row_for_edit['userGroup'] == 4){
					$admin_rights = 1;
					$delete_rights = 1;
					$tpl->set('edit','');
				}
				if ($row_for_edit['username'] == $autor){
					$delete_rights = 1;
					$tpl->set('edit','<p><a class="inner" href="index.php?cmd=editImgScreen&imgid='.$id.'">Редактирай</a>');
				} else {
					$tpl->set('edit','');
				}
				}
			}
		if ($user == ''){
			$_SESSION['sessusr'] = '';
			$tpl->set('rated', 'Само регистрирани потребители могат да коментират и оценяват.<br>');
			$tpl->set('commentPanel', '');
			$tpl->set('edit', '');
		} else {
			$voted = mysql_query("select * from ratings where user='".$_SESSION['sessusr']."'and image='".$id."'");
			if ($_SESSION['sessusr'] == $autor){
				$tpl->set('rated', '');
			} else if (!$row = mysql_fetch_assoc($voted)) {
				$tpl->set('imgid', $id);
				$tpl->set('rated', $tpl->fetch('_rate_image.html'));
			} else {
				$tpl->set('imgid', $id);
				$tpl->set('grade', $row['rating']);
				$tpl->set('rated', $tpl->fetch('_already_rated.html'));					
			}
			$tpl->set('commentPanel', $tpl->fetch('_comment_area.html'));
		}
		if (($admin_rights == 1) AND ($selected==0)){
				$tpl->set('selected', '<a class="inner" href="index.php?cmd=select&id='.$imgid.'">Добави в избрани</a>');
			} else {
				$tpl->set('selected','');
			}
		if ($admin_rights == 1){
				$tpl->set('deleteimg', '<a class="inner" href="index.php?cmd=deleteImg&imgid='.$imgid.'&lnk=0">Изтрий</a>');
			} else {
				$tpl->set('deleteimg','');
			}
		$query = "SELECT * FROM comments WHERE $id = picture ORDER BY date";
	// izpylniavame zaiavkata
	if (!$result2 = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	
	$i = 0;
	while ($row = mysql_fetch_assoc($result2)) {
		
		$tpl->set('autor', $row['user']);
		$tpl->set('text', $row['text']);
		if ($delete_rights == 1){
			$tpl->set('delete', '<a class="inner" href="index.php?cmd=deleteComment&id='.$row['id'].'&imgid='.$imgid.'">Изтрий');
		} else {
			$tpl->set('delete','');
		}
		
		// obrabotva i izvlicha obrabotenia HTML kod za tekushtia red
		$comments = $comments . $tpl->fetch('_comments_row.html');
	}
	
	// tuk veche redowete sa izgenerirani i sega triabva
	// mnojestvoto redove da go vmyknem v HTML koda na _main_template.html
	$tpl->set('comments', $comments);
	$tpl->set('content', $tpl->fetch('_view_image.html'));
	print $tpl->fetch('_main_template.html');
		
	}
	
	function EditImageScr($id, $formData=''){
		$dir = "gallery/";
		global $DBH, $tpl;
		$query = "select * from gallery where id=$id";
		$result1 = mysql_query($query);
		while ($row1 = mysql_fetch_assoc($result1)){
			if ($row1['iso'] == 0){
				$tpl->set('iso', '');
			} else {
				$tpl->set('iso', $row1['iso']);
			}
			$tpl->set('speed', $row1['speed']);
			$tpl->set('camera', $row1['camera']);
			$tpl->set('lense', $row1['lense']);
			$tpl->set('aperture', $row1['aperture']);
			$tpl->set('name', $row1['name']);
			$tpl->set('descr', $row1['descr']);
			$tpl->set('imgid', $row1['id']);
			$cat =  $row1['category'];
		}
		
		$query = 'SELECT * FROM categories';
		if (!$result = mysql_query($query)) {
			print mysql_error();
			exit; 
		}
	$cats='';
	while ($row2 = mysql_fetch_assoc($result)) {
		$tpl->set('opTitle', $row2['name']);
		$tpl->set('opValue', $row2['id']);
		
		if(($cat == $row2['id'])) {
			$tpl->set('opSelected', 'selected');
		}else {
			$tpl->set('opSelected', '');
		}

		
		$cats = $cats.$tpl->fetch('_select_box.html');
	}				
	$tpl->set('imgRow', $cats);
	
	$tpl->set('imgid', $id);
	$tpl->set('content', $tpl->fetch('_edit_image.html'));
	print $tpl->fetch('_main_template.html');
		
	}
	
	function Select($id){
		global $DBH;
		$query = 'UPDATE gallery	
						SET selected=1
						WHERE id='.$id;
		$result = mysql_query($query);

	viewImgScr($id, $_SESSION['sessusr']);
	}
	
	function EditImage($id, $name, $descr, $camera, $lense, $aperture, $iso ,$speed, $catid){
		global $DBH, $tpl;
		//$descr = tpl->fetch($imgDescr);
		//do tuka sme, pisna mi
		$query = 'UPDATE gallery
		
						SET descr='. "'" . addslashes($descr) . "',"
						. "name='" . addslashes($name) . "',"
						. "iso='" . addslashes($iso) . "',"
						. "lense='" . addslashes($lense) . "',"
						. "camera='" . addslashes($camera) . "',"
						. "speed='" . addslashes($speed) . "',"
						. "aperture='" . addslashes($aperture) . "',".'
						category = '.$catid.'
						WHERE id='.$id;
		$result = mysql_query($query);
	viewImgScr($id, $_SESSION['sessusr']);
	
		
	}
	
	function countImages(){
		global $DBH, $tpl;
		$query = "SELECT categories.name nm, count(fname) cn
					FROM categories, gallery
					where categories.id = gallery.category
					group by gallery.name";
		
		if (!$result = mysql_query($query)) {
			print mysql_error();
			exit; 
		} 
		$imgs='';
		while ($row = mysql_fetch_assoc($result)) {
			$tpl->set('name', $row['nm']);
			$tpl->set('num', $row['cn']);
			$imgs = $imgs . $tpl->fetch('_groups_row.html');
			
		}
		$tpl->set('groups', $imgs);
		$tpl->set('content', $tpl->fetch('_groups.html'));
		print $tpl->fetch('_main_template.html');
	}
	
	function browseRes($search = '') {
	//СЃРїСЂР°РІРєР° Р·Р° СЂРµР·РѕР»СЋС†РёРё, Р·Р° РєСѓСЂСЃРѕРІР°С‚Р°
	global $DBH, $tpl;
	
	if (!empty($search)) {
		$WHERE_SEARCH = "(resolution LIKE '%$search%')";
		$tpl->set('search', $search);
	} else {
		$WHERE_SEARCH = '(1)';
		$tpl->set('search', '');
	}
	
	$query = 'SELECT * FROM gallery, categories WHERE '.$WHERE_SEARCH.' and gallery.cat=categories.id order by cat';
	
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit;
	}
	
	$resolutions = '';
	while ($row = mysql_fetch_assoc($result)) {
		
		$tpl->set('fileRes', $row['resolution']);
		$tpl->set('catName', $row['name']);
		$tpl->set('descr', $row['descr']);
		
		$resolutions = $resolutions . $tpl->fetch('_upr_row.html');
	}
	
	$tpl->set('resolutions', $resolutions);

	$tpl->set('content', $tpl->fetch('_upr.html'));
	print $tpl->fetch('_main_template.html');
	
}
function deletePic($id, $linkTo) {
	
	global $DBH, $tpl, $msgArray;
	$dir = "gallery/";
	$query1 = "Select * FROM gallery WHERE id='$id'";
	$query3 = "DELETE FROM gallery WHERE id='$id'";
	$query2 = "DELETE FROM comments WHERE picture='$id'";
	$query4 = "DELETE FROM ratings WHERE image='$id'";
	mysql_query($query1);
	if (!$result = mysql_query($query1)) {
		print mysql_error();
		exit; 
	}
	
	if($row = mysql_fetch_assoc($result)){
	// izpylniavame zaiavkata
	$small = $dir . "small_" . $row['fname'];
	$big = $dir . $row['fname'];
		if(!unlink ($small)){
			echo "error";
		}
		if(!unlink ($big)){
			echo "error";
		}
	}
	$msg = 'delete_successfull';
	if(($result = mysql_query($query2)) AND ($result = mysql_query($query3)) AND ($result = mysql_query($query4))){
		$msg = 'delete_successfull';
	} else{
		$msg = 'delete_unsuccessfull';
	}
	if ($linkTo == 1) {
		browsePictures($_SESSION['sessusr'],1, $msg);
	} else{
		home();
	}
}
?>