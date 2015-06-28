<script type="text/javascript">
//***Step5 expand search Result table
function expandSearchNewsContentFunc()
{
   if ($('span.NewTitle, span.NewMsg').hasClass('fixWidth'))
   {
      $('span.NewTitle, span.NewMsg').removeClass('fixWidth');
      $('.expandSR').text('隐藏过长内容');
   }
   else
   {
      $('span.NewTitle, span.NewMsg').addClass('fixWidth');
      $('.expandSR').text('显示过长内容');
   }
}

//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchNews(NewId, Status)
{
   //ajax
   str = "cmd=actionNews" + "&NewId=" + NewId + "&Status=" + Status;
   url_str = "News_action.php?";
   
   //alert(str);
   $('#loadingWrap').show();
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
               document.getElementsByName("searchNewsButton")[0].click();
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
function deleteSearchNews(NewId)
{
   ret = confirm("确定要删除此公告吗?");
   if (!ret) // user cancels
      return;
   //ajax
   str = "cmd=deleteNews" + "&" + "NewId=" + NewId;
   url_str = "News_delete.php?";
   
   //alert(str);
   $('#loadingWrap').show();
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
               document.getElementsByName("searchNewsButton")[0].click();
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
function modifySearchNews(NewId)
{
   str = "cmd=read&NewId=" + NewId;
   url_str = "News_modify.php?";
   window.open(url_str + str);

   //alert(str);
   // $.ajax
   // ({
      // beforeSend: function()
      // {
         // //alert(str);
      // },
      // type: "GET",
      // url: url_str + str,
      // cache: false,
      // success: function(res)
      // {
         // //alert("Data Saved: " + res);
         // if (res.match(/^-\d+$/))  //failed
         // {
            // alert(MSG_OPEN_CONTENT_ERROR);
         // }
         // else  //success
         // {
            // document.getElementById("searchNewsContent").innerHTML = res;		
            // $('.blockUI').show();
         // }
      // },
      // error: function(xhr)
      // {
         // alert("ajax error: " + xhr.status + " " + xhr.statusText);
      // }
   // });
}

function clickSearchNewsPage(obj, n)  //搜尋換頁
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

//***Step12 修改页面点击保存按钮出发Ajax动作
// function modifyNewsContent(NewId)
// {
   // NewTitle = document.getElementsByName("NewTitleModify")[0].value.trim();
   // NewMsg = document.getElementsByName("NewMsgModify")[0].value.trim();
   // OccurTime = document.getElementsByName("OccurTimeModify")[0].value.trim();
//    
   // if (NewTitle.length == 0 || NewMsg.length == 0)
   // {
      // alert("公告主题及公告内容不可为空白");
      // return;
   // }
//    
   // if (OccurTime.length > 0)
   // {
      // if (OccurTime.length != 10)
      // {
         // alert("日期格式必须为 yyyy-mm-dd");
         // return;
      // }
      // var reg=/2[0-9]{3}-(01|02|03|04|05|06|07|08|09|10|11|12)-(([0-2][1-9])|([1-3][0-1]))/;
      // if (!reg.exec(OccurTime))
      // {
         // alert("日期格式必须为 yyyy-mm-dd " + OccurTime);
         // return;
      // }
   // }
//    
   // str = "cmd=write&NewId=" + NewId + "&NewTitle=" + encodeURIComponent(NewTitle) + 
         // "&NewMsg=" + encodeURIComponent(NewMsg) + "&OccurTime=" + encodeURIComponent(OccurTime);
   // url_str = "News_modify.php?";
// 
   // //alert(str);
   // $.ajax
   // ({
      // beforeSend: function()
      // {
         // //alert(str);
      // },
      // type: "GET",
      // url: url_str + str,
      // cache: false,
      // success: function(res)
      // {
         // //alert("Data Saved: " + res);
         // if (res.match(/^-\d+$/))  //failed
         // {
            // alert(MSG_OPEN_CONTENT_ERROR);
         // }
         // else  //success
         // {
            // hideContent();
            // document.getElementsByName("searchNewsButton")[0].click();
         // }
      // },
      // error: function(xhr)
      // {
         // alert("ajax error: " + xhr.status + " " + xhr.statusText);
      // }
   // });
// }

//***Step13 新增页面点击保存按钮出发Ajax动作
function newSearchNewsContentFunc()
{ 
   str = "cmd=read&NewId=0";
   url_str = "News_modify.php?";
   window.open(url_str + str);

   //alert(str);
   // $.ajax
   // ({
      // beforeSend: function()
      // {
         // //alert(str);
      // },
      // type: "GET",
      // url: url_str + str,
      // cache: false,
      // success: function(res)
      // {
         // //alert("Data Saved: " + res);
         // if (res.match(/^-\d+$/))  //failed
         // {
            // alert(MSG_OPEN_CONTENT_ERROR);
         // }
         // else  //success
         // {
            // document.getElementById("searchNewsContent").innerHTML = res;		
            // $('.blockUI').show();
         // }
      // },
      // error: function(xhr)
      // {
         // alert("ajax error: " + xhr.status + " " + xhr.statusText);
      // }
   // });
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchNewsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>标题/信息搜索 ：</th>
               <td><input id="searchNewsTitleMsg" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchNewsCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchNewsCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th>最后修改时间 ：</th>
               <td colspan="3">
                  <input id="from1" type="text" name="searchNewsfrom1" class="from" readonly="true"/> ~ <input id="to1" type="text" class="to" name="searchNewsto1" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th>发生时间 ：</th>
               <td colspan="3">
                  <input id="from2" type="text" name="searchNewsfrom2" class="from" readonly="true"/> ~ <input id="to2" type="text" class="to" name="searchNewsto2" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchNews"><input name="searchNewsButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchNewsPages">
            <!-- <div id="sResultTitle" class="sResultTitle">查詢結果 : 共有 <span>256</span> 筆檔案符合查詢條件</div> -->
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchNewsContentFunc();">新增</span>
               <span class="btn expandSR" OnClick="expandSearchNewsContentFunc();">显示过长内容</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="NewTitle" />
                  <col class="NewMsg" />
                  <col class="Status" />
                  <col class="OccurTime" />
                  <col class="EditTime" />
                  <col class="action" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>主题</th>
                  <th>内容</th>
                  <th>状态</th>
                  <th>发生时间</th>
                  <th>最后修改时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="7" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchNewsContentFunc();">新增</span>
               <span class="btn expandSR" OnClick="expandSearchNewsContentFunc();">显示过长内容</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->