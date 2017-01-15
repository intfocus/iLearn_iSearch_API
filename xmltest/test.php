<?php
// $file = 'd:/phptest/index3.html';
// $filetmp = realpath($file); //要上传的文件
// $fields['upimg'] = curl_file_create($filetmp); // 前面加@符表示上传图片 
// $fields['type'] = "images";
// $ch =curl_init();
// 
// 
// curl_setopt($ch,CURLOPT_URL,'http://192.168.186.134/phptest/api/FileUpload_Api.php');
// 
// curl_setopt($ch,CURLOPT_POST,true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
// curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
// 
// 
// $content = curl_exec($ch);
// 
// echo $content;

$ch = curl_init();
$post_data = array(
'UserId' => '111',
'FileType' => 'sql',
'file' => curl_file_create('d:/uat.sql')
);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/uat/api/FileUpload_Api.php');
$info = curl_exec($ch);
curl_close($ch);
print_r($info);

?> 