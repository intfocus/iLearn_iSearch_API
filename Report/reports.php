<script type="text/javascript">
   function iFrameHeight() {
      // var ifm= document.getElementById("report_list");
      // var subWeb = document.frames ? document.frames["report_list"].document : ifm.contentDocument;
      // if(ifm != null && subWeb != null) {
         // ifm.height = subWeb.body.scrollHeight;
      // }
      //alert("---1---");
      //alert(this.document.body.scrollHeight); //弹出当前页面的高度  
      //alert(this.document.body.scrollWidth);
      var frame = document.getElementById('report_list');
      frame.setAttribute('height', this.document.body.scrollHeight);
      // var frame = document.getElementById('report_list');
      // var win = frame.contentWindow;
      // var doc = win.document;
      //alert("---2---");
      //alert(body.scrollHeight);
   }
   function change()
   {
      var myselect = document.getElementById("srcpaht");
      var index = myselect.selectedIndex;//获取下拉框中
      //alert(index);
      var srcvalue = myselect.options[index].value;//获取下拉框中的value
	  srcvalue = "/ibi_apps/WFServlet?IBIAPP_app=ireport&IBIF_ex=" + srcvalue; 
      //alert(srcvalue);
      //alert(srcvalue);
      if(srcvalue != "")
      {
         var frame = document.getElementById('report_list');
         frame.setAttribute('src', srcvalue);
      }
   }
</script>

<!--新增修改所跳出的 block 开始-->
<div id="searchUsersContent" class="blockUI" style="display:none;">
</div>
<!--新增修改所跳出的 block 结束--> 

<!--快速查詢 從這裡開始-->
   <div class="searchW">
   
   <form class="form-horizontal" role="form">
      <div class="form-group">
        <label for="srcpaht" class="col-sm-2 control-label">选择报表</label>
		<div class="col-sm-4">
      <select id="srcpaht" class="form-control" onchange="change()">
         <option value="m000">选择报表</option>
         <option value="m001_01">考试分数查询</option>
         <option value="m002_01">查询</option>
         <option value="m003_01">查询</option>
         <option value="m004_01">查询</option>
         <option value="m005_01">查询</option>
         <option value="m006_01">查询</option>
      </select>
	  </div>
      </div>

   </form>

	  </div>

   
    <!-- ***Step2 搜索框的设计 开始 -->
    <div id="sResultW" class="reportW" style="display:block;">
	  <form>
         <iframe id="report_list" src="/approot/ireport/blank_01.html" width="100%" height="100%" frameborder="0"  onLoad="iFrameHeight()"></iframe>
      </form>
    </div>
	<!-- ***Step2 搜索框的设计 结束 -->
	  
   
   <!-- ***Step3 表格框架 结束 -->