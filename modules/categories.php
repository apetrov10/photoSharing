<?php

function editCatScreen($sid, $formData = '', $errorMsg = '') {
	
	global $DBH, $tpl, $msgArray;
	
	$query = 'SELECT * FROM categories WHERE id='.$sid;
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; 
	}
	
	if (mysql_num_rows($result) < 1) {
		print 'Opitvate se da redaktirate nevalidna specialnost';
		exit;
	}
	
	$row = mysql_fetch_assoc($result);
	$tpl->set('catName', $row['name']);
	$tpl->set('sid', $row['id']);
	$tpl->set('cmd', 'editCat');
	

	if (!empty($formData)) {
		$tpl->set('catName', $formData);	
	}
	
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	
	//print $tpl->fetch('_addEdit_speciality_screen.html');
	$tpl->set('content', $tpl->fetch('_addEdit_Cat.html'));
	print $tpl->fetch('_main_template.html');
	
} // end of editSpecScreen();

function addCategoryScreen($formData, $catID = '') {

	global $DBH, $tpl, $msgArray;
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	if (!empty($formData)) {
		$tpl->set('catName', $formData);	
	} else {
		$tpl->set('catName', '');
	}
	$tpl->set('cmd', 'addCat');
	$tpl->set('content', $tpl->fetch('_addEdit_Cat.html'));
	print $tpl->fetch('_main_template.html');

}

function addEditCat($formData, $catID = '') {
	
	global $DBH, $tpl;
	
	if (empty($formData)) {
		// znachi ne e vyvedeno ime na specialnostta
		if (!empty($catID)) {
			// znachi redaktirame
			editCat('', 'empty_specName');
			exit;
		} else {
			// dobaviame
			addCat('', '');
			exit;
		}
	}
	if (!empty($catID)) {
		$SQLQuery = 'UPDATE categories
						SET name='. "'" . addslashes($formData) . "'".' 
						WHERE id='.$catID;
		$msg = 'cat_edited';
	} else {
		$SQLQuery = 'INSERT INTO categories(name) VALUES('. "'" . addslashes($formData) . "'".')';
		$msg = 'cat_added';
	}
	if (!mysql_query($SQLQuery, $DBH)) {
		print 'Ne moje da se izpylni slednata SQL zaiavka: '.$SQLQuery.'<br>';
		print mysql_error();
	}
	
	// otiva se kym ekrana za pregled na specialnostite
	browseCats('', $msg);
} // end of addEditSpeciality();

function browseCats($search = '', $msg = '') {
	
	global $DBH, $tpl, $msgArray;
	
	// ako $search ne e prazna, znachi potrebiteliat e vyvel neshto po koeto da tyrsi
	if (!empty($search)) {
		$WHERE_SEARCH = "(name LIKE '%$search%')";
		$tpl->set('search', $search);
	} else {
		// a ako e prazna ne se tyrsi po nishto i zatova sled where ima (1), koeto vse edno oznachava che niama where
		$WHERE_SEARCH = '(1)';
		$tpl->set('search', '');
	}
	
	$query = 'SELECT * FROM categories WHERE '.$WHERE_SEARCH.' ORDER BY name';
	
	// izpylniavame zaiavkata
	if (!$result = mysql_query($query)) {
		print mysql_error();
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
	
	$tpl->set('numberOfCategories', mysql_num_rows($result));
	$categories = '';
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
		
		$tpl->set('sid', $row['id']);
		$tpl->set('catName', $row['name']);
		// i tuk moje vmesto da se pishe set() 2 pyti da se polzva cikyl - ama za dva etiketa ...
		
		if ($i % 2) {
			$tpl->set('bgColor', '#F2F2F2');
		} else {
			$tpl->set('bgColor', '#E0E0E0');
		}
		$i++;
		

		$categories = $categories . $tpl->fetch('_browse_categories_row.html');
	}
	$tpl->set('categories', $categories);
	if ($i == 0) {
		$tpl->set('categories', $msgArray['br_no_such_cat']);
	}

	if (!empty($msg)) {
		$tpl->set('msg', $msgArray[$msg]);
	} else {
		$tpl->set('msg', '');
	}
	$tpl->set('JavaScript', $tpl->fetch('JavaScript.js'));
	
	$tpl->set('content', $tpl->fetch('_browse_categories.html'));
	print $tpl->fetch('_main_template.html');
	
} // end of listSpecialities();

function deleteCat($id) {
	
	global $DBH, $tpl, $msgArray;
	$dir = "gallery/";
	$query1 = "DELETE FROM categories WHERE id='$id'";
	$query2 = "Select * FROM gallery WHERE category='$id'";
	$query3 = "DELETE FROM gallery WHERE category='$id'";
	$querytemp = "CREATE Table temp (id INT)";
	$qryInsTemp = "Insert into temp (id) SELECT id FROM gallery WHERE category='$id'";
	$qryDrop = "Drop table temp";
	$query4 = "DELETE FROM ratings WHERE image IN (SELECT Id FROM temp)";
	$query5 = "DELETE FROM comments WHERE picture IN (SELECT Id FROM temp)";
	mysql_query($querytemp);
	mysql_query($qryInsTemp);
	mysql_query($query2);
	if (!$result = mysql_query($query2)) {
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
	if (mysql_query($query1) and mysql_query($query3) and mysql_query($query4) and mysql_query($query5)) {
		mysql_query($qryDrop);
		browseCats('', '');
	} else{
		print mysql_error();
	}
}
	
?>