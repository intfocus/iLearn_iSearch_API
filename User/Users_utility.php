<?php
   define("UPLOAD_SUCCESS", 0);
   
   define("ERR_UPDATE_DATABASE", -10);
   define("ERR_INSERT_DATABASE", -11);
   define("ERR_SELECT_DATABASE", -12);
   define("ILLEGAL_CHAR", "'-;<>");
   define("SYMBOL_ERROR", -3);
   define("TIME_ZONE", "Asia/Shanghai");
   
   define("ERR_PROB_NOT_EXIST", -100);
   define("ERR_PROB_DESC_FORMAT", -101);
   define("ERR_PROB_SELECTOR_FORMAT", -102);
   define("ERR_PROB_ANSWER_FORMAT", -103);
   define("ERR_PROB_CATEGORY_FORMAT", -104);
   define("ERR_PROB_LEVEL_FORMAT", -105);
   define("ERR_USER_DEPT_NOT_EXIST", -106);
   
   define("ERR_FILE_EXIST", -200);
   define("ERR_MOVE_FILE", -201);
   define("ERR_FILE_TYPE", -202);
   define("ERR_FILE_LOAD", -203);
   define("ERR_EMPTY_FILE", -204);
   
   define("MSG_ERR_FILE_EXIST", "已存在相同档案");
   define("MSG_ERR_MOVE_FILE", "移动上传档案失败");
   define("MSG_ERR_FILE_TYPE", "错误的档案类型");
   define("MSG_ERR_EMPTY_FILE", "档案为空档案");
   
   define("MSG_ERR_CONNECT_TO_DATABASE", "无法连接到资料库");
   
   define("MSG_ERR_UPDATE_DATABASE", "无法更新资料库");
   define("MSG_ERR_INSERT_DATABASE", "无法新增资料库");
   
   define("MSG_ERR_FILE_CONTENT_SYNTAX", "档案格式不为预设的汇入档案格式");
   define("MSG_ERR_PROB_NOT_EXIST", "题目ID不存在");
   define("MSG_ERR_USER_EID_FORMAT","员工号不正确");
   define("MSG_ERR_USER_NAME_FORMAT", "姓名不正确");
   define("MSG_ERR_USER_EMAIL_FORMAT", "邮箱地址不正确");
   define("MSG_ERR_PROB_SELECTOR_FORMAT", "题目选型格式不正确");
   define("MSG_ERR_PROB_ANSWER_FORMAT", "题目答案格式不正确");
   define("MSG_ERR_USER_DEPT_FORMAT", "部门格式不正确");
   define("MSG_ERR_PROB_LEVEL_FORMAT", "题目难易格式不正确");
   
   define("MSG_PROBLEM_MODIFY", "题目修改");

   define("TRUE_FALSE_PROB", 1);
   define("SINGLE_CHOICE_PROB", 2);
   define("MULTI_CHOICE_PROB", 3);
   
   define("TRUE_FALSE_CHINESE", "是非题");
   define("SINGLE_CHOICE_CHINESE", "单选题");
   define("MULTI_CHOICE_CHINESE", "多选题");

   define("EASY_LEVEL", 1);
   define("MID_LEVEL", 2);
   define("HIGH_LEVEL", 3);
   define("NO_LEVEL", -1);
   
   define("EASY_LEVEL_NAME", "易");
   define("MID_LEVEL_NAME", "中");
   define("HARD_LEVEL_NAME", "难");

   define("FUNCTION_PRODUCT", 1);
   define("FUNCTION_ADAPTATION", 2);
   define("FUNCTION_OTHER", 3);

   define("FUNCTION_ADAPTATION_NAME", "TA/适应症");
   define("FUNCTION_PRODUCT_NAME", "产品名称");
   define("FUNCTION_OTHER_NAME", "题库类别");

 
   class UploadFileStatus
   {
      public $status;
      public $errors = array();
      public $upload_user_syntax = array("UserWId","UserName","EmployeeId","Email","UserArea","UserPosition","UserProduct",
         "UserParent","UserParentId","PositionCode","DeptCode","CanApprovestr");
   }
   
   class UploadUser
   {
      function __construct($user_details)
      {
         $this->UserWId = $user_details[0];
         $this->UserName = $user_details[1];
         $this->EmployeeId = $user_details[2];
         $this->Email = $user_details[3];
         $this->UserArea = $user_details[4];
         $this->UserPosition = $user_details[5];
         $this->UserProduct = $user_details[6];
         $this->UserParent = $user_details[7];
         $this->UserParentId = $user_details[8];
         $this->PositionCode = $user_details[9];
         $this->DeptCode = $user_details[10];
         $this->CanApprovestr = $user_details[11];
         $this->CheckInTime = get_check_datetime($user_details[12]);
         $this->DeptId = 0;
         $this->CanApprove = 0;
      }
      
      /*
      function _parse_level($level_name)
      {
         if (strlen($level_name) == 0)
         {
            return MID_LEVEL;
         }
         else
         {
            return get_level_id($level_name);
         }
      }
      */
      function _parse_level($level_id)
      {
         $level_id = trim($level_id);
         if (strlen($level_id) == 0)
         {
            return MID_LEVEL;
         }
         else
         {
            return $level_id;
         }
      }
      
      function _parse_product($input)
      {
         // input: product_name1, product_name2, ...
         
         if (strlen($input) == 0)
         {
            return array();
         }
         return preg_split("/,(\s)*/", trim($input));
      }
      
      function _parse_adaptation($input)
      {
         // input: adaptation_name1, adaptation_name2
         if (strlen($input) == 0)
         {
            return array();
         }
         return preg_split("/,(\s)*/", trim($input));
      }
      
      function _parse_category($input)
      {
         // input: adaptation_name1, adaptation_name2
         if (strlen($input) == 0)
         {
            return array();
         }
      return preg_split("/,(\s)*/", trim($input));
      } 

      public $UserName;
      public $Email;
      public $UserWId;
      public $UserArea;
      public $UserPosition;
      public $UserProduct;
      public $UserParent;
      public $UserParentId;
      public $DeptCode;
      public $CanApprovestr;
      public $PositionCode;
      public $EmployeeId;
      public $DeptId;
      public $CanApprove;
      public $CheckInTime;
   }

   function get_check_datetime($check_str)
   {
      //----- check illegal char -----
      if(strpbrk($check_str, ILLEGAL_CHAR) == true)
      {
         return SYMBOL_ERROR;
      }
      //----- check empty string -----
      date_default_timezone_set(TIME_ZONE);
      if(trim($check_str) == "")
      {
         return date('Y-m-d H:i:s',strtotime('now'));
      }
      //----- format begin range mm/dd/yy to yyyy-mm-dd 00:00:00 -----
      $check_str = $check_str . " 00:00:00";
      if(($check_str = strtotime($check_str)) == "")
      {
         //----- str to time failure ----- 
         return SYMBOL_ERROR;
      }
      $check_str = date("Y-m-d H:i:s", $check_str);
      return $check_str; 
   }
   
   function get_func_type_name($type)
   {
      if ($type == FUNCTION_ADAPTATION)
      {
         return FUNCTION_ADAPTATION_NAME;
      }
      else if ($type == FUNCTION_PRODUCT)
      {
         return FUNCTION_PRODUCT_NAME;
      }
      else if ($type == FUNCTION_OTHER)
      {
         return FUNCTION_OTHER_NAME;
      }
   }

   function get_type_name_from_id($type_id)
   {
      if ($type_id == TRUE_FALSE_PROB)
      {
         return TRUE_FALSE_CHINESE;
      }
      else if ($type_id == SINGLE_CHOICE_PROB)
      {
         return SINGLE_CHOICE_CHINESE;
      }
      else if ($type_id == MULTI_CHOICE_PROB)
      {
         return MULTI_CHOICE_CHINESE;
      }
   }

   function get_level_name($level)
   {
      if ($level == EASY_LEVEL)
      {
         return EASY_LEVEL_NAME;
      }
      else if ($level == MID_LEVEL)
      {
         return MID_LEVEL_NAME;
      }
      else if ($level == HIGH_LEVEL)
      {
         return HARD_LEVEL_NAME;
      }  
   }

   function get_level_id($level_name)
   {
      if ($level_name == EASY_LEVEL_NAME)
      {
         return EASY_LEVEL;
      }
      else if ($level_name == MID_LEVEL_NAME)
      {
         return MID_LEVEL;
      }
      else if ($level_name == HARD_LEVEL_NAME)
      {
         return HIGH_LEVEL;
      }
   }
   
   function get_function_id($category_str)
   {  
      $removed_start_end_comma_str = substr($category_str, 1, strlen($category_str)-2);
      $func_ids = preg_split("/,+/", $removed_start_end_comma_str);
      return $func_ids;
   }
   
   function output_category_str_from_func_array($func_array)
   {
      $output_str = ",";

      for ($i=0; $i<count($func_array); $i++)
      {
         if ($i == count($func_array) - 1)
         {
            $output_str = $output_str.$func_array[$i];
         }
         else
         {
            $output_str = $output_str.$func_array[$i].",,";
         }
      }

      $output_str = $output_str.",";
      
      return $output_str;
   }

   function is_empty_row($row)
   {
      foreach ($row as $element)
      {
         if (strlen($element) > 0)
         {
            return false;
         }
      }
      return true;
   }

   // check first row, first row should be 用户名（Windows ID）, 姓名, 员工号, 邮箱地址, 区域, 职位, 产品, 上级主管姓名, 上级主管编号, 岗位编号, 部门编号, 是否审批者
   function is_valid_syntax_import_file($row)
   {
      if ($row[0] != "用户名（Windows ID）" || $row[1] != "姓名" || $row[2] != "员工号" || $row[3] != "邮箱地址" || $row[4] != "区域" ||
          $row[5] != "职位" || $row[6] != "产品" || $row[7] != "上级主管姓名" || $row[8] != "上级主管编号" || $row[9] != "岗位编号" || 
          $row[10] != "部门编号" || $row[11] != "是否审批者" || $row[12] != "入职时间")
      {
         return false;
      }

      return true;
   }
   
   function is_correct_user_eid_format($user_eid)
   {
      if (!preg_match("/^E[0-9]+$/", $user_eid))
      {
         return false;
      }
      
      return true;
   }
   
   function is_correct_user_name_format($user_name)
   {
      if ($user_name == "")
      {
         return false;
      }
      
      return true;
   }
   
   function is_correct_user_email_format($user_email)
   {
      if ($user_email == "")
      {
         return false;
      }
      else 
      {
         $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
         if(!preg_match($pattern,$user_email))
         {
            return false;
         }
      }
      
      return true;
   }
   
   function is_correct_prob_selection_format($selections, $prob_type)
   {
      //check the number of selector depend on the type

      $selections_count = 0;
      foreach ($selections as $selection)
      {
         if ($selection != "")
         {
            $selections_count++;
         }
      }

      if ($selections[0] == "" || $selections[1] == "")
      {
         return false;
      }

      if ($prob_type == TRUE_FALSE_PROB)
      {
         if ($selections_count != 2)
         {
            return false;
         }
      }
      else
      {
         if ($selections_count < 2)
         {
            return false;
         }
      }
      return true;
   }

   function is_correct_prob_answer_format($prob_answer, $selections, $prob_type)
   {
      // only [A-H]+
      if (!preg_match('/^[A-H]+$/', $prob_answer))
      {
         return false;
      }

      // true false answer is A or B
      if ($prob_type == TRUE_FALSE_PROB)
      {
         if ($prob_answer != "A" && $prob_answer != "B")
         {
            return false;
         }
      }
      else if ($prob_type == SINGLE_CHOICE_PROB)
      {
         if (strlen($prob_answer) != 1 || is_answer_has_empty_selection($prob_answer, $selections))
         {
            return false;
         }
      }
      else if ($prob_type == MULTI_CHOICE_PROB)
      {
         if (strlen($prob_answer) < 1 || is_answer_has_empty_selection($prob_answer, $selections))
         {
            return false;
         }
      }
  
      return true;
   }
   
   function is_correct_prob_category_format($prob_category)
   {
      // only check format now
      if (!preg_match('/,[d+(,,)?]*,/', $prob_category))
      {
         return false;
      }
      
      return true;
      // TODO: check function id exist in function table
   }
   
   function is_correct_user_dept_format($user_dept)
   {
      if ($user_dept == "")
      {
         return false;
      }
      return true;
   }
   
   function is_answer_has_empty_selection($prob_answer, $selections)
   {
      $answers = array();
      
      if (strlen($prob_answer) == 1)
      {
         array_push($answers, $prob_answer);
      }
      else
      {
         $answers = str_split($prob_answer);
      }

      foreach ($answers as $answer)
      {
         if ($selections[convert_answer_char_to_number($answer)] == "")
         {
            return true;
         }
      }

      return false;
   }
   
   function is_no_any_functions($functions)
   {
      if (count($functions) == 0)
      {
         return true;
      }
      else
      {
         return false;
      }
   }
   
   function convert_answer_char_to_number($char)
   {
      return ord($char) - ord("A");
   }

?>
