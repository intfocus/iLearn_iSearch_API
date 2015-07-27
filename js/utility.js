
function get_checkbox_checked_values(checkbox_name)
{
   var choices = [];
   var els = document.getElementsByName(checkbox_name);
   for (var i=0;i<els.length;i++){
      if ( els[i].checked ) {
         choices.push(els[i].value);
      }
   }
   
   return choices;
}

function output_category_str_from_func_array(func_array)
{
   var output_str = ",";

   for (var i=0; i<func_array.length ; i++)
   {
      if (i == func_array.length - 1)
      {
         output_str = output_str + func_array[i];
      }
      else
      {
         output_str = output_str + func_array[i] + ",,";
      }
   }

   output_str = output_str + ",";
   
   return output_str;
}

function trim_start_end(str)
{
   return str.replace(/^\s+|\s+$/g, "");
}

