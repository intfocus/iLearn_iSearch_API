//check reportname length
function checklength(ctlid,maxlength) {              
   if ($("#"+ctlid).val().length > maxlength) {
	  $('.maxHint').show();
   }
   else { 
	  $('.maxHint').hide();
   }
}

//Initial load of page
$(document).ready(comCheck);

//Every resize of window
$(window).resize(comCheck);

//Dynamically check comList
function comCheck() {
	if($('#comList').hasClass('hide')) {
		if($('#comList').prop('scrollHeight') > $('#comList').height()) {
			$('.showHide').show();
		}
		else {
			$('.showHide').hide();
		}
		return false;
	}
}


$(function() {
	//show and hide customer list	
	$('#enterpriceList .title').click(function() {
		if($(this).siblings('ul.list').is(':visible')) {
			$(this).siblings('ul.list').hide();
		}
		else {
			$(this).siblings('ul.list').show();
		};
	});
	
	//expand report table
	$('.expandR').click(function() {
		if($('span.rName, span.rItem').hasClass('fixWidth')) {
			$('span.rName, span.rItem').removeClass('fixWidth');
			$(this).text('隱藏過長內文');
		}
		else {
			$('span.rName, span.rItem').addClass('fixWidth');
			$(this).text('顯示過長內文');
		};
	});
	
	
	//enter a company
	$('span.name').click(function() {
		$('span.bLink.company').css('display','inline-block');
		$('#enterpiceReport').show();
		$('#reportW').show();
		$('#enterpriceList').hide();
	});
	$('span.bLink.first').click(function() {
		$('span.bLink.company').css('display','none');
		$('#enterpiceReport').hide();
		$('#reportW').hide();
		$('#enterpriceList').show();
	});
	
	
	//report vHigh and high color
	$('.vHighW, .highW').each(function() {
		if($(this).text() == "0") {
			$(this).css('color','#444');
		}
	});
	
	
	//check reportname length
	$("#reportName").keyup(function(){
		checklength("reportName",15);
	});
	$("#reportName").live('blur',function(){
		checklength("reportName",15);
	});
	
	
	
	//create report
	$("#createReport").click(function() {
		$('#newReport').show();
		$('#reportW').hide();
	});
	$('.btn_submit_new.cancel, .link').click(function() {
		$('#newReport, .reportDetail').hide();
		$('#reportW').show();
	});
	$('.btn_submit_new.confirm').click(function() {
		$('#loadingWrap').show();
		$('#loadingWrap').delay(2000).fadeOut('slow', function() {
			$('#newReport').hide();
			$('#reportW').show();
		});
	});
	//create report
	$("#createDepart, #editDepart").click(function() {
		$('#newDepart').show();
		$('#departW').hide();
	});
	$('.settingW .btn_submit_new.cancel').click(function() {
		$('#newDepart').hide();
		$('#departW').show();
	});
	
	
	
	
	//del report 
	$('.del').click(function() {
		confirm('確定要刪除?');
	});
	
	
	//show hide comList 
	$('.showHide').click(function() {
		if($(this).siblings('#comList').hasClass('hide')) {
			$(this).siblings('#comList').removeClass('hide');
			$(this).text('...隱藏')
		}
		else {			
			$(this).siblings('#comList').addClass('hide');
			$(this).text('...顯示更多')
		}
	});
	
	
	//view report detail
	$('.fixWidth a').click(function() {
		window.open('reportDetail.html', 'Report', 'top=0, left=0, toolbar=no, menubar=no, scrollbars=no, resizable=no,location=n o, status=no');
  		return false;
	});
	
	
	// setting types check
	$('input.types').change(function() {
		if($('input.types:checked').length <= 2){
			$('input.types:checked').attr('disabled', true);
		}
		else {
		  $('input.types').attr('disabled', false);
		}
	});
	
	//setting input check
	$('.hMin').blur(function() {
		if($(this).val() < 1){
			$(this).addClass('error');
		}
		else {
			$(this).removeClass('error');
		}
	});
	
	var $max = $("input.max");
	var $min = $("input.min");	
	$max.add($min).blur(function() {	
		var max_string = $max.val();
		var min_string = $min.val();
		max = parseInt(max_string, 10);
		min = parseInt(min_string, 10);
		
		var valid = min <= max;
		$max.add($min).toggleClass("error", !valid);
	});
	
	
	//switch mainTab 
	$('.mainTabW li').click(function() {
		$(this).addClass('active').siblings('li.active').removeClass('active');
		var cur = $(this).index();
		$('.container').eq(cur).show().siblings().hide();
	});
});