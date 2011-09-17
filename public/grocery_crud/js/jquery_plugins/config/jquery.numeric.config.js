$(function(){
	$('.numeric').numeric();
	$('.numeric').keydown(function(e){
		
		if(e.keyCode == 38)
		{
			if(IsNumeric($(this).val()))
			{
				var new_number = parseInt($(this).val()) + 1;
				$(this).val(new_number);
			}else if($(this).val().length == 0)
			{
				var new_number = 1;
				$(this).val(new_number);
			}
		}
		else if(e.keyCode == 40)
		{
			if(IsNumeric($(this).val()))
			{
				var new_number = parseInt($(this).val()) - 1;
				$(this).val(new_number);
			}else if($(this).val().length == 0)
			{
				var new_number = -1;
				$(this).val(new_number);
			}
		}
		
		
	});
});
function IsNumeric(input)
{
   return (input - 0) == input && input.length > 0;
}
