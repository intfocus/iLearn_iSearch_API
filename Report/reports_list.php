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
      //var frame = document.getElementById('report_list');
      //frame.setAttribute('height', this.document.body.scrollHeight);
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
	  srcvalue = "http://tsa-china.takeda.com.cn/ibi_apps/WFServlet?IBIAPP_app=ireport&IBIF_ex=" + srcvalue; 
      //alert(srcvalue);
      //alert(srcvalue);
      if(srcvalue != "")
      {
         //var frame = document.getElementById('report_list');
         //frame.setAttribute('src', srcvalue);
		 window.open(srcvalue,'_blank');
      }
   }
</script>

            <div class="searchW">
                <div class="row">
   <form class="form-horizontal" role="form">
      <div class="form-group">
        <label for="srcpaht" class="col-sm-2 control-label">选择报表</label>
		<div class="col-sm-4">
      <select id="srcpaht" class="form-control" onchange="change()">
         <option value="m000">选择报表</option>
         <option value="m005_01">员工信息查询</option>
         <option value="m001_01">考试分数查询</option>
         <option value="m002_01">考卷明细</option>
         <option value="m004_01">考题正确率</option>
         <option value="m006_01">问卷结果查询</option>
         <option value="m003_01">使用日志查询</option>
         <option value="m101_01">iSEARCH用户日志</option>
         <option value="m201_01">iSEARCH文件日志</option>
         <option value="m202_01">iSEARCH推广文件按月统计</option>
         <option value="m203_01">iSEARCH月明细By小时导出</option>
         <option value="m007_01">iLearn学习进度查询</option>
         <option value="m008_01">培训班信息查询</option>
      </select>
	  </div> 
      </div>

   </form>
   
	  </div>
    </div>
    <!-- ***Step2 搜索框的设计 开始 -->
    <div id="sResultW" class="reportW">

    </div>
	<!-- ***Step2 搜索框的设计 结束 -->
	  
   
   <!-- ***Step3 表格框架 结束 -->