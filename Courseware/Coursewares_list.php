<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchCoursewaresContentFunc()
{
   if ($('span.CoursewareName, span.CoursewareDesc').hasClass('fixWidth'))
   {
      $('span.CoursewareName, span.CoursewareDesc').removeClass('fixWidth');
      $('.CoursewaresexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.CoursewareName, span.CoursewareDesc').addClass('fixWidth');
      $('.CoursewaresexpandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchCoursewares(CoursewareId, Status)
{
   //ajax
   str = "cmd=actionCoursewares" + "&CoursewareId=" + CoursewareId + "&Status=" + Status;
   url_str = "Courseware/Coursewares_action.php?";
   
   alert(str);
   return;
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
               document.getElementsByName("searchCoursewaresButton")[0].click();
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
function deleteSearchCoursewares(CoursewareId)
{
   ret = confirm("确定要删除此课件吗?");
   if (!ret)
      return;
   //ajax
   str = "cmd=deleteCoursewares" + "&" + "CoursewareId=" + CoursewareId;
   url_str = "Courseware/Coursewares_delete.php?";
   
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
               document.getElementsByName("searchCoursewaresButton")[0].click();
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
function modifySearchCoursewares(CoursewareId)
{
   str = "cmd=read&CoursewareId=" + CoursewareId;
   url_str = "Courseware/Coursewares_modify.php?";
   window.open(url_str + str);
}

function clickSearchCoursewaresPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_Courseware_page active")
      return;
   nPage = document.getElementsByName("search_Courseware_page_no")[0].value;
   document.getElementsByName("search_Courseware_page_no")[0].value = n;
   str = "search_Courseware_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_Courseware_page";
   str = "search_Courseware_page_end_no_" + nPage;
   document.getElementById(str).className = "search_Courseware_page";
   str = "search_Courseware_page_begin_no_" + n;
   document.getElementById(str).className = "search_Courseware_page active";
   str = "search_Courseware_page_end_no_" + n;
   document.getElementById(str).className = "search_Courseware_page active"; 
   
   //clear current table
   str = "search_Courseware_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_Courseware_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function newSearchCoursewaresContentFunc()
{ 
   str = "cmd=read&CoursewareId=0";
   url_str = "Courseware/Coursewares_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchCoursewaresContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>课件名称/课件备注 ：</th>
               <td><input id="searchCoursewaresNameDesc" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchCoursewaresCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchCoursewaresCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchCoursewares"><input name="searchCoursewaresButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchCoursewaresPages">
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchCoursewaresContentFunc();">新增</span>
               <span class="btn CoursewaresexpandSR" OnClick="expandSearchCoursewaresContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="CoursewareTitle" />
                  <col class="CoursewareDesc" />
                  <col class="Status" />
                  <col class="EditTime" />
                  <col class="action" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课件名称</th>
                  <th>课件备注</th>
                  <th>状态</th>
                  <th>最后修改时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="6" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchCoursewaresContentFunc();">新增</span>
               <span class="btn CoursewaresexpandSR" OnClick="expandSearchCoursewaresContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->