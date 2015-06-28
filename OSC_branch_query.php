<div class="container branchQueryC" style="display:block;">
   <div class="searchW">
      <form>
         <table class="searchField">
            <tr>
               <th>搜尋時間區間: 
                  <input type=text id="from3" name="from" readonly="true" size="7"/> ~ <input type=text id="to3" name="to" readonly="true" size="7"/>
               </th>
               <th class="uSubmitW"><A class="btn_submit_new branch_query"><input name="branchQueryButton" type="button" value="查詢"></a></th>
            </tr>
         </table>
      </form>

      <div class="uResultW" id="branchQueryPages">
         <table class="report" border="0" cellspacing="0" cellpadding="0">
            <colgroup>
               <col class="cIndex" />
               <col class="cName" />
               <col class="cCompleted" />
               <col class="cWaiting" />
               <col class="cDropped" />
               <col class="cNotyet" />
               <col class="cPercentage" />
            </colgroup>
            <tr>
               <th>序號</th>
               <th>分行部門</th>
               <th>已完成</th>
               <th>清查中</th>
               <th>已逾時</th>
               <th>未實施</th>
               <th>成功率</th>
            </tr>
            <tr>
               <td colspan="7">請設定上方搜尋條件，並點選查詢按鈕</td>
            </tr>
         </table>
      </div>
   </div>
</div>
