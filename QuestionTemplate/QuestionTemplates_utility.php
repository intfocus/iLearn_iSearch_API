<?php
   define("UPLOAD_SUCCESS", -1);
   
   define("ERR_UPDATE_DATABASE", -10);
   define("ERR_INSERT_DATABASE", -11);
   define("ERR_SELECT_DATABASE", -12);
   
   define("ERR_PROB_NOT_EXIST", -100);
   define("ERR_PROB_DESC_FORMAT", -101);
   define("ERR_PROB_SELECTOR_FORMAT", -102);
   define("ERR_PROB_ANSWER_FORMAT", -103);
   define("ERR_PROB_CATEGORY_FORMAT", -104);
   define("ERR_PROB_LEVEL_FORMAT", -105);
   define("ERR_PROB_FUNC_NOT_EXIST", -106);
   
   define("ERR_FILE_EXIST", -200);
   define("ERR_MOVE_FILE", -201);
   define("ERR_FILE_TYPE", -202);
   define("ERR_FILE_LOAD", -203);
   define("ERR_EMPTY_FILE", -204);
   
   define("MSG_ERR_FILE_EXIST", "已存在相同档案");
   define("MSG_ERR_MOVE_FILE", "移动上传档案失败");
   define("MSG_ERR_FILE_TYPE", "错误的档案类型");
   define("MSG_ERR_EMPTY_FILE", "模板为空档案");
   
   define("MSG_ERR_CONNECT_TO_DATABASE", "无法连接到数据库");
   
   define("MSG_ERR_UPDATE_DATABASE", "无法更新问卷模板");
   define("MSG_ERR_INSERT_DATABASE", "无法新增问卷模板");
   
   define("MSG_ERR_FILE_CONTENT_SYNTAX", "问卷格式不为预设的汇入问卷格式");
   define("MSG_ERR_PROB_NOT_EXIST", "题目ID不存在");
   define("MSG_ERR_QT_TYPE_FORMAT","问卷类型不正确");
   define("MSG_ERR_QT_DESC_FORMAT", "问卷题目描述格式不正确");
   define("MSG_ERR_QT_SELECTOR_FORMAT", "问卷题目选型格式不正确");
   define("MSG_ERR_PROB_ANSWER_FORMAT", "题目答案格式不正确");
   define("MSG_ERR_PROB_CATEGORY_FORMAT", "题目分类格式不正确");
   define("MSG_ERR_PROB_LEVEL_FORMAT", "题目难易格式不正确");
   
   define("MSG_PROBLEM_MODIFY", "题目修改");

   define("TRUE_FALSE_QT", 1);
   define("SINGLE_CHOICE_QT", 2);
   define("MULTI_CHOICE_QT", 3);
   define("FILL_VACANT_POSITION_QT", 4);
   define("FAQ_QT", 5);
   
   define("TRUE_FALSE_CHINESE", "是非题");
   define("SINGLE_CHOICE_CHINESE", "单选题");
   define("MULTI_CHOICE_CHINESE", "多选题");
   define("FILL_VACANT_POSITION", "填空");
   define("FAQ", "问答");

   define("EASY_LEVEL", 1);
   define("MID_GROUP_NAME", "");
   define("HIGH_LEVEL", 3);
   define("NO_LEVEL", -1);
   
   define("EASY_LEVEL_NAME", "易");
   define("MID_LEVEL_NAME", "中");
   define("HARD_LEVEL_NAME", "难");

   define("FUNCTION_PRODUCT", 1);
   define("FUNCTION_ADAPTATION", 2);
   define("FUNCTION_OTHER", 3);

   define("FUNCTION_ADAPTATION_NAME", "适应症");
   define("FUNCTION_PRODUCT_NAME", "产品名称");
   define("FUNCTION_OTHER_NAME", "题库类别");

 
   class UploadQTStatus
   {
      public $status;
      public $errors = array();
      public $upload_qd_syntax = array("id", "type", "desc", "groupName", "selA", "selB", "selC", "selD", 
                                       "selE", "selF", "selG", "selH", "selI");
   }
   
   class UploadQT
   {
      function __construct($qt_details, $qtid)
      {
         $this->id = $qtid;
         $this->type = get_type_id_from_name($qt_details[0]);
         $this->desc = $qt_details[1];
         $this->groupName = $this->_parse_groupname($qt_details[2]);
         $this->selections = array_slice($qt_details, 3);
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
      function _parse_groupname($group_name)
      {
         $group_name = trim($group_name);
         if (strlen($group_name) == 0)
         {
            return MID_GROUP_NAME;
         }
         else
         {
            return $group_name;
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

      public $id;
      public $type;
      public $desc;
      public $groupName;
      public $selections = array();
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

   function get_type_id_from_name($type_name)
   {
      if ($type_name == TRUE_FALSE_CHINESE)
      {
         return TRUE_FALSE_QT;
      }
      else if ($type_name == SINGLE_CHOICE_CHINESE)
      {
         return SINGLE_CHOICE_QT;
      }
      else if ($type_name == MULTI_CHOICE_CHINESE)
      {
         return MULTI_CHOICE_QT;
      }
      else if ($type_name == FILL_VACANT_POSITION)
      {
         return FILL_VACANT_POSITION_QT;
      }
      else if ($type_name == FAQ)
      {
         return FAQ_QT;
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

   // check first row, first row should be 类型, 标题, 
   function is_valid_syntax_import_file($row)
   {
      if ($row[0] != "类型" || $row[1] != "标题" || $row[2] != "分组名称")
      {
         return false;
      }

      return true;
   }
   
   function is_correct_qt_type_format($qt_type)
   {
      if ($qt_type != TRUE_FALSE_QT && $qt_type != SINGLE_CHOICE_QT &&
          $qt_type != MULTI_CHOICE_QT && $qt_type != FILL_VACANT_POSITION_QT && $qt_type != FAQ_QT)
      {
         return false;
      }
      
      return true;
   }
   
   function is_correct_qt_desc_format($qt_desc)
   {
      if ($qt_desc == "")
      {
         return false;
      }
      
      return true;
   }
   
   function is_correct_qt_selection_format($selections, $qt_type)
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

      if ($qt_type == TRUE_FALSE_QT)
      {
         if ($selections[0] == "" || $selections[1] == "")
         {
            return false;
         }
         
         if ($selections_count != 2)
         {
            return false;
         }
      }
      else if ($qt_type == SINGLE_CHOICE_QT || $qt_type == MULTI_CHOICE_QT)
      {
         if ($selections[0] == "" || $selections[1] == "")
         {
            return false;
         }
         
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
   
   function is_correct_prob_level_format($prob_level)
   {
      if ($prob_level != EASY_LEVEL && $prob_level != MID_LEVEL &&   
          $prob_level != HIGH_LEVEL && $prob_level != NO_LEVEL)
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
