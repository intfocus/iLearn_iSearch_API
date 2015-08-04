
function get_checkbox_checked_value(checkbox_name)
{
   var choices = [];
   var els = document.getElementsByName(checkbox_name));
   for (var i=0;i<els.length;i++){
      if ( els[i].checked ) {
         choices.push(els[i].value);
      }
   }
   
   return choices;
}