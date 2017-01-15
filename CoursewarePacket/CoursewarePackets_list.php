<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchpptsContentFunc()
{
   if ($('span.PPTName, span.PPTDesc').hasClass('fixWidth'))
   {
      $('span.PPTName, span.PPTDesc').removeClass('fixWidth');
      $('.pptsexpandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.PPTName, span.PPTDesc').addClass('fixWidth');
      $('.pptsexpandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchppts(pptId, Status)
{
   //ajax
   str = "cmd=actionppts" + "&pptId=" + pptId + "&Status=" + Status;
   url_str = "CoursewarePacket/CoursewarePackets_action.php?";
   
   //alert(url_str + str);
   
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
               document.getElementsByName("searchpptsButton")[0].click();
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
function deleteSearchppts(pptId)
{
   ret = confirm("确定要删除此课件包吗?");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteppts" + "&" + "pptId=" + pptId;
   url_str = "CoursewarePacket/CoursewarePackets_delete.php?";
   
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
               document.getElementsByName("searchpptsButton")[0].click();
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
function modifySearchppts(pptId)
{
   str = "cmd=read&pptId=" + pptId;
   url_str = "CoursewarePacket/CoursewarePackets_modify.php?";
   window.open(url_str + str);
}

function clickSearchpptsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_ppt_page active")
      return;
   nPage = document.getElementsByName("search_ppt_page_no")[0].value;
   document.getElementsByName("search_ppt_page_no")[0].value = n;
   str = "search_ppt_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_ppt_page";
   str = "search_ppt_page_end_no_" + nPage;
   document.getElementById(str).className = "search_ppt_page";
   str = "search_ppt_page_begin_no_" + n;
   document.getElementById(str).className = "search_ppt_page active";
   str = "search_ppt_page_end_no_" + n;
   document.getElementById(str).className = "search_ppt_page active";	
   
   //clear current table
   str = "search_ppt_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_ppt_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function pptsearchpptsContentFunc()
{ 
   str = "cmd=read&pptId=0";
   url_str = "CoursewarePacket/CoursewarePackets_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchpptsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>课件包名称/课件包备注 ：</th>
               <td><input id="searchpptsNameDesc" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchpptsCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchpptsCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th>最后修改时间 ：</th>
               <td colspan="3">
                  <input id="from13" type="text" name="searchpptsfrom1" class="from" readonly="true"/> ~ <input id="to13" type="text" class="to" name="searchpptsto1" readonly="true"/>
               </td>
            </tr>

            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchppts"><input name="searchpptsButton" class="btn btn-success" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchpptsPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu">
               <span align=right class="btn" OnClick="pptsearchpptsContentFunc();">新增</span>
               <span class="btn pptsexpandSR" OnClick="expandSearchpptsContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="pptName" />
                  <col class="pptDesc" />
                  <col class="Status" />
                  <col class="EditTime" />
                  <col class="pptAction" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>课件包名称</th>
                  <th>课件包备注</th>
                  <th>状态</th>
                  <th>最后修改时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="6" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="pptsearchpptsContentFunc();">新增</span>
               <span class="btn pptsexpandSR" OnClick="expandSearchpptsContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->