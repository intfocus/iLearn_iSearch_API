/////////////////////
// combine two datepicker into a daterange
// Created by Billy
// Modification history
// #001 by Odie 2013/02/06
//    Add date3 for 用戶管理
// #002 by Odie 2013/09/12
//    1. 加上to和from互相牽制程式碼的註解
//    2. 加上年份的下拉式選單(changeYear: true)
/////////////////////
$(function() {
	
	//jQuery daterange datepicker
   
	var dates = $( "#from, #to" ).datepicker({
		maxDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
         // 如果自己是from，option就是minDate，如果自己是to，option就是maxDate
			var option = this.id == "from" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
            // date是自己挑中的日期，這裡在做parse
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
         // 如果自己是from，就把to的minDate設成自己的日期(所以to就不能選擇比自己，也就是from還小的日期
         // 如果自己是to，就把from的maxDate設成自己的日期(所以from就不能選擇比自己，也就是to還大的日期
			dates.not( this ).datepicker( "option", option, date );
		}
	});
   
   //$( "#from, #to" ).datepicker();
   
	var dates1 = $( "#from1, #to1" ).datepicker({
		maxDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var option = this.id == "from1" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date1 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates1.not( this ).datepicker( "option", option, date1 );
		}
	});
	var dates2 = $( "#from2, #to2" ).datepicker({
		maxDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var option = this.id == "from2" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date2 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates2.not( this ).datepicker( "option", option, date2 );
		}
	});
	
   var dates = $( "#userDate" ).datepicker({
		maxDate: new Date(),
		defaultDate: "+1w",
		showOn: "both",
		buttonImage: "./images/calendar.gif",
		buttonImageOnly: true
	});

   var dates3 = $( "#from3, #to3" ).datepicker({
		maxDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var option = this.id == "from3" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date3 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates3.not( this ).datepicker( "option", option, date3 );
		}
	});
   
   var dates4 = $( "#from4, #to4" ).datepicker({
		maxDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var option = this.id == "from4" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date4 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates4.not( this ).datepicker( "option", option, date4 );
		}
	});
   
   var dates5 = $( "#from5, #to5" ).datepicker({
		maxDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var option = this.id == "from5" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date5 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates5.not( this ).datepicker( "option", option, date5 );
		}
	});
   
   var dates6 = $( "#from6, #to6" ).datepicker({
		maxDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var option = this.id == "from6" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date6 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates6.not( this ).datepicker( "option", option, date6 );
		}
	});
	
	var dates0 = $( "#from0" ).datepicker({
      minDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      onSelect: function( selectedDate ) {
         var option = "minDate",
            instance = $( this ).data( "datepicker" ),
            date0 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates0.not( this ).datepicker( "option", option, date0 );
      }
   });
   
   var dates7 = $("#from7, #to7" ).datepicker({
      maxDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
      
		onSelect: function( selectedDate ) {
			var option = this.id == "from7" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date7 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates7.not( this ).datepicker( "option", option, date7 );
		}
	});
   

 var dates100 = $("#exam_begin_time, #exam_end_time" ).datepicker({
     minDate: new Date(),
     defaultDate: "+1w",
     changeMonth: true,
     changeYear: true,
     numberOfMonths: 1,

     onSelect: function( selectedDate ) {
        var option = this.id == "exam_begin_time" ? "minDate" : "maxDate",
        instance = $( this ).data( "datepicker" ),
        date7 = $.datepicker.parseDate( instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
        selectedDate, instance.settings );
        dates100.not( this ).datepicker( "option", option, date7 );}
 });




   var dates101 = $("#exam_expire_time").datepicker({
      minDate: new Date(),
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
      
		onSelect: function( selectedDate ) {
			var option = this.id == "exam_expire_time" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date8 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates101.not( this ).datepicker( "option", option, date8 );
		}
	});
   
   var dates20 = $( "#from20, #to20" ).datepicker({
		defaultDate: "+1w",
		changeMonth: true,
      changeYear: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var option = this.id == "from20" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date4 = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates20.not( this ).datepicker( "option", option, date4 );
		}
	});
	
});
