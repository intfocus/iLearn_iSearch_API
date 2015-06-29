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
   
   if(($true_false_amount = check_number($_GET["true_false_amount"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }   
   if(($single_selection_amount = check_number($_GET["single_selection_amount"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($multi_selection_amount = check_number($_GET["multi_selection_amount"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($easy_level_percent = check_number($_GET["easy_level_percent"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($mid_level_percent = check_number($_GET["mid_level_percent"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   if(($high_level_percent = check_number($_GET["hard_level_percent"])) == SYMBOL_ERROR)
   {
      sleep(DELAY_SEC);
      echo SYMBOL_ERROR;
      return;
   }
   
   // get each type score
   
   if (!isset($_GET["functions_id"]))
   {
      $functions_id = array();
   }
   else
   {
      $functions_id = $_GET["functions_id"];
   }

   //link
   $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
   if (!$link)  //connect to server failure    
   {
      sleep(DELAY_SEC);
      echo DB_ERROR;       
      return;
   }   
 
   //----- query -----
   //***Step16 页面搜索SQl语句 起始
   if (count($functions_id) == 0)
   {
      $str_query1 = "select * from problems where Status = 1";
   }
   else
   {
      $str_query1 = "select * from problems where Status = 1 AND (";
      for ($i=0; $i<count($functions_id); $i++)   
      {
         if ($i == (count($functions_id) - 1))
         {
            $str_query1 = $str_query1."ProblemCategory like ',%$functions_id[$i]%,')";
         }
         else
         {
            $str_query1 = $str_query1."ProblemCategory like ',%$functions_id[$i]%,'"." OR ";
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
         echo json_encode(array("code"=>ERR_NOT_ENOUGH_PROBLEM, "message"=>"问题数量不够"));
         return;
      }
      for ($i=0; $i<$row_number; $i++)
      {
         $row = mysqli_fetch_assoc($result);
         if ($row["ProblemType"] == TRUE_FALSE_PROB)
         {
            array_push($TrueFalseProbs, new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"]));
         }
         else if ($row["ProblemType"] == SINGLE_CHOICE_PROB)
         {
            array_push($SingleSelProbs, new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"]));
         }
         else if ($row["ProblemType"] == MULTI_CHOICE_PROB)
         {
            array_push($MultiSelProbs, new Problem($row["ProblemId"], $row["ProblemDesc"], $row["ProblemType"], $row["ProblemLevel"]));
         }
      }
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
  
   $SelectedTrueFalseProbs = rand_select_problems($TrueFalseProbs, $true_false_amount);
   $SelectedSingleSelProbs = rand_select_problems($SingleSelProbs, $single_selection_amount);
   $SelectedMultiSelProbs = rand_select_problems($MultiSelProbs, $multi_selection_amount);
 
   $total_prob_amount = count($SelectedTrueFalseProbs) + count($SelectedSingleSelProbs) + count($SelectedMultiSelProbs);
   $estimated_easy_level_prob_amount = $total_prob_amount * ($easy_level_percent / 100);
   $estimated_mid_level_prob_amount = $total_prob_amount * ($mid_level_percent / 100);
   $estimated_high_prob_amount = $total_prob_amount * ($high_level_percent / 100);

 
   $ExamDetail = new ExamDetail();

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

   // merge all selected problem to final problem
   for ($i=0; $i<count($SelectedTrueFalseProbs); $i++)
   {
      array_push($ExamDetail->problems, $SelectedTrueFalseProbs[$i]);
   }
   for ($i=0; $i<count($SelectedSingleSelProbs); $i++)
   {
      array_push($ExamDetail->problems, $SelectedSingleSelProbs[$i]);
   }
   for ($i=0; $i<count($SelectedMultiSelProbs); $i++)
   {
      array_push($ExamDetail->problems, $SelectedMultiSelProbs[$i]);
   }

   // true false, single , multi, easy level, mid level, high level
   array_push($ExamDetail->status, count($SelectedTrueFalseProbs));
   array_push($ExamDetail->status, count($SelectedSingleSelProbs));
   array_push($ExamDetail->status, count($SelectedMultiSelProbs));
   array_push($ExamDetail->status, $cur_easy_level_amount);
   array_push($ExamDetail->status, $cur_mid_level_amount);
   array_push($ExamDetail->status, $cur_high_level_amount);

   echo json_encode($ExamDetail);
   return;   
?>
