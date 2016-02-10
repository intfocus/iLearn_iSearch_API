<script type="text/javascript">
//***Step9 列表中的动作上架/下架Ajax呼叫
function actionSearchCategories(CategoryId, Status)
{
   //ajax
   str = "cmd=actionCategories" + "&CategoryId=" + CategoryId + "&Status=" + Status;
   url_str = "Category/Categories_action.php?";
   
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
               document.getElementsByName("searchCategoriesButton")[0].click();
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
function deleteSearchCategories(CategoryId)
{
   ret = confirm("确定要删除此分类吗?");
   if (!ret)
      return;
   //ajax
   str = "cmd=deleteCategories" + "&" + "CategoryId=" + CategoryId;
   url_str = "Category/Categories_delete.php?";
   
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
         // alert(res);
         $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
         {
            if (!res.match(/^-\d+$/))  //success
            {
               document.getElementsByName("searchCategoriesButton")[0].click();
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
function modifySearchCategories(CategoryId)
{
   str = "cmd=read&CategoryId=" + CategoryId;
   url_str = "Category/Categories_modify.php?";
   window.open(url_str + str);
}

function updateSearchCategorieDepts(CategoryId)
{
   str = "cmd=read&CategoryId=" + CategoryId;
   url_str = "Category/Categories_dept.php?";
   window.open(url_str + str);
}

function clickSearchCategoriesPage(obj, n)  //搜尋換頁
{
   if (obj.className == "search_category_page active")
      return;
   nPage = document.getElementsByName("search_category_page_no")[0].value;
   document.getElementsByName("search_category_page_no")[0].value = n;
   str = "search_category_page_begin_no_" + nPage;
   document.getElementById(str).className = "search_category_page";
   str = "search_category_page_end_no_" + nPage;
   document.getElementById(str).className = "search_category_page";
   str = "search_category_page_begin_no_" + n;
   document.getElementById(str).className = "search_category_page active";
   str = "search_category_page_end_no_" + n;
   document.getElementById(str).className = "search_category_page active"; 
   
   //clear current table
   str = "search_category_page" + nPage;
   document.getElementById(str).style.display = "none";
   str = "search_category_page" + n;
   document.getElementById(str).style.display = "block";
}

//***Step13 新增页面点击保存按钮出发Ajax动作
function newSearchCategoriesContentFunc()
{ 
   str = "cmd=read&CategoryId=0";
   url_str = "Category/Categories_modify.php?";
   window.open(url_str + str);
}

//***Eric 是否可以 删除
function occurTimeDatePicker()
{
   datepicker();
}
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchCategoriesContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <table class="searchField" border="0" cellspacing="0" cellpadding="0">
            <tr>
               <th>分类名称 ：</th>
               <td><input id="searchCategoriesName" type="text" maxlength="50"></td>
               <th>状态 ：</th>
               <td colspan="3">
                  <label><input id="searchCategoriesCheckBox1" type="checkbox" checked> 上架</label>
                  <label><input id="searchCategoriesCheckBox2" type="checkbox" checked> 下架</label>
               </td>
            </tr>
            <tr>
               <th>最后修改时间 ：</th>
               <td>
                  <input id="from5" type="text" name="searchCategoriesfrom1" class="from" readonly="true"/> ~ <input id="to5" type="text" class="to" name="searchCategoriesto1" readonly="true"/>
               </td>
            </tr>
            <tr>
               <th colspan="4" class="submitBtns">
                  <a class="btn_submit_new searchCategories"><input name="searchCategoriesButton" type="button" value="开始查询"></a>
               </th>
            </tr>
         </table>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   
      <!-- ***Step3 表格框架 开始 -->
      <div id="sResultW" class="reportW" style="display:block;">
         <div id="searchCategoriesPages">
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchCategoriesContentFunc();">新增</span>
            </div>
            <table class="report" border="0" cellspacing="0" cellpadding="0">
               <colgroup>
                  <col class="num" />
                  <col class="CategoryName" />
                  <col class="Status" />
                  <col class="EditTime" />
                  <col class="CategoryAction" />
               </colgroup>
               <tr>
                  <th>编号</th>
                  <th>分类名称</th>
                  <th>状态</th>
                  <th>最后修改时间</th>
                  <th>分类夹路径</th>
                  <th>动作</th>
               </tr>
               <tr>
                  <td colspan="6" class="empty">请输入上方查询条件，并点选[开始查询]</td>
               </tr>
            </table>
            <div class="toolMenu">
               <span align=right class="btn" OnClick="newSearchCategoriesContentFunc();">新增</span>
            </div>
         </div>
      </div>
      <!-- search pages-->
   </div>
<!-- ***Step3 表格框架 结束 -->