jQuery(document).ready(function($) {
	$('#pwHideDescriptions').click(function() {
		$('.wrap p.description').hide();
		$(this).hide();
	});

});