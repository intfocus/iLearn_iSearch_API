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
      <select id="srcpaht" onchange="change()">
         <option value="http://baidu.com">baidu</option>
         <option value="http://bing.com">bing</option>
      </select>
   <!-- ***Step2 搜索框的设计 开始 -->
      <form>
         <iframe id="report_list" src="http://baidu.com" width="100%" height="100%" frameborder="0"  onLoad="iFrameHeight()"></iframe>
      </form>
      <!-- ***Step2 搜索框的设计 结束 -->
   </div>
<!-- ***Step3 表格框架 结束 -->