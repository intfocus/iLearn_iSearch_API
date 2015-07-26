<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchCoursePacketsContentFunc()
{
   if ($('span.CoursePacketName, span.CoursePacketDesc').hasClass('fixWidth'))
   {
      $('span.CoursePacketName, span.CoursePacketDesc').removeClass('fixWidth');
      $('.CoursePacketsexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.CoursePacketName, span.CoursePacketDesc').addClass('fixWidth');
      $('.CoursePacketsexpandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchCoursePackets(CoursePacketId, Status)
{
   //ajax
   str = "cmd=actionCoursePackets" + "&CoursePacketId=" + CoursePacketId + "&Status=" + Status;
   url_str = "CoursePacket/CoursePackets_action.php?";
   
   alert(url_str + str);
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
               document.getElementsByName("searchCoursePacketsButton")[0].click();
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
function deleteSearchCoursePackets(CoursePacketId)
{
   ret = confirm("确定要删除此课程包吗?");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteCoursePackets" + "&" + "CoursePacketId=" + CoursePacketId;
   url_str = "CoursePacket/CoursePackets_delete.php?";
   
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
               document.getElementsByName("searchCoursePacketsButton")[0].click();
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
function modifySearchCoursePackets(CoursePacketId)
{
   str = "cmd=read&CoursePacketId=" + CoursePacketId;
   url_str = "CoursePacket/CoursePackets_modify.php?";
   window.open(url_str + str);
}

function clickSearchCoursePacketsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_page active")
      return;
   nPage = document.getElementsByName("search_page_no")[0].value;
   document.getElementsByName("search_page_no")[0].value = n;
   str = "search_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_page";
   str = "search_page_end_no_" + nPage;
   document.getElementById(str).className = "search_page";
   str = "search_page_begin_no_" + n;
   document.getElementById(str).className = "search_page active";
   str = "search_page_end_no_" + n;
   document.getElementById(str).className = "search_page active";	
   
   //clear current table
   str = "search_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function CoursePacketsearchCoursePacketsContentFunc()
{ 
   str = "cmd=read&CoursePacketId=0";
   url_str = "CoursePacket/CoursePackets_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchCoursePacketsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>课程包名称/课程包备注 ：</th>
               <td><input id="searchCoursePacketsNameDesc" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchCoursePacketsCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchCoursePacketsCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th>最后修改时间 ：</th>
               <td colspan="3">
                  <input id="from14" type="text" name="searchCoursePacketsfrom1" class="from" readonly="true"/> ~ <input id="to14" type="text" class="to" name="searchCoursePacketsto1" readonly="true"/>
               </td>
            </tr>

            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchCoursePackets"><input name="searchCoursePacketsButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchCoursePacketsPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu">
               <span align=right class="btn" OnClick="CoursePacketsearchCoursePacketsContentFunc();">新增</span>
               <span class="btn CoursePacketsexpandSR" OnClick="expandSearchCoursePacketsContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="CoursePacketName" />
                  <col class="CoursePacketDesc" />
                  <col class="Status" />
                  <col class="EditTime" />
                  <col class="action" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课程包名称</th>
                  <th>课程包备注</th>
                  <th>状态</th>
                  <th>最后修改时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="6" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="CoursePacketsearchCoursePacketsContentFunc();">新增</span>
               <span class="btn CoursePacketsexpandSR" OnClick="expandSearchCoursePacketsContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->