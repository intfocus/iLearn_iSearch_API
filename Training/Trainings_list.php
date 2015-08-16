<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchTrainingsContentFunc()
{
   if ($('span.TrainingName, span.Speaker, span.TrainingManager').hasClass('fixWidth'))
   {
      $('span.TrainingName, span.Speaker, span.TrainingManager').removeClass('fixWidth');
      $('.TrainingsexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.TrainingName, span.Speaker, span.TrainingManager').addClass('fixWidth');
      $('.TrainingsexpandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchTrainings(TrainingId, Status)
{
   //ajax
   str = "cmd=actionTrainings" + "&TrainingId=" + TrainingId + "&Status=" + Status;
   url_str = "Training/Trainings_action.php?";
   
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
               document.getElementsByName("searchTrainingsButton")[0].click();
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


function uploadUserTrainings(TrainingId)
{
   str = "TrainingId=" + TrainingId;
   url_str = "Training/Trainings_roll_list.php?";
   window.open(url_str + str);
}

function uploadUserTrainingManagers(TrainingId)
{
   str = "TrainingId=" + TrainingId;
   url_str = "Training/TrainingManagers_roll_list.php?";
   window.open(url_str + str);
}

//***Step10 列表中动作删除Ajax呼叫
function deleteSearchTrainings(TrainingId)
{
   ret = confirm("确定要删除此公告吗?");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteTrainings" + "&" + "TrainingId=" + TrainingId;
   url_str = "Training/Trainings_delete.php?";
   
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
               document.getElementsByName("searchTrainingsButton")[0].click();
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
function modifySearchTrainings(TrainingId)
{
   str = "cmd=read&TrainingId=" + TrainingId;
   url_str = "Training/Trainings_modify.php?";
   window.open(url_str + str);
}

function clickSearchTrainingsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_training_page active")
      return;
   nPage = document.getElementsByName("search_training_page_no")[0].value;
   document.getElementsByName("search_training_page_no")[0].value = n;
   str = "search_training_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_training_page";
   str = "search_training_page_end_no_" + nPage;
   document.getElementById(str).className = "search_training_page";
   str = "search_training_page_begin_no_" + n;
   document.getElementById(str).className = "search_training_page active";
   str = "search_training_page_end_no_" + n;
   document.getElementById(str).className = "search_training_page active";	
   
   //clear current table
   str = "search_training_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_training_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function TrainingsearchTrainingsContentFunc()
{ 
   str = "cmd=read&TrainingId=0";
   url_str = "Training/Trainings_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchTrainingsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>课程名称/讲师名称搜索 ：</th>
               <td><input id="searchTrainingsNameSpeaker" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchTrainingsCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchTrainingsCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th>报名截止时间 ：</th>
               <td colspan="3">
                  <input id="from8" type="text" name="searchTrainingsfrom1" class="from" readonly="true"/> ~ <input id="to8" type="text" class="to" name="searchTrainingsto1" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th>课程截止时间 ：</th>
               <td colspan="3">
                  <input id="from9" type="text" name="searchTrainingsfrom2" class="from" readonly="true"/> ~ <input id="to9" type="text" class="to" name="searchTrainingsto2" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchTrainings"><input name="searchTrainingsButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchTrainingsPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu">
               <span align=right class="btn" OnClick="TrainingsearchTrainingsContentFunc();">新增</span>
               <span class="btn TrainingsexpandSR" OnClick="expandSearchTrainingsContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="TrainingName" />
                  <col class="Speaker" />
                  <col class="TrainingManager" />
                  <col class="Status" />
                  <col class="TrainingDate" />
                  <col class="Date" />
                  <col class="TrainingAction" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课程名称</th>
                  <th>讲师名称</th>
                  <th>课程负责人</th>
                  <th>课程状态</th>
                  <th>课程时间</th>
                  <th>报名时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="8" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="TrainingsearchTrainingsContentFunc();">新增</span>
               <span class="btn TrainingsexpandSR" OnClick="expandSearchTrainingsContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->