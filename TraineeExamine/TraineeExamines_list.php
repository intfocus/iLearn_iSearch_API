<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchTraineeExaminesContentFunc()
{
   if ($('span.TraineeName, span.SpeakerName').hasClass('fixWidth'))
   {
      $('span.TraineeName, span.SpeakerName').removeClass('fixWidth');
      $('.TraineesexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.TraineeName, span.SpeakerName').addClass('fixWidth');
      $('.TraineesexpandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchTraineeExamines(TrainingId, Status, UserId)
{
   //ajax
   str = "cmd=actionTraineeExamines&TrainingId=" + TrainingId + "&Status=" + Status + "&UserId=" + UserId;
   url_str = "TraineeExamine/TraineeExamines_action.php?";
   
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
               document.getElementsByName("searchTraineesExamineButton")[0].click();
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

//***Step10 列表中动作删除Ajax呼叫
function deleteSearchTraineeExamines(TrainingId,UserId)
{
   ret = confirm("确定要审核驳回吗?");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteTraineeExamines&TrainingId=" + TrainingId + "&UserId=" + UserId;
   url_str = "TraineeExamine/TraineeExamines_delete.php?";
   
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
               document.getElementsByName("searchTraineesExamineButton")[0].click();
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
function modifySearchTraineeExamines(TraineeId)
{
   str = "cmd=read&TraineeId=" + TraineeId;
   url_str = "TraineeExamine/TraineeExamines_modify.php?";
   window.open(url_str + str);
}

function clickSearchTraineeExaminesPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_TraineeExamine_page active")
      return;
   nPage = document.getElementsByName("search_TraineeExamine_page_no")[0].value;
   document.getElementsByName("search_TraineeExamine_page_no")[0].value = n;
   str = "search_TraineeExamine_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_TraineeExamine_page";
   str = "search_TraineeExamine_page_end_no_" + nPage;
   document.getElementById(str).className = "search_TraineeExamine_page";
   str = "search_TraineeExamine_page_begin_no_" + n;
   document.getElementById(str).className = "search_TraineeExamine_page active";
   str = "search_TraineeExamine_page_end_no_" + n;
   document.getElementById(str).className = "search_TraineeExamine_page active";	
   
   //clear current table
   str = "search_TraineeExamine_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_TraineeExamine_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function TraineesearchTraineesContentFunc()
{ 
   str = "cmd=read&TraineeId=0";
   url_str = "Trainee/Trainees_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchTraineesContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>课程名称/讲师名称/学员（名称/编号)搜索 ：</th>
               <td><input id="searchTraineesNameSpeaker" type="text" maxlength="50"></td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchTraineeExamines"><input name="searchTraineesExamineButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchTraineeExaminesPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu">
               <span class="btn TraineesexpandSR" OnClick="expandSearchTraineeExaminesContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="TraineeName" />
                  <col class="Speaker" />
                  <col class="UserName" />
                  <col class="EmployeeId" />
                  <col class="Date" />
                  <col class="TraineeExaminesAction" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课程名称</th>
                  <th>讲师名称</th>
                  <th>学员名称</th>
                  <th>学员编号</th>
                  <th>报名时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="7" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span class="btn TraineesexpandSR" OnClick="expandSearchTraineeExaminesContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->