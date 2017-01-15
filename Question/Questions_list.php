<script type="text/javascript">
   function expandSearchQuestionsContentFunc()
   {
      if ($('span.QuestionName, span.QuestionDesc').hasClass('fixWidth'))
      {
         $('span.QuestionName, span.QuestionDesc').removeClass('fixWidth');
         $('span.QuestionName, span.QuestionDesc').addClass('breakAll');
         $('.QuestionsexpandSR').text('隐藏过长内容');
      }
      else
      {
         $('span.QuestionName, span.QuestionDesc').addClass('fixWidth');
         $('span.QuestionName, span.QuestionDesc').removeClass('breakAll');
         $('.QuestionsexpandSR').text('显示过长内容');
      }
   }
   //***Step9 列表中的动作上架/下架Ajax呼叫
   function actionSearchQuestions(QuestionId, Status)
   {
      //ajax
      str = "cmd=actionQuestions" + "&QuestionId=" + QuestionId + "&Status=" + Status;
      url_str = "Question/Questions_action.php?";
      
      //alert(str);
      //$('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(url_str + str);
         },
         type: 'GET',
         url: url_str + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (!res.match(/^-\d+$/))  //success
               {
                  document.getElementsByName("searchQuestionsButton")[0].click();
               }
               else  //failed
               {  
                  //echo "1.0";
                  alert(MSG_SEARCH_ERROR);
               }
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   }
   
   function uploadUserQuestions(QuestionId)
   {
      str = "QuestionId=" + QuestionId;
      url_str = "Question/Questions_roll_list.php?";
      window.open(url_str + str);
   }
   
   //***Step10 列表中动作删除Ajax呼叫
   function deleteSearchQuestions(QuestionId)
   {
      ret = confirm("确定要删除此问卷吗?");
      if (!ret)
         return;
      //ajax
      str = "cmd=deleteQuestions" + "&" + "QuestionId=" + QuestionId;
      url_str = "Question/Questions_delete.php?";
      
      //alert(str);
      //$('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(url_str + str);
         },
         type: 'GET',
         url: url_str + str,
         cache: false,
         success: function(res)
         {
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (!res.match(/^-\d+$/))  //success
               {
                  document.getElementsByName("searchQuestionsButton")[0].click();
               }
               else  //failed
               {  
                  //echo "1.0";
                  alert(MSG_SEARCH_ERROR);
               }
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   }
   
   //***Step11 列表中动作修改Ajax长出修改页面
   function modifySearchQuestions(QuestionId)
   {
      str = "cmd=read&QuestionId=" + QuestionId;
      url_str = "Question/Questions_modify.php?";
      window.open(url_str + str);
   }
   
   function clickSearchQuestionsPage(obj, n)  //搜尋換頁
   {
      if (obj.className == "search_question_page active")
         return;
      nPage = document.getElementsByName("search_question_page_no")[0].value;
      document.getElementsByName("search_question_page_no")[0].value = n;
      str = "search_question_page_begin_no_" + nPage;
      document.getElementById(str).className = "search_question_page";
      str = "search_question_page_end_no_" + nPage;
      document.getElementById(str).className = "search_question_page";
      str = "search_question_page_begin_no_" + n;
      document.getElementById(str).className = "search_question_page active";
      str = "search_question_page_end_no_" + n;
      document.getElementById(str).className = "search_question_page active"; 
      
      //clear current table
      str = "search_question_page" + nPage;
      document.getElementById(str).style.display = "none";
      str = "search_question_page" + n;
      document.getElementById(str).style.display = "block";
   }
   
   //***Step13 新增页面点击保存按钮出发Ajax动作
   function newSearchQuestionsContentFunc()
   { 
      str = "cmd=read&QuestionId=0";
      url_str = "Question/Questions_modify.php?";
      window.open(url_str + str);
   }
   
   //***Eric 是否可以 删除
   function occurTimeDatePicker()
   {
      datepicker();
   }
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchQuestionsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
<div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
   <form>
      <table class="searchField" border="0" cellspacing="0" cellpadding="0">
         <tr>
            <th>问卷名称/说明：</th>
            <td><input id="searchQuestionsNameDesc" type="text" maxlength="50"></td>
            <th>状态 ：</th>
            <td colspan="3">
               <label><input id="searchQuestionsCheckBox1" type="checkbox" checked> 上架</label>
               <label><input id="searchQuestionsCheckBox2" type="checkbox" checked> 下架</label>
            </td>
         </tr>
         <tr>
            <th>问卷截止时间 ：</th>
            <td>
               <input id="from15" type="text" name="searchQuestionsfrom15" class="from" readonly="true"/> ~ <input id="to15" type="text" class="to" name="searchQuestionsto15" readonly="true"/>
            </td>
         </tr>
         <tr>
            <th colspan="4" class="submitBtns">
               <a class="btn_submit_new searchQuestions"><input name="searchQuestionsButton" class="btn btn-success" type="button" value="开始查询"></a>
            </th>
         </tr>
      </table>
   </form>
   <!-- ***Step2 搜索框的设计 结束 -->
   <!-- ***Step3 表格框架 开始 -->
   <div id="sResultW" class="reportW" style="display:block;">
      <div id="searchQuestionsPages">
         <div class="toolMenu">
            <span align=right class="btn" OnClick="newSearchQuestionsContentFunc();">新增</span>
            <span class="btn QuestionsexpandSR" OnClick="expandSearchQuestionsContentFunc();">显示过长内容</span>
         </div>
         <table class="report" border="0" cellspacing="0" cellpadding="0">
            <colgroup>
               <col class="num" />
               <col class="QuestionName" />
               <col class="QuestionDesc" />
               <col class="Status" />
               <col class="EditTime" />
               <col class="QuestionStartTime" />
               <col class="QuestionEndTime" />
            </colgroup>
            <tr>
               <th>编号</th>
               <th>问卷名称</th>
               <th>问卷描述</th>
               <th>状态</th>
               <th>开始时间</th>
               <th>结束时间</th>
               <th>动作</th>
            </tr>
            <tr>
               <td colspan="7" class="empty">请输入上方查询条件，并点选[开始查询]</td>
            </tr>
         </table>
         <div class="toolMenu">
            <span align=right class="btn" OnClick="newSearchQuestionsContentFunc();">新增</span>
            <span class="btn QuestionsexpandSR" OnClick="expandSearchQuestionsContentFunc();">显示过长内容</span>
         </div>
      </div>
   </div>
   <!-- search pages-->
</div>
<!-- ***Step3 表格框架 结束 -->