/*
PMarkFunction.js

1.functions of OSC_index.php
2.AJAX to php
*/

/////////////////////////////////////
// Modification history
// #001 by Odie 2013/02/05 ~ 02/06
//    1. 於"用戶管理"頁面新增"依部門統計電腦清查狀況"的功能，並加入相對應的按鈕功能
//    2  "搜尋時間"的"結束日期"原本無法選擇，現在加上一個datepicker可讓使用者自己選擇
// #002 by Odie 2013/04/02
//    1. OSC_index.php 新增"搜尋盤點紀錄"功能，加入相對應的功能
//
// #003 by Phantom+Odie 2013/05/20
//    1. Add netDisk and removableDisk options
//
// #004 by Odie 2013/06/17
//    1. Fix Bug 35088, "新增部門" 直接按enter會submit，不會新增
//       => 將該input field用jquery加上按enter會不動作的判斷
// 
// #005 by Odie 2013/07/03
//    1. OSC_index.php 新增紀錄盤點失敗檔案清單的checkbox，
//       於'.btn_submit_new.search' function 中加上對應的code
//
// #006 by Odie 2013/08/23
//    1. 新增"盤點模式設定"功能，加入相對應的功能
//
// #007 by Odie 2013/09/06
//    1. 加入第八種個資類型
//
// #008 by Odie 2013/09/11
//    1. 製作一份報表後要再重新製作另一份，日期選擇會有問題，因為忘記把datepicker的日期清除，
//       導致選擇日期的欄位雖然是空白，但是還是存著先前的日期設定，使得"開始"日期會受先前"結束"
//       的日期影響。已經加入在製作報表後會將datepicker保留的值清除的動作。
//
// #009 by Odie 2013/09/25
//    1. 解決檔名中含有連續空白時，在web搜尋之後點選"觀看內容"會出現"open content error"的問題。
//
// TODO: D_EXTREME_TYPE_NUM 目前必須手動調整，如果有7項個資，要改成7，如果顯示8項個資，要改成8
//       如果設定頁面沒有顯示第8種個資，而這裡是8的話，會有問題，會因為reference不到第8項而出現javascript error
//       （設定頁面的第8種個資由資料庫控制）
// 
// #010 by Odie 2014/08/04
//    1. 經濟部客製，下載用戶掃描狀態（用戶管理->人員列表）
//
// #011 by Phantom 2014/09/03
//    1. Add systemScanDirEnabled and systemScanDirContent
//
// #012 by Phantom 2014/09/10
//    1. 白名單
/////////////////////////////////////
contentType="text/html; charset=utf-8";

//msg
var MSG_NO_NAME = "未輸入名稱";
var MSG_REPORT_NAME_OVERLIMIT = "名稱字數超過100";
var MSG_DEPART_NAME_OVERLIMIT = "名稱字數超過50";
var MSG_USER_NAME_OVERLIMIT = "名稱字數超過128";
var MSG_NO_IDENTITY = "未選擇掃描個資類型";
var MSG_NO_RANGE_BEGIN = "未選擇開始時間";
var MSG_NO_RANGE_END = "未選擇結束時間";
var MSG_MAX_REPORT = "報表數量達到上限";
var MSG_MAX_DEPART = "部門數量達到上限";
var MSG_EXTREME_UNDER_LIMIT1 = "風險個資類型低於兩種";
var MSG_EXTREME_UNDER_LIMIT2 = "個資類型少於判斷值";
var MSG_NUMBER_OVER_LIMIT = "數量不能超過10000";
var MSG_NUMBER_ILLEGAL = "欄位中含有非法字元";
var MSG_EXTREME_NUMBER_UNDER_LIMIT = "極高度風險檔案數低於1筆";
var MSG_HIGH_NUMBER_ILLEGAL = "高度風險檔案數不能小於低度風險加3";
var MSG_HIGH_NUMBER_UNDER_LIMIT = "高度風險檔案數低於20筆";
var MSG_LOW_NUMBER_UNDER_LIMIT = "低度風險檔案數低於5筆";
var MSG_SCANTIME_ERROR = "盤點模式之快速模式盤點時間選項錯誤";
var MSG_NO_EXTREME_NUM = "欄位未填";
var MSG_SET_RISK = "系統設定完成";
var MSG_SAME_NAME = "已有同名部門";
var MSG_SAME_USER_NAME = "已有同名使用者";
var MSG_USER_PASSWORD_LENGTH_ERROR = "密碼長度應為6~12字元";
var MSG_ADMIN_PASSWORD_LENGTH_ERROR = "密碼長度應為8~30字元";
var MSG_PASSWORD_ILLEGAL = "密碼含有非法字元";
var MSG_PASSWORD_CONFIRM_ERROR = "確認密碼不一致";
var MSG_PASSWORD_ILLEGAL_FORMAT = "密碼至少要有一個英文字母及一個數字";
var MSG_CHANGE_PASSWORD_SUCCESS = "變更密碼成功";
var MSG_NO_SEARCH_CHECKBOX = "未選擇風險等級";
var MSG_DELETE_ERROR = "delete error";
var MSG_CREATE_ERROR = "create error:";
var MSG_EDIT_ERROR = "edit error";
var MSG_TIMEOUT = "session expired 請重新登入";
var MSG_CHECK_TIMEOUT_ERROR = "ckeckTimeout error";
var MSG_SET_DEFAULT_EXTREME_ERROR = "setDefaultExtreme error";
var MSG_GEN_REPORT_CHART_ERROR = "genReportChart error:";
var MSG_CHECK_IMG_ERROR = "checkImg error";
var MSG_CHANGE_PASSWORD_ERROR = "change pw error";
var MSG_SEARCH_ERROR = "search error";
var MSG_OPEN_CONTENT_ERROR = "open content error";
var MSG_WHITELIST_SUCCESS = "白名單設定成功";
var MSG_WHITELIST_ERROR = "白名單設定失敗";

//data
var D_CMD1 = "new_report";
var D_CMD2 = "delete_report";
var D_CMD3 = "create_dep";
var D_CMD4 = "edit_dep";
var D_CMD5 = "delete_dep";
var D_CMD6 = "check_timeout";
var D_CMD7 = "user";
var D_CMD8 = "admin";
var D_CMD9 = "open_content";
var D_CMD10 = "userLogin";
var D_TIMEOUT = 60000;                         //60秒檢查一次session
var D_LOADING = 2000;                          //loading圖案跑2秒
var D_MAX_REPORT = 1000;                       //報表數量上限
//var D_MAX_DEPART = 100;                      //部門數量上限
var D_REPORT_NAME_LENGTH = 100;                //報表名稱長度
var D_DEPART_NAME_LENGTH = 10;                 //部門名稱長度
var D_USER_NAME_LENGTH = 128;                  //使用者名稱長度
var D_EXTREME_TYPE_NUM = 8;                    //個資類型數目   #007
var D_EXTREME_TYPE_LIMIT = 2;                  //個資類型數目限制
var D_EXTREME_NUM_LEN = 4;                     //風險設定檔案數目字串長度
var D_EXTREME_NUM_SIZE = 1;                    //極高風險設定檔案數目限制
var D_HIGH_NUM_SIZE = 20;                      //高風險設定檔案數目限制
var D_LOW_NUM_SIZE= 5;                         //低風險設定檔案數目限制
var D_HIGH_NUM_SIZE_LIMIT = 3;                 //高 - 低 >= 3
var D_PHONE_CHECKBOX = 3;
var D_USER_PASSWORD_LENGTH_LIMIT1 = 6;
var D_USER_PASSWORD_LENGTH_LIMIT2 = 12;
var D_ADMIN_PASSWORD_LENGTH_LIMIT1 = 8;
var D_ADMIN_PASSWORD_LENGTH_LIMIT2 = 30;
var D_SEARCH_EXTREME = 1;
var D_SEARCH_HIGH = 2;
var D_SEARCH_MEDIUM = 4;
var D_ERROR_TIMEOUT = -1;
var D_ERROR_FILE_ERROR = -2;
var D_ERROR_SAME_NAME = -5;                    //有同名部門
var D_ERROR_PASSWORD = -7;                     //PASSWORD 長度錯誤
var g_data_name1 = "cmd=";
//var g_data_name2 = "GUID=";
//var D_GUID = "8f44a8ab_5c6c_6232_cd4f_642761007428";
var g_data_name3 = "report_name=";
var g_data_name4 = "identity_type=";
var g_data_name5 = "range_begin=";
var g_data_name6 = "range_end=";
var g_data_name7 = "reportID=";
var g_data_name8 = "departName=";
var g_data_name9 = "departID=";
var g_data_name10 = "oldpass=";
var g_data_name11 = "newpass1=";
var g_data_name12 = "newpass2=";
var g_data_name13 = "riskCategorySelect=";
var g_checkbox_str = "";
var g_mapping = [1, 6, 3, 5, 4, 0, 2, 7];      //項目欄位對應  #007
var g_edit_depart_id;                          //要修改的部門id
var g_edit_depart_name;                        //要修改的部門名稱
var g_json;

var D_URL1 = "deleteReportID.php?";
var D_URL2 = "createReport.php?";
var D_URL3 = "genReportChart.php?";
var D_URL4 = "setDefaultExtreme.php?";
var D_URL5 = "createDepartment.php?";
var D_URL6 = "editDepartment.php?";
var D_URL7 = "deleteDepartment.php?";
var D_URL8 = "checkTimeout.php?";
//var D_URL9 = "ajaxTimeout.php?";
var D_URL10 = "showHtml.php?";
var D_URL11 = "modifyPassword.php?";
var D_URL12 = "searchIdentityFile.php?";
var D_URL13 = "openSearchContent.php?";
var D_URL14 = "searchUserMgmt.php?";
var D_URL15 = "deleteXML.php?";
var D_URL16 = "searchUserDep.php?";
// #002 add
var D_URL17 = "searchScanHistory.php?";
var D_URL18 = "searchPersonalScan.php?";
var D_URL19 = "modifyUserPassword.php?";
var D_URL20 = "modifyWhiteList.php?"; //#012
var D_URL_POST_IMAGE = "save_img.pl?";

var data_1;
var data_2;
var data_3;
var data_4;
var data_5;
var data_6;
var data_7;

//set checkbox color and call sessionTimeout()
function loaded()
{
   for (i = 0; i < D_EXTREME_TYPE_NUM; i++)
   {
      str_checkboxid = "checkbox_" + i;
      if (document.getElementById(str_checkboxid) != undefined)
      {
         if (g_defaultExtremeType[i] == 1)
            document.getElementById(str_checkboxid).style.color = "red";
         else
            document.getElementById(str_checkboxid).style.color = "black";
      }
   }
   var timeout = window.setInterval(sessionTimeout, D_TIMEOUT);
}

//check session to keep alive
function sessionTimeout()
{
   //AJAX
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "GET",
      url: D_URL8,
      cache: false,
      success: function(res)
      {
         //alert(res);
         if (res.match(/^-\d+$/))  //error
         {
            if (res == D_ERROR_TIMEOUT)  //timeout
            {
               alert(MSG_TIMEOUT);
               window.location.replace("main.php");
            }
            else
            {
               alert(MSG_CHECK_TIMEOUT_ERROR);
            }
         }
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });
}

//expand content
function expandContentFunc()
{
   if ($('span.rName, span.rItem').hasClass('fixWidth'))
   {
      $('span.rName, span.rItem').removeClass('fixWidth');
      $('.expandR').text('隱藏過長內文');
   }
   else
   {
      $('span.rName, span.rItem').addClass('fixWidth');
      $('.expandR').text('顯示過長內文');
   }
}

//showTypeDis, hideTypeDis
function showTypeDis()
{
   $('.typeDes').show();
}
function hideTypeDis()
{
   $('.typeDes').hide();
}

//觀看內容
function openContent(n, xmlID,total)
{
   //20120409 Billy begin
   //var type_found = document.getElementById("content_type_found" + n).innerText;
   var type_found;   
   
   if (document.getElementById("content_type_found" + n).innerText)  //ie, chrome
   {
      type_found = document.getElementById("content_type_found" + n).innerText;
   }
   else  //firefox
   {
      type_found = document.getElementById("content_type_found" + n).textContent;
   }

   /////////////////////
   // yaoan 20120511 add
   /////////////////////
   if (document.getElementById("content_last_modify" + n).innerText)  //ie, chrome
   {
      last_modify = document.getElementById("content_last_modify" + n).innerText;
   }
   else  //firefox
   {
      last_modify = document.getElementById("content_last_modify" + n).textContent;
   }

   if (document.getElementById("content_filepath" + n).innerText)  //ie, chrome
   {
      filepath = document.getElementById("content_filepath" + n).innerText;
   }
   else  //firefox
   {
      filepath = document.getElementById("content_filepath" + n).textContent;
   }

   // #009, replace the non-breaking space ("&nbsp;" for HTML entity; \xA0 (160) for char code) to space (\x20 (32))
   filepath = filepath.replace(/\xA0/g, " ");
   // the following two lines also work
   // var regex = new RegExp(String.fromCharCode(160), "g");
   // filepath = filepath.replace(regex, " ");
   
   if (document.getElementById("content_filetype" + n).innerText)  //ie, chrome
   {
      filetype = document.getElementById("content_filetype" + n).innerText;
   }
   else  //firefox
   {
      filetype = document.getElementById("content_filetype" + n).textContent;
   }


   /////////////////
   // yaoan end add
   ////////////////

   //20120409 Billy end
   str = g_data_name1 + D_CMD9 + "&" + "xmlID=" + xmlID + "&" + "total=" + total + "&" + "type_found=" + encodeURIComponent(type_found);

   // 20120511 yaoan add
   str = str + "&last_modify=" + last_modify + "&filepath=" + encodeURIComponent(filepath) + "&filetype=" + filetype;
   // end add

   //alert(str);
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: "GET",
      url: D_URL13 + str,
      cache: false,
      success: function(res)
      {
         //alert("Data Saved: " + res);
         if (res.match(/^-\d+$/))  //failed
         {
            alert(MSG_OPEN_CONTENT_ERROR);
         }
         else  //success
         {
            document.getElementById("searchContent").innerHTML = res;		
            $('.blockUI').show();
         }
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });
}
function hideContent()
{
   $('.blockUI').hide();
}

//create new report
function newReportFunc()
{
   $('#newReport').show();
   $('#reportW').hide();
}

//create new depart
function newDepartFunc()
{
   $('#newDepart').show();
   $('#departW').hide();
}

//create new depart
function newUserFunc()
{
   $('#newUser').show();
   $('#userW').hide();
}

//edit depart
function editDepartFunc(n, name)
{
   g_edit_depart_id = n;
   g_edit_depart_name = name;
   var str = n + "_dep";
   //20120409 Billy begin
   //var dep_name = document.getElementById(str).innerText;
   var dep_name;   
   if (document.getElementById(str).innerText)  //ie, chrome
   {
      dep_name = document.getElementById(str).innerText;
   }
   else  //firefox
   {
      dep_name = document.getElementById(str).textContent;
   }
   //20120409 Billy end
   document.getElementById("editDepartName").value = dep_name;
   $('#editDepart').show();
   $('#departW').hide();
}

function editUserFunc(n, login_name, dept_list)
{
   if(dept_list.length > 0)
   {
      var arr_dept = dept_list.match(/'(.*?)'/g);
      var str = "";
      var checkStr = "";
      var i = 0;
      var c = document.getElementsByName("editUserDepartment");
      // first clear all the checkbox
      for(i = 0; i < c.length; i++)
      {
         c[i].checked = false;
      }
      // check the chosen department
      for(i = 0; i < arr_dept.length; i++)
      {
         str = arr_dept[i].match(/'(.*?)'/);
         checkStr = "editUser_" + str[1];
         document.getElementById(checkStr).checked = true;
      }
   }
   document.getElementById("editUserName").value = login_name;
   document.getElementById("editUserNameShow").innerHTML = login_name;
   $('#editUser').show();
   $('#userW').hide();
}

//check report name length
function checkReportNameLength(ctlid,maxlength)
{              
   if ($("#"+ctlid).val().length > maxlength)
   {
      $('#reportNameHint').show();
   }
   else
   { 
      $('#reportNameHint').hide();
   }
}

//check new department name length
function checkNewDepartNameLength(ctlid,maxlength)
{              
   if ($("#"+ctlid).val().length > maxlength)
   {
      $('#newDepartNameHint').show();
   }
   else
   { 
      $('#newDepartNameHint').hide();
   }
}

//check edit department name length
function checkEditDepartNameLength(ctlid,maxlength)
{              
   if ($("#"+ctlid).val().length > maxlength)
   {
      $('#editDepartNameHint').show();
   }
   else
   { 
      $('#editDepartNameHint').hide();
   }
}

//delete report 
function deleteReport(obj)
{
   var answer = confirm('確定要刪除?');
   if (answer)
   {
      temp = obj.id;
      str = g_data_name1 + D_CMD2 + "&" + g_data_name7 + parseInt(temp, 10);
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: "GET",
         url: D_URL1 + str,
         cache: false,
         success: function(res)
         {
            //alert("Data Saved: " + res);
            if (res.match(/^-\d+$/))  //failed               
               alert(MSG_DELETE_ERROR);
            else  //success
               document.getElementById("refreshPages").innerHTML = res;
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   }
}

//delete depart
function deleteDepart(obj, n, name)
{
   var answer = confirm('確定要刪除?');
   if (answer)
   {
      temp = obj.id;
      //str = g_data_name1 + D_CMD5 + "&" + g_data_name9 + parseInt(temp, 10);
      str = g_data_name1 + D_CMD5 + "&" + g_data_name9 + n + "&oldDepartName=" + encodeURIComponent(name);
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: "GET",
         url: D_URL7 + str,
         cache: false,
         success: function(res)
         {
            //alert("Data Saved: " + res);
            if (res.match(/^-\d+$/))  //failed               
               alert(MSG_DELETE_ERROR);
            else  //success
            {
               refreshUser();
               refreshDepartCheckbox();
               document.getElementById("refreshDepartPages").innerHTML = res;
            }
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   }
}

//delete user 
function deleteUser(obj, n)
{
   var answer = confirm('確定要刪除?');
   if (answer)
   {
      temp = obj.id;
      //str = g_data_name1 + D_CMD5 + "&" + g_data_name9 + parseInt(temp, 10);
      str = "cmd=delete_user&login_name=" + encodeURIComponent(n);
      //alert(str);
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: "GET",
         url: "deleteUser.php?" + str,
         cache: false,
         success: function(res)
         {
            //alert("Data Saved: " + res);
            if (res.match(/^-\d+$/))  //failed               
               alert(MSG_DELETE_ERROR);
            else  //success
            {
               document.getElementById("refreshUserPages").innerHTML = res;
            }
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   }
}

function refreshUser()
{   
   $.ajax
   ({
      beforeSend: function()
      {
          //alert(str);
      },
      type: 'GET',
      url: "refreshUser.php?cmd=refresh_user",
      cache: false,
      success: function(res)
      {
         //alert(res);
         if (!res.match(/^-\d+$/))  //success
         {             
            document.getElementById("refreshUserPages").innerHTML = res;
            document.formNewUser.reset();
            $('#editUser').hide();              
            $('#userW').show();              
         }
         else  //failed
         {
            alert(MSG_EDIT_ERROR); 
         }   
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });  
}

function refreshDepartCheckbox()
{   
   $.ajax
   ({
      beforeSend: function()
      {
          //alert(str);
      },
      type: 'GET',
      url: "refreshDepart.php?cmd=refresh_checkbox",
      cache: false,
      success: function(res)
      {
         if (!res.match(/^-\d+$/))  //success
         {             
            document.getElementById("departCheckbox").innerHTML = res;
            document.formNewUser.reset();
            $('#editUser').hide();              
            $('#userW').show();              
         }
         else  //failed
         {
            alert(MSG_EDIT_ERROR); 
         }   
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });  
}

//open report
function openReport(r_id)
{
   var fileStr = D_URL10 + g_data_name7 + r_id;
   window.open(fileStr);
}


// #010
function downloadStatus()
{
   var statusStr = document.getElementsByName("status")[0].value;
   $('#downloadStatus').submit();
   /*
   $.ajax
   ({
         beforeSend: function()
         {
            alert(statusStr);
         },
         type: "GET"
         url: "deleteUser.php?" + str,
         cache: false,
         success: function(res)
         {
            //alert("Data Saved: " + res);
            if (res.match(/^-\d+$/))  //failed               
               alert(MSG_DELETE_ERROR);
            else  //success
            {
               document.getElementById("refreshUserPages").innerHTML = res;
            }
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
   });
   */
   //alert(statusStr);
   //$.post("downloadStatus.php", statusStr, function(data){alert(data);});
}

function newBranchFunc()
{
   $('#newBranch').show();
   $('#branchW').hide();
}

$(function()
{
   // #004 begin
   $('#newDepartName, #editDepartName').keypress(function(e){
      if(e.which == 13){return false;}
   });
   // #004 end
   
   //report vHigh and high color
   $('.vHighW, .highW').each(function()
   {
      if ($(this).text() == "0")
      {
         $(this).css('color','#444');
      }
   });	
	
   //check report name length
   $("#reportName").keyup(function()
   {
      checkReportNameLength("reportName", D_REPORT_NAME_LENGTH);
   });
   $("#reportName").live('blur',function()
   {
      checkReportNameLength("reportName", D_REPORT_NAME_LENGTH);
   });
   
   //check new department name length
   $("#newDepartName").keyup(function()
   {
      checkNewDepartNameLength("newDepartName", D_DEPART_NAME_LENGTH);
   });
   $("#newDepartName").live('blur',function()
   {
      checkNewDepartNameLength("newDepartName", D_DEPART_NAME_LENGTH);
   });
   
   //check edit department name length
   $("#editDepartName").keyup(function()
   {
      checkEditDepartNameLength("editDepartName", D_DEPART_NAME_LENGTH);
   });
   $("#editDepartName").live('blur',function()
   {
      checkEditDepartNameLength("editDepartName", D_DEPART_NAME_LENGTH);
   });
	
   //trim
   String.prototype.trim = function()
   {
      return this.replace(/(^[\s]*)|([\s]*$)/g, "");
   }

   //switch mainTab 
   $('.mainTabW li').click(function()
   {
      $(this).addClass('active').siblings('li.active').removeClass('active');
      var cur = $(this).index();
      $('.container2').eq(cur).show().siblings().hide();
   });
   
   //Billy 2012/2/1
   //cancel from newReportFunc()
   $('.btn_submit_new.report_cancel, .link').click(function()
   {
      document.formNewReport.reset();
      $('#reportNameHint').hide();
      $('#newReport').hide();
      $('#reportW').show();
      $( "#from, #to" ).datepicker("option", "maxDate", new Date());
      $( "#from, #to" ).datepicker("option", "minDate", null);
   });
   
   //cancel from newDepartFunc()
   $('.settingW .btn_submit_new.new_depart_cancel').click(function() 
   {
      document.formNewDepart.reset();
      $('#newDepartNameHint').hide();
      $('#newDepart').hide();
      $('#departW').show();
   });
   
   //cancel from newUserFunc()
   $('.settingW .btn_submit_new.new_user_cancel').live("click", function() 
   {
      document.formNewUser.reset();
      $('#newUserNameHint').hide();
      $('#newUser').hide();
      $('#userW').show();
   });
   
   //cancel from editDepartFunc()
   $('.settingW .btn_submit_new.edit_depart_cancel').click(function() 
   {
      document.formEditDepart.reset();
      $('#editDepartNameHint').hide();
      $('#editDepart').hide();
      $('#departW').show();
   });

   //cancel from editUserFunc()
   //$('.submitW .btn_submit_new.edit_user_cancel').click(function() 
   $('.submitW .btn_submit_new.edit_user_cancel').live("click", function() 
   {
      document.formEditUser.reset();
      $('#editUser').hide();
      $('#userW').show();
   });

   //new user button
   $('.settingW #addNewUser').click(function()
   {
      $("#dialog-addUser").dialog({
         resizeable: false
      })
   });
   
   //change validcode
   $('#changeValidcodeBtn').click(function()
   {
      $('#curValidcode').hide();
      $('#changeValidcode').show();
      $('#curAdminPW').show();
      $('#changeAdminPW').hide();
      document.formAdminPW.reset();
   });
   $('#cancelChangeValidcode').click(function()
   {
      $('#curValidcode').show();
      $('#changeValidcode').hide();
      document.formValidcode.reset();
      document.getElementById("oldValidcode").value = g_validcode;
   });
   $('#submitChangeValidcode').click(function()
   {
      var oldValidcode = document.getElementById("oldValidcode").value;
      var newValidcode1 = document.getElementById("newValidcode").value;
      var newValidcode2 = document.getElementById("newValidcodeConfirm").value;

      var str;  //送出資料字串      
      var temp;
      
	  //check length
      if (newValidcode1.length < D_USER_PASSWORD_LENGTH_LIMIT1 || newValidcode1.length > D_USER_PASSWORD_LENGTH_LIMIT2)
      {
         alert(MSG_USER_PASSWORD_LENGTH_ERROR);
         return false;
      }
      //check illegal	  
      if (newValidcode1.match(/['-]/))
      {
         alert(MSG_PASSWORD_ILLEGAL);
         return false;
      }
      //at least one alphabet and one number
/*      if (!newValidcode1.match(/[a-zA-Z]/) || !newValidcode1.match(/\d/))
      {
         alert(MSG_PASSWORD_ILLEGAL_FORMAT);
         return false;
      } */
      //confirm password
      if (newValidcode1 != newValidcode2)
      {
         alert(MSG_PASSWORD_CONFIRM_ERROR);
         return false;
      }
	  
      str = g_data_name1 + D_CMD7 + "&" + g_data_name10 + encodeURIComponent(oldValidcode) + "&" + g_data_name11 + encodeURIComponent(newValidcode1) + "&" + g_data_name12 + encodeURIComponent(newValidcode2);
      //alert(str);
      $('#loadingWrap').show();
      //AJAX
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: 'GET',
         url: D_URL11 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);                 
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if(!res.match(/^-\d+$/))  //success
               {             
                  temp = newValidcode1 + " ";
                  g_validcode = newValidcode1;
                  document.formValidcode.reset();
                  document.getElementById("oldValidcode").value = g_validcode;
                  
                  //set new validcode on page
                  if (document.getElementById("Validcode").innerText)  //ie, chrome
                  {
                     document.getElementById("Validcode").innerText = temp;
                  }
                  else  //firefox
                  {
                     document.getElementById("Validcode").textContent = temp;
                  }
                  alert(MSG_CHANGE_PASSWORD_SUCCESS);
                  $('#changeValidcode').hide();            
                  $('#curValidcode').show();
               }
               else  //failed
               {
                  alert(MSG_CHANGE_PASSWORD_ERROR);
                  alert(res);
               }   
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   });
   
   //change administrator password
   $('#changeAdminPWBtn').click(function()
   {
      $('#curAdminPW').hide();
      $('#changeAdminPW').show();
      $('#curValidcode').show();
      $('#changeValidcode').hide();
      document.formValidcode.reset();
      document.getElementById("oldValidcode").value = g_validcode;
   });
   $('#cancelChangeAdminPW').click(function()
   {
      $('#curAdminPW').show();
      $('#changeAdminPW').hide();
      document.formAdminPW.reset();
   });
   $('#submitChangeAdminPW').click(function()
   {
      var oldAdminPW = document.getElementById("oldAdminPW").value;
      var newAdminPW1 = document.getElementById("newAdminPW").value;
      var newAdminPW2 = document.getElementById("newAdminPWConfirm").value;
      var pwdType = document.getElementById("loginLevel").value;
      var str;  //送出資料字串
		
      //check length
      if (newAdminPW1.length < D_ADMIN_PASSWORD_LENGTH_LIMIT1 || newAdminPW1.length > D_ADMIN_PASSWORD_LENGTH_LIMIT2)
      {
         alert(MSG_ADMIN_PASSWORD_LENGTH_ERROR);
         return false;
      }
/*       //check illegal	  
      if (newAdminPW1.match(/['-]/))
      {
         alert(MSG_PASSWORD_ILLEGAL);
         return false;
      }
      //at least one alphabet and one number
      if (!newAdminPW1.match(/[a-zA-Z]/) || !newAdminPW1.match(/\d/))
      {
         alert(MSG_PASSWORD_ILLEGAL_FORMAT);
         return false;
      } */
      //confirm password
      if (newAdminPW1 != newAdminPW2)
      {
         alert(MSG_PASSWORD_CONFIRM_ERROR);
         return false;
      }
	   
      if(pwdType == 1)
      {
         str = D_URL11 + g_data_name1 + D_CMD8 + "&" + g_data_name10 + encodeURIComponent(oldAdminPW) + "&" + g_data_name11 + encodeURIComponent(newAdminPW1) + "&" + g_data_name12 + encodeURIComponent(newAdminPW2);
      }
      else if(pwdType == 2)
      {
         str = D_URL19 + g_data_name1 + D_CMD10 + "&" + g_data_name10 + encodeURIComponent(oldAdminPW) + "&" + g_data_name11 + encodeURIComponent(newAdminPW1) + "&" + g_data_name12 + encodeURIComponent(newAdminPW2);
      }
      
      $('#loadingWrap').show();
      //AJAX
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: 'GET',
         url: str,
         cache: false,
         success: function(res)
         {
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (!res.match(/^-\d+$/))  //success
               {             
                  document.formAdminPW.reset();
                  alert(MSG_CHANGE_PASSWORD_SUCCESS);
                  $('#curAdminPW').show();
                  $('#changeAdminPW').hide();
               }
               else  //failed
               {
                  alert("change pw error");
               }   
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   });
   $('#changeContactEmail').click(function()
   {
      var contact_email = document.getElementsByName("contact_email")[0].value;
      str = "changeContactEmail.php?contact=" + encodeURIComponent(contact_email);
      //AJAX
      $.ajax
      ({
         beforeSend: function()
         {
            //alert("1.000" + str);
         },
         type: 'GET',
         url: str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            if (!res.match(/^-\d+$/))  //success
            {             
               alert("管理者信箱更新成功");
            }
            else  //failed
            {
               alert("更新管理者信箱失敗");
            }   
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   });

   //submit new report
   $('.btn_submit_new.report_confirm').click(function()
   {
      var report_name = document.getElementById("reportName").value.trim();
      var checkbox = document.getElementsByName("IdentityCheckbox");
      var reportHostname = document.getElementById("reportHostname").value.trim();
      var range_begin = document.getElementById("from5").value;
      var range_end = document.getElementById("to5").value;
      var departName = document.getElementById("searchDepartName2").value;
      var riskCategorySelect="2";
      if (document.getElementsByName("riskCategorySelect")[0].checked)
         riskCategorySelect = "2";
      else if (document.getElementsByName("riskCategorySelect")[1].checked)
         riskCategorySelect = "3";

      var i;
      var str;  //送出資料字串      
      var temp;
      var count = 0;
      var flag = 0;

      //identity type
      g_checkbox_str = "";
      for(i = 0; i < checkbox.length; i++)
         if (checkbox[g_mapping[i]].checked == true)
         {
            count++;
            if (count == 1)
               temp = i + "";
            else
               temp = "," + i;
            g_checkbox_str += temp;
         }
		
      //check illegal
      if (report_name == "")
      {
         alert(MSG_NO_NAME);
         return false;
      }
      else if (report_name.length > D_REPORT_NAME_LENGTH)
      {
         alert(MSG_REPORT_NAME_OVERLIMIT);
         return false;
      }
      if (count == 0)
      {
         alert(MSG_NO_IDENTITY);
         return false;
      }
      if (range_begin == "")
      {
         alert(MSG_NO_RANGE_BEGIN);
         return false;
      }
      if (range_end == "")
      {
         alert(MSG_NO_RANGE_END);
         return false;
      }
      if (document.getElementById("report_no").value >= D_MAX_REPORT)
      {
         alert(MSG_MAX_REPORT);
         return false;
      }
      str = g_data_name1 + D_CMD1 + "&" + g_data_name3 + encodeURIComponent(report_name) + "&" + g_data_name4 + g_checkbox_str 
          + "&" + g_data_name5 + range_begin + "&" + g_data_name6 + range_end + "&departName=" + encodeURIComponent(departName)
          + "&reportHostname=" + encodeURIComponent(reportHostname) + "&" +  g_data_name13 + riskCategorySelect;
      $('#loadingWrap').show();
      //AJAX
      //alert(str);
      ajax_genReportChart(str);
   });

   //***Step4 searchNews begin
   $('.btn_submit_new.searchNews').click(function()
   {
      var searchNewsTitleMsg = document.getElementById("searchNewsTitleMsg").value;
      var searchNewsfrom1 = document.getElementsByName("searchNewsfrom1")[0].value;
      var searchNewsto1 = document.getElementsByName("searchNewsto1")[0].value;
      var searchNewsfrom2 = document.getElementsByName("searchNewsfrom2")[0].value;
      var searchNewsto2 = document.getElementsByName("searchNewsto2")[0].value;
   
      var statusCheckbox = 0;
      if (document.getElementById("searchNewsCheckBox1").checked == true)
      {
         statusCheckbox += 1; 
      }
      if (document.getElementById("searchNewsCheckBox2").checked == true)
      {
         statusCheckbox += 2; 
      }
      var str;                            //送出資料字串  
      
      //ajax
      str = "cmd=searchNews" + "&" + "searchNewsTitleMsg=" + encodeURIComponent(searchNewsTitleMsg) + "&" + "searchNewsfrom1=" + searchNewsfrom1 + "&"
            + "searchNewsto1=" + searchNewsto1 + "&" + "searchNewsfrom2=" + searchNewsfrom2 + "&" + "searchNewsto2=" + searchNewsto2 + "&" + "statusCheckbox=" + statusCheckbox;
      url_str = "New/News_load.php?";
      
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
                  document.getElementById("searchNewsPages").innerHTML = res;
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
   });
   //***Step4 searchNews end
   
   //***Step21 searchUsers begin
   $('.btn_submit_new.searchUsers').click(function()
   {
      var searchUsersNameEmail = document.getElementById("searchUsersNameEmail").value;
      var searchUsersfrom1 = document.getElementsByName("searchUsersfrom1")[0].value;
      var searchUsersto1 = document.getElementsByName("searchUsersto1")[0].value;
   
      var statusCheckbox = 0;
      if (document.getElementById("searchUsersCheckBox1").checked == true)
      {
         statusCheckbox += 1; 
      }
      if (document.getElementById("searchUsersCheckBox2").checked == true)
      {
         statusCheckbox += 2; 
      }
      
      var searchUsersRadio = 0;
      if (document.getElementById("searchUsersRadio1").checked == true)
      {
         searchUsersRadio = 1; 
      }
      var str;                            //送出内文字串  
      
      //ajax
      str = "cmd=searchUsers" + "&" + "searchUsersNameEmail=" + encodeURIComponent(searchUsersNameEmail) + "&" + "searchUsersfrom1=" + searchUsersfrom1 + "&"
            + "searchUsersto1=" + searchUsersto1 + "&" + "statusCheckbox=" + statusCheckbox + "&searchUsersRadio=" + searchUsersRadio;
      url_str = "User/Users_load.php?";
      
      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            // alert(url_str + str);
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
                  document.getElementById("searchUsersPages").innerHTML = res;
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
   });
   //***Step21 searchUsers end
   
   //***Step21 searchPrivileges begin
   $('.btn_submit_new.searchPrivileges').click(function()
   {
      var searchPrivilegesNameEmail = document.getElementById("searchPrivilegesNameEmail").value;
      var searchPrivilegesfrom1 = document.getElementsByName("searchPrivilegesfrom1")[0].value;
      var searchPrivilegesto1 = document.getElementsByName("searchPrivilegesto1")[0].value;
   
      var statusCheckbox = 0;
      if (document.getElementById("searchPrivilegesCheckBox1").checked == true)
      {
         statusCheckbox += 1; 
      }
      if (document.getElementById("searchPrivilegesCheckBox2").checked == true)
      {
         statusCheckbox += 2; 
      }
      
      var searchPrivilegesRadio = 0;
      if (document.getElementById("searchPrivilegesRadio1").checked == true)
      {
         searchPrivilegesRadio = 1; 
      }
      var str;                            //送出内文字串  
      
      //ajax
      str = "cmd=searchPrivileges" + "&" + "searchPrivilegesNameEmail=" + encodeURIComponent(searchPrivilegesNameEmail) + "&" + "searchPrivilegesfrom1=" + searchPrivilegesfrom1 + "&"
            + "searchPrivilegesto1=" + searchPrivilegesto1 + "&" + "statusCheckbox=" + statusCheckbox + "&searchPrivilegesRadio=" + searchPrivilegesRadio;
      url_str = "Admin/Privileges_load.php?";
      
      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            // alert(url_str + str);
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
                  document.getElementById("searchPrivilegesPages").innerHTML = res;
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
   });
   //***Step21 searchPrivileges end
   
   //***Step21 searchFiles begin
   $('.btn_submit_new.searchFiles').click(function()
   {
      var searchFilesNameCode = document.getElementById("searchFilesNameCode").value;
      var searchFilesfrom1 = document.getElementsByName("searchFilesfrom1")[0].value;
      var searchFilesto1 = document.getElementsByName("searchFilesto1")[0].value;
   
      var statusCheckbox = 0;
      if (document.getElementById("searchFilesCheckBox1").checked == true)
      {
         statusCheckbox += 1; 
      }
      if (document.getElementById("searchFilesCheckBox2").checked == true)
      {
         statusCheckbox += 2; 
      }
      
      var str;                            //送出内文字串  
      
      //ajax
      str = "cmd=searchFiles" + "&" + "searchFilesNameCode=" + encodeURIComponent(searchFilesNameCode) + "&" + "searchFilesfrom1=" + searchFilesfrom1 + "&"
            + "searchFilesto1=" + searchFilesto1 + "&" + "statusCheckbox=" + statusCheckbox;
      url_str = "File/Files_load.php?";
      
      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            // alert(url_str + str);
         },
         type: 'GET',
         url: url_str + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (res.match(/^-/))  //success
               {
                  alert(MSG_SEARCH_ERROR + res);
               }
               else  //failed
               {  
                  //echo "1.0";
                  document.getElementById("searchFilesPages").innerHTML = res;
               }
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   });
   //***Step21 searchFiles end
   
   //***Step22 searchDepts begin
   $('.btn_submit_new.searchDepts').click(function()
   {
      var searchDeptsNameCode = document.getElementById("searchDeptsNameCode").value;
      var searchDeptsfrom1 = document.getElementsByName("searchDeptsfrom1")[0].value;
      var searchDeptsto1 = document.getElementsByName("searchDeptsto1")[0].value;
   
      var statusCheckbox = 0;
      if (document.getElementById("searchDeptsCheckBox1").checked == true)
      {
         statusCheckbox += 1; 
      }
      if (document.getElementById("searchDeptsCheckBox2").checked == true)
      {
         statusCheckbox += 2; 
      }
      
      var str;                            //送出内文字串  
      
      //ajax
      str = "cmd=searchDepts" + "&searchDeptsNameCode=" + encodeURIComponent(searchDeptsNameCode) + "&searchDeptsfrom1=" + searchDeptsfrom1 
           + "&searchDeptsto1=" + searchDeptsto1 + "&statusCheckbox=" + statusCheckbox;
      url_str = "Dept/Depts_load.php?";
      
      // alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            // alert(url_str + str);
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
                  document.getElementById("searchDeptsPages").innerHTML = res;
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
   });
   //***Step22 searchDepts end
   
   //***Step23 searchCategories begin
   $('.btn_submit_new.searchCategories').click(function()
   {
      var searchCategoriesName = document.getElementById("searchCategoriesName").value;
      var searchCategoriesfrom1 = document.getElementsByName("searchCategoriesfrom1")[0].value;
      var searchCategoriesto1 = document.getElementsByName("searchCategoriesto1")[0].value;
   
      var statusCheckbox = 0;
      if (document.getElementById("searchCategoriesCheckBox1").checked == true)
      {
         statusCheckbox += 1; 
      }
      if (document.getElementById("searchCategoriesCheckBox2").checked == true)
      {
         statusCheckbox += 2; 
      }
      
      var str;                            //送出内文字串  
      
      //ajax
      str = "cmd=searchCategories" + "&searchCategoriesName=" + encodeURIComponent(searchCategoriesName) + "&searchCategoriesfrom1=" + searchCategoriesfrom1 
           + "&searchCategoriesto1=" + searchCategoriesto1 + "&statusCheckbox=" + statusCheckbox;
      url_str = "Category/Categories_load.php?";
      
      // alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            // alert(url_str + str);
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
                  document.getElementById("searchCategoriesPages").innerHTML = res;
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
   });
   //***Step24 searchCategories end
   
   //computerList search, added by Phantom, 20120613
   $('.btn_submit_new.userMgmt_confirm').click(function()
   {
      var userMgmt_type = ""; 
      var userMgmt_targetCom = "all";
      var userMgmt_keyword = document.getElementsByName("userMgmt_keyword")[0].value;
      //var userMgmt_date = document.getElementsByName("userMgmt_date")[0].value;
      var range_begin = document.getElementById("from3").value;   //#001
      var range_end = document.getElementById("to3").value;       //#001
      len = document.getElementsByName("userMgmt_type").length;
      for (i=0;i<len;i++) 
      {
         if (document.getElementsByName("userMgmt_type")[i].checked)
            userMgmt_type = userMgmt_type + document.getElementsByName("userMgmt_type")[i].value + ";"
      }

      len = document.getElementsByName("userMgmt_targetCom").length;
      for (i=0;i<len;i++)
      {
         if (document.getElementsByName("userMgmt_targetCom")[i].checked)
         {
            userMgmt_targetCom = document.getElementsByName("userMgmt_targetCom")[i].value;
            break;
         }
      }

      if (userMgmt_keyword == "電腦、人員或部門名稱")
         userMgmt_keyword = "";
      
      //搜尋條件 checkbox 一定要選一個
      if (userMgmt_type == "")
      {
         alert("請至少勾選一個搜尋條件(已完成、清查中、未實施、已逾時)");
         return;
      }
      
      var str;                            //送出資料字串  
      //ajax
      str = "cmd=search" + "&" + "userMgmt_type=" + encodeURIComponent(userMgmt_type) + "&" + "userMgmt_targetCom=" + encodeURIComponent(userMgmt_targetCom) + "&" 
            + "userMgmt_keyword=" + encodeURIComponent(userMgmt_keyword) + "&" + "range_begin=" + encodeURIComponent(range_begin) + "&"
            + "range_end=" + encodeURIComponent(range_end);    //#001

      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL4 + str);
         },
         type: 'GET',
         url: D_URL14 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (!res.match(/^-\d+$/))  //success
               {
                  document.getElementById("userMgmtPages").innerHTML = res;
                  count1 = document.getElementsByName("count1")[0].value;
                  count2 = document.getElementsByName("count2")[0].value;
                  count3 = document.getElementsByName("count3")[0].value;
                  count4 = document.getElementsByName("count4")[0].value;
                  str = "(已完成:" + count1 + "台, 清查中:" + count2 + "台, 未實施:" + count3 + "台, 已逾時:" + count4 + "台)";
                  document.getElementById("countStr").innerHTML = str;
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
   });

   // #001 start modify
   $('.btn_submit_new.userMgmt_cancel').click(function()
   {
      // clear 搜尋條件
      len = document.getElementsByName("userMgmt_type").length;
      for (i=0;i<len;i++) 
      {
         document.getElementsByName("userMgmt_type")[i].checked = true;
      }
      // clear 搜尋範圍
      document.getElementsByName("userMgmt_targetCom")[0].checked = true;
      // clear 關鍵字搜尋
	   if(!_input.hasClass('empty'))
         _input.addClass('empty').val(user_searchHint);
      // clear 時間區間
      document.getElementById("from3").value="";
      document.getElementById("to3").value="";
      // The syntax of following two lines is correct but no effect
      //$("#from3").datepicker(function(){$.datepicker._clearDate(this);});
      //$("#to3").datepicker(function(){$.datepicker._clearDate(this);});
   });

   $('.btn_submit_new.userDep_confirm').click(function()
   {
      var userMgmt_type = ""; 
      var userMgmt_targetCom = "all";
      var userMgmt_keyword = document.getElementsByName("userMgmt_keyword")[0].value;
      var range_begin = document.getElementById("from3").value;
      var range_end = document.getElementById("to3").value;
      len = document.getElementsByName("userMgmt_type").length;
      for (i=0;i<len;i++) 
      {
         if (document.getElementsByName("userMgmt_type")[i].checked)
            userMgmt_type = userMgmt_type + document.getElementsByName("userMgmt_type")[i].value + ";"
      }

      len = document.getElementsByName("userMgmt_targetCom").length;
      for (i=0;i<len;i++)
      {
         if (document.getElementsByName("userMgmt_targetCom")[i].checked)
         {
            userMgmt_targetCom = document.getElementsByName("userMgmt_targetCom")[i].value;
            break;
         }
      }

      if (userMgmt_keyword == "電腦、人員或部門名稱")
         userMgmt_keyword = "";
	  
	   //搜尋條件 checkbox 一定要選一個
      if (userMgmt_type == "")
      {
         alert("請至少勾選一個搜尋條件(已完成、清查中、未實施、已逾時)");
         return;
      }
      
      var str;                            //送出資料字串  
	  
      //ajax
      str = "cmd=search" + "&" + "userMgmt_type=" + encodeURIComponent(userMgmt_type) + "&" + "userMgmt_targetCom=" + encodeURIComponent(userMgmt_targetCom) + "&" 
            + "userMgmt_keyword=" + encodeURIComponent(userMgmt_keyword) + "&" + "range_begin=" + encodeURIComponent(range_begin) + "&"
            + "range_end=" + encodeURIComponent(range_end);

      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL4 + str);
         },
         type: 'GET',
         url: D_URL16 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {			
               if (!res.match(/^-\d+$/))  //success
               {
                  document.getElementById("userMgmtPages").innerHTML = res;
                  count1 = document.getElementsByName("count1")[0].value;
                  count2 = document.getElementsByName("count2")[0].value;
                  count3 = document.getElementsByName("count3")[0].value;
                  count4 = document.getElementsByName("count4")[0].value;
                  str = "(已完成:" + count1 + "台, 清查中:" + count2 + "台, 未實施:" + count3 + "台, 已逾時:" + count4 + "台)";
                  document.getElementById("countStr").innerHTML = str;
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
   }); 
   // #001 end modify

   // #002 begin modify
   // function for searching scan history
   $('.btn_submit_new.scan_history').click(function()
   {
      var scan_history_keyword = document.getElementsByName("scan_history_keyword")[0].value;
      var range_begin = document.getElementById("from4").value;
      var range_end = document.getElementById("to4").value;
      if (scan_history_keyword == "電腦、人員或部門名稱")
         scan_history_keyword = "";
      var str;                            //送出資料字串  
	   
      //ajax
      str = "cmd=search" + "&" + "scan_history_keyword=" + encodeURIComponent(scan_history_keyword) + "&" 
            + "range_begin=" + encodeURIComponent(range_begin) + "&"
            + "range_end=" + encodeURIComponent(range_end);
      
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL4 + str);
         },
         type: 'GET',
         url: D_URL17 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {			
               if (!res.match(/^-\d+$/))  //success
               {
                  document.getElementById("scanHistoryPages").innerHTML = res;
                  completed = document.getElementsByName("completed")[0].value;
                  searching = document.getElementsByName("searching")[0].value;
                  str = "查詢結果：共 " + completed + " 筆盤點完成紀錄，尚有 " + searching + " 台電腦進行清查中";
                  document.getElementById("countStrScan").innerHTML = str;
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
   }); 
   // #002 end modify
   
   // #012 begin modify
   $('.btn_submit_new.modify_whitelist').click(function()
   {
      var modify_whitelist_content = document.getElementsByName("whiteListContent")[0].value;
      var str;                            //送出資料字串  
	   
      //ajax
      str = "cmd=modify_whitelist" + "&" + "modify_whitelist_content=" + encodeURIComponent(modify_whitelist_content); 
      
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL20 + str);
         },
         type: 'GET',
         url: D_URL20 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {			
               if (!res.match(/^-\d+$/))  //success
               {
                  alert(MSG_WHITELIST_SUCCESS);
               }
               else  //failed
               {  
			         //echo "1.0";
                  alert(MSG_WHITELIST_ERROR);
               }
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   }); 
   // #012 end modify
   
   // problem search
   $('.btn_submit_new.searchProbs').click(function()
   {
      var searchProbsDescMemo = document.getElementById("searchProbsDescMemo").value;
      var searchProbsLevel = document.getElementById("searchProbsLevel").value;
   
      var statusCheckbox = 0;
      if (document.getElementById("searchProbsCheckBox1").checked == true)
      {
         statusCheckbox += 1; 
      }
      if (document.getElementById("searchProbsCheckBox2").checked == true)
      {
         statusCheckbox += 2; 
      }
      
      var str;                            //送出内文字串  
      
      //ajax
      str = "cmd=searchProbs" + "&searchProbsDescMemo=" + encodeURIComponent(searchProbsDescMemo) +
            "&searchProbsLevel=" + encodeURIComponent(searchProbsLevel) +
            "&statusCheckbox=" + statusCheckbox;
      url_str = "Problem/Problems_load.php?";

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
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (!res.match(/^-\d+$/))  //success
               {
                  document.getElementById("searchProbsPages").innerHTML = res;
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
            $('#loadingWrap').hide();
         }
      });
   });
   //***problem search end
   
   // exam search
   $('.btn_submit_new.searchExams').click(function()
   {
      var searchExamsName = document.getElementById("searchExamsName").value;
   
      var statusCheckbox = 0;
      if (document.getElementById("searchExamsCheckBox1").checked == true)
      {
         statusCheckbox += 1; 
      }
      if (document.getElementById("searchExamsCheckBox2").checked == true)
      {
         statusCheckbox += 2; 
      }
      
      var str;                            //送出内文字串  
      
      //ajax
      str = "cmd=searchExams" + "&searchExamsName=" + encodeURIComponent(searchExamsName) +
            "&statusCheckbox=" + statusCheckbox;
      url_str = "Exam/Exams_load.php?";

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
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (!res.match(/^-\d+$/))  //success
               {
                  document.getElementById("searchExamsPages").innerHTML = res;
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
            $('#loadingWrap').hide();
         }
      });
   });
   //***exam end
   
   //computerList search, delete XMLID, added by Phantom, 20120618
   $('.btn_submit_new.deleteXMLClass').click(function()
   {
      var XMLID = "";
      var entryID = "";
      XMLID = document.getElementsByName("deleteXMLID")[0].value;
      entryID = document.getElementsByName("deleteEntryID")[0].value;
      if (XMLID.length == 0 || entryID.length == 0)
      {
         return;
      }

      var str;                            //送出資料字串  
	  
      //ajax
      str = "cmd=deleteXML" + "&" + "XMLID=" + encodeURIComponent(XMLID) + "&" +
            "entryID=" + encodeURIComponent(entryID);

      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL15 + str);
         },
         type: 'GET',
         url: D_URL15 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {			
               if (!res.match(/^-\d+$/))  //success
               {
                  alert("資料已成功刪除，系統將自動重新查詢");
                  location.reload();
               }
               else  //failed
               {  
                  alert(MSG_SEARCH_ERROR);
               }
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   }); 

   //submit default extreme type
   $('.btn_submit_new.extreme_confirm').click(function()
   {
      var checkbox = document.getElementsByName("ExtremeCheckbox");
		//20120316 Dylan begin
      var uploadMask = 0;	
		//20120316 Dylan end
      var netDisk = 0; //#003 add
      var removableDisk = 0; //#003 add
      var systemScanDirEnabled = 0; //#011 add
      var riskTypeNumber = document.getElementById("risktype").value;
      var extremeNumber = document.getElementById("riskExtreme").value;
      var highNumber = document.getElementById("riskHigh").value;
      var lowNumber = document.getElementById("riskLow").value;
      var scanTime = document.getElementById("scanTime").value;
      var scanMode = 1;
      var i;
      var str;  //送出資料字串      
      var temp;
      var temp2 = [0, 0, 0, 0, 0, 0, 0, 0];  //extreme type array
      var count = 0;
      var flag = 0;     

		//20120316 Dylan begin		
      if(document.getElementsByName("uploadMask")[0].checked)
      {
			uploadMask = 0;
      }
      else if (document.getElementsByName("uploadMask")[1].checked)
      {
         uploadMask = 1;
      }
		//20120316 Dylan end
      
      // #006 
      if(document.getElementsByName("scanMode")[0].checked)
         scanMode = 1;
      else if(document.getElementsByName("scanMode")[1].checked)
         scanMode = 0;

      //#003 add begin
      if(document.getElementsByName("netDisk")[0].checked)
         netDisk = 1;
      else
         netDisk = 0;

      if(document.getElementsByName("removableDisk")[0].checked)
         removableDisk = 1;
      else
         removableDisk = 0;
      //#003 add end
      //#011 add begin
      if(document.getElementsByName("systemScanDirEnabled")[0].checked)
         systemScanDirEnabled = 1;
      else
         systemScanDirEnabled = 0;

      systemScanDirContent=document.getElementsByName("systemScanDirContent")[0].value;
      systemScanDirContent=encodeURI(systemScanDirContent);
      //#011 add end

      //check num <= 9999
      if (extremeNumber.length > D_EXTREME_NUM_LEN || highNumber.length > D_EXTREME_NUM_LEN || lowNumber.length > D_EXTREME_NUM_LEN)
      {
         alert(MSG_NUMBER_OVER_LIMIT);
         return false;
      }
      
      //check != null
      if (extremeNumber == "" || highNumber == "" || lowNumber == "")
      {
         alert(MSG_NO_EXTREME_NUM);
         return false;
      }
      
      //check illegal char
      for (i = 0; i < extremeNumber.length; i++)
      {
         if(extremeNumber.charAt(i) < '0' || extremeNumber.charAt(i) > '9')
         {
            alert(MSG_NUMBER_ILLEGAL);
            return false;
         }
      }
      for (i = 0; i < highNumber.length; i++)
      {
         if(highNumber.charAt(i) < '0' || highNumber.charAt(i) > '9')
         {
            alert(MSG_NUMBER_ILLEGAL);
            return false;
         }
      }
      for (i = 0; i < lowNumber.length; i++)
      {
         if(lowNumber.charAt(i) < '0' || lowNumber.charAt(i) > '9')
         {
            alert(MSG_NUMBER_ILLEGAL);
            return false;
         }
      }
   	  
      //check extremeNumber
      if (parseInt(extremeNumber, 10) < D_EXTREME_NUM_SIZE)
      {
         alert(MSG_EXTREME_NUMBER_UNDER_LIMIT);
         return false;
      }
      
      //check highNumber
      if (parseInt(highNumber, 10) < parseInt(lowNumber, 10) + D_HIGH_NUM_SIZE_LIMIT)
      {
         alert(MSG_HIGH_NUMBER_ILLEGAL);
         return false;
      }      
      if (parseInt(highNumber, 10) < D_HIGH_NUM_SIZE)
      {
         alert(MSG_HIGH_NUMBER_UNDER_LIMIT);
         return false;
      }      
      
      //check lowNumber
      if (parseInt(lowNumber, 10) < D_LOW_NUM_SIZE)
      {
         alert(MSG_LOW_NUMBER_UNDER_LIMIT);
         return false;
      }
      
      // #006
      // check scanTime
      if (parseInt(scanTime, 10) > 5 || parseInt(scanTime, 10) < 1)
      {
         alert(MSG_SCANTIME_ERROR);    // #006 add
         return false;
      }
      g_checkbox_str = "";
      for (i = 0; i < checkbox.length; i++)
      {
         if (checkbox[g_mapping[i]].checked == true)
         {
            count++;
            if (count == 1)
            {
               temp = i + "";
               temp2[i] = 1;
            }
            else
            {
               temp = "," + i;
               temp2[i] = 1;
            }
            g_checkbox_str += temp;
         }
      }
      //check type >= 2
      if (count < D_EXTREME_TYPE_LIMIT)
      {
         alert(MSG_EXTREME_UNDER_LIMIT1);
         return false;
      }
      
      //check if checked checkbox num >= risk type number
      if (count < riskTypeNumber)
      {
         alert(MSG_EXTREME_UNDER_LIMIT2);
         return false;
      }
         
      //ajax
		//20120316 Dylan begin		
      //str = "cmd=set_default_extreme" + "&defaultExtremeType=" + g_checkbox_str + "&riskTypeNumber=" + riskTypeNumber + "&extremeNumber=" + extremeNumber + 
      //      "&highNumber=" + highNumber + "&lowNumber=" + lowNumber + "&uploadMask=" + uploadMask + "&netDisk=" + netDisk + "&removableDisk=" + removableDisk; //#003 modified
      
      str = "cmd=set_default_extreme" + "&defaultExtremeType=" + g_checkbox_str + "&riskTypeNumber=" + riskTypeNumber + "&extremeNumber=" + extremeNumber + 
            "&highNumber=" + highNumber + "&lowNumber=" + lowNumber + "&uploadMask=" + uploadMask + "&netDisk=" + netDisk + "&removableDisk=" + removableDisk +
            "&expressEnable=" + scanMode + "&expressTimeout=" + scanTime; //#006 modified
      str = str + "&systemScanDirEnabled=" + systemScanDirEnabled + "&systemScanDirContent=" + systemScanDirContent; //#011 add 
      //alert(str);
		//20120316 Dylan end	

      //20120409 Billy begin
      //document.getElementById("mediumRangeBegin").innerText = parseInt(lowNumber) + 1;
      //document.getElementById("mediumRangeEnd").innerText = parseInt(highNumber) - 1;
      if (document.getElementById("mediumRangeBegin").innerText)  //ie, chrome
      {
         document.getElementById("mediumRangeBegin").innerText = parseInt(lowNumber) + 1;
         document.getElementById("mediumRangeEnd").innerText = parseInt(highNumber) - 1;
      }
      else  //firefox
      {
         document.getElementById("mediumRangeBegin").textContent = parseInt(lowNumber) + 1;
         document.getElementById("mediumRangeEnd").textContent = parseInt(highNumber) - 1;
      }
      //20120409 Billy end

      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL4 + str);
         },
         type: 'GET',
         url: D_URL4 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);                 
            if (!res.match(/^-\d+$/))  //success
            {
               alert(MSG_SET_RISK);
               //refresh checkbox
               for (i = 0; i < D_EXTREME_TYPE_NUM; i++)
               {
                  g_defaultExtremeType[i] = temp2[i];
                  str_checkboxid = "checkbox_" + i;
                  if (g_defaultExtremeType[i] == 1)
                  {
                     document.getElementsByName("IdentityCheckbox")[g_mapping[i]].checked = true;
                     document.getElementById(str_checkboxid).style.color = "red";
                  }
                  else
                  {
                     document.getElementById(str_checkboxid).style.color = "black";
                  }
               }
               document.getElementById("extremeNum").innerText = extremeNumber;
            }
            else  //failed
            {  
               alert(MSG_SET_DEFAULT_EXTREME_ERROR);
            }
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   });

	//******** Search input hints ********//
	var _input = $('#userSearch');
	
	// _input.attr('value', user_searchHint);//eric20150604Edit
	
	_input.focus(function() {
		if($(this).hasClass('empty')) {
			$(this).removeClass('empty').val('');
		}
	});
	_input.blur(function() {
		if($(this).val().length == 0) {			
			$(this).addClass('empty').val(user_searchHint);
		}
	});
	
   var _input_1 = $('#scanSearch');
	
	// _input_1.attr('value', user_searchHint);//eric20150604Edit
	
	_input_1.focus(function() {
		if($(this).hasClass('empty')) {
			$(this).removeClass('empty').val('');
		}
	});
	_input_1.blur(function() {
		if($(this).val().length == 0) {			
			$(this).addClass('empty').val(user_searchHint);
		}
	});
   
   //submit new department
   $('.btn_submit_new.new_depart_confirm').click(function()
   {
      var depart_name = document.getElementById("newDepartName").value.trim();

      var i;
      var str;  //送出資料字串      
      var temp;
      var count = 0;
      var flag = 0;
		
      //check illegal
      if (depart_name == "")
      {
         alert(MSG_NO_NAME);
         return false;
      }
      else if (depart_name.length > D_DEPART_NAME_LENGTH)
      {
         alert(MSG_DEPART_NAME_OVERLIMIT);
         return false;
      }
      /*
      if (document.getElementById("depart_no").value >= D_MAX_DEPART)
      {
         alert(MSG_MAX_DEPART);
         return false;
      }
      */
      
      str = g_data_name1 + D_CMD3 + "&" + g_data_name8 + encodeURIComponent(depart_name);
      $('#loadingWrap').show();
      //AJAX
      //alert(str);
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: 'GET',
         url: D_URL5 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);                 
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if(!res.match(/^-\d+$/))  //success
               {           
                  refreshDepartCheckbox();
                  document.getElementById("refreshDepartPages").innerHTML = res;
                  document.formNewDepart.reset();
                  $('#newDepart').hide();              
                  $('#departW').show();              
               }
               else  //failed
               {
               	  if(res == D_ERROR_SAME_NAME)
               	     alert(MSG_SAME_NAME);
               	  else
                     alert(MSG_CREATE_ERROR);
               }   
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });  
   });
   
   //submit new user 
   $('.btn_submit_new.new_user_confirm').live("click", function()
   {
      var user_name = document.getElementById("newUserName").value.trim();
      var user_password = document.getElementById("newUserPassword").value.trim();
      var c = document.getElementsByName("newUserDepartment");
      var i = 0;
      var dept_list = "";
      for(i; i < c.length; i++)
      {
         if(c[i].checked == true)
         {
            if(dept_list != "")
               dept_list = dept_list + ",'" + c[i].value + "'";
            else
               dept_list = dept_list + "'" + c[i].value + "'";
         }
      }

      var i;
      var str;  //送出資料字串      
      var temp;
      var count = 0;
      var flag = 0;
		
      //check illegal
      if (user_name == "")
      {
         alert(MSG_NO_NAME);
         return false;
      }
      else if (user_name.length > D_USER_NAME_LENGTH)
      {
         alert(MSG_USER_NAME_OVERLIMIT);
         return false;
      }
      else if (user_password.length < D_ADMIN_PASSWORD_LENGTH_LIMIT1)
      {
         alert(MSG_ADMIN_PASSWORD_LENGTH_ERROR);
         return false;
      }
      else if (user_password.length > D_ADMIN_PASSWORD_LENGTH_LIMIT2)
      {
         alert(MSG_ADMIN_PASSWORD_LENGTH_ERROR);
         return false;
      }
      /*
      if (document.getElementById("depart_no").value >= D_MAX_DEPART)
      {
         alert(MSG_MAX_DEPART);
         return false;
      }
      */
      
      str = "cmd=new_user&login_name=" + encodeURIComponent(user_name) + "&password=" + encodeURIComponent(user_password)
            + "&dept_list=" + encodeURIComponent(dept_list);
      $('#loadingWrap').show();
      //AJAX
      //alert(str);
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: 'GET',
         url: "createUser.php?" + str,
         cache: false,
         success: function(res)
         {
            //alert(res);                 
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if(!res.match(/^-\d+$/))  //success
               {           
                  document.getElementById("refreshUserPages").innerHTML = res;
                  document.formNewUser.reset();
                  $('#newUser').hide();              
                  $('#userW').show();              
               }
               else  //failed
               {
               	if(res == D_ERROR_SAME_NAME)
               	   alert(MSG_SAME_USER_NAME);
               	else
                     alert(MSG_CREATE_ERROR);
               }   
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });  
   });

   //submit edit department
   $('.btn_submit_new.edit_depart_confirm').click(function()
   {
      var depart_name = document.getElementById("editDepartName").value.trim();

      var i;
      var str;  //送出資料字串      
      var temp;
      var count = 0;
      var flag = 0;
		
      //check illegal
      if (depart_name == "")
      {
         alert(MSG_NO_NAME);
         return false;
      }
      else if (depart_name.length > D_DEPART_NAME_LENGTH)
      {
         alert(MSG_DEPART_NAME_OVERLIMIT);
         return false;
      }

      str = g_data_name1 + D_CMD4 + "&" + g_data_name8 + encodeURIComponent(depart_name) + "&" + g_data_name9 + g_edit_depart_id
            + "&oldDepartName=" + encodeURIComponent(g_edit_depart_name);
      $('#loadingWrap').show();
      //AJAX
      //alert(str);
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: 'GET',
         url: D_URL6 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);                 
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (!res.match(/^-\d+$/))  //success
               {             
                  refreshUser();
                  refreshDepartCheckbox();
                  document.getElementById("refreshDepartPages").innerHTML = res;
                  document.formNewDepart.reset();
                  $('#editDepart').hide();              
                  $('#departW').show();              
               }
               else  //failed
               {
               	  if(res == D_ERROR_SAME_NAME)
               	     alert(MSG_SAME_NAME);
               	  else
                     alert(MSG_EDIT_ERROR);                  
               }   
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });  
   });
   
   // submit edit user
   $('.btn_submit_new.edit_user_confirm').live("click", function()
   {
      var c = document.getElementsByName("editUserDepartment");
      var i = 0;
      var str = "";
      for(i; i < c.length; i++)
      {
         if(c[i].checked == true)
         {
            if(str != "")
               str = str + ",'" + c[i].value + "'";
            else
               str = str + "'" + c[i].value + "'";
         }
      }
      var user_name = document.getElementById("editUserName").value.trim();
      var user_password = document.getElementById("editUserPassword").value.trim(); 
      str = "cmd=edit_user&dept_list=" + str + "&login_name=" + user_name + "&password=" + user_password;
      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(str);
         },
         type: 'GET',
         url: "editUser.php?" + str,
         cache: false,
         success: function(res)
         {
            //alert(res);                 
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               if (!res.match(/^-\d+$/))  //success
               {             
                  document.getElementById("refreshUserPages").innerHTML = res;
                  document.formNewUser.reset();
                  $('#editUser').hide();              
                  $('#userW').show();              
               }
               else  //failed
               {
              	   if(res == D_ERROR_SAME_NAME)
                     alert(MSG_SAME_NAME);
                  else if (res == D_ERROR_PASSWORD)
                     alert(MSG_ADMIN_PASSWORD_LENGTH_ERROR);
                  else
                     alert(MSG_EDIT_ERROR); 
               }   
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });  
   });
});

function open_scan_result(domain_name, hostname, range_begin, range_end)
{
   /*
   alert(domain_name + " / " + hostname);
   $.post(D_URL17, function(data){
      var win=window.open('about:blank');
      with(win.document)
      {
         open();
         write(data);
         close();
      }
   });
   */
   var str = D_URL18 + "cmd=search" + "&" + "domain_name=" + encodeURIComponent(domain_name) + "&"
             + "hostname=" + encodeURIComponent(hostname) + "&"
             + "range_begin=" + encodeURIComponent(range_begin) + "&"
             + "range_end=" + encodeURIComponent(range_end);
   window.open(str);

}

function ofc_ready(id)
{
   var LOAD_DELAY_TIME = 2000;
   //firefox/chrome
   if (navigator.appName.indexOf("Microsoft") == -1)
   {
      document.getElementById(id).style.visibility = 'hidden';
   }
   //ie
   else
   {
      document.getElementById(id).style.display = 'none';
   }
   func_str = "post_image('" + id + "',false)";  //ex.post_image('barChart',false);
   setTimeout(func_str,LOAD_DELAY_TIME);
}

function post_image(id, debug)
{
   //var SAVE_IMG_PHP = "http://10.0.4.108/save_img.pl?";
   //var SAVE_IMG_PHP = "/trial/save_img.pl?";
   var SAVE_IMG_PHP = D_URL_POST_IMAGE;

   /*
   fileFolder:guid/timestamp_pid ex. 8f44a8ab_5c6c_6232_cd4f_642761007428/20120206152800_1234
   name:chart image name   ex. barChart.png
   */
   var fileFolder = "fileFolder=" + document.getElementById('GUID').value + "/" + g_json["fileFolder"];
   name = "name=" + id + ".png";
   url = SAVE_IMG_PHP + fileFolder + "&" + name;
   ofc = findSWF(id);
   ofc.post_image(url, "", debug);
}

function findSWF(movieName)
{
   return document[movieName];
}

function ajax_genReportChart(str)
{
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);
      },
      type: 'GET',
      url: D_URL3 + str,
      cache: false,
      success: function(res)
      {
         //alert(res);                 
         if (!(ret = res.match(/^-\d+$/)))  //success
         {
            eval('g_json = ' + res);
            ajax_checkImg();
         }
         else  //failed
         {  
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               alert(MSG_GEN_REPORT_CHART_ERROR + ret[0]);
            });
         }
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });     
}

function ajax_checkImg()
{
   var fileFolder = document.getElementById('GUID').value + "/" + g_json["fileFolder"];
   url = 'check_img.pl?fileFolder=' + fileFolder; 
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(url);            
      },
      type: 'GET',
      url: url,
      cache: false,
      success: function(res)
      {
         //alert(res);
         if (res == "success\n")  //success
         {  
            ajax_createReport();
         }
         else  //failed
         {  
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {
               alert(MSG_CHECK_IMG_ERROR);
               //alert(res);
            });
         }          
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });     
}

function report_msg()
{
   alert("敬告用戶：微軟於八月中所釋出的 Office 安全性更新中，改變了 Excel 巨集相關設定，導致您於 8 月 21 日之前所製作的報表，可能會無法於 Office 中正確開啟；請您重新「產生新的報表」(可選擇一樣的時間區間及條件)，重新「下載報表」後，便可於更新過後的 Microsoft Office 2007 或 2010 正常執行。");
}

function ajax_createReport()
{
   var str = "";
   for (var key in g_json)
   {
      //str += key + "=" + encodeURIComponent(g_json[key]) + "&";
      str += key + "=" + g_json[key] + "&";
   }
   //alert (g_json["report_name"]);
   
   $.ajax
   ({
      beforeSend: function()
      {
         //alert(str);            
      },
      type: "GET",
      url: D_URL2 + str,
      //url: D_URL2,
      cache: false,
      success: function(res)
      {
         //alert( "Data Saved: " + res);         
         $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
         {
            if (!(ret = res.match(/^-\d+$/)))  //success
            {               
               document.getElementById("refreshPages").innerHTML = res;
               document.formNewReport.reset();
               $( "#from5, #to5" ).datepicker("option", "maxDate", new Date());     // #008
               $( "#from5, #to5" ).datepicker("option", "minDate", null);           // #008
               $('.newReport').hide();               
               $('.reportW').show();
            }
            else  //failed
            {
               alert(MSG_CREATE_ERROR + ret[0]);
            }   
         });
      },
      error: function(xhr)
      {
         alert("ajax error: " + xhr.status + " " + xhr.statusText);
      }
   });
}

function get_data_1()
{
   return JSON.stringify(data_1);
}
function get_data_2()
{
   return JSON.stringify(data_2);
}
function get_data_3()
{
   return JSON.stringify(data_3);
}
function get_data_4()
{
   return JSON.stringify(data_4);
}
function get_data_5()
{
   return JSON.stringify(data_5);
}
function get_data_6()
{
   return JSON.stringify(data_6);
}
function get_data_7()
{
   return JSON.stringify(data_7);
}
