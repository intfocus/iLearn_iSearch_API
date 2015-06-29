<div class="container whiteListC" style="display:none;">
   <div class="searchW">
      <table class="sysInfo" border="0" cellspacing="0" cellpadding="0">
<?php
   if ($systemAdminFlag == 1)
      echo "<tr><th>(身份為系統管理者，此設定會套用到所有單位)</th></tr>";
   if ($entieFlag == 1)
      echo "<tr><th>(分行管理者無法修改白名單)</th></tr>";
?>
         <tr>
            <th>掃描時需要跳過的個資，例如單位本身的電話地址，每一行代表一筆個資</th>
         </tr>
      </table>
      <div class="toolMenu">
<?php
   if ($entieFlag != 1) { //安泰銀行客製, 分行管理者不能修改白名單
?>
         <a class="btn_submit_new modify_whitelist"><input name="modifyWhiteListButton" type="button" value="設定完成"></a>
<?php
   }
?>
         <div id="whiteList" class="newReport" st:qyle="display:none;">
            <form name="formWhiteList">
            <Textarea name="whiteListContent" cols=80 rows=30 <?php if ($entieFlag == 1) echo "disabled"?>><?php
      /////////////////////////////
      // Read from whitelist.txt as default
      /////////////////////////////
      $guid_dir_path = '/usr/local/www/apache22/data/upload_old' . "/$GUID";
      if (!file_exists($guid_dir_path)) {
         system("mkdir -p -m 0774 $guid_dir_path");
      }
      $systemScanDirPath = $guid_dir_path . "/whitelist.txt";
      if (file_exists($systemScanDirPath)) {
         $fp = fopen($systemScanDirPath,"r");
         if ($fp) {
            while(!feof($fp)) {
               $buf = fgets($fp);
               echo $buf;
            }
            fclose($fp);
         }
      }
               ?></Textarea>
            </form>
         </div>
<?php
   if ($entieFlag != 1) { //安泰銀行客製, 分行管理者不能修改白名單
?>
         <a class="btn_submit_new modify_whitelist"><input name="modifyWhiteListButton" type="button" value="設定完成"></a>
<?php
   }
?>
      </div>
   </div>
</div>
