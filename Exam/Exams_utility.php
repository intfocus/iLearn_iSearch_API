<?php
   define("SUCCESS", 0);
   
   define("INACTIVE", 0);
   define("ACTIVE", 1);
   
   define("NOT_SUBMIT", 0);
   define("SUBMIT", 1);

   define("EXAM_DELETED", -1);
   define("EXAM_INACTIVE", 0);
   define("EXAM_ACTIVE", 1);
   
   define("ERR_NOT_ENOUGH_PROBLEM", -500);
   
   define("ERR_ADJUST_LEVEL", -1000);
   
   define("EXAM_FILES_DIR", "./Exam_files");
   define("EXAM_RESULT_FILES_DIR", "./Result_files");
   
   define("MSG_NOT_ENOUGH_TRUE_FALSE", "是非题数不足");
   define("MSG_NOT_ENOUGH_SEL_PROB", "单选题数不足");
   define("MSG_NOT_ENOUGH_MULTI_PROB", "多选题数不足");
   
   define("MSG_EXAM_MODIFY", "考卷修改");
   
   define("MOCK_EXAM", 0);
   define("OFFICIAL_EXAM", 1);
   
   define("MSG_MOCK_EXAM", "模拟考");
   define("MSG_OFFICIAL_EXAM", "正式考");
   
   define("GIVE_ANSWER_AFTER_SUBMIT", 1);
   define("GIVE_ANSWER_AFTER_EXAM_FINISHED", 2);

   define("MSG_GIVE_ANSWER_AFTER_SUBMIT", "考试交卷后公布答案");
   define("MSG_GIVE_ANSWER_AFTER_EXAM_FINISHED", "考试交卷后答案公布");

   define("OLINE_TEST", 0);
   define("ONSITE_TEST", 1);

   define("MSG_ONLINE_TEST", "线上考");
   define("MSG_ONSITE_TEST", "落地考");

   define("ERR_INVALID_PARAMETER", -10);
   define("ERR_EXAM_NOT_EXIST", -59);
   define("ERR_SAVE_JSON_FILE",-60);
<<<<<<< HEAD
   
=======
   define("ERR_PROBLEM_COUNT_NOT_ENOUGH", -61);

>>>>>>> master
   class Problem
   {
      function __construct($id, $desc, $type, $level)
      {
         $this->id = $id;
         $this->desc = $desc;
         $this->type = $type;
         $this->level = $level;
      }

      public $id;
      public $desc;
      public $type;
      public $level;
   }
   
   class ExamDetail
   {
      /*
      error
      {
         "status":,
         "problems": [problem_obj, ...],
         "errors":[message, message..]
      }
      */

      function __construct(){}
 
      public $status = array();
      public $problems = array();
      public $errors = array();
   }
   
   
   function get_exam_type_name_from_id($type)
   {
      if ($type == MOCK_EXAM)
      {
         return MSG_MOCK_EXAM;
      }
      else if ($type == OFFICIAL_EXAM)
      {
         return MSG_OFFICIAL_EXAM;
      }
   }
   
   function rand_select_problems($problems, $num)
   {
      $selected_problems = array();

      for ($i=0; $i<$num; $i++)
      {
         if(count($problems) == 0)
         {
            break;
         }
         else
         {
            $index = rand(0, count($problems)-1);
            array_push($selected_problems, $problems[$index]);
            array_splice($problems, $index, 1);
         }
      }

      return $selected_problems;
   }
   
   function add_one_level_and_remove_one_level($problems, &$selected_problems, $added_level, $removed_level)
   {
      $added_problems;
      
      foreach ($problems as $problem)
      {
         if($problem->level == $added_level && !is_in_problem_set($problem, $selected_problems))
         {
            $added_problems = $problem;

         }
      }
      
      if (isset($added_problem))
      {
         for ($i=0; $i<count($selected_problems); $i++)
         {
            if ($selected_problems[$i]->level == $removed_level)
            {
               array_splice($selected_problems, $i, 1);
               array_push($selected_problems, $added_problems);
               return SUCCESS;
            }
         }
      }

      return ERR_ADJUST_LEVEL;
   }
   
   function is_in_problem_set($problem, $selected_problems)
   {
      for($i=0; $i<count($selected_problems); $i++)
      {
         if ($problem->id == $selected_problems[$i])
         {
            return true;
         }
      }

      return false;
   }
   
   function timestamp_to_datetime($timestamp)
   {
      return date("Y-m-d H:i:s", $timestamp);
   }
   
   function timestamp_to_datetime_with_only_date($timestamp)
   {
      return date("Y-m-d", $timestamp);
   }
   
   function is_empty($str)
   {
      if (!isset($str)) 
      {
         return true;
      }
      else if ($str == "")
      {
         return true;
      }

      return false;
   }
   
   function parse_answer($str)
   {
      $answers = array();

      for ($i=0; $i<strlen($str); $i++)
      {
         array_push($answers, $str[$i]);
      }

      return $answers;
   }
   
   function get_random_password()
   {
      $pwd = "";
      for ($i=0; $i<4; $i++)
      {
         $chr = (string)rand(0, 9);
         $pwd = $pwd.$chr;
      }
      return $pwd;
   }
   
?>
