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
	
	var dates8 = $("#from8, #to8" ).datepicker({
      minDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      
      onSelect: function( selectedDate ) {
         var option = this.id == "from8" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date8 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates8.not( this ).datepicker( "option", option, date8 );
      }
   });
   
   var dates9 = $("#from9, #to9" ).datepicker({
      minDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      
      onSelect: function( selectedDate ) {
         var option = this.id == "from9" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date9 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates9.not( this ).datepicker( "option", option, date9);
      }
   });
   
   var dates10 = $("#from10, #to10" ).datepicker({
      minDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      
      onSelect: function( selectedDate ) {
         var option = this.id == "from10" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date10 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates10.not( this ).datepicker( "option", option, date10 );
      }
   });
   
   var dates11 = $("#from11, #to11" ).datepicker({
      minDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      
      onSelect: function( selectedDate ) {
         var option = this.id == "from11" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date11 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates11.not( this ).datepicker( "option", option, date11);
      }
   });
   
   var dates12 = $( "#from12, #to12" ).datepicker({
      maxDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      onSelect: function( selectedDate ) {
         var option = this.id == "from12" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date12 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates12.not( this ).datepicker( "option", option, date12 );
      }
   });
   
   var dates13 = $( "#from13, #to13" ).datepicker({
      maxDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      onSelect: function( selectedDate ) {
         var option = this.id == "from13" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date13 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates13.not( this ).datepicker( "option", option, date13 );
      }
   });
   
   var dates14 = $( "#from14, #to14" ).datepicker({
      maxDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      onSelect: function( selectedDate ) {
         var option = this.id == "from14" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date14 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates14.not( this ).datepicker( "option", option, date14 );
      }
   });
   
   var dates15 = $( "#from15, #to15" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      onSelect: function( selectedDate ) {
         var option = this.id == "from15" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date15 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates15.not( this ).datepicker( "option", option, date15 );
      }
   });
   
   var dates16 = $( "#from16, #to16" ).datepicker({
      minDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      onSelect: function( selectedDate ) {
         var option = this.id == "from16" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date16 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates16.not( this ).datepicker( "option", option, date16 );
      }
   });
   
   var dates17 = $( "#from17, #to17" ).datepicker({
      maxDate: new Date(),
      defaultDate: "+1w",
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      onSelect: function( selectedDate ) {
         var option = this.id == "from17" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date17 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates17.not( this ).datepicker( "option", option, date17 );
      }
   });
   
   var dates18 = $( "#from18, #to18" ).datepicker({
      changeMonth: true,
      changeYear: true,
      numberOfMonths: 1,
      onSelect: function( selectedDate ) {
         var option = this.id == "from18" ? "minDate" : "maxDate",
            instance = $( this ).data( "datepicker" ),
            date18 = $.datepicker.parseDate(
               instance.settings.dateFormat ||
               $.datepicker._defaults.dateFormat,
               selectedDate, instance.settings );
         dates18.not( this ).datepicker( "option", option, date18 );
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
          date100 = $.datepicker.parseDate( instance.settings.dateFormat || $.datepicker._defaults.dateFormat,
          selectedDate, instance.settings );
          dates100.not( this ).datepicker( "option", option, date100 );}
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
