function clearForm(theForm) {
	var frm_elements = theForm.elements;
	for(var i = 0; i < frm_elements.length; i++) {
		var field_type = frm_elements[i].type.toLowerCase();
		switch(field_type) {
			case "text":
			case "number":
			case "password":
			case "textarea":
			case "hidden":
				frm_elements[i].value = "";
				break;
			case "radio":
			case "checkbox":
				if (frm_elements[i].checked) {
					frm_elements[i].checked = false;
				}
				break;
			case "select-one":
			case "select-multi":
				frm_elements[i].selectedIndex = 0;
				break;
		}
	}
}

function clearJQueryForm(theForm) {
	var $form = $(theForm);
	$form.find('input:not(:radio , :button , :reset , :submit , :checkbox, :hidden), select, textarea').val('');
	$form.find('.dropdown').dropdown('clear');
	$form.find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
}

// Popups initialization
$(document)
  .ready(function() {
	$('.button')
		.popup()
	;
	$('.message')
		.popup()
	;
	$('.info.circle.icon')
		.popup({
			hoverable: true,
			inline: true,
			lastResort: true
		})
	;
  })
;
