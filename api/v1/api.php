<?
   require_once("utility.php");
   require_once("../../Exam/Exams_utility.php");
   require_once("../../Problem/Problems_utility.php");

   define("FILE_NAME", "../../DB.conf");
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

   //define
   define("DB_HOST", $db_host);
   define("ADMIN_ACCOUNT", $admin_account);
   define("ADMIN_PASSWORD", $admin_password);
   define("CONNECT_DB", $connect_db);
   define("TIME_ZONE", "Asia/Shanghai");
   define("ILLEGAL_CHAR", "'-;<>");                         //illegal char

   //return value
   define("DB_ERROR", -1);
   define("SYMBOL_ERROR", -3);
   define("SYMBOL_ERROR_CMD", -4);
   define("MAPPING_ERROR", -5);
   
   //timezone
   date_default_timezone_set(TIME_ZONE);   
   
   $request_method = $_SERVER["REQUEST_METHOD"];
   $request_uri = $_SERVER["REQUEST_URI"];
   
   if (preg_match("/.*\/api\/v1(\/.*)/", $request_uri, $matches))
   {
      $resource = $matches[1];
   }
   else
   {
      http_response_code(404);
      echo json_encode(array("message"=>"not valid api url", "code"=> ERR_INVALID_API));
      return;
   }

   if ($request_method == "GET")
   {
      // /exam/{exam_id}, download the exam json file
      if (preg_match("/exam\/([0-9]+)/", $resource, $matches))
      {
         $exam_id = $matches[1];
         $exam_json_file_path = EXAM_JSON_FILE_DIR."/$exam_id.json";
         $download_file_name = $exam_id.".json";
         
         if (!file_exists($exam_json_file_path))
         {
           //header('Content-Type:text/html;charset=utf-8');
           http_response_code(404);
           echo(json_encode(array("message"=> "exam not found", "code"=> ERR_FILE_NOT_EXIST)));
           return;
         }
         // get exam json file
         header("Content-type: application/octet-stream");
         header("Content-Disposition: attachment; filename=\"$download_file_name\"");
         readfile($exam_json_file_path);
      }
      // /user/{user_id}/exam, list all exams related to this user
      else if (preg_match("/user\/([0-9]+)\/exam/", $resource, $matches))
      {
         $exams_info = array();
         
         $user_id = $matches[1];

         $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);    
         if (!$link)  //connect to server failure    
         {
            sleep(DELAY_SEC);
            echo DB_ERROR;
            return;
         }
         
         // get active exam list related to this user
         // for each exam, get exam info, and added IsSubmit info
         $str_query = "select * from examroll where UserId=$user_id AND Status=".ACTIVE;
         if($result = mysqli_query($link, $str_query)){
            $row_number = mysqli_num_rows($result);
            for ($i=0; $i<$row_number; $i++)
            {
               //get exam info
               $row = mysqli_fetch_assoc($result);
               // pus exam only when exam is active
               if (is_exam_active($row["ExamId"]))
               {
                  array_push($exams_info, get_exam_info($row["ExamId"], $row["IsSubmit"]));
               }
            }   
         }
   
         echo json_encode(array("exams"=>$exams_info));
         return;
      }
      else
      {
         http_response_code(404);
         echo json_encode(array("message"=> "not valid api url", "code"=> ERR_INVALID_API));
         return;
      }
   }
   else if ($request_method == "PUT")
   {
      // upload result
      if (preg_match("/user\/([0-9]+)\/result\/([0-9]+)/", $resource, $matches))
      {
         $user_id = $matches[1];
         $exam_id = $matches[2];
         $data = file_get_contents("php://input");

         if (strlen($data) == 0)
         {
            http_response_code(400);
            echo json_encode(array("message"=> "empty result file", "code"=> ERR_EMPTY_FILE));
            return;
         }

         $content_obj = json_decode($data);

         // check rule
         if (!isset($content_obj->exam_id) || !isset($content_obj->user_id) ||
             !isset($content_obj->score)|| !isset($content_obj->result))
         {               
            http_response_code(400);
            echo json_encode(array("message"=> "invalid file", "code"=> ERR_INVALID_API));
            return;
         }

         if (!is_upload_exam_exist($content_obj->exam_id))
         {
            http_response_code(404);
            echo json_encode(array("message"=> "exam not found", "code"=> ERR_EXAM_NOT_FOUND));
            return;
         }
         if (!is_upload_user_exist($content_obj->user_id))
         {
            http_response_code(404);
            echo json_encode(array("message"=>"user not found", "code"=> ERR_USER_NOT_FOUND));
            return;
         }

         if (is_result_upload_before($content_obj->exam_id, $content_obj->user_id))
         {
            if (($ret = update_exam_score($content_obj->exam_id, $content_obj->user_id, $content_obj->score)) != SUCCESS)
            {
               http_response_code(500);
               echo json_encode(array("message"=>"failed to update exam score", "code"=> ERR_OTHER));
               return;
            }
            if (($ret = update_exam_result($content_obj->exam_id, $content_obj->user_id, $content_obj->score, $content_obj->result)) != SUCCESS)
            {
               http_response_code(500);
               echo json_encode(array("message"=>"failed to update exam result", "code"=> ERR_OTHER));
               return;
            }
         }               
         else
         {
            if (($ret = insert_exam_score($content_obj->exam_id, $content_obj->user_id, $content_obj->score)) != SUCCESS)
            {
               http_response_code(500);
               echo json_encode(array("message"=>"failed to insert exam score", "code"=> ERR_OTHER));
               return;
            }
            if (($ret = insert_exam_result($content_obj->exam_id, $content_obj->user_id, $content_obj->score, $content_obj->result)) != SUCCESS)
            {
               http_response_code(500);
               echo json_encode(array("message"=>"failed to insert exam result", "code"=> ERR_OTHER));
               return;
            }
         }

         update_the_submit_status($exam_id, $user_id, true);
         
         $tmp_file_path = EXAM_RESULT_UPLOAD_DIR."/".time().hash('md5', $user_id).".json";
         $file_path = EXAM_RESULT_UPLOAD_DIR."/".$exam_id."_".$user_id.".json";

         file_put_contents($tmp_file_path, $data);
         copy($tmp_file_path, $file_path);

         echo json_encode(array("status"=>"success"));
         return;
      }
      else
      {
         http_response_code(404);
         echo json_encode(array("message"=> "not valid api url", "code"=> ERR_INVALID_API));
         return;
      }
   }
   else if ($request_method == "POST")
   {
      if (preg_match("/user\/([0-9]+)\/result\/([0-9]+)\/offline/", $resource, $matches))
      {
         $user_id = $matches[1];
         $exam_id = $matches[2];
         $score = 0;

         $raw_post_data = file_get_contents("php://input");
         mb_parse_str($raw_post_data, $post_data);

         if (isset($post_data["score"]))
         {
            $score = (int)$post_data["score"];
         }
         else
         {
            http_response_code(400);
            echo json_encode(array("message"=> "invalid parameter", "code"=> ERR_INVALID_API));
            return;
         }
         
         if (!is_upload_exam_exist($exam_id))
         {
            http_response_code(404);
            echo json_encode(array("message"=> "exam not found", "code"=> ERR_EXAM_NOT_FOUND));
            return;
         }
         if (!is_upload_user_exist($user_id))
         {
            http_response_code(404);
            echo json_encode(array("message"=>"user not found", "code"=> ERR_USER_NOT_FOUND));
            return;
         }

         if (is_result_upload_before($exam_id, $user_id))
         {
            if (($ret = update_exam_score($exam_id, $user_id, $score)) != SUCCESS)
            {
               http_response_code(500);
               echo json_encode(array("message"=>"failed to update exam score", "code"=> ERR_OTHER));
               return;
            }
         }               
         else
         {
            if (($ret = insert_exam_score($exam_id, $user_id, $score)) != SUCCESS)
            {
               http_response_code(500);
               echo json_encode(array("message"=>"failed to insert exam score", "code"=> ERR_OTHER));
               return;
            }
         }

         update_the_submit_status($exam_id, $user_id, true);
         echo json_encode(array("status"=>"success"));
         return;
      }
      else
      {
         http_response_code(404);
         echo json_encode(array("message"=> "not valid api url", "code"=> ERR_INVALID_API));
         return;
      }
     
   }

   
   function get_exam_info($exam_id, $is_submit)
   {
      $exam_info = array();
      
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
      
      $str_query = "select * from exams where ExamId=$exam_id";
      if($result = mysqli_query($link, $str_query)){
         $row = mysqli_fetch_assoc($result);
         $exam_info = array("exam_id"=>(int)$row["ExamId"],
                            "exam_name"=>$row["ExamName"],
                            "submit"=>(int)$is_submit,
                            "status"=>(int)$row["Status"],
                            "type"=>(int)$row["ExamType"],
                            "begin"=>strtotime($row["ExamBegin"]),
                            "end"=>strtotime($row["ExamEnd"]),
                            "duration"=>(int)($row["Duration"]),
                            "ans_type"=>(int)$row["ExamAnsType"],
                            "description"=>$row["ExamDesc"],
                            "location"=>(int)$row["ExamLocation"],
                            "password"=>$row["ExamPassword"]);
      }
      return $exam_info;
   }

   function is_upload_exam_exist($exam_id)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
      
      $str_query = "select * from exams where ExamId=$exam_id";
      if($result = mysqli_query($link, $str_query))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            return true;
         }
         else
         {
            return false;
         }   
      }

      return false;
   }
   
   function is_upload_user_exist($user_id)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
      
      $str_query = "select * from users where UserId=$user_id";
      if($result = mysqli_query($link, $str_query))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            return true;
         }
         else
         {
            return false;
         }   
      }

      return false;
   }
   
   function is_result_upload_before($exam_id, $user_id)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
      
      $str_query = "select * from examscore where ExamId=$exam_id AND UserId=$user_id";
      if($result = mysqli_query($link, $str_query))
      {
         $row_number = mysqli_num_rows($result);
         if ($row_number > 0)
         {
            return true;
         }
         else
         {
            return false;
         }   
      }

      return false;
   }
   
   function update_exam_score($exam_id, $user_id, $score)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
      
      $str_query = <<<EOD
                Update examscore set Score=$score where ExamId=$exam_id AND UserId=$user_id
EOD;
      mysqli_query($link, $str_query);
      if(!mysqli_query($link, $str_query))
      {
         return ERR_UPDATE_DATABASE;
      }

      return SUCCESS;
   }

   function update_exam_result($exam_id, $user_id, $score, $results)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
   
      foreach ($results as $result)
      {
         $answer = get_answer_string($result->selected_answer);
         $str_query = <<<EOD
                        Update examanswer set ExamId=$exam_id, ProblemId=$result->problem_id, UserId=$user_id,
                        ProblemType=$result->type, Score=$result->score, Answer='$answer', Result=$result->result
                        where ExamId=$exam_id AND ProblemId=$result->problem_id AND UserId=$user_id
EOD;
         if(!mysqli_query($link, $str_query))
         {
            return ERR_UPDATE_DATABASE;
         }
      }
      
      return SUCCESS;
   }
 
   function insert_exam_score($exam_id, $user_id, $score)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }

      $str_query = <<<EOD
                INSERT INTO examscore (ExamId,UserId,Score) VALUES ($exam_id, $user_id, $score)
EOD;
      if(!mysqli_query($link, $str_query))
      {
         return ERR_INSERT_DATABASE;
      }

      return SUCCESS;
   }
   
   function insert_exam_result($exam_id, $user_id, $score, $results)
   {
      //"result": [{"problem_id": 123,"type": 1,"selected_answer": ["A"],"result": 0,"score":0},{"problem_id":456,"type": 2,"selected_answer": ["A","B","C"],"result":1,"score": 2}]
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
   
      foreach ($results as $result)
      {
         $answer = get_answer_string($result->selected_answer);
         $str_query = <<<EOD
                        INSERT INTO examanswer (ExamId, ProblemId, UserId, ProblemType, Score, Answer, Result)
                        Values ($exam_id, $result->problem_id, $user_id, $result->type, $result->score, '$answer', $result->result)
EOD;
         if(!mysqli_query($link, $str_query))
         {
            return ERR_INSERT_DATABASE;
         }
      }
      
      return SUCCESS;
   }

   function update_the_submit_status($exam_id, $user_id, $status)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
      
      $str_query = <<<EOD
               Update examroll set isSubmit=$status
               where ExamId=$exam_id AND UserId=$user_id AND Status=1
EOD;
      if(!mysqli_query($link, $str_query))
      {
         return ERR_UPDATE_DATABASE;
      }
      return SUCCESS;
   }
   
   
   function get_answer_string($answers)
   {
      $answer_str = "";
      
      foreach ($answers as $answer)
      {
         $answer_str = $answer_str.$answer;
      }
      
      return $answer_str;
   }
 
   function is_exam_active($exam_id)
   {
      $link = @mysqli_connect(DB_HOST, ADMIN_ACCOUNT, ADMIN_PASSWORD, CONNECT_DB);
      if (!$link) 
      {   
         die(MSG_ERR_CONNECT_TO_DATABASE);
      }
      
      $str_query = "select * from exams where ExamId=$exam_id";
      if($result=mysqli_query($link, $str_query))
      {
         $row = mysqli_fetch_assoc($result);
         if ($row["Status"] == EXAM_ACTIVE)
         {
            return true;
         }
      }
      return false;
   }
 
?>
