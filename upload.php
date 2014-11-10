<?php
function upload(){
 global $DBH, $tpl, $msgArray;
require_once ('./modules/images.php');
$dir = "gallery/";
$msg = 'upload_unsuccesfull';
function resize($src, $dest, $width, $height, $quality) { 

$im=imagecreatefromjpeg($src); 
$im1=imagecreatetruecolor($width, $height); 
imagecopyresampled($im1,$im,0,0,0,0,$width,$height,imagesx($im),imagesy($im)); 

imagejpeg($im1,$dest,$quality); 

imagedestroy($im); 
imagedestroy($im1); 
}


// В Multipart-форме мы определили имя input-поля upfile
// Это имя используем при работе с массивом $_FILES
if(isset($_FILES["upfile"])) 
{ 
// получаем информацию о загруженном файле
    $upfile      = $_FILES['upfile']['tmp_name']; 
    $upfile_name = $_FILES['upfile']['name']; 
    $upfile_size = $_FILES['upfile']['size']; 
    $upfile_type = $_FILES['upfile']['type']; 
    $error_code  = $_FILES['upfile']['error']; 
      
  // Если ошибок нет
    if($error_code == 0) 
    {
    // проверка MIME-типа, если не нужно, закомментируйте   
     if ($upfile_type!=="image/jpeg") {
		$msg = 'upload_unsuccesfull';
		profile($_SESSION['sessusr'],$msg);
		exit;
   }

   // получаем ширину и высоту изображения функцией exif_read_data
   $info = exif_read_data ($upfile,'', true, false);
      
   $w = $info['COMPUTED']['Width'];
   
   if ($w > 1024){
		$msg = 'upload_unsuccesfull';
		profile($_SESSION['sessusr'],$msg);
		exit;
   }
     
   // Загруженный файл прошел наши проверки. Нужно дать ему новое имя, создать миниатюру и добавить информацию в таблицу  
   // дополняем имя файла для обеспечения его уникальности
	 $tm = time();
	 // определяем расширение файла
	 $ext = ".jpg";

	 // полное имя файла на сервере будет:
	 // каталог/базовое_имя_временного_файла_временная_метка.расширение
   $upfile_name = $dir . basename($upfile) . $tm . $ext;
   
   // имя файла для БД (то же самое, но без каталога)
   $upfile_name_db = basename($upfile) . $tm . $ext;

   // перемещаем загруженный файл из временного каталога в каталог $dir 
   move_uploaded_file($upfile, $upfile_name);
   
   // добавляем информацию в БД
   mysql_select_db("images");
   
   $descr = $_POST['descr'];
   $lense = $_POST['lense'];
   $iso = $_POST['iso'];
   $aperture = $_POST['aperture'];
   $speed = $_POST['speed'];
   $camera = $_POST['camera'];
   $category = $_POST ['id'];
   $name = $_POST ['name'];
   $user = $_SESSION['sessusr'];
   $q = "insert into gallery (`id`, `name`, `user`, `category`, `fname`, `descr`, `camera`, `lense`, `aperture`, `speed`, `iso`, `picdate`)
   values(0,'".addslashes($name) ."',\"$user\", $category, \"$upfile_name_db\",".
   "'" . addslashes($descr) . "',"."'" . addslashes($camera) ."',". "'" . addslashes($lense) ."',". "'" . addslashes($aperture) ."',".
   "'" . addslashes($speed) ."',"."'" . addslashes($iso) ."'".", NOW())";
   if (!$result = mysql_query($q)) {
		print "error";
		exit; // s tozi operator prekratiavame ispylnenieto na programata
	}
    $msg = 'upload_succesfull';
   // создаем миниатюру файла
   $mini_name = $dir . "small_" . basename($upfile) . $tm . $ext;
   
   // размер миниатюры 100х100, качество JPEG = 75%
   resize($upfile_name, $mini_name, 250,200,75);

   // файл загружен, миниатюра создана, информация в БД добавлена:

     } 
} else {
	$msg = 'upload_unsuccesfull';
}
profile($_SESSION['sessusr'],$msg);
}
?>