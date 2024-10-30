$(document).ready(function(){
    $('input[type="radio"]').click(function(){
        if($(this).attr("value")=="Users_list"){
            $(".raido_default").not(".Users_list").hide();
            $(".Users_list").show();
        }
        if($(this).attr("value")=="csv_upload"){
            $(".raido_default").not(".csv_upload").hide();
            $(".csv_upload").show();
        }
        if($(this).attr("value")=="single_email"){
            $(".raido_default").not(".single_email").hide();
            $(".single_email").show();
        }
    });
});



function selectAllFiles(c) {
	var counts = $('.email_count').val();
	
            for (i = 1; i <= counts; i++) {
                document.getElementById('chkFile' + i).checked = c;
            }
        }
		
// JavaScript Document
function incrementCount() {

	document.single_emails.count.value = parseInt(document.single_emails.count.value) + 1;
	addTextBox();
}

function decCount() {
	document.single_emails.count.value = parseInt(document.single_emails.count.value) - 1;
	removeTextBox();
}

function addTextBox() {
	
	var form = document.single_emails;
	var mycount = document.single_emails.count.value;
	
  
	document.getElementById("listtypes").appendChild(document.createElement('li')).innerHTML = "<label></label>Email&nbsp;"+ mycount +": &nbsp;<input type=\"text\" class=\"email_name\" name='email_name" + mycount +"' size='48' required>";
	

	// Show Submit button
	if(mycount >= 1)
	{
		$('.sub_disable').show();
		$('.del_text').show();
		
	} 
	
}
	

function removeTextBox() {
	var form = document.getElementById("listtypes");
	var count_decount = document.single_emails.count.value;

	//ON decCount hide submit 
	if(count_decount == 0)
	{
		$('.sub_disable').hide();
		$('.del_text').hide();
		
	} 
	
	if (form.lastChild.nodeName.toLowerCase() == 'li')
		
		form.removeChild(form.lastChild);
}