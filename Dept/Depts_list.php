<script type="text/javascript">
//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchDepts(DeptId, Status)
{
   //ajax
   str = "cmd=actionDepts" + "&DeptId=" + DeptId + "&Status=" + Status;
   url_str = "Dept/Depts_action.php?";
   
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
               document.getElementsByName("searchDeptsButton")[0].click();
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
function deleteSearchDepts(DeptId)
{
   ret = confirm("确定要删除此部门吗?");
   if (!ret)
      return;
   //ajax
   str = "cmd=deleteDepts" + "&" + "DeptId=" + DeptId;
   url_str = "Dept/Depts_delete.php?";
   
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
               document.getElementsByName("searchDeptsButton")[0].click();
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
function modifySearchDepts(DeptId)
{
   str = "cmd=read&DeptId=" + DeptId;
   url_str = "Dept/Depts_modify.php?";
   window.open(url_str + str);
}

function clickSearchDeptsPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_dept_page active")
      return;
   nPage = document.getElementsByName("search_dept_page_no")[0].value;
   document.getElementsByName("search_dept_page_no")[0].value = n;
   str = "search_dept_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_dept_page";
   str = "search_dept_page_end_no_" + nPage;
   document.getElementById(str).className = "search_dept_page";
   str = "search_dept_page_begin_no_" + n;
   document.getElementById(str).className = "search_dept_page active";
   str = "search_dept_page_end_no_" + n;
   document.getElementById(str).className = "search_dept_page active"; 
   
   //clear current table
   str = "search_dept_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_dept_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function newSearchDeptsContentFunc()
{ 
   str = "cmd=read&DeptId=0";
   url_str = "Dept/Depts_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchDeptsContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <!--<form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>部门名称/部门编号 ：</th>
               <td><input id="searchDeptsNameCode" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchDeptsCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchDeptsCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th>最后修改时间 ：</th>
               <td>
                  <input id="from4" type="text" name="searchDeptsfrom1" class="from" readonly="true"/> ~ <input id="to4" type="text" class="to" name="searchDeptsto1" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchDepts"><input name="searchDeptsButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>-->
		<form class="cmxform form-horizontal tasi-form searchField">
			 <div class="form-group">
				<label for="cname" class="control-label col-md-2">部门名称/部门编号:</label>
				<div class="col-md-5">
					<input class="form-control" id="searchDeptsNameCode" type="text" maxlength="50">
				</div>
				<label for="cname" class="control-label col-md-1">状态:</label>
				<div class="col-md-2" style="padding-top:6px;">
					<label class="cr-styled">
						<input id="searchDeptsCheckBox1" type="checkbox" checked>
						<i class="fa"></i> 
						上架
					</label>
					<label class="cr-styled">
						<input id="searchDeptsCheckBox2" type="checkbox" checked>
						<i class="fa"></i> 
						下架
					</label>
				</div>
			</div>
			<div class="form-group ">
				<label for="cemail" class="control-label col-md-2">最后修改时间：</label>
				<div class="col-md-5">
					<input id="from4" type="text" style="width:46%; display:inline-block" name="searchDeptsfrom1" class="from form-control" readonly="true"/> ~ <input id="to4" type="text" style="width:46%; display:inline-block" class="to form-control" name="searchDeptsto1" readonly="true"/>
				</div>
			</div>
			  <div class="form-group">
				<label class="control-label col-md-2">　</label>
				<div class="col-md-5">
					<a class="btn_submit_new searchDepts"><input class="btn btn-success" name="searchDeptsButton" type="button" value="开始查询"></a>
				</div>
			</div>
		</form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchDeptsPages">
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchDeptsContentFunc();">新增</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="DeptName" />
                  <col class="DeptCode" />
                  <col class="Status" />
                  <col class="EditTime" />
                  <col class="action" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>部门名称</th>
                  <th>部门编号</th>
                  <th>状态</th>
                  <th>最后修改时间</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="6" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchDeptsContentFunc();">新增</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->