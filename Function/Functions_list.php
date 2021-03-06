<script type="text/javascript">
//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchFunctions(FunctionId, Status)
{
   //ajax
   str = "cmd=actionFunctions" + "&FunctionId=" + FunctionId + "&Status=" + Status;
   url_str = "Function/Functions_action.php?";
   
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
               document.getElementsByName("searchFunctionsButton")[0].click();
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
function deleteSearchFunctions(FunctionId)
{
   ret = confirm("确定要删除此功能吗?");
   if (!ret)
      return;
   //ajax
   str = "cmd=deleteFunctions" + "&" + "FunctionId=" + FunctionId;
   url_str = "Function/Functions_delete.php?";
   
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
               document.getElementsByName("searchFunctionsButton")[0].click();
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
function modifySearchFunctions(FunctionId)
{
   str = "cmd=read&FunctionId=" + FunctionId;
   url_str = "Function/Functions_modify.php?";
   window.open(url_str + str);
}

function clickSearchFunctionsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_Function_page active")
      return;
   nPage = document.getElementsByName("search_Function_page_no")[0].value;
   document.getElementsByName("search_Function_page_no")[0].value = n;
   str = "search_Function_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_Function_page";
   str = "search_Function_page_end_no_" + nPage;
   document.getElementById(str).className = "search_Function_page";
   str = "search_Function_page_begin_no_" + n;
   document.getElementById(str).className = "search_Function_page active";
   str = "search_Function_page_end_no_" + n;
   document.getElementById(str).className = "search_Function_page active"; 
   
   //clear current table
   str = "search_Function_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_Function_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function newSearchFunctionsContentFunc()
{ 
   str = "cmd=read&FunctionId=0";
   url_str = "Function/Functions_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchFunctionsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>产品/适应症/题库类别名称 ：</th>
               <td><input id="searchFunctionsName" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="4">
                  <label><input id="searchFunctionsRadio1" name="FunA" type="radio" value="" />产品</label>
                  <label><input id="searchFunctionsRadio2" name="FunA" type="radio" value="" />适应症</label>
                  <label><input id="searchFunctionsRadio3" name="FunA" type="radio" value="" />题库类别</label>
                  <label><input id="searchFunctionsRadio4" name="FunA" type="radio" value="" checked />全部</label>
               </td>
            </tr>
            <tr>
               <th>最后修改时间 ：</th>
               <td>
                  <input id="from17" type="text" name="searchFunctionsfrom17" class="from" readonly="true"/> ~ <input id="to17" type="text" class="to" name="searchFunctionsto17" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchFunctions"><input name="searchFunctionsButton" class="btn btn-success" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchFunctionsPages">
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchFunctionsContentFunc();">新增</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="FunctionName" />
                  <col class="FunctionCode" />
                  <col class="EditTime" />
                  <col class="FunctionAction" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>功能名称</th>
                  <th>功能类型</th>
                  <th>最后修改时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="5" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchFunctionsContentFunc();">新增</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->