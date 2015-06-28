<?php
////////////////////////////////////////
//refreshDepartCheckbox.php
//
// 1.return string of new / edit user check box
//
// 2013/05/03 created by Odie
//
////////////////////////////////////////

define(DELAY_SEC, 3);
define(GUID_LENGTH, 36);
define(PARAMETER_ERROR, -2);
 
function refreshDepartCheckbox($link, $GUID)
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
   $return_string = "<div id=\"newUser\" class=\"newReport\" style=\"display:none;\">
                     <form name=\"formNewUser\">
                        <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                           <tr>
                              <th>部門管理者名稱 : </th>
                              <td>
                                 <input id=\"newUserName\" class=\"reportName\" type=\"text\" maxlength=\"128\">
                                 <div id=\"newUserNameHint\" class=\"maxHint\">*已超過最大字數限制</div>
                              </td>
                           </tr>                  
                           <tr>
                              <th>密碼(長度 8~30): </th>
                              <td>
                                 <input id=\"newUserPassword\" class=\"reportName\" type=\"password\" maxlength=\"30\">
                              </td>
                           </tr>
                           <tr>
                              <th>負責部門：</th>
                              <td>";
   // print all the department names for new created account
   for ($i=0; $i<$depNumber; $i++)
   {
      $return_string .= "<input type=checkbox name='newUserDepartment' value='$dep_name[$i]'> $dep_name[$i]<br/>";
   }

   $return_string .= "
                              </td>
                           </tr>
                        </table>
                     </form>
                     <div class=\"submitW\">
                        <a class=\"btn_submit_new new_user_confirm\"><input type=\"submit\" value=\"確定\"></a>
                        <a class=\"btn_submit_new new_user_cancel\"><input type=\"button\" value=\"取消\"></a>
                     </div>
                  </div>
                  <div id=\"editUser\" class=\"newReport\" style=\"display:none;\">
                     <form name=\"formEditUser\">
                        <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                           <tr>
                              <th>部門管理者名稱 : </th>
                              <td>
                                 <input id=\"editUserName\" class=\"reportName\" type=\"hidden\" value=\"\" maxlength=\"128\">
                                 <span id=\"editUserNameShow\" class=\"reportName\"></span>
                              </td>
                           </tr>
                           <tr>
                              <th>負責部門：</th>
                              <td>";
   for ($i=0; $i<$depNumber; $i++)
   {
      $return_string .= "<input type=checkbox name='editUserDepartment' id='editUser_$dep_name[$i]' value='$dep_name[$i]'> $dep_name[$i]<br/>";
   }

   $return_string .= "
                              </td>
                           </tr>
                           <tr>
                              <th>重新設定密碼：<br/>(長度 8~30)</th>
                              <td>
                                 <input id=\"editUserPassword\" class=\"reportName\" type=password maxlength=\"30\">
                              </td>
                           </tr> 
                        </table>
                     </form>
                     <div class=\"submitW\">
                        <a class=\"btn_submit_new edit_user_confirm\"><input type=\"submit\" value=\"確定\"></a>
                        <a class=\"btn_submit_new edit_user_cancel\"><input type=\"button\" value=\"取消\"></a>
                     </div>
                  </div>";

   echo $return_string;
   return;           
}
?>
