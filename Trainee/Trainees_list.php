<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchTraineesContentFunc()
{
   if ($('span.TraineeName, span.SpeakerName, span.TraineeDate, span.RegisterDate').hasClass('fixWidth'))
   {
      $('span.TraineeName, span.SpeakerName, span.TraineeDate, span.RegisterDate').removeClass('fixWidth');
      $('.TraineesexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.TraineeName, span.SpeakerName, span.TraineeDate, span.RegisterDate').addClass('fixWidth');
      $('.TraineesexpandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchTrainee(TrainingId, Status, UserId)
{
   ret = confirm("确定要通过审核吗?\n\r无法撤销本操作!");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=actionTrainees&TrainingId=" + TrainingId + "&Status=" + Status + "&UserId=" + UserId;
   url_str = "Trainee/Trainees_action.php?";
   
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
               document.getElementsByName("searchTraineesButton")[0].click();
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
function deleteSearchTrainee(TrainingId,UserId)
{
   ret = confirm("确定要审核驳回吗?\n\r无法撤销本操作!");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteTrainees&TrainingId=" + TrainingId + "&UserId=" + UserId;
   url_str = "Trainee/Trainees_delete.php?";
   
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
               document.getElementsByName("searchTraineesButton")[0].click();
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
function modifySearchTrainees(TraineeId)
{
   str = "cmd=read&TraineeId=" + TraineeId;
   url_str = "Trainee/Trainees_modify.php?";
   window.open(url_str + str);
}

function clickSearchTraineesPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_Trainee_page active")
      return;
   nPage = document.getElementsByName("search_Trainee_page_no")[0].value;
   document.getElementsByName("search_Trainee_page_no")[0].value = n;
   str = "search_Trainee_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_Trainee_page";
   str = "search_Trainee_page_end_no_" + nPage;
   document.getElementById(str).className = "search_Trainee_page";
   str = "search_Trainee_page_begin_no_" + n;
   document.getElementById(str).className = "search_Trainee_page active";
   str = "search_Trainee_page_end_no_" + n;
   document.getElementById(str).className = "search_Trainee_page active";	
   
   //clear current table
   str = "search_Trainee_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_Trainee_page" + n;
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
               <th>状态 ：</th>
               <td colspan="3">
                  <select id="searchTraineesStatus">
                     <option value="1">审核中...</option>
                     <option value="2">通过审核</option>
                     <option value="3">流程中断</option>
                     <option value="4">审核驳回</option>
                     <option value="5" selected>全部</option>
                  </select>
               </td>
            </tr>
            <tr>
               <th>报名时间 ：</th>
               <td colspan="3">
                  <input id="from10" type="text" name="searchTraineesfrom1" class="from" readonly="true"/> ~ <input id="to10" type="text" class="to" name="searchTraineesto1" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th>课程截止时间 ：</th>
               <td colspan="3">
                  <input id="from11" type="text" name="searchTraineesfrom2" class="from" readonly="true"/> ~ <input id="to11" type="text" class="to" name="searchTraineesto2" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchTrainees"><input name="searchTraineesButton" class="btn btn-success" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchTraineesPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu">
               <span class="btn TraineesexpandSR" OnClick="expandSearchTraineesContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="TraineeName" />
                  <col class="Speaker" />
                  <col class="TraineeManager" />
                  <col class="Status" />
                  <col class="TraineeDate" />
                  <col class="Date" />
				  <col class="ExamineUser" />
                  <col class="Status" />
				  <col class="TraineeStatus" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课程名称</th>
                  <th>讲师名称</th>
                  <th>学员名称</th>
                  <th>学员编号</th>
                  <th>课程时间</th>
                  <th>报名时间</th>
				  <th>审核人</th>
                  <th>用戶报名状态</th>
				  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="10" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span class="btn TraineesexpandSR" OnClick="expandSearchTraineesContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->