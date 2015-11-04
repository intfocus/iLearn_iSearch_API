<?php
//首先检测是否支持curl
if (!extension_loaded("curl")) {
   trigger_error("对不起，请开启curl功能模块！", E_USER_ERROR);
}

//构造xml
$xmldata='<AATAvailReq1>'.
'<Agency>'.
'<Iata>1234567890</Iata>'.
'<Agent>lgsoftwares</Agent>'.
'<Password>mypassword</Password>'.
'<Brand>phpmind.com</Brand>'.
'</Agency>'.
'<Passengers>'.
'<Adult AGE="" ID="1"></Adult>'.
'<Adult AGE="" ID="2"></Adult>'.
'</Passengers>'.
'<HotelAvailReq1>'.
'<DestCode>JHM</DestCode>'.
'<HotelCode>OGGSHE</HotelCode>'.
'<CheckInDate>101009</CheckInDate>'.
'<CheckOutDate>101509</CheckOutDate>'.
'<UseField>1</UseField>'.
'</HotelAvailReq1>'.
'</AATAvailReq1>';

$jsonData = array(
   array(
   'ProblemId' => '123',
   'SubmitAnswer' => 'ABCD' 
   ),
   array(
   'ProblemId' => '1234',
   'SubmitAnswer' => 'ABCD' 
   )
);

$jsonData = array(
   'UserId' => '4',
   'TrainingId' => '1',
   'CancelMsg' => 'abcd'
);
// $jsonData = array(
   // 'UserId' => '1427',
   // 'FunctionName' => 'LoginViewController.m, -[LoginViewController actionOutsideLoginSuccessfully], 275',
   // 'ActionName' => 'U767bU5f55',
   // 'ActionTime' => '2015-08-24 11:02:01',
   // 'ActionReturn' => 'network: online',
   // 'ActionObject' => 'U6210U529f/U5728U7ebf',
   // 'AppName' => 'iSearch1');

$jsonDataEncoded = json_encode($jsonData);
// echo $jsonDataEncoded;
// return;

//初始一个curl会话
$curl = curl_init();

//设置url
//curl_setopt($curl, CURLOPT_URL,"http://192.168.186.134/phptest/xmltest/dealxml.php");
curl_setopt($curl, CURLOPT_URL,"http://192.168.186.134/phptest/api/TraineeCancel_Api.php");

//设置发送方式：post
curl_setopt($curl, CURLOPT_POST, true);

//设置发送数据
// curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);
curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonDataEncoded);

//抓取URL并把它传递给浏览器
curl_exec($curl);

//关闭cURL资源，并且释放系统资源
curl_close($curl);
?>
