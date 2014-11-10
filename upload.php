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


// � Multipart-����� �� ���������� ��� input-���� upfile
// ��� ��� ���������� ��� ������ � �������� $_FILES
if(isset($_FILES["upfile"])) 
{ 
// �������� ���������� � ����������� �����
    $upfile      = $_FILES['upfile']['tmp_name']; 
    $upfile_name = $_FILES['upfile']['name']; 
    $upfile_size = $_FILES['upfile']['size']; 
    $upfile_type = $_FILES['upfile']['type']; 
    $error_code  = $_FILES['upfile']['error']; 
      
  // ���� ������ ���
    if($error_code == 0) 
    {
    // �������� MIME-����, ���� �� �����, ���������������   
     if ($upfile_type!=="image/jpeg") {
		$msg = 'upload_unsuccesfull';
		profile($_SESSION['sessusr'],$msg);
		exit;
   }

   // �������� ������ � ������ ����������� �������� exif_read_data
   $info = exif_read_data ($upfile,'', true, false);
      
   $w = $info['COMPUTED']['Width'];
   
   if ($w > 1024){
		$msg = 'upload_unsuccesfull';
		profile($_SESSION['sessusr'],$msg);
		exit;
   }
     
   // ����������� ���� ������ ���� ��������. ����� ���� ��� ����� ���, ������� ��������� � �������� ���������� � �������  
   // ��������� ��� ����� ��� ����������� ��� ������������
	 $tm = time();
	 // ���������� ���������� �����
	 $ext = ".jpg";

	 // ������ ��� ����� �� ������� �����:
	 // �������/�������_���_����������_�����_���������_�����.����������
   $upfile_name = $dir . basename($upfile) . $tm . $ext;
   
   // ��� ����� ��� �� (�� �� �����, �� ��� ��������)
   $upfile_name_db = basename($upfile) . $tm . $ext;

   // ���������� ����������� ���� �� ���������� �������� � ������� $dir 
   move_uploaded_file($upfile, $upfile_name);
   
   // ��������� ���������� � ��
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
   // ������� ��������� �����
   $mini_name = $dir . "small_" . basename($upfile) . $tm . $ext;
   
   // ������ ��������� 100�100, �������� JPEG = 75%
   resize($upfile_name, $mini_name, 250,200,75);

   // ���� ��������, ��������� �������, ���������� � �� ���������:

     } 
} else {
	$msg = 'upload_unsuccesfull';
}
profile($_SESSION['sessusr'],$msg);
}
?>