<?php
/////////////////////////////
// 20130624, created by Odie
//    Use to check and the value of the config file
/////////////////////////////
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<title>Openfind P-Marker</title>
<script type="text/javascript" src="lib/jquery.min.js"></script>
<script type="text/javascript">

function check(){
   var guid = $('input[name="guid"]').val();
   var arg = {};
   arg["cmd"] = "show";
   arg["guid"] = guid;
   $.post("processConfig.php", arg, function(data){
      if(!data.match(/^-\d+$/)){  //success
         $('.guidInput').hide();
         $('.configResult').empty().append(data);
      }
      else{
         if(data == "-2")
            alert("GUID輸入錯誤!");
      }
   });
}

function modify(){
   var guid = $('input[name="guid"]').val();
   var form_pair = document.forms["conf"].getElementsByTagName("input");
   var conf_pair = {};
   var key = "";
   var value = "";
   var count = 0;
   for(var i = 0; i < form_pair.length; i++){
      key = form_pair[i].name;
      val = form_pair[i].value;
      if(key != "modifybutton" && key != "hidden" && val != ""){
         if(val.length > 1){
            alert("輸入值過長，只能是一位數!");
            return;
         }
         else{
            conf_pair[key] = val;
            count = count + 1;
         }
      }
   }
   if(count == 0){
      alert("沒有輸入任何值!");
      return;
   }
   var arg = {};
   arg["cmd"] = "modify";
   arg["guid"] = guid;
   arg["conf"] = conf_pair;
   $.post("processConfig.php", arg, function(data){
      if(!data.match(/^-\d+$/)){  //success
         alert("修改成功");
         $('.configResult').empty().append(data);
      }
      else alert("修改失敗");
   });
}  
   
</script>
<style type="text/css">
table {
   border-collapse:collapse;
}
table, tr, td {
   
   border: 1px solid black;
}
</style>
</head>
<body>
<div class="guidInput">
請輸入GUID：<input type="text" name="guid" size="48" maxlength="36"><br/>
<input type="button" value="確認" name="checkbutton" onClick="check();"><br />
</div>
<div class="configResult">
</div>
</body>
</html>
