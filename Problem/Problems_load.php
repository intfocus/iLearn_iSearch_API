<?php
   require_once("Problems_utility.php");

   define("FILE_NAME", "../DB.conf");
   define("DELAY_SEC", 3);
   define("FILE_ERROR", -2);
   
   if (file_exists(FILE_NAME))
   {
      include(FILE_NAME);
   }
   else
   {
      sleep(DELAY_SEC);
      echo FILE_ERROR;
      return;
   }

   header('Content-Type:text/html;charset=utf-8');
   
   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                          //illegal char
   define("STR_LENGTH", 50);
   define("SEARCH_SIZE", 1000);                             //上限1000笔数
   define("PAGE_SIZE", 100);                                //设置列表显示笔数

   //return value
   define("SUCCESS", 0);
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);
   //----- Check command -----
   function check_command($check_str)
   {
      if(strcmp($check_str, "searchProbs"))
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   //----- Check name -----
   function check_name($check_str)
   {
      //----- check str length -----
      if(mb_strlen($check_str, "utf8") > STR_LENGTH)
      {
         return SYMBOL_ERROR;
      }       
      //----- replace "<" to "&lt" -----
      if(strpbrk($check_str, "<") == true)
      {
         $check_str = str_replace("<", "&lt;", $check_str);
      }
      //----- replace ">" to "&gt" -----
      if(strpbrk($check_str, ">") == true)
      {
         $check_str = str_replace(">", "&gt;", $check_str);
      }
      return $check_str;
   }
   //----- Check number -----
   function check_number($check_str)
   {
      if(!is_numeric($check_str))
      {
         return SYMBOL_ERROR; 
      }

      return $check_str;
   }
   //----- Check encrypt setting -----
   function check_encrypt($check_str)
   {
      if(!is_numeric($check_str))
      {
         return SYMBOL_ERROR; 
      }
      if($check_str != 0 && $check_str != 1)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   //get data from client
   $cmd;
   $ProbName;
   $Status;

   //query
   $link;
   $str_query;
   $str_update;
   $result;                 //query result
   $row;                    //1 data array
   $return_string;
   

   
   //1.get information from client
   if(($cmd = check_command($_GET["cmd"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR_CMD;
      return;
   }
   if(($searchProbsDescMemo = check_name($_GET["searchProbsDescMemo"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($searchProbsLevel = check_number($_GET["searchProbsLevel"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($statusCheckbox = check_number($_GET["statusCheckbox"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }

   //link
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }   
 
   $funcs_id_name_mapping = get_all_funcs_id_name_mapping();
 
   //----- query -----
   //***Step16 页面搜索SQl语句 起始
   $str_query1 = "
      select *
      from problems where ProblemId > 0 ";

   if ($statusCheckbox == 1)
   {
      $str_query1 = $str_query1 . "AND status=1 ";
   }
   else if ($statusCheckbox == 2)
   {
      $str_query1 = $str_query1 . "AND status=0 ";
   }
   else if ($statusCheckbox == 3)
   {
      $str_query1 = $str_query1 . "AND status>=0 ";
   }
 
   //TODO: trim space
   if (strlen($searchProbsDescMemo) > 0)
   {
      $str_query1 = $str_query1 . "AND (ProblemDesc like '%$searchProbsDescMemo%' OR ProblemMemo like '%$searchProbsDescMemo%') ";
   }
   
   if ($searchProbsLevel > NO_LEVEL)
   {
      $str_query1 = $str_query1 . "AND (ProblemLevel=$searchProbsLevel) ";
   }
 
   //***Step16 页面搜索SQl语句 结束
   
   // echo $str_query1;
   // return;
   /////////////////////
   // prepare the SQL command and query DB
   /////////////////////
   if($result = mysqli_query($link, $str_query1)){
      $row_number = mysqli_num_rows($result);

      //4.return string of the refreshed Pages
      //----- Print Search Pages -----
      $return_string = "";
      $page_default_no = 1;
      $page_size = PAGE_SIZE;
      
      $return_string = $return_string . "<div id=\"sResultTitle\" class=\"sResultTitle\">查询结果 : 共有 <span>" 
                                      . number_format($row_number) 
                                      . "</span> 笔数据符合查询条件</div>";
      if ($row_number > SEARCH_SIZE)
         $row_number = SEARCH_SIZE;
      $page_num = (int)(($row_number - 1) / $page_size + 1);
      $return_string = $return_string . "<div class=\"toolMenu\">"
                                      . "<span class=\"paging\">"
                                      . "<input type=\"hidden\" id=search_no value=$row_number>"
                                      . "<input type=\"hidden\" name=search_prob_page_no value=1>"
                                      . "<input type=\"hidden\" name=search_prob_page_size value=" . $page_size . ">";
      if ($page_num > 1)
      {
         for ($i = 0; $i < $page_num; $i++)
         {
            $return_string = $return_string . "<span class=\"search_prob_page";
            if ($i + 1 == $page_default_no)
               $return_string = $return_string . " active";
            //***Step6 function name ==> clickSearchNewsPage
            $return_string = $return_string . "\" id=search_prob_page_begin_no_" . ($i + 1) . " OnClick=clickSearchProbsPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
         }
      }
      //***Step7 function name ==> expandSearchNewsContentFunc
      $return_string = $return_string . "</span>"
                       . "<span align=right class=\"btn\" OnClick=\"newSearchProbsContentFunc();\">上传题库</span>&nbsp;"
                       . "<span class=\"btn ProblemsexpandSR\" OnClick=\"expandSearchProbsContentFunc();\">显示过长内容</span>"
                       . "</div>";                   
      
      //----- Print Search Tables -----
      //***Step8 Field name and field number must be modified. begin
      //Be care of colspan=7 
      //----- If No Data -----
      if ($row_number == 0)
      {
         $return_string = $return_string . "<table id=\"search_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                         . "<colgroup>"
                                         . "<col class=\"num\"/>"
                                         . "<col class=\"ProbType\"/>"
                                         . "<col class=\"ProbDesc\"/>"
                                         . "<col class=\"ProbCategory\"/>"
                                         . "<col class=\"ProblLevel\"/>"
                                         . "<col class=\"ProbMemo\"/>"
                                         . "<col class=\"ProbStatus\"/>"
                                         . "<col class=\"ProbAction\"/>"
                                         . "</colgroup>"
                                         . "<tr>"
                                         . "<th>编号</th>"
                                         . "<th>类型</th>"
                                         . "<th>描述</th>"
                                         . "<th>分类</th>"
                                         . "<th>难易</th>"
                                         . "<th>备注</th>"
                                         . "<th>状态</th>"
                                         . "<th>动作</th>"
                                         . "</tr>"
                                         . "<tr>"
                                         . "<td colspan=\"8\" class=\"empty\">请输入上方查询条件，并点选\"开始查询\"</td>"
                                         . "</tr>"
                                         . "</table>";
      }
      else
      {
         $i = 0;
         $page_no = 1;
         $page_count = 0;
         while ($i < $row_number)
         {
            if ($page_count == 0)
            {
               $return_string = $return_string . "<div id=\"search_prob_page" . $page_no . "\" ";
               if ($page_no == 1)
                  $return_string = $return_string . "style=\"display:block;\"";
               else
                  $return_string = $return_string . "style=\"display:none;\"";
               $return_string = $return_string . ">"
                                         . "<table id=\"search_table\" class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                         . "<colgroup>"
                                         . "<col class=\"num\"/>"
                                         . "<col class=\"ProbType\"/>"
                                         . "<col class=\"ProbDesc\"/>"
                                         . "<col class=\"ProbCategory\"/>"
                                         . "<col class=\"ProbLevel\"/>"
                                         . "<col class=\"ProbMemo\"/>"
                                         . "<col class=\"ProbStatus\"/>"
                                         . "<col class=\"ProbAction\"/>"
                                         . "</colgroup>"
                                         . "<tr>"
                                         . "<th>编号</th>"
                                         . "<th>类型</th>"
                                         . "<th>描述</th>"
                                         . "<th>分类</th>"
                                         . "<th>难易</th>"
                                         . "<th>备注</th>"
                                         . "<th>状态</th>"
                                         . "<th>动作</th>"
                                         . "</tr>";
            }
            
            if ($page_count < $page_size)
            {
               $row = mysqli_fetch_assoc($result);
               $ProbId = $row["ProblemId"];
               $ProbType = $row["ProblemType"];
               $ProbTypeStr = get_type_name_from_id($ProbType);
               $ProbDesc = $row["ProblemDesc"];
               $ProbCategory = $row["ProblemCategory"];
               $funcs_id = get_function_id($ProbCategory);               
               $funcs_name = get_funcs_name($funcs_id, $funcs_id_name_mapping);
               $funcs_name_str = get_funcs_name_str($funcs_name);
               $ProbLevel = $row["ProblemLevel"];
               $ProbLevelStr = get_level_name($ProbLevel);
               $ProbMemo = $row["ProblemMemo"];
               $ProbStatus = $row["Status"];
               $StatusStr = $ProbStatus == 0 ? "下架" : "上架";
               $StatusAction = $ProbStatus == 1 ? "下架" : "上架";
               $page_count_display = $page_count + 1;
               $return_string = $return_string 
                  . "<tr>"
                  . "<td>$page_count_display</td>"
                  . "<td><span class=\"ProbType fixWidth\">$ProbTypeStr</span></td>"
                  . "<td><span class=\"ProbDesc fixWidth\">$ProbDesc</span></td>"
                  . "<td><span class=\"ProbCategory fixWidth\">$funcs_name_str</span></td>"
                  . "<td><span class=\"ProbLevel fixWidth\">$ProbLevelStr</span></td>"
                  . "<td><span class=\"ProbMemo fixWidth\">$ProbMemo</span></td>"
                  . "<td>$StatusStr</td>"
                  . "<td><A OnClick=\"actionSearchProbs($ProbId,$ProbStatus);\">$StatusAction</A><br/>"
                  . "<A OnClick=\"modifySearchProbs($ProbId);\">修改</A><br/>"
                  . "<A OnClick=\"deleteSearchProbs($ProbId);\">删除</A></td>"
                  . "</tr>";

               $i++;
               $page_count++;
               if ($page_count == $page_size)
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
      //***Step8 Field name and field number must be modified. end


      $return_string = $return_string . "<div class=\"toolMenu\">"
                        . "<span align=right class=\"btn\" OnClick=\"newSearchProbsContentFunc();\">上传题库</span>&nbsp;"
                        . "<span class=\"btn ProblemsexpandSR\" OnClick=\"expandSearchProbsContentFunc();\">显示过长内容</span>"
                        . "<span class=\"paging\">";
      
      //----- Print Search Pages -----
      if ($page_num > 1)
      {
         for ($i = 0; $i < $page_num; $i++)
         {
            $return_string = $return_string . "<span class=\"search_prob_page";
            if ($i + 1 == $page_default_no)
               $return_string = $return_string . " active";
            $return_string = $return_string . "\" id=search_prob_page_end_no_" . ($i + 1) . " OnClick=clickSearchProbsPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
         }
      }
      $return_string = $return_string . "</span>"
                                      . "</div>";
      echo $return_string;
      mysqli_free_result($result);
      mysqli_close($link);
      return;
   }
   else
   {
      if($link){
         mysqli_close($link);
      }
      sleep(DELAY_SEC);
      echo -__LINE__;
      return;
   }
   
   function get_funcs_name($funcs_id, $funcs_name_id_mapping)
   {
      $funcs_name = array();

      foreach ($funcs_id as $func_id)
      {
         array_push($funcs_name, $funcs_name_id_mapping[$func_id]);
      }

      return $funcs_name;
   }

   function get_funcs_name_Str($funcs_name)
   {
      $str = "";
      
      $funcs_name_count = count($funcs_name);
      for ($i=0; $i<$funcs_name_count; $i++)
      {
         if ($i < ($funcs_name_count-1))
         {
            $str = $str.$funcs_name[$i].",";
         }
         else
         {
            $str = $str.$funcs_name[$i];
         }
      }
      
      return $str;
   }
   
   
   function get_all_funcs_id_name_mapping()
   {
      $funcs_id_name_mapping = array();

      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
      if (!$link)  //connect to server failure    
      {
         sleep(DELAY_SEC);
         echo DB_ERROR;       
         return;
      }
      
      $str_query = "select * from functions where FunctionType=".FUNCTION_PRODUCT." or FunctionType=".FUNCTION_ADAPTATION." or FunctionType=".FUNCTION_OTHER;
      if($result = mysqli_query($link, $str_query)){
         $row_number = mysqli_num_rows($result);
         for ($i=0; $i<$row_number;$i++)
         {
            $row = mysqli_fetch_assoc($result);
            $funcs_id_name_mapping[$row["FunctionId"]] = $row["FunctionName"];
         }
      }
      return $funcs_id_name_mapping;
   }
?>
