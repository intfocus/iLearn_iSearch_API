<?php

////////////////////////////////////////////////////////////////////////////////////
// 2014/08/05 created by odie
// Customize for MOEA, generate scan status CSV file to download
// Also solve the problem to generate csv file in UTF-16LE 
//
// #001 2014/12/03 EnTie customization, modify the header column for downloadingn CSV
////////////////////////////////////////////////////////////////////////////////////


define("DELAY_SEC", 3);

session_start();
if (!session_is_registered("GUID") || !session_is_registered("loginLevel") || !session_is_registered("loginName"))  //check session
{
   session_write_close();
   sleep(DELAY_SEC);
   header("Location:main.php");
   exit();
}
if ($_SESSION["GUID"] == "" || $_SESSION["loginLevel"] == "" || $_SESSION["loginName"] == "")
{
   session_write_close();
   sleep(DELAY_SEC);
   header("Location:main.php");
   exit();
}
session_write_close();

$data = urldecode($_POST['status']);

// add table header
$data = "序號\t電腦名稱\t人員名稱\t部門\tIP\t登入帳號\t狀態\t含個資檔案數\t開始時間\t完成時間\r\n" . $data;

// convert to UTF-16LE for Windows Excel
$data = mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');

// add UTF-16LE BOM
$data = chr(255) . chr(254) . $data;

header('Content-type: text/csv;');
header('Content-disposition: attachment; filename=P-Marker_Report_User_Status.csv');
echo $data;

?>
