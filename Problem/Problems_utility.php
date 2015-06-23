<?php
   define("UPLOAD_SUCCESS", 0);
   
   define("ERR_UPDATE_DATABASE", -10);
   define("ERR_INSERT_DATABASE", -11);
   define("ERR_SELECT_DATABASE", -12);
   define("ERR_DELETE_DATABASE", -13);
   
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
   define("MSG_ERR_EMPTY_FILE", "档案为空档案");
   
   define("MSG_ERR_CONNECT_TO_DATABASE", "无法连接到资料库s");
   
   define("MSG_ERR_UPDATE_DATABASE", "无法更新资料库");
   define("MSG_ERR_INSERT_DATABASE", "无法新增资料库");
   
   define("MSG_ERR_FILE_CONTENT_SYNTAX", "档案格式不为预设的汇入档案格式");
   define("MSG_ERR_PROB_NOT_EXIST", "题目ID不存在");
   define("MSG_ERR_PROB_TYPE_FORMAT","题目类型不正确");
   define("MSG_ERR_PROB_DESC_FORMAT", "题目描述格式不正确");
   define("MSG_ERR_PROB_SELECTOR_FORMAT", "题目选型格式不正确");
   define("MSG_ERR_PROB_ANSWER_FORMAT", "题目答案格式不正确");
   define("MSG_ERR_PROB_CATEGORY_FORMAT", "题目分类格式不正确");
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

   define("FUNCTION_ADAPTATION_NAME", "适应症");
   define("FUNCTION_PRODUCT_NAME", "产品名称");
   define("FUNCTION_OTHER_NAME", "题库类别");

 
   class UploadFileStatus
   {
      public $status;
      public $errors = array();
      public $upload_problem_syntax = array("type", "desc", "level", "product",
                                      "adaptaion", "problem_category", "answer", "memo",
                                      "selA", "selB", "selC", "selD", "selE", "selF", "selG",
                                      "selH");
   }
   
   class UploadProblem
   {
      function __construct($problem_details)
      {
         $this->type = get_type_id_from_name($problem_details[0]);
         $this->desc = $problem_details[1];
         $this->level = $problem_details[2];
         $this->category_product = $this->_parse_product($problem_details[3]);
         $this->category_adaptaion = $this->_parse_adaptation($problem_details[4]);
         $this->problem_category = $this->_parse_category($problem_details[5]);
         $this->answer = trim($problem_details[6]);
         $this->memo = $problem_details[7];
         $this->selections = array_slice($problem_details, 8);
      }
      
      function _parse_level($level)
      {
         if (strlen($level) == 0)
         {
            return MID_LEVEL;
         }
         else
         {
            return $level;
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
      
        
      public $type;
      public $desc;
      public $level;
      public $category_product = array();
      public $category_adaptaion = array();
      public $problem_category;
      public $answer;
      public $selections = array();
      public $memo;
      public $functions_str;
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
         return TRUE_FALSE_PROB;
      }
      else if ($type_name == SINGLE_CHOICE_CHINESE)
      {
         return SINGLE_CHOICE_PROB;
      }
      else if ($type_name == MULTI_CHOICE_CHINESE)
      {
         return MULTI_CHOICE_PROB;
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

   // check first row, first row should be 类型, 标题, 难度, 产品, 适应症, 题库类别, 正确答案, 题目解析
   function is_valid_syntax_import_file($row)
   {
      if ($row[0] != "类型" || $row[1] != "标题" || $row[2] != "难度" || $row[3] != "产品" || $row[4] != "适应症" ||
          $row[5] != "题库类别" || $row[6] != "正确答案" || $row[7] != "题目解析")
      {
         return false;
      }

      return true;
   }
   
   function is_correct_prob_type_format($prob_type)
   {
      if ($prob_type != TRUE_FALSE_PROB && $prob_type != SINGLE_CHOICE_PROB &&
          $prob_type != MULTI_CHOICE_PROB)
      {
         return false;
      }
      
      return true;
   }
   
   function is_correct_prob_desc_format($prob_desc)
   {
      if ($prob_desc == "")
      {
         return false;
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
