<?php

//接收传送的数据
$fileContent = file_get_contents("php://input"); 

//转换为simplexml对象
$xmlResult = simplexml_load_string($fileContent);

//foreach循环遍历
foreach($xmlResult->children() as $childItem)    //遍历所有节点数据
{
	//输出xml节点名称和值
	echo $childItem->getName() . "->".$childItem."<br />"; 

	//其他操作省略
	if($childItem->getName()=="HotelAvailReq1")   //捡取要操作的节点
   {
      echo "i say ". ": get you!". "<br />"; //操作节点数据
      foreach($childItem->children() as $childI)    //遍历所有节点数据
      {
         echo $childI->getName() . "->".$childI."<br />";
      }
   }
}

?>