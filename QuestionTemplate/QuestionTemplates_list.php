<script type="text/javascript">
//***Step9 列表中的动作上架/下架Ajax呼叫
function expandSearchQTsContentFunc()
{
   if ($('span.QTDesc').hasClass('fixWidth'))
   {
      $('span.QTDesc').removeClass('fixWidth');
      $('span.QTDesc').addClass('breakAll');
      $('.QTsexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.QTDesc').addClass('fixWidth');
      $('span.QTDesc').removeClass('breakAll');
      $('.QTsexpandSR').text('显示过长内容');
   }
}

function actionSearchQTs(QTId, Status)
{
   //ajax
   str = "cmd=actionQTs" + "&QTId=" + QTId + "&Status=" + Status;
   url_str = "QuestionTemplate/QuestionTemplates_action.php?";

   $.ajax
   ({
      beforeSend: function()
      {
         // alert(url_str + str);
      },
      type: 'GET',
      url: url_str + str,
      cache: false,
      success: function(res)
      {
         if (!res.match(/^-\d+$/))  //success
         {
            document.getElementsByName("searchQTsButton")[0].click();
         }
         else  //failed
         {  
            //echo "1.0";
            alert(MSG_SEARCH_ERROR);
         }
      },
      error: function(xhr)
      {
         $('#loadingWrap').hide();
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });

}

//***Step10 列表中动作删除Ajax呼叫
function deleteSearchQTs(QTId)
{
   ret = confirm("确定要删除此问卷模板吗?");
   if (!ret)
      return;
   //ajax
   str = "cmd=deleteQTs" + "&" + "QTId=" + QTId;
   url_str = "QuestionTemplate/QuestionTemplates_delete.php?";

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
         if (!res.match(/^-\d+$/))  //success
         {
            document.getElementsByName("searchQTsButton")[0].click();
         }
         else  //failed
         {
            alert(MSG_SEARCH_ERROR);
         }
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });
}

//***Step11 列表中动作修改Ajax长出修改页面
function modifySearchQTs(QTId)
{
   str = "cmd=read&QTId=" + QTId;
   url_str = "QuestionTemplate/QuestionTemplates_modify.php?";
   window.open(url_str + str);
}

function clickSearchQTsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_qts_page active")
      return;
   nPage = document.getElementsByName("search_qts_page_no")[0].value;
   document.getElementsByName("search_qts_page_no")[0].value = n;
   str = "search_qts_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_qts_page";
   str = "search_qts_page_end_no_" + nPage;
   document.getElementById(str).className = "search_qts_page";
   str = "search_qts_page_begin_no_" + n;
   document.getElementById(str).className = "search_qts_page active";
   str = "search_qts_page_end_no_" + n;
   document.getElementById(str).className = "search_qts_page active"; 
   
   //clear current table
   str = "search_qts_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_qts_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function newSearchQTsContentFunc()
{
   url_str = "QuestionTemplate/QuestionTemplates_upload.php?";
   window.open(url_str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchQTsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>问卷模板名称或备注：</th>
               <td><input id="searchQTsDescName" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchQTsCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchQTsCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchQTs"><input name="searchQTsButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchQTsPages">
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchQTsContentFunc();">上传问卷模板</span>
               <span class="btn ProblemsexpandSR" OnClick="expandSearchQTsContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num">
                  <col class="QTName" />
                  <col class="QTDesc" />
                  <col class="QTStatus" />
                  <col class="QTAction" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>问卷模板名称</th>
                  <th>问卷模板说明</th>
                  <th>状态</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="5" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchQTsContentFunc();">上传问卷模板</span>
               <span class="btn QTsexpandSR" OnClick="expandSearchQTsContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->