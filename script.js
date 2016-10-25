
jQuery(document).ready(function($) {

	(function liczniki(){
	    
		var wpis = $('#pwpostcontent_ifr').contents().find('#tinymce').text();

		odnosniki = $('#pwpostcontent_ifr').contents().find('#tinymce a').size();

		$('#postcontentchars').html(wpis.length);

		$('#postcontentlinks').html(odnosniki);

		kategorie = 0;

		var drugaKat = $('#kategoriaDruga').val();

		if (drugaKat != 0) kategorie++;

		// alert(odnosniki);

		aktualizujTotal();

	    setTimeout(liczniki, 200);
	})();

	function aktualizujTotal() {

		var totalPrice = basePrice + linkPrice * odnosniki + catPrice * kategorie;
		// alert(linkPrice);

		$('#totalPrice span').html(totalPrice);

	}

	$('#transferujPowrot').click(function() {
		$('#transferujPowrot').css('display', 'none');
	});
});

