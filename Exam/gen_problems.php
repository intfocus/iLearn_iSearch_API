<?php
   require_once("../Problem/Problems_utility.php");
   require_once("Exams_utility.php");
   
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
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);

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
   function check_number($check_str)
   {
      if ($check_str == "")
      {
         $check_str = 0;
      }
      if(!is_numeric($check_str))
      {
         return SYMBOL_ERROR; 
      }
      if($check_str < 0)
      {
         return SYMBOL_ERROR;
      }
      return $check_str;
   }
   
   function check_functions_id($check_str)
   {
      if (preg_match("/([0-9]+,)*([0-9])*/", $check_str))
      {
         return true;
      }
      else
      {
         return false;
      }
   }
   
   function get_functions_id($func_id_str)
   {
      return preg_split("/,/", $func_id_str);
   }
   
   // get select problem
   // get all problem set

   $ExamDetail = new ExamDetail();

   $select_problems = array();
   if (isset($_GET["select_problems"]))
   {
      $select_problems = $_GET["select_problems"];
      //print_r($select_problems);
   }

   $problem_sets = array();
   if (isset($_GET["problem_sets"]))
   {
      $problem_sets = $_GET["problem_sets"];
      //print_r($problem_sets);
   }
   else
   {
      sleep(DELAY_SEC);
      //echo SYMBOL_ERROR;
      echo -100;
      return;
   }

   $SelectedTrueFalseProbs = array();
   $SelectedSingleSelProbs = array();
   $SelectedMultiSelProbs = array();
   $easy_level_amount = 0;
   $mid_level_amount = 0;
   $high_level_amount = 0;

   $problem_sets = json_decode($problem_sets);

   foreach ($problem_sets as $problem_set)
   {
      //print_r($problem_set);
      
      $cur_problems = get_problems($problem_set);
      if ($cur_problems == ERR_NOT_ENOUGH_PROBLEM)
      {
         continue;
      }

      $SelectedTrueFalseProbs = array_merge($SelectedTrueFalseProbs, $cur_problems["true_false_problems"]);
      $SelectedSingleSelProbs = array_merge($SelectedSingleSelProbs, $cur_problems["single_problems"]);
      $SelectedMultiSelProbs = array_merge($SelectedMultiSelProbs, $cur_problems["multi_problems"]);
      $easy_level_amount = $easy_level_amount + $cur_problems["easy_level_amount"];
      $mid_level_amount = $mid_level_amount + $cur_problems["mid_level_amount"];
      $high_level_amount = $high_level_amount + $cur_problems["high_level_amount"];
   }



   // get problems detail about select problems
   foreach ($select_problems as $problem_id)
   {
      $cur_problem = get_problem_detail($problem_id);
      if ($cur_problem->type == TRUE_FALSE_PROB)
      {
         array_push($SelectedTrueFalseProbs, $cur_problem);
      }
      else if ($cur_problem->type == SINGLE_CHOICE_PROB)
      {
         array_push($SelectedSingleSelProbs, $cur_problem);
      }
      else if ($cur_problem->type == MULTI_CHOICE_PROB)
      {
         array_push($SelectedMultiSelProbs, $cur_problem);
      }

      if ($cur_problem->level == EASY_LEVEL)
      {
         $easy_level_amount++;
      }
      else if ($cur_problem->level == MID_LEVEL)
      {
         $mid_level_amount++;
      }
      else if ($cur_problem->level == HIGH_LEVEL)
      {
         $high_level_amount++;
      }
   }
   $SelectedTrueFalseProbs = unqiue_problem($SelectedTrueFalseProbs);
   $SelectedSingleSelProbs = unqiue_problem($SelectedSingleSelProbs);
   $SelectedMultiSelProbs = unqiue_problem($SelectedMultiSelProbs);

   if (count($SelectedTrueFalseProbs) == 0 && count($SelectedSingleSelProbs) == 0 && count($SelectedMultiSelProbs) == 0)
   {
      echo json_encode(array("code"=>ERR_NOT_ENOUGH_PROBLEM, "message"=>"问题数量不够"));
      return;
   }

   //count required number of problem
   $true_false_amount = 0;
   $single_selection_amount = 0;
   $multi_selection_amount = 0;
   foreach ($problem_sets as $problem_set)
   {
      $true_false_amount = $true_false_amount + $problem_set->true_false_amount;
      $single_selection_amount = $single_selection_amount + $problem_set->single_selection_amount;
      $multi_selection_amount = $multi_selection_amount + $problem_set->multi_selection_amount;
   }

   // check number of each type
   if (count($SelectedTrueFalseProbs) < $true_false_amount)
   {
      array_push($ExamDetail->errors, MSG_NOT_ENOUGH_TRUE_FALSE);
   }
   if (count($SelectedSingleSelProbs) < $single_selection_amount)
   {
      array_push($ExamDetail->errors, MSG_NOT_ENOUGH_SEL_PROB);
   }
   if (count($SelectedMultiSelProbs) < $multi_selection_amount)
   {
      array_push($ExamDetail->errors, MSG_NOT_ENOUGH_MULTI_PROB);
   }


   $ExamDetail->problems = array_merge($ExamDetail->problems, $SelectedTrueFalseProbs);
   $ExamDetail->problems = array_merge($ExamDetail->problems, $SelectedSingleSelProbs);
   $ExamDetail->problems = array_merge($ExamDetail->problems, $SelectedMultiSelProbs);

   // true false, single , multi, easy level, mid level, high level
   array_push($ExamDetail->status, count($SelectedTrueFalseProbs));
   array_push($ExamDetail->status, count($SelectedSingleSelProbs));
   array_push($ExamDetail->status, count($SelectedMultiSelProbs));
   array_push($ExamDetail->status, $easy_level_amount);
   array_push($ExamDetail->status, $mid_level_amount);
   array_push($ExamDetail->status, $high_level_amount);
   //print_r($ExamDetail->status);

   echo json_encode($ExamDetail);
   return;

   function get_problems($problem_set)
   {
      $require_function_id = $problem_set->require_function_id;
      $product_functions_id = $problem_set->product_functions_id;
      $adapation_functions_id = $problem_set->adapation_functions_id;
      $true_false_amount = trim($problem_set->true_false_amount);
      $single_selection_amount = trim($problem_set->single_selection_amount);
      $multi_selection_amount = trim($problem_set->multi_selection_amount);
      $easy_level_percent = $problem_set->easy_level_percent;
      $mid_level_percent = $problem_set->mid_level_percent;
      $high_level_percent = $problem_set->hard_level_percent;
      $is_obu_require = $problem_set->is_obu_require;

      //link
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
      if (!$link)  //connect to server failure    
      {
         sleep(DELAY_SEC);
         echo DB_ERROR;       
         return;
      }   

      // get obu require id
      $func_type = FUNCTION_OTHER;
      $str_query = "Select * from functions where FunctionType=$func_type AND FunctionName='OBL'";
      if($result = mysqli_query($link, $str_query))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            $row = mysqli_fetch_assoc($result);
            $obu_id = $row["FunctionId"];
         }
      }

      if (!isset($obu_id))
      {
         sleep(DELAY_SEC);
         echo DB_ERROR;       
         return;
      }

      if ($is_obu_require == 1)
      {
         $str_query1 = "select * from problems where Status = 1 AND (ProblemCategory like ',%$obu_id%,')";
      }
      else
      {
         $str_query1 = "select * from problems where Status = 1 AND (ProblemCategory NOT like ',%$obu_id%,')";
       }

      //----- query -----
      if (count($product_functions_id) == 0 && count($adapation_functions_id) == 0)
      {
		 if($require_function_id != 0)
         {
			$str_query1 = $str_query1." AND (ProblemCategory like ',%$require_function_id%,')";
		 }
      }
      else
      {
         if (count($product_functions_id) > 0)
         {
			if($require_function_id != 0)
            {
               $str_query1 = $str_query1." AND (ProblemCategory like ',%$require_function_id%,')";
			}
			$str_query1 = $str_query1." AND (" ;
            for ($i=0; $i<count($product_functions_id); $i++)   
            {
               if ($i == (count($product_functions_id) - 1))
               {
                  $str_query1 = $str_query1."ProblemCategory like ',%$product_functions_id[$i]%,')";
               }
               else
               {
                  $str_query1 = $str_query1."ProblemCategory like ',%$product_functions_id[$i]%,'"." OR ";
               }
            }
         }

         if (count($adapation_functions_id) > 0)
         {
            $str_query1 = $str_query1." AND (";
            for ($i=0; $i<count($adapation_functions_id); $i++)   
            {
               if ($i == (count($adapation_functions_id) - 1))
               {
                  $str_query1 = $str_query1."ProblemCategory like ',%$adapation_functions_id[$i]%,')";
               }
               else
               {
                  $str_query1 = $str_query1."ProblemCategory like ',%$adapation_functions_id[$i]%,'"." OR ";
               }
            }
         }
      }

      //***Step16 页面搜索SQl语句 结束
      /////////////////////
      // prepare the SQL command and query DB
      /////////////////////

      $TrueFalseProbs = array();
      $SingleSelProbs = array();
      $MultiSelProbs = array();

      $SelectedTrueFalseProbs = array();
      $SelectedSingleSelProbs = array();
      $SelectedMultiSelProbs = array();
      $SelectedProbs = array();
      
      
      if($result = mysqli_query($link, $str_query1)){
         $row_number = mysqli_num_rows($result);
         if ($row_number == 0)
         {
            //echo json_encode(array("code"=>ERR_NOT_ENOUGH_PROBLEM, "message"=>"问题数量不够"));
            return ERR_NOT_ENOUGH_PROBLEM;
         }
         for ($i=0; $i<$row_number; $i++)
         {
            $row = mysqli_fetch_assoc($result);
            if ($row["ProblemType"] == TRUE_FALSE_PROB)
            {
               array_push($TrueFalseProbs, new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"], $row["CreatedTime"]));
            }
            else if ($row["ProblemType"] == SINGLE_CHOICE_PROB)
            {
               array_push($SingleSelProbs, new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"], $row["CreatedTime"]));
            }
            else if ($row["ProblemType"] == MULTI_CHOICE_PROB)
            {
               array_push($MultiSelProbs, new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"], $row["CreatedTime"]));
            }
         }
      }
      else
      {
         if($link){
            mysqli_close($link);
         }
         sleep(DELAY_SEC);
         //echo -__LINE__;
         return DB_ERROR;
      }
     
      $SelectedTrueFalseProbs = rand_select_problems($TrueFalseProbs, $true_false_amount);
      $SelectedSingleSelProbs = rand_select_problems($SingleSelProbs, $single_selection_amount);
      $SelectedMultiSelProbs = rand_select_problems($MultiSelProbs, $multi_selection_amount);
    
      $total_prob_amount = count($SelectedTrueFalseProbs) + count($SelectedSingleSelProbs) + count($SelectedMultiSelProbs);
      $estimated_easy_level_prob_amount = $total_prob_amount * ($easy_level_percent / 100);
      $estimated_mid_level_prob_amount = $total_prob_amount * ($mid_level_percent / 100);
      $estimated_high_prob_amount = $total_prob_amount * ($high_level_percent / 100);
     
      // check level
      $cur_easy_level_amount = 0;
      $cur_mid_level_amount = 0;
      $cur_high_level_amount = 0;
      foreach ($SelectedTrueFalseProbs as $selected_prob)
      {
         if ($selected_prob->level == EASY_LEVEL)
         {
            $cur_easy_level_amount++;
         }
         else if ($selected_prob->level == MID_LEVEL)
         {
            $cur_mid_level_amount++;
         }
         else if ($selected_prob->level == HIGH_LEVEL)
         {
            $cur_high_level_amount++;
         }
      }
      
      foreach ($SelectedSingleSelProbs as $selected_prob)
      {
         if ($selected_prob->level == EASY_LEVEL)
         {
            $cur_easy_level_amount++;
         }
         else if ($selected_prob->level == MID_LEVEL)
         {
            $cur_mid_level_amount++;
         }
         else if ($selected_prob->level == HIGH_LEVEL)
         {
            $cur_high_level_amount++;
         }
      }
      
      foreach ($SelectedMultiSelProbs as $selected_prob)
      {
         if ($selected_prob->level == EASY_LEVEL)
         {
            $cur_easy_level_amount++;
         }
         else if ($selected_prob->level == MID_LEVEL)
         {
            $cur_mid_level_amount++;
         }
         else if ($selected_prob->level == HIGH_LEVEL)
         {
            $cur_high_level_amount++;
         }
      }

      // cur easy level < cur easy level amount
      // pick a mid level prob not be included in the selected true false type
      // if there is no true false type can select -> change to single level type
      // if there is no single level type -> change to multi level type
      
      // don't modify high level
      
      // in true false type, select a new easy level, kick the first that is not easy
      // in single select type, select a new easy level, kick the first that is not easy
      // in high select type, select a new easy level, kick the fiest that is not easy
      $stage = TRUE_FALSE_PROB;
      $added_level = EASY_LEVEL;
      $removed_level = MID_LEVEL;
      while ($cur_easy_level_amount < $estimated_easy_level_prob_amount)
      {

         if ($stage == TRUE_FALSE_PROB)
         {
            $cur_problems_set = $TrueFalseProbs;
            $cur_selected_problems = $SelectedTrueFalseProbs;
         }
         else if ($stage == SINGLE_CHOICE_PROB)
         {
            $cur_problems_set = $SingleSelProbs;
            $cur_selected_problems = $SelectedSingleSelProbs;
         }
         else if ($stage == MULTI_CHOICE_PROB)
         {
            $cur_problems_set = $MultiSelProbs;
            $cur_selected_problems = $SelectedMultiSelProbs;
         }

         if (($ret = add_one_level_and_remove_one_level(
                     $cur_problems_set, $cur_selected_problems, EASY_LEVEL, $removed_level)) == SUCCESS)
         {
            $cur_easy_level_amount++;
            if ($removed_level == MID_LEVEL)
            {
               $cur_mid_level_amount--;
            }
            else if ($removed_level == HIGH_LEVEL)
            {
               $cur_high_level_amount--;
            }
         }
         else
         {
            $removed_level++;
            if ($removed_level > HIGH_LEVEL)
            {
               $removed_level = MID_LEVEL;
               $stage++;
            }
            if ($stage > MULTI_CHOICE_PROB)
            {
               break;
            }
         }
      }
     
      // in true false type, select a new mid level, kick the first that is hard
      // in single select type, select a new mid level, kick the first that is hard
      // in multi select type, select a new mid level, kick the first that is hard
      $stage = TRUE_FALSE_PROB;
      $added_level = MID_LEVEL;
      while ($cur_mid_level_amount < $estimated_mid_level_prob_amount)
      {  
         if ($stage == TRUE_FALSE_PROB)
         {
            $cur_problems_set = $TrueFalseProbs;
            $cur_selected_problems = $SelectedTrueFalseProbs;
         }
         else if ($stage == SINGLE_CHOICE_PROB)
         {
            $cur_problems_set = $SingleSelProbs;
            $cur_selected_problems = $SelectedSingleSelProbs;
         }
         else if ($stage == MULTI_CHOICE_PROB)
         {
            $cur_problems_set = $MultiSelProbs;
            $cur_selected_problems = $SelectedMultiSelProbs;
         }

         if (($ret = add_one_level_and_remove_one_level(
                     $cur_problems_set, $cur_selected_problems, MID_LEVEL, HIGH_LEVEL)) == SUCCESS)
         {
            $cur_mid_level_amount++;
            $cur_high_level_amount--;
         }
         else {
            $stage++;
            if ($stage > MULTI_CHOICE_PROB)
            {
               break;
            }
         }
      }
      
      $stage = TRUE_FALSE_PROB;
      $added_level = HIGH_LEVEL;
      $removed_level = EASY_LEVEL;
      // if hard < estimated problem && easy > estimated, adjust it
      while ($cur_high_level_amount < $estimated_high_prob_amount && 
             ($cur_easy_level_amount > $estimated_easy_level_prob_amount || $cur_mid_level_amount > $estimated_mid_level_prob_amount))
      {  
         if ($stage == TRUE_FALSE_PROB)
         {
            $cur_problems_set = $TrueFalseProbs;
            $cur_selected_problems = $SelectedTrueFalseProbs;
         }
         else if ($stage == SINGLE_CHOICE_PROB)
         {
            $cur_problems_set = $SingleSelProbs;
            $cur_selected_problems = $SelectedSingleSelProbs;
         }
         else if ($stage == MULTI_CHOICE_PROB)
         {
            $cur_problems_set = $MultiSelProbs;
            $cur_selected_problems = $SelectedMultiSelProbs;
         }

         if (($ret = add_one_level_and_remove_one_level(
                     $cur_problems_set, $cur_selected_problems, HIGH_LEVEL, $removed_level)) == SUCCESS)
         {
            $cur_high_level_amount++;
            if ($removed_level == EASY_LEVEL)
            {
               $cur_esay_level_amount--;
            }
            else if ($removed_level == MID_LEVEL)
            {
               $cur_mid_level_amount--;
            }
         }
         else
         {
            $removed_level++;
            if ($removed_level > MID_LEVEL)
            {
               $removed_level = EASY_LEVEL;
               $stage++;
            }
            if ($stage > MULTI_CHOICE_PROB)
            {
               break;
            }
         }      
      }

      return array("true_false_problems"=>$SelectedTrueFalseProbs, "single_problems"=>$SelectedSingleSelProbs, "multi_problems"=>$SelectedMultiSelProbs, 
                   "easy_level_amount"=>$cur_easy_level_amount, "mid_level_amount"=>$cur_mid_level_amount, "high_level_amount"=>$cur_high_level_amount);
   }

   function get_problem_detail($problem_id)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
      if (!$link)  //connect to server failure    
      {
         sleep(DELAY_SEC);
         echo DB_ERROR;       
         return;
      }   

      $str_query1 = "select * from problems where ProblemId=$problem_id";
      if($result = mysqli_query($link, $str_query1))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number == 0)
         {
            return;
         }

         $row = mysqli_fetch_assoc($result);
         return new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"], $row["CreatedTime"]);
      }

   }

   function unqiue_problem($problems)
   {
      $orig_problems = $problems;
      $exist_problems_id = array();
      $new_problems = array();
      foreach ($orig_problems as $problem)
      {
         if (!in_array($problem->id, $exist_problems_id))
         {
            array_push($exist_problems_id, $problem->id);
            array_push($new_problems, $problem);
         }
      }
      return $new_problems;
   }
?>
