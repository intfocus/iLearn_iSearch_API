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
   'UserId' => 'E00001',
   'TrainingId' => '3',
   'IssueDate' => '2015/08/23 14:33:43',
   'Status' => '1',
   'Reason' => '等快递快递收到伐',
   'CreatedUser' => '1',
   'CheckInId' => '1'
);
$jsonData = array(
	'UserId' => 'UserId001logapi',
	'FunctionName' => 'FunctionName002logapi',
	'ActionName' => 'ActionName002logapi',
	'ActionTime' => '2015-06-1 18:18:18',
	'ActionReturn' => 'ActionReturn--092logapi',
	'ActionObject' => 'ActionObject--003logapi',
	'AppName' => 'iSearch');

$jsonData = array(
	'ActionName' => 'action log uuid',
	'ActionObject' => '{ app: { name: iLearn version: 2.1.25, dbVersion:NotSet, machine: x86_64[Simulator], sdkName: iphonesimulator8.3, lang: en }, ios: { release: [14.5.0], sysname: [Darwin], nodename: [lijunjiedeMacBook-Air.local] } }',
	'ActionReturn' => 'IGY32M7CEFBMREUK4PW7EWV2OA',
	'ActionTime' => '2015-08-29 16:25:17',
	'AppName' => 'iLearn',
	'FunctionName' => '',
	'UserId' => '1430'
);

$jsonData = array(
   'UserId' => '1429',
   'TrainingId' => '15'
);

$jsonDataEncoded = json_encode($jsonData);
//echo $jsonDataEncoded;
//return;

//初始一个curl会话
$curl = curl_init();

//设置url
//curl_setopt($curl, CURLOPT_URL,"http://192.168.186.134/phptest/xmltest/dealxml.php");
curl_setopt($curl, CURLOPT_URL,"http://127.0.0.1/uat/api/Trainee_Api.php");

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
