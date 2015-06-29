<?php
////////////////////////////////////////
//refreshDepartPage.php
//
//1.return string of the department page
//
//#001 Phantom+Odie, 20130430, 
//         For those departments come from computer list, cannot be edited or deleted.
//         flag=1 means manually
//         flag=2 means added by computer list
//         flag=3(1+2) means manually first, then added by computer list
////////////////////////////////////////

define(DELAY_SEC, 3);
define(GUID_LENGTH, 36);
define(PARAMETER_ERROR, -2);
 
function refreshDepartPage($link, $GUID)
{
   if(!$link || strlen($GUID) != GUID_LENGTH)
      return PARAMETER_ERROR;
   
   define(PAGE_SIZE, 100);  //page size
  
   //return value
   define(DB_ERROR, -1);
   
   //query
   $str_query;
   $result;                 //query result
   $row;                    //1 data array
   
   //depart
   $depID;
   $dep_name;

   //return page
   $return_string;

   //link 
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }

   //----- query -----
   $str_query = "
      select *
      from department
      where GUID = '$GUID' order by flag";

   if ($result = mysqli_query($link, $str_query))
   {
      $depNumber = mysqli_num_rows($result);
      while ($row = mysqli_fetch_assoc($result))
      {
         $depID[] = $row["depID"];
         $dep_name[] =$row["dep_name"];
         $dep_flag[] = $row["flag"]; //#001 add
      }
      mysqli_free_result($result);
      unset($row);
   }
   else //query failed
   {
      if ($link)
      {
         mysqli_close($link);
         $link = 0;
      }
      echo DB_ERROR;
      return;
   }
   
   //----- Print Department Pages -----
   $return_string = "";
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">";
   $depart_page_default_no = 1;
   $depart_page_size = PAGE_SIZE;              
   $depart_page_num = (int)(($depNumber - 1) / $depart_page_size + 1);
   $return_string = $return_string . "<input type=\"hidden\" id=depart_no value=$depNumber>"
                                   . "<input type=\"hidden\" name=depart_page_no value=1>"
                                   . "<input type=\"hidden\" name=depart_page_size value=" . $depart_page_size . ">";
   if ($depart_page_num > 1)
   {
      for ($i = 0; $i < $depart_page_num; $i++)
      {
         $return_string = $return_string . "<span class=\"depart_page";
         if ($i + 1 == $depart_page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=depart_page_begin_no_" . ($i + 1) . " OnClick=clickDepartPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }
   $return_string = $return_string . "</span>"
                                   . "<span id=\"createDepart\" class=\"btn new\" OnClick=\"newDepartFunc();\">新增部門</span>"
                                   . "</div>";
         
   //----- Print Department Tables -----
   if($depNumber == 0)
   {
      $return_string = $return_string . "<div id=\"departTableHead\">"
                                      . "<table class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                      . "<colgroup>"
                                      . "<col class=\"rNameW\"/>"
                                      . "<col class=\"actW\"/>"
                                      . "</colgroup>"
                                      . "<tr>"
                                      . "<th>部門名稱</th>"
                                      . "<th>動作</th>"
                                      . "</tr>"
                                      . "<tr>"
                                      . "<td colspan=\"2\" class=\"empty\">目前沒有任何部門，請點選&quot;<a>新增部門</a>&quot;</td>"
                                      . "</tr>"
                                      . "</table>"
                                      . "</div>";
   }
   else
   {
      $i = 0;
      $page_no = 1;
      $page_count = 0;
      while ($i < $depNumber)
      {
         //----- If No Data -----                        
         if ($page_count == 0)
         {
            $return_string = $return_string . "<div id=\"depart_page" . $page_no . "\" ";
            if ($page_no == 1)
               $return_string = $return_string . "style=\"display:block;\"";
            else
               $return_string = $return_string . "style=\"display:none;\"";
            $return_string = $return_string . ">"
                                            . "<table class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                            . "<colgroup>"
                                            . "<col class=\"rNameW\"/>"
                                            . "<col class=\"actW\"/>"
                                            . "</colgroup>"
                                            . "<tr>"
                                            . "<th>部門名稱</th>"
                                            . "<th>動作</th>"
                                            . "</tr>";
         }
         if ($page_count < $depart_page_size)
         {
            $return_string = $return_string . "<tr>"
                                            . "<td id=\"" . $depID[$i] . "_dep\" class=\"rNameW\"><span class=\"rName fixWidth\">" . $dep_name[$i] . "</span></td>";

            if ($dep_flag[$i] > 1) //#001, flag=2 and flag=3 ===> should not be edited or deleted
               $return_string = $return_string . "<td class=\"actW\">上傳自用戶端電腦清單，無法編輯</td>";
            else
               $return_string = $return_string . "<td class=\"actW\"><a id=\"editDepart\" class=\"edit\" onClick=\"editDepartFunc($depID[$i], '$dep_name[$i]');\">編輯</a><a class=\"del\" onClick=\"deleteDepart(this, $depID[$i], '$dep_name[$i]');\">刪除</a></td>";
            $return_string = $return_string . "</tr>";
            $i++;
            $page_count++;
            if ($page_count == $depart_page_size)
            {
               $return_string = $return_string . "</table>"
                                               . "</div>\n";
               $page_no++;
               $page_count = 0;
            }                    
         }
      }
      if ($page_count > 0)
      {
         $return_string = $return_string . "</table>"
                                         . "</div>\n";
      }
   }
    
   //----- Print Department Pages -----
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">";
   if ($depart_page_num > 1)
   {
      for ($i = 0; $i < $depart_page_num; $i++)
      {
         $return_string = $return_string . "<span class=\"depart_page";
         if ($i + 1 == $depart_page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=depart_page_end_no_" . ($i + 1) . " OnClick=clickDepartPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }
   $return_string = $return_string . "</span>"
                                   . "<span id=\"createDepart\" class=\"btn new\" OnClick=\"newDepartFunc();\">新增部門</span>"
                                   . "</div>";

   echo $return_string;
   return;           
}
?>
