<?php
////////////////////////////////////////
//refreshUserPage.php
//
//1.return string of the user page
//
////////////////////////////////////////

define(DELAY_SEC, 3);
define(GUID_LENGTH, 36);
define(PARAMETER_ERROR, -2);
 
function refreshUserPage($link, $GUID)
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
   
   //user
   $dept_list = array();
   $login_name = array();

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
      from userLogin 
      where GUID = '$GUID'";

   if ($result = mysqli_query($link, $str_query))
   {
      $userNumber = mysqli_num_rows($result);
      while ($row = mysqli_fetch_assoc($result))
      {
         $login_name[] = $row["login_name"];
         $dept_list[] =$row["dept_list"];
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
   
   //----- Print User Pages -----
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">";
   $user_page_default_no = 1;
   $user_page_size = PAGE_SIZE;              
   $user_page_num = (int)(($userNumber - 1) / $user_page_size + 1);
   $return_string = $return_string . "<input type=\"hidden\" id=user_no value=$userNumber>"
                                   . "<input type=\"hidden\" name=user_page_no value=1>"
                                   . "<input type=\"hidden\" name=user_page_size value=" . $user_page_size . ">";
   if ($user_page_num > 1)
   {
      for ($i = 0; $i < $user_page_num; $i++)
      {
         $return_string = $return_string . "<span class=\"depart_page";
         if ($i + 1 == $user_page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=user_page_begin_no_" . ($i + 1) . " OnClick=clickUserPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }
   $return_string = $return_string . "</span>"
                                   . "<span id=\"createUser\" class=\"btn new\" OnClick=\"newUserFunc();\">新增部門管理者</span>"
                                   . "</div>";
         
   //----- Print User Tables -----
   if($userNumber == 0)
   {
      $return_string = $return_string . "<div id=\"userTableHead\">"
                                      . "<table class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                      . "<colgroup>"
                                      . "<col class=\"userNameW\"/>"
                                      . "<col class=\"rNameW\"/>"
                                      . "<col class=\"actW\"/>"
                                      . "</colgroup>"
                                      . "<tr>"
                                      . "<th>帳號</th>"
                                      . "<th>負責部門</th>"
                                      . "<th>動作</th>"
                                      . "</tr>"
                                      . "<tr>"
                                      . "<td colspan=\"3\" class=\"empty\">目前沒有任何部門管理者，請點選&quot;<a>新增部門管理者</a>&quot;</td>"
                                      . "</tr>"
                                      . "</table>"
                                      . "</div>";
   }
   else
   {
      $i = 0;
      $page_no = 1;
      $page_count = 0;
      while ($i < $userNumber)
      {
         //----- If No Data -----                        
         if ($page_count == 0)
         {
            $return_string = $return_string . "<div id=\"user_page" . $page_no . "\" ";
            if ($page_no == 1)
               $return_string = $return_string . "style=\"display:block;\"";
            else
               $return_string = $return_string . "style=\"display:none;\"";
            $return_string = $return_string . ">"
                                            . "<table class=\"report\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"
                                            . "<colgroup>"
                                            . "<col class=\"userNameW\"/>"
                                            . "<col class=\"rNameW\"/>"
                                            . "<col class=\"actW\"/>"
                                            . "</colgroup>"
                                            . "<tr>"
                                            . "<th>帳號</th>"
                                            . "<th>負責部門</th>"
                                            . "<th>動作</th>"
                                            . "</tr>";
         }
         if ($page_count < $user_page_size)
         {
            $return_string = $return_string . "<tr>"
                                            . "<td id=\"" . $login_name[$i] . "_dep\" class=\"userNameW\">" . $login_name[$i] . "</td>";

            $return_string = $return_string . "<td class=\"rNameW\">" . $dept_list[$i] . "</td>";
            $login_name2 = addslashes($login_name[$i]);
            $dept_list2 = addslashes($dept_list[$i]);

            $return_string = $return_string . "<td class=\"actW\"><a id=\"editUser\" class='edit' onClick=\"editUserFunc($i, '$login_name2', '$dept_list2');\">修改</a><a class='del' onClick=\"deleteUser(this,'$login_name2')\">刪除</a></td>";
            $return_string = $return_string . "</tr>";
            $i++;
            $page_count++;
            if ($page_count == $user_page_size)
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
    
   //----- Print User Pages -----
   $return_string = $return_string . "<div class=\"toolMenu\">"
                                   . "<span class=\"paging\">";
   if ($user_page_num > 1)
   {
      for ($i = 0; $i < $user_page_num; $i++)
      {
         $return_string = $return_string . "<span class=\"user_page";
         if ($i + 1 == $user_page_default_no)
            $return_string = $return_string . " active";
         $return_string = $return_string . "\" id=user_page_end_no_" . ($i + 1) . " OnClick=clickUserPage(this," . ($i + 1) . ");>" . ($i + 1) . "</span>";
      }
   }
   $return_string = $return_string . "</span>"
                                   . "<span id=\"createUser\" class=\"btn new\" OnClick=\"newUserFunc();\">新增部門管理者</span>"
                                   . "</div>";

   echo $return_string;
   return;           
}
?>
