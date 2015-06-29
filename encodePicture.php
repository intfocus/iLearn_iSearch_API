<?php
////////////////////////////////////////////////////////
//Dylan
//根據網頁中的圖片連結,將圖片以base64_encode內嵌在網頁中
///////////////////////////////////////////////////////

define(FILE_ERROR, -3);

////////////////////////////////////////////////////////////////////////////////////////
// 該函數會分析img標籤，提取src的屬性值。但是，src的屬性值必須被引號包圍，否則不能提取
// 目前只有針對png圖檔處理
// @param string $content HTML內容
// @param string $absolutePath網頁的絕對路徑。如果HTML內容裡的圖片路徑為相對路徑，那麼就需要填寫這個參數，來讓該函數自動填補成絕對路徑。這個參數最後需要以/結束
// @param bool $isEraseLink是否去掉HTML內容中的鏈接
////////////////////////////////////////////////////////////////////////////////////////
function getPicDocument($content , $absolutePath = "" , $isEraseLink = true)
{
   /*
   if ($isEraseLink)
     $content = preg_replace('/<a\s*.*?\s*>(\s*.*?\s*)<\/a>/i' , '$1' , $content);  
   */
   $images = array();
   $files = array();
   $matches = array();
   ///////////////////////////////////////////////
   //取出圖片位址
   //////////////////////////////////////////////
   if (preg_match_all('/<img[\d\D]*?src\s*?=\s*?[\"\'](.*?)[\"\'](.*?)\/>/i',$content ,$matches))
   {
      //echo "reading picture path";
      $arrPath = $matches[1];
      for ($i = 0 ; $i < count($arrPath) ; $i++)
      {
         $path = $arrPath[$i];
         $imgPath = trim( $path );
         if ( $imgPath != "")
         {
             //$files[] = $imgPath;
             $images[] = $imgPath;
         }
      }
   }
   ///////////////////////////////////////////////
   //對圖片內容作64位元編碼
   //////////////////////////////////////////////        
   for ($i = 0 ; $i < count($images) ; $i++)
   {
      $image = $images[$i];
      if (@fopen($image , 'r'))
      {
         $imgcontent = @file_get_contents($image);
         if ($content)
         {
            $pic = "data:image/png;base64,";
            $pic .= chunk_split(base64_encode($imgcontent));            
            $content = str_replace($image, $pic, $content);
         }
      }
      else
      {
         return FILE_ERROR;
      }
   }
   ///////////////////////////////////////////////
   //回傳圖片經過編碼的html字串
   //////////////////////////////////////////////        
   return $content;
}

/* $source = file_get_contents('temp.html');
$source = getPicDocument($source, "");
echo $source;
file_put_contents("enc64.html",  $source);   */
?>
