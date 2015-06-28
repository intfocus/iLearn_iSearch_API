<?php
//////////////////////////////////////////
// #001 20140128 modified by Odie, 修改更換字串的部分，換成空字串("")會造成Chrome顯示錯誤，把它改成換成空白字串
//
//////////////////////////////////////////
//////////////////////////////////////////
//Dylan
//修改html中的下載按鈕,使之功能恢復
//並以此修改過的html轉換成mht與圖片內嵌的html
//覆蓋原本沒有下載功能的mht與html
///////////////////////////////////////
  //----- Define -----
   define(FILE_ERROR, -3);   
   define(SUCCESS, 0);      
   define(INPUT_PARAMETER_ERROR, -4);  
   $working_path = dirname(__FILE__);  
   if(file_exists("$working_path/convertToMht.php"))
   {
      include_once("$working_path/convertToMht.php");
   }
   else
   {
      return FILE_ERROR;
   }
   if(file_exists("$working_path/encodePicture.php"))
   {
      include_once("$working_path/encodePicture.php");
   }
   else
   {
      return FILE_ERROR;
   }

   function changeHtml($reportID, $reportPath, $reportName, $working_link)
   {   
      $url_prefix = $working_link;
      $workButton = 
         " 
            <!-- send through mail end -->
            <span class=\"funcBtn send\" onclick=\"mailBox()\"><span class=\"icon\"></span>寄送</span>

            <a style=\"text-decoration:none;\" href='$url_prefix/mailPersonalReport.php?reportID=$reportID' target=_blank 
            onclick=\"return confirm('提醒您：請先確認已匯入「用戶端電腦清單」，方能將簡易個人報表寄送給每個掃描者。找不到對應 email 的用戶報表將會寄送至指定的管理者。\\n\\n確定要繼續執行此功能嗎？');\">
            <span class=\"funcBtn download\">
            <span class=\"icon\"></span>
            寄送簡易個人報表 
            </span>
            </a>

            <a style=\"text-decoration:none;\" href='$url_prefix/downloadReport.php?reportID=$reportID' 
            onclick=\"alert('提醒您：報表請先將 zip 「解壓縮」另存之後，再使用其中的 Excel 檔，謝謝！');\">
            <span class=\"funcBtn download\">
            <span class=\"icon\"></span>
            下載報表
            </span>
            </a>
         ";     
      if(!@($file = file_get_contents("$reportPath/originalHtml.tmpl")))
      {
         return FILE_ERROR;
      }               
      $deleteStart = strrpos($file, "<!--replaceButtonStart-->");
      $deleteEnd = strrpos($file, "<!--replaceButtonEnd-->");   
      $htmlFile;
      $mhtFile;
      $tempFileName = "temp";
      
      if($reportPath == null || $reportName == null || $reportID == null || $working_link == null)
      {
         return INPUT_PARAMETER_ERROR;      
      }
      for($i = $deleteStart ; $i < $deleteEnd ; $i++)
      {
         $file[$i] = " ";  // #001
      }
      $file = str_replace("<!--replaceEnd-->", $workButton, $file);  
      $file = str_replace("\$reportID", $reportID, $file);   
      $file = str_replace("\$url_prefix", $url_prefix, $file);      
      $htmlFile = getPicDocument($file , "");
      $mhtFile = getMhtDocument($file , "");
      if(!@(file_put_contents("$reportPath/$tempFileName.html" , $htmlFile)))
      {
         return FILE_ERROR;
      }  
      if(!@(file_put_contents("$reportPath/$tempFileName.mht" , $mhtFile)))
      {
         return FILE_ERROR;
      }              
      if(!@(rename("$reportPath/$tempFileName.html", "$reportPath/$reportName.html")))
      {
         return FILE_ERROR;      
      }
      if(!@(rename("$reportPath/$tempFileName.mht", "$reportPath/$reportName.mht")))
      {
         return FILE_ERROR;      
      } 
      return SUCCESS;
   }

/*    $reportID = "abcdefg";
   $reportName = "pmark";   
   $reportPath = "doc";
   $file = changeHtml($reportID, $reportPath, $reportName);
   echo $file; */
?>
