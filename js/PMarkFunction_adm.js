/*
PMarkFunction_adm.js

1.functions of OSC_index_adm.php
2.AJAX to php
*/

/////////////////////////////////////
// Modification history
// #000 2014/09/13 Phantom             File created.
//    1. 因應安泰銀行客製 系統管理員頁面
//    2. 顯示 (1)分行管理, (2)系統資訊, (3)設定, (4)白名單
//
// #001 2014/11/14 Odie
//    1. 修改loginName的regex，讓它可接受大小寫英數字及-_+.作為帳號
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
var MSG_LOGINNAME_LENGTH_ERROR = "帳號為3~31個字元，接受英文、數字、符號_及符號.";
var MSG_CUSTOMER_NAME_ERROR = "分行名稱不能空白";
var MSG_EMAIL_ERROR = "請填寫正確 Email";
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
var MSG_MODIFYADM_ERROR = "系統管理者更新失敗";
var MSG_MODIFY_ADM_EMAIL_SUCCESS = "管理者信箱更新成功";
var MSG_MODIFY_ADM_EMAIL_ERROR = "管理者信箱更新失敗";
var MSG_SYSTEMADMIN_LEN_ERROR1 = "帳號或密碼長度不合法，帳號至少長度 3，密碼至少長度 8";
var MSG_SYSTEMADMIN_LEN_ERROR2 = "密碼長度不合法，密碼至少長度 8";
var MSG_SYSTEMADMIN_NEW_SUCCESS = "系統管理者 - 新增成功";
var MSG_SYSTEMADMIN_DELETE_SUCCESS = "系統管理者 - 刪除成功";
var MSG_SYSTEMADMIN_MODIFY_SUCCESS = "系統管理者 - 密碼修改成功";
var MSG_SYSTEMADMIN_ACCOUNT_ERROR = "帳號只接受英數字、底線 (_) 及點 (.)";
var MSG_CUSTOMER_MODIFY_SUCCESS = "分行 - 修改成功";
var MSG_CUSTOMER_NEW_SUCCESS = "分行 - 新增成功";
var MSG_CUSTOMER_DELETE_SUCCESS = "分行 - 刪除成功";
var MSG_REMAIN_ERROR = "剩餘次數不合法，請輸入數字";

//data
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
var D_EXTREME_TYPE_NUM = 8;                    //個資類型數目
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
var g_mapping = [1, 6, 3, 5, 4, 0, 2, 7];      //項目欄位對應 
var g_edit_depart_id;                          //要修改的部門id
var g_edit_depart_name;                        //要修改的部門名稱
var g_json;

var D_URL4 = "setDefaultExtreme_adm.php?";
var D_URL8 = "checkTimeout.php?";
var D_URL11 = "modifyPassword_adm.php?";
var D_URL14 = "branchQueryResult.php?";
var D_URL20 = "modifyWhiteList.php?";
var D_URL21 = "systemAdminMgt.php?";
var D_URL22 = "customerMgt.php?";

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
               window.location.replace("main_adm.php");
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

//showTypeDis, hideTypeDis
function showTypeDis()
{
   $('.typeDes').show();
}
function hideTypeDis()
{
   $('.typeDes').hide();
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

//
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

//systemAdmin new, added by Phantom, 20140916
function newSystemAdmin()
{
   document.getElementsByName("submitAdminAction")[0].value = "new";
   document.getElementsByName("submitAdminAccount")[0].value = document.getElementsByName("newAdminAccount")[0].value;
   document.getElementsByName("submitAdminPassword")[0].value = document.getElementsByName("newAdminPassword")[0].value;
   document.getElementsByName("submitAdminButton")[0].click();
}

//systemAdmin delete, added by Phantom, 20140916
function modifyAdminDelete(account)
{
   document.getElementsByName("submitAdminAction")[0].value = "delete";
   document.getElementsByName("submitAdminAccount")[0].value = account;
   document.getElementsByName("submitAdminButton")[0].click();
}

//systemAdmin modify password, added by Phantom, 20140916
function modifyAdminPasswd(account, count)
{
   var n = count - 1;
   document.getElementsByName("submitAdminAction")[0].value = "modify";
   document.getElementsByName("submitAdminAccount")[0].value = account;
   document.getElementsByName("submitAdminPassword")[0].value = document.getElementsByName("modifyAdminPassword")[n].value;
   document.getElementsByName("submitAdminButton")[0].click();
}

//Customer new, add by Phantom, 20140916
function newCustomer()
{
   document.getElementsByName("submitCustomerAction")[0].value = "new";
   document.getElementsByName("submitCustomerName")[0].value = document.getElementsByName("newCustomerName")[0].value;
   document.getElementsByName("submitCustomerLoginName")[0].value = document.getElementsByName("newCustomerLoginName")[0].value;

   document.getElementsByName("submitCustomerPassword")[0].value = document.getElementsByName("newCustomerPassword")[0].value;
   document.getElementsByName("submitCustomerValidcode")[0].value = document.getElementsByName("newCustomerValidcode")[0].value;
   document.getElementsByName("submitCustomerEmail")[0].value = document.getElementsByName("newCustomerEmail")[0].value;
   if (document.getElementsByName("submitCustomerRemain")[0] != undefined)
   {
      document.getElementsByName("submitCustomerRemain")[0].value = document.getElementsByName("newCustomerRemain")[0].value;
   }
   document.getElementsByName("submitCustomerButton")[0].click();
}

//Customer delete, add by Phantom, 20140916
function modifyCustomerDelete(guid)
{
   document.getElementsByName("submitCustomerAction")[0].value = "delete";
   document.getElementsByName("submitCustomerGUID")[0].value = guid;
   document.getElementsByName("submitCustomerButton")[0].click();
}

//Customer modify password, add by Phantom, 20140916
function modifyCustomerPasswd(guid, count)
{
   var n = count - 1;
   document.getElementsByName("submitCustomerAction")[0].value = "modify_password";
   document.getElementsByName("submitCustomerGUID")[0].value = guid;
   document.getElementsByName("submitCustomerPassword")[0].value = document.getElementsByName("modifyCustomerPassword")[n].value;
   document.getElementsByName("submitCustomerButton")[0].click();
}

//Customer modify validcode, add by Phantom, 20140916
function modifyCustomerValidcode(guid, count)
{
   var n = count - 1;
   document.getElementsByName("submitCustomerAction")[0].value = "modify_validcode";
   document.getElementsByName("submitCustomerGUID")[0].value = guid;
   document.getElementsByName("submitCustomerValidcode")[0].value = document.getElementsByName("modifyCustomerValidcode")[n].value;
   document.getElementsByName("submitCustomerButton")[0].click();
}

//Customer modify email, add by Phantom, 20140916
function modifyCustomerEmail(guid, count)
{
   var n = count - 1;
   document.getElementsByName("submitCustomerAction")[0].value = "modify_email";
   document.getElementsByName("submitCustomerGUID")[0].value = guid;
   document.getElementsByName("submitCustomerEmail")[0].value = document.getElementsByName("modifyCustomerEmail")[n].value;
   document.getElementsByName("submitCustomerButton")[0].click();
}

$(function()
{
   $('#newDepartName, #editDepartName').keypress(function(e){
      if(e.which == 13){return false;}
   });
   
   //report vHigh and high color
   $('.vHighW, .highW').each(function()
   {
      if ($(this).text() == "0")
      {
         $(this).css('color','#444');
      }
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
      $('.container').eq(cur).show().siblings().hide();
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

   // submit system admin password to systemLogin Table
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
      //confirm password
      if (newAdminPW1 != newAdminPW2)
      {
         alert(MSG_PASSWORD_CONFIRM_ERROR);
         return false;
      }
	   
      str = D_URL11 + g_data_name1 + D_CMD8 + "&" + g_data_name10 + encodeURIComponent(oldAdminPW) + "&" + g_data_name11 + encodeURIComponent(newAdminPW1) + "&" + g_data_name12 + encodeURIComponent(newAdminPW2);
      
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
               alert(MSG_MODIFY_ADM_EMAIL_SUCCESS);
            }
            else  //failed
            {
               alert(MSG_MODIFY_ADM_EMAIL_ERROR);
            }   
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   });

   // modify white list
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
   
   //submit default extreme type
   $('.btn_submit_new.extreme_confirm').click(function()
   {
      var checkbox = document.getElementsByName("ExtremeCheckbox");
      var uploadMask = 0;	
      var netDisk = 0; 
      var removableDisk = 0;
      var systemScanDirEnabled = 0;
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

      if(document.getElementsByName("uploadMask")[0].checked)
      {
			uploadMask = 0;
      }
      else if (document.getElementsByName("uploadMask")[1].checked)
      {
         uploadMask = 1;
      }
      
      if(document.getElementsByName("scanMode")[0].checked)
         scanMode = 1;
      else if(document.getElementsByName("scanMode")[1].checked)
         scanMode = 0;

      if(document.getElementsByName("netDisk")[0].checked)
         netDisk = 1;
      else
         netDisk = 0;

      if(document.getElementsByName("removableDisk")[0].checked)
         removableDisk = 1;
      else
         removableDisk = 0;

      if(document.getElementsByName("systemScanDirEnabled")[0].checked)
         systemScanDirEnabled = 1;
      else
         systemScanDirEnabled = 0;

      systemScanDirContent=document.getElementsByName("systemScanDirContent")[0].value;
      systemScanDirContent=encodeURI(systemScanDirContent);

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
      
      // check scanTime
      if (parseInt(scanTime, 10) > 5 || parseInt(scanTime, 10) < 1)
      {
         alert(MSG_SCANTIME_ERROR);
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
         
      //ajax for 設定
      str = "cmd=set_default_extreme" + "&defaultExtremeType=" + g_checkbox_str + "&riskTypeNumber=" + riskTypeNumber + "&extremeNumber=" + extremeNumber + 
            "&highNumber=" + highNumber + "&lowNumber=" + lowNumber + "&uploadMask=" + uploadMask + "&netDisk=" + netDisk + "&removableDisk=" + removableDisk +
            "&expressEnable=" + scanMode + "&expressTimeout=" + scanTime; 
      str = str + "&systemScanDirEnabled=" + systemScanDirEnabled + "&systemScanDirContent=" + systemScanDirContent;
      //alert(str);

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

   //brahcnQuery search, added by Phantom, 20140915
   $('.btn_submit_new.branch_query').click(function()
   {
      var range_begin = document.getElementById("from3").value; 
      var range_end = document.getElementById("to3").value; 

      var str;                            //送出資料字串  
      //ajax
      str = "cmd=search" + "&" + 
            "range_begin=" + encodeURIComponent(range_begin) + "&" +
            "range_end=" + encodeURIComponent(range_end); 

      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL14 + str);
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
                  document.getElementById("branchQueryPages").innerHTML = res;
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

   //systemAdmin new, delete, modify, added by Phantom, 20140916
   $('.link_action.modifyAdmin').click(function()
   {
      var cmd = document.getElementsByName("submitAdminAction")[0].value;
      var account = document.getElementsByName("submitAdminAccount")[0].value;
      var password = document.getElementsByName("submitAdminPassword")[0].value;

      cmd = cmd.trim();
      account = account.trim();
      password = password.trim();

      if (cmd == "new" && (account.length < 3 || password.length < 8))
      {
         alert(MSG_SYSTEMADMIN_LEN_ERROR1);
         return;
      }
      len = account.length;

      var pattern=/^[a-zA-Z0-9_\.]*$/;  // #001
      var match=pattern.exec(account);
      if (match == null)
      {
         alert(MSG_SYSTEMADMIN_ACCOUNT_ERROR);
         return;
      }
      /*
      for (i=0; i<len; i++)
      {
         if ((account[i] < 'a' || account[i] > 'z') &&
             (account[i] < 'A' || account[i] > 'Z') &&
             (account[i] < '0' || account[i] > '9'))
         {
            return;
         }
      }
      */
      if (cmd == "modify" && password.length < 8)
      {
         alert(MSG_SYSTEMADMIN_LEN_ERROR2);
         return;
      }
      var str;                            //送出資料字串  
      //ajax
      str = "cmd=" + cmd + "&" + 
            "account=" + encodeURIComponent(account) + "&" +
            "password=" + encodeURIComponent(password); 

      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL21 + str);
         },
         type: 'GET',
         url: D_URL21 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {			
               if (!res.match(/^-\d+$/))  //success
               {
                  document.getElementById("systemAdminMgtPages").innerHTML = res;
                  if (cmd == "delete")
                     alert(MSG_SYSTEMADMIN_DELETE_SUCCESS);
                  else if (cmd == "modify")
                     alert(MSG_SYSTEMADMIN_MODIFY_SUCCESS);
                  else if (cmd == "new")
                     alert(MSG_SYSTEMADMIN_NEW_SUCCESS);
               }
               else  //failed
               {  
                  alert(MSG_MODIFYADM_ERROR);
               }
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   });

   //Customer new, delete, modify (password, validcode, email), added by Phantom, 20140916
   $('.link_action.modifyCustomer').click(function()
   {
      var cmd = document.getElementsByName("submitCustomerAction")[0].value.trim();
      var guid = document.getElementsByName("submitCustomerGUID")[0].value.trim();
      var name = document.getElementsByName("submitCustomerName")[0].value.trim();
      var loginName = document.getElementsByName("submitCustomerLoginName")[0].value.trim();
      var password = document.getElementsByName("submitCustomerPassword")[0].value.trim();
      var validcode = document.getElementsByName("submitCustomerValidcode")[0].value.trim();
      var email = document.getElementsByName("submitCustomerEmail")[0].value.trim();
   
      len = loginName.length;
      if (cmd == "new" && (len < 3 || len > 31))
      {
         alert(MSG_LOGINNAME_LENGTH_ERROR);
         return;
      }
      if (cmd == "new")
      {
         var pattern=/^[a-zA-Z0-9_\.]*$/;  // #001
         var match=pattern.exec(loginName);
         if (match == null)
         {
            alert(MSG_LOGINNAME_LENGTH_ERROR);
            return;
         }
      }
      if (cmd == "new" && name.length <= 0)
      {
         alert(MSG_CUSTOMER_NAME_ERROR);
         return;
      }
      if (cmd == "new" || cmd == "modify_password")
      {
         if (password.length < 8 || password.length > 30)
         {
            alert(MSG_ADMIN_PASSWORD_LENGTH_ERROR);
            return;
         }
      }
      if (cmd == "new" || cmd == "modify_validcode")
      {
         if (validcode.length < 6 || validcode.length > 12)
         {
            alert(MSG_USER_PASSWORD_LENGTH_ERROR);
            return;
         }
      }
      if (cmd == "new" || cmd == "modify_email")
      {
         var emailReg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
         if (emailReg.test(email) == false)
         {
            alert(MSG_EMAIL_ERROR);
            return;
         }
      }
      var str;                            //送出資料字串  
      //ajax
      str = "cmd=" + cmd + "&" + 
            "GUID=" + encodeURIComponent(guid) + "&" +
            "name=" + encodeURIComponent(name) + "&" +
            "loginName=" + encodeURIComponent(loginName) + "&" +
            "password=" + encodeURIComponent(password) + "&" +
            "validcode=" + encodeURIComponent(validcode) + "&" +
            "email=" + encodeURIComponent(email);
      
      //alert(str);
      $('#loadingWrap').show();
      $.ajax
      ({
         beforeSend: function()
         {
            //alert(D_URL22 + str);
         },
         type: 'GET',
         url: D_URL22 + str,
         cache: false,
         success: function(res)
         {
            //alert(res);
            $('#loadingWrap').delay(D_LOADING).fadeOut('slow', function()
            {			
               if (!res.match(/^(<\!DOCTYPE)|(-\d+)$/))  //success
               // Note : if match "<!DOCTYPE" then it is returned by main_adm.php due to session expire, consider it as fail
               {
                  document.getElementById("customerMgtPages").innerHTML = res;
                  if (cmd == "delete")
                     alert(MSG_CUSTOMER_DELETE_SUCCESS);
                  else if (cmd == "modify_password" || cmd == "modify_validcode" || cmd == "modify_email")
                     alert(MSG_CUSTOMER_MODIFY_SUCCESS);
                  else if (cmd == "new")
                     alert(MSG_CUSTOMER_NEW_SUCCESS);
               }
               else  //failed
               {  
                  alert(MSG_MODIFYADM_ERROR);
               }
            });
         },
         error: function(xhr)
         {
            alert("ajax error: " + xhr.status + " " + xhr.statusText);
         }
      });
   });

	//------- Search input hints ------//
	var _input = $('#userSearch');
	
	_input.attr('value', user_searchHint);
	
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
	
	_input_1.attr('value', user_searchHint);
	
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
});

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

function report_msg()
{
   alert("敬告用戶：微軟於八月中所釋出的 Office 安全性更新中，改變了 Excel 巨集相關設定，導致您於 8 月 21 日之前所製作的報表，可能會無法於 Office 中正確開啟；請您重新「產生新的報表」(可選擇一樣的時間區間及條件)，重新「下載報表」後，便可於更新過後的 Microsoft Office 2007 或 2010 正常執行。");
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
