<?php
/*
Plugin Name: Pisz Tu single license
Description: Allows site visitors to publish their articles after payment. 
Text Domain: pwdomain
Domain Path: /
Version: 1.0.1
Author: Konrad "Muzungu" Karpieszuk
Author URI: http://muzungu.pl
*/

global $pwErrors;

/* funkcja wysylajaca debugujące maile
podczas łączenia się niejawnie transferuj.pl
z wtyczką. usun return z początku by zaczęła działać
*/
function pwmail($content) {
return;

list($tytul, $tresc) = explode("/", $content);

$email = get_option('admin_email');

wp_mail($email, $tytul, $tresc);

}


// ładujemy tłumaczenia
add_action('init', 'pw_load_textdomain');
function pw_load_textdomain() {
 load_plugin_textdomain('pwdomain', false, basename( dirname( __FILE__ ) ));

 // _e('', 'pwdomain')
 // __('', 'pwdomain')
 
}


add_action( 'wp_enqueue_scripts', 'pw_add_my_stylesheet' );

function pw_add_my_stylesheet() {
    wp_register_style( 'pw-style', plugins_url('style.css', __FILE__) );
    wp_enqueue_style( 'pw-style' );

    wp_enqueue_script('pw-script', 
    	plugins_url('script.js', __FILE__),
		array('jquery'),
		'',
		true);
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
}

add_action( 'admin_enqueue_scripts', 'pw_admin_enqueue' );

function pw_admin_enqueue($hook) {
    wp_enqueue_script( 'pw_admin_script', plugins_url('/admin.js', __FILE__) );
}


// http://kovshenin.com/2012/the-wordpress-settings-api/

add_action( 'admin_menu', 'pw_admin_menu' );
function pw_admin_menu() {
    add_options_page( 'Pisz Tu', 'Pisz Tu', 'manage_options', 'premium-post', 'premium_post_options_page' );
}

function premium_post_options_page() {
	?>
    <div class="wrap">
        <h2><?php _e('Pisz Tu plugin settings', 'pwdomain') ?></h2>

		<button id="pwHideDescriptions" class="button button-secondary button-small"><?php _e('Hide descriptions', 'pwdomain'); ?></button>

        <form action="options.php" method="POST">
            <?php settings_fields( 'pw-settings-group' ); ?>
            <?php do_settings_sections( 'premium-post' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_init', 'pw_admin_init' );
function pw_admin_init() {
    register_setting( 'pw-settings-group', 'pw-setting', 'pw_setting_sanitize' );
    
    add_settings_section( 'pw-section-one', __('Settings of form page', 'pwdomain'), 'pw_section_one_callback', 'premium-post' );
    add_settings_field( 'field-one', __('Page for publish form', 'pwdomain'), 'pw_field_one_callback', 'premium-post', 'pw-section-one' );
    add_settings_field( 'field-two', __('Terms and conditions', 'pwdomain'), 'pw_field_two_callback', 'premium-post', 'pw-section-one' );

    add_settings_section( 'pw-section-two', __('Settings relative to price', 'pwdomain'), 'pw_section_two_callback', 'premium-post' );
    add_settings_field( 'field-three', __('Base price for publish', 'pwdomain'), 'pw_field_three_callback', 'premium-post', 'pw-section-two' );
    add_settings_field( 'field-four', __('Price for links in text', 'pwdomain'), 'pw_field_four_callback', 'premium-post', 'pw-section-two' );
    add_settings_field( 'field-five', __('Price for additional categories', 'pwdomain'), 'pw_field_five_callback', 'premium-post', 'pw-section-two' );

    add_settings_section( 'pw-section-three', __('Other important settings', 'pwdomain'), 'pw_section_three_callback', 'premium-post' );
    add_settings_field( 'field-six', __('Add article as post type', 'pwdomain'), 'pw_field_six_callback', 'premium-post', 'pw-section-three' );
    add_settings_field( 'field-seven', __('Publisher can connect his post with taxonomy', 'pwdomain'), 'pw_field_seven_callback', 'premium-post', 'pw-section-three' );
    add_settings_field( 'field-eight', __('After payment change post status to', 'pwdomain'), 'pw_field_eight_callback', 'premium-post', 'pw-section-three' );
    add_settings_field( 'field-nine', __('Send remind to author', 'pwdomain'), 'pw_field_nine_callback', 'premium-post', 'pw-section-three' );

	add_settings_section( 'pw-section-image', __('Uploading attachments', 'pwdomain'), 'pw_section_image_callback', 'premium-post' );
	add_settings_field( 'field-image', __('Enable attachments', 'pwdomain'), 'pw_field_image_callback', 'premium-post', 'pw-section-image' );

    add_settings_section( 'pw-section-four', __('Payment service', 'pwdomain'), 'pw_section_four_callback', 'premium-post' );
    add_settings_field( 'field-ten', __('ID in transferuj.pl service', 'pwdomain'), 'pw_field_ten_callback', 'premium-post', 'pw-section-four' );


}

function pw_section_one_callback() {
	// echo "test";
}

function pw_field_one_callback() {
// Strona dodawania wpisu

	$setting = (array) get_option( 'pw-setting' );
    $pageID = esc_attr($setting['pageID']);

	$args = array(
		'numberposts' => -1,
		'post_type' => 'page'
		);
	$strony = get_posts($args);

	?>
	<select name="pw-setting[pageID]">
		<?php foreach ($strony as $strona) {
			$selected = '';
			if ($pageID == $strona->ID) $selected = "selected";
			?>
			<option value="<?php echo $strona->ID; ?>" <?php echo $selected; ?>>
				<?php echo $strona->post_title; ?>
			</option>
			<?php
		}
		?>
	</select>
	<p class="description">
	<?php _e('Choose page where you want to attach post publishing form. If you do not have this page, first you should create it. Tip: form will be attached to the end of the content of this page, so you can write any information about rules of publishing etc. as regular content of this page. ', 'pwdomain'); ?>
	</p>


	<?php
    
}

function pw_field_two_callback() {
// Regulamin dodawania wpisu

	$setting = (array) get_option( 'pw-setting' );
    $terms = esc_attr($setting['terms']);

    ?>
	<textarea name="pw-setting[terms]" style="width: 100%" rows="4"><?php echo $terms; ?></textarea>

	<p class="description">
	<?php _e('Do you have any statues of publishing at your site? Paste it here. After that visitors who want to publish anything at your site will have to set checkbox that they agree to those terms. Content of the statues will be shown after clicking in automatically generated link to it.', 'pwdomain'); ?>
	</p>

    <?php

}

function pw_section_two_callback() {

}

function pw_field_three_callback() {
// Cena publikacji wpisu	

	$setting = (array) get_option( 'pw-setting' );
    $price1 = esc_attr($setting['prices'][1][price]);
    $duration1 = esc_attr($setting['prices'][1][duration]);


    ?>
	<input type="text" name="pw-setting[prices][1][price]" value="<?php echo $price1 ?>">
	<?php _e('PLN for', 'pwdomain'); ?>
	<input type="number" min="1" name="pw-setting[prices][1][duration]" value="<?php echo $duration1 ?>">
    <?php _e('days', 'pwdomain'); ?>
	<p class="description">
	<?php _e('Simply: what is the price for publishing at your website? And how long this text will be visible? When duration pass, post will be changed into draft. If you do not want set duration, just type here huge number of days', 'pwdomain'); ?>
	</p>
    <?php
}

function pw_field_four_callback() {
// Cena odnośników we wpisie

	$setting = (array) get_option( 'pw-setting' );
    $linkprice = esc_attr($setting['linkprice']);

    ?>
	<input type="text" name="pw-setting[linkprice]" value="<?php echo $linkprice ?>">
    <?php _e('PLN for every single link (type 0 if free)', 'pwdomain');
    ?>
    <p class="description">
	<?php _e('Do you want to get more for every link published post? What is the price?', 'pwdomain'); ?>
	</p>
	<?php

}

function pw_field_five_callback() {
// Cena za dodatkowe kategorie

	$setting = (array) get_option( 'pw-setting' );
    $catprice = esc_attr($setting['catprice']);

    ?>
	<input type="text" name="pw-setting[catprice]" value="<?php echo $catprice ?>">
    <?php _e('PLN for every additional category (type 0 if free, first category is always free', 'pwdomain');
    ?>
    <p class="description">
	<?php _e('Publisher can attach post to one category (or other taxonomy, see below) for free. If he want to add next category, this can cost more. Type how much more here.', 'pwdomain'); ?>
	</p>
	<?php
}

function pw_section_three_callback() {

}

function pw_field_six_callback() {
// Publikuj jako typ	

	$setting = (array) get_option( 'pw-setting' );
    $posttype = esc_attr($setting['posttype']);

    $args = array(
    	'public' => true
    	);

    $post_types = get_post_types($args, 'objects');

	?>
	<select name="pw-setting[posttype]">
		<?php foreach ($post_types as $key => $value) {
			$selected = '';
			if ($key == $posttype) $selected = 'selected';
			?>
			<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value->labels->singular_name; ?></option>
			<?php
		}
		?>
	</select>
	<p class="description">
	<?php _e('I think you know, that in WordPress you can publish posts, pages or any other custom post types. Decide here which post type should be used when somebody want to publish article. If you do not understand it, just set post type as Post and it will be probably ok.', 'pwdomain'); ?>
	</p>
	<?php
}

function pw_field_seven_callback() {
// Publikujący wybiera taxonomię

	$setting = (array) get_option( 'pw-setting' );
    $taxonomy = esc_attr($setting['taxonomy']);

    $args = array(
    	'public' => true
    	);

    $taxonomies = get_taxonomies($args, 'objects');

    // na szybko usuwam jednak teraz obsluge innych taksonomii

    // $taxonomies = array();

    // $taxonomies['category'] = __('Category', 'pwdomain');

    ?>
	<select name="pw-setting[taxonomy]">
		<?php foreach ($taxonomies as $key => $value) {
			if ($key == 'post_tag' OR $key == 'post_format') continue;
			$selected = '';
			if ($key == $taxonomy) $selected = 'selected';
			?>
			<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value->labels->singular_name; ?></option>
			<?php
		}
		?>
	</select>
	<p class="description">
	<?php _e('If little above you choosed that published article has post type Post, you should choose here taxonomy Category. If your page uses other post types and other taxonomies, you can choose them here.', 'pwdomain'); ?>
	</p>
	<?php
}

function pw_field_eight_callback() {
// Po dodaniu i opłaceniu zmień status na
	$setting = (array) get_option( 'pw-setting' );
    $setstatus = esc_attr($setting['setstatus']);

    $statusy = array(
    	'publish' => 'Opublikowano',
    	'pending' => 'Oczekuje na przegląd'
    	);
    ?>
	<select name="pw-setting[setstatus]">
		<?php foreach ($statusy as $key => $value) {
			$selected = '';
			if ($key == $setstatus) $selected = 'selected';
			?>
			<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
			<?php
		}
		?>
	</select>
	<p class="description">
	<?php _e('If you really trust your publishers, set status as Publish. If you want to manually review post and after that publish it or not, set status to Pending review.', 'pwdomain'); ?>
	</p>
	<?php
}

function pw_field_nine_callback() {
// Wyślij przypomnienie dodającemu

	$setting = (array) get_option( 'pw-setting' );
    $remind = esc_attr($setting['remind']);

    ?>
	<input type="number" name="pw-setting[remind]" value="<?php echo $remind; ?>"> 
	<?php _e('days before article expiration date', 'pwdomain'); ?>
	<p class="description">
	<?php _e('Publisher can be warned before his post will be set as Draft after publishing duration will be passed.', 'pwdomain'); ?> <?php _e('Type -1 if you do not want to send it', 'pwdomain'); ?>		
	</p>
    <?php
}

function pw_section_image_callback() {

}

function pw_field_image_callback() {
	$setting = (array) get_option( 'pw-setting' );
	$enableAttachment = esc_attr($setting['enableAttachment']);

	?>
	<input type="checkbox" name="pw-setting[enableAttachment]" value="enable" <?php checked('enable', $enableAttachment); ?>
	<p class="description">
		<?php _e('Select if you want to enable attachments upload,', 'pwdomain'); ?>
	</p>
	<?php

}

function pw_section_four_callback() {
	// echo "test";
}

function pw_field_ten_callback() {
	$setting = (array) get_option( 'pw-setting' );
    $transferujID = esc_attr($setting['transferujID']);

    ?>
	<input type="number" name="pw-setting[transferujID]" value="<?php echo $transferujID; ?>"> 
	<p class="description">
	<?php _e('If you do not have Transferuj.pl account,', 'pwdomain'); ?> <a href="https://secure.transferuj.pl/panel/rejestracja.htm" target="_blank"> <?php _e('create it</a> and type acoount ID in this field', 'pwdomain'); ?> 
	<?php _e('This account will collect deposits from publishers. and you will take it out anytime you want.', 'pwdomain'); ?>		
	</p>
    <?php
}

function pw_znumeruj($input) {
	$cena1 = $input;
	$cena1 = trim($cena1);
	$cena1 = str_replace(",", ".", $cena1);
	$cena1 = str_replace(" ", "", $cena1);
	$cena1 = number_format($cena1, 2, ".", "");
	$input = $cena1;
	return $input;
}

function pw_setting_sanitize($input) {
	// cena musi mieć kropkę i dwa miejsca po przecinku
	$input['prices'][1]['price'] = pw_znumeruj($input['prices'][1]['price']);
	$input['linkprice'] = pw_znumeruj($input['linkprice']);
	$input['catprice'] = pw_znumeruj($input['catprice']);

	return $input;
}


add_filter('the_content', 'pw_stronaForm');

function pw_stronaForm($content) {

	global $pwErrors;

	$setting = (array) get_option( 'pw-setting' );
    $pageID = esc_attr($setting['pageID']);

    $price1 = esc_attr($setting['prices'][1][price]);
    $duration1 = esc_attr($setting['prices'][1][duration]);
    $linkprice = esc_attr($setting['linkprice']);
    $catprice = esc_attr($setting['catprice']);
    $taxonomy = esc_attr($setting['taxonomy']);

	if (!is_page($pageID)) return $content;


	$errors = '';

	if (!empty($pwErrors)) $errors .= "<div class='pwerrors'>";

	if ($pwErrors['postTitle']) $errors .= __('Post title is empty <br>', 'pwdomain'); // "Brak tytułu wpisu<br>";

	if ($pwErrors['postContent']) $errors .= __('Post content is empty<br>', 'pwdomain'); // "Brak treści wpisu<br>";

	if ($pwErrors['pwInsertPost']) $errors .= __('Error when tried to save post draft, please try again<br>', 'pwdomain'); // "Nie udało się zapisać szkicu, spróbuj jeszcze raz<br>";

	if ($pwErrors['emailAutora']) $errors .= __('Error with email address<br>', 'pwdomain'); // "Błędny adres email lub brak adresu<br>";

	if ($pwErrors['pwtermsaccept']) $errors .= __('You have to accept terms<br>', 'pwdomain'); // "Musisz zaakceptować regulamin<br>";

	if (!empty($pwErrors)) $errors .= "</div>";

	ob_start();

	wp_editor($_POST['pwpostcontent'], 'pwpostcontent', 
		array(
			'media_buttons' => false,
			'teeny' => true,
			'quicktags' => false
			));

	$editor_contents = ob_get_clean();

    $terms = $setting['terms'];

    if ($terms != '') {
    	$checked = '';
    	if ($_POST['pwtermsaccept'] == 'yes') {
    		$checked = 'checked';
    	}
    	$termsHtml = "
    	<fieldset>
    		<input type='checkbox' name='pwtermsaccept' value='yes' ".$checked."> ".__('I accept', 'pwdomain'). ' <a href="#TB_inline?width=600&inlineId=hiddenTermsContent" class="thickbox">' . __('terms and conditions', 'pwdomain'). "</a>.

    		<div id='hiddenTermsContent' style='display:none'>
    		<p>".nl2br($terms)."</p>
    		</div>
    	</fieldset>
    	";
    }

    $taxonomies = get_terms(
    	array($taxonomy),
    	array(
    		'hide_empty' => false
    		)
    	);

    $menuKategorii = '';

    if (!empty($taxonomies)) {
    	$menuKategorii .= "
    	<fieldset class='categories maincat'>
    		<label>".__('Main category', 'pwdomain')."</label>
	    	<select name='kategoriaGlowna' id='kategoriaGlowna'>";
	    	foreach ($taxonomies as $key => $value) {
	    		$selected = '';
	    		if ($_POST['kategoriaGlowna'] == $value->term_id) $selected = 'selected';
	    		$menuKategorii .= "<option value='".$value->term_id."' ".$selected.">".$value->name."</option>";
	    	}
	    	$menuKategorii .= "</select>
    	</fieldset>
    	<fieldset class='categories seccat'>
    		<label>".__('Second category', 'pwdomain')." <span>".__('(Additional payment', 'pwdomain')." ".$catprice." ".__('PLN)', 'pwdomain')."</span></label>
	    	<select name='kategoriaDruga' id='kategoriaDruga'>";
	    	$menuKategorii .= "<option value='0'>".__('none', 'pwdomain')."</option>";
	    	foreach ($taxonomies as $key => $value) {
	    		$selected = '';
	    		if ($_POST['kategoriaDruga'] == $value->term_id) $selected = 'selected';
	    		$menuKategorii .= "<option value='".$value->term_id."' ".$selected.">".$value->name."</option>";
	    	}
	    	$menuKategorii .= "</select>
    	</fieldset>
    	";
    }

    if (is_user_logged_in() and empty($_POST['emailAutora'])) {
    	global $current_user;
    	$email = $current_user->user_email;
    }
    else {
    	$email = $_POST['emailAutora'];
    }



    $poleimage = '';
	$enableAttachment = esc_attr($setting['enableAttachment']);
	if ($enableAttachment == 'enable') {
		$poleimage = "
	        <fieldset class='image'>
	            <label>".__('Image', 'pwdomain')."</label>
	            <input type='file' name='attachedImage' > 
	        </fieldset>    
	    ";
	}



    $poleemail = "
    	<fieldset class='email'>
    		<label>".__('Email', 'pwdomain')."</label>
    		<input type='email' name='emailAutora' value='".$email."'> 
    	</fieldset>
    ";

	$form = "
<form id='pwDodajWpisForm' method='post' enctype='multipart/form-data'>
	<fieldset>
		<label>".__('Post title', 'pwdomain')."</label>
		<input type='text' name='pwPostTitle' value='".$_POST['pwPostTitle']."'>
	</fieldset>

	<fieldset>
		<label>".__('Content', 'pwdomain')."</label>
		".$editor_contents." 
		<div>".__('Letters', 'pwdomain').": <span id='postcontentchars'></span>, ".__('Links', 'pwdomain').": <span id='postcontentlinks'></span> (".__('Price per link', 'pwdomain').": ".$linkprice." ".__('PLN', 'pwdomain').")</div>
	</fieldset>

	".$menuKategorii."

	".$poleemail."
	
	".$poleimage."

	".$termsHtml."

	<fieldset>
	<div id='totalPrice'>
		".__('Total price:', 'pwdomain')." <span>".$price1."</span> ".__('PLN. Publication for ', 'pwdomain')." ".$duration1." ".__('days', 'pwdomain')."
	</div>
		<input type='submit' name='pwPostSubmit' value='".__('Go to payment', 'pwdomain')."'>
	</fieldset>

</form>
	";

	return $content . $errors . $form;

}

add_action('wp', 'pw_odbierzForm');

function pw_odbierzForm() {

	// odbierz dane,
	// sprawdz dane,
	// oblicz cene,
	// zapisz wpis w bazie
	// dodaj info o autorze, email =
	// dodaj info o dlugosci
	// dodaj info o cenie
	// przekieruj do platnosci

	global $pwErrors;
	$setting = (array) get_option( 'pw-setting' );
	$terms = $setting['terms'];
	$setstatus = esc_attr($setting['setstatus']);
	$duration1 = esc_attr($setting['prices'][1][duration]);
	$transferujID = esc_attr($setting['transferujID']);
	$taxonomy = esc_attr($setting['taxonomy']);



	$idsystemuplatnosci = $transferujID;
	$kwota = policzKwote();

	if (empty($_POST['pwPostSubmit'])) return;

	if (empty($_POST['pwPostTitle'])) {
		$pwErrors['postTitle'] = true;
		
	}

	if (empty($_POST['pwpostcontent'])) {
		$pwErrors['postContent'] = true;
		
	}

    if ($terms != '') {
    	if ($_POST['pwtermsaccept'] != 'yes') {
    		$pwErrors['pwtermsaccept'] = true;
    	}
    }

    if (empty($_POST['emailAutora']) or !is_email($_POST['emailAutora'])) {
    	$pwErrors['emailAutora'] = true;
    }

	if (!empty($pwErrors)) return;

	global $current_user;

	if (is_user_logged_in()) {
		$autor = $current_user->ID;
	}
	else {
		$autor = 1;
	}

	$post = array('post_author' => $autor,
		'post_title' => wp_strip_all_tags($_POST['pwPostTitle']),
		'post_content' => $_POST['pwpostcontent']
	 );

	$idWpisu = wp_insert_post($post);

	if ($idWpisu == 0) {
		$pwErrors['pwInsertPost'] = true;
		return;
	}

	add_post_meta($idWpisu, 'emailpowiadomien', $_POST['emailAutora']);

	add_post_meta($idWpisu, 'cenazawpis', $kwota);

	add_post_meta($idWpisu, 'dlugosc', $duration1);


	// kategorie

	$kategorie = array();

	if (!empty($_POST['kategoriaGlowna'])) $kategorie[] = $_POST['kategoriaGlowna'];

	if ($_POST['kategoriaDruga'] != 0 and !empty($_POST['kategoriaDruga'])) $kategorie[] = $_POST['kategoriaDruga'];

	if (!empty($kategorie)) {
		wp_set_post_terms( $idWpisu, $kategorie, $taxonomy );
	}

	if (!empty($_FILES['attachedImage']) && $setting['enableAttachment'] == 'enable') {

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$file_type = wp_check_filetype($_FILES['attachedImage']['name'], array(
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif'
		));

		if ($file_type['ext']) {
			$attachment_id = media_handle_upload( 'attachedImage', $idWpisu );

			if (!is_wp_error($attachment_id)) {
				add_post_meta($idWpisu, 'idpliku', $attachment_id);
			}
		}

	}


	
	$opis = 'wpis_'.$idWpisu;
	$crc = $idWpisu;
	$kodBezp = '';
	$md5 = md5($idsystemuplatnosci.$kwota.$crc.$kodBezp);
	$wyn_url = urlencode(home_url('/'));
	$pow_url = urlencode(home_url('/') . "?transferuj=powrot");

	header("Location: https://secure.transferuj.pl?id={$idsystemuplatnosci}&kwota={$kwota}&opis={$opis}&crc={$crc}&wyn_url={$wyn_url}&pow_url={$pow_url}&md5sum={$md5}");
}

function policzKwote() {
	$setting = (array) get_option( 'pw-setting' );
	$price1 = esc_attr($setting['prices'][1][price]);
    $catprice = esc_attr($setting['catprice']);
    $linkprice = esc_attr($setting['linkprice']);

    // ilosc linkow
    $ilosclinkow = 0;

    // byc moze zmienic na to
	// http://stackoverflow.com/questions/2295761/count-html-links-in-a-string-and-add-a-list

	preg_match_all('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $_POST['pwpostcontent'], $matches);

	$ilosclinkow = count($matches[0]);

    // ilosc dodatkowych kategorii
    $ilosckategorii = 0;

    if ($_POST['kategoriaDruga'] != 0) {
    	$ilosckategorii++;
    }

    $policzone = $price1 + $linkprice * $ilosclinkow + $catprice * $ilosckategorii;

    $policzone = number_format($policzone, 2, '.', '');

    return $policzone;

}

add_action('publish_post', 'pw_wpis_opublikowany');

function pw_wpis_opublikowany($id) {
	$dlugosc = get_post_meta($id, 'dlugosc', true);

	$setting = (array) get_option( 'pw-setting' );
	$remind = esc_attr($setting['remind']);


	if (is_numeric($dlugosc)) {
		$end = time() + $dlugosc * 24 * 60 * 60;
		add_post_meta($id, 'koniecpublikacji', $end);
		if ($remind >= 0) {
			$przypomnienie = $end - $remind * 24 * 60 * 60;
			add_post_meta($id, 'wyslacprzypomnienie', $przypomnienie);
		}
	}

	$emailpowiadomien = get_post_meta($id, 'emailpowiadomien', true);

	if (is_email($emailpowiadomien)) {
		$temat = __('Your post is online!', 'pwdomain');
		$tresc = __('Your post is published. You can see it here: ', 'pwdomain') .  " " . get_permalink($id);
		wp_mail($emailpowiadomien, $temat, $tresc);
	}

}

add_action('wp', 'odbierzTransferuj'); 

function odbierzTransferuj() {

	$setting = (array) get_option( 'pw-setting' );
	$terms = $setting['terms'];
	$setstatus = esc_attr($setting['setstatus']);
	$duration1 = esc_attr($setting['prices'][1][duration]);

	if ($_SERVER['REMOTE_ADDR']!='195.149.229.109') return; 

	pwmail('Transferuj.pl: just connected/As in title');

	$id_sprzedawcy = $_POST['id'];
	$status_transakcji = $_POST['tr_status'];
	$id_transakcji = $_POST['tr_id'];
	$kwota_transakcji = $_POST['tr_amount'];
	$kwota_zaplacona = $_POST['tr_paid'];
	$blad = $_POST['tr_error'];
	$data_transakcji = $_POST['tr_date'];
	$opis_transakcji = $_POST['tr_desc'];
	$ciag_pomocniczy = $_POST['tr_crc']; // 
	$email_klienta = $_POST['tr_email'];
	$suma_kontrolna = $_POST['md5sum'];


	if($status_transakcji!='TRUE' && $blad!='none'){ 
		echo 'TRUE';
		pwmail('transferuj: status transakcji to nie true a blad to nie none/jak w temacie: ');
		exit();
	}

	$dozaplaty = get_post_meta($ciag_pomocniczy, 'cenazawpis', true);

	if ($kwota_zaplacona < $dozaplaty) {
		echo 'TRUE';
		pwmail('transferuj: zaplacono za malo/jak w temacie: '.$kwota_zaplacona);
		exit();
	}

	$post = array('ID' => $ciag_pomocniczy,
		'post_status' => $setstatus
	 );

	$idWpisu = wp_update_post($post);

	if (!is_wp_error($idWpisu)) {

		echo 'TRUE';

		// wyslij mail o tym do admina

		$temat = __('Payment accepted. Post status changed.', 'pwdomain');

		$tresc = __('Payment service got money, thank you. The new post status is', 'pwdomain') . " " . $setstatus;

		wp_mail(get_option('admin_email'), $temat, $tresc);

		// wyslij mail o tym do dodajacego

		$email = get_post_meta($idWpisu, 'emailpowiadomien', true);

		wp_mail($email, $temat, $tresc);

		pwmail('transferuj: opublikowano wpis/jak w temacie: '.$ciag_pomocniczy);
		exit();
	}

	else {
		echo 'nie da sie zaktualizowac wpisu';
		pwmail('nie da sie zaktualizowac wpisu/jak w temacie: '.$ciag_pomocniczy);
		exit();
	}



}

add_action('wp_head', 'pw_headscript');

function pw_headscript() {
	$setting = (array) get_option( 'pw-setting' );
    $pageID = esc_attr($setting['pageID']);

    $price1 = esc_attr($setting['prices'][1][price]);
    $catprice = esc_attr($setting['catprice']);
    $linkprice = esc_attr($setting['linkprice']);

?>
<script>
var basePrice = <?php echo $price1; ?>;
var linkPrice = <?php echo $linkprice; ?>;
var catPrice = <?php echo $catprice; ?>;
var odnosniki = 0;
var kategorie = 0;

</script>
<?php

}

add_action ('wp_footer', 'pw_transferujPowrot');

function pw_transferujPowrot() {
	if ($_GET['transferuj'] == 'powrot') {
	?>

<div id="transferujPowrot">

	<p>
		<?php
		_e('Thank you for your payment. When payment service will send us transaction status and everything is ok according to our terms and conditions, your post will be published soon. You will be informed about this by email', 'pwdomain');
		?>
	</p>
	<p>
		<?php _e('(Click to close)', 'pwdomain'); ?> 
	</p>

</div>



	<?php
	}
}

add_action('wp', 'pw_zadaniaZaplanowane'); 

function pw_zadaniaZaplanowane() {
	$setting = (array) get_option( 'pw-setting' );
	$posttype = esc_attr($setting['posttype']);
	$pageID = esc_attr($setting['pageID']);

	// wyslij przypomnienia o koncu publikacji

	$args = array(
		'post_type' => $posttype,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => 'wyslacprzypomnienie',
				'value' => time(),
				'compare' => '<'
				)
			)
		);
	$doprzypomnienia = new WP_Query($args);

	$doprzypomnieniawpisy = $doprzypomnienia->posts;

	foreach ($doprzypomnieniawpisy as $value) {
		$czywyslano = get_post_meta($value->ID, 'czywyslano', true);

		if ($czywyslano == 'tak') continue;

		$email = get_post_meta($value->ID, 'emailpowiadomien', true);

		if (!is_email($email)) {
			add_post_meta($value->ID, 'czywyslano', 'tak');
			continue;
		}


		$tytul = __('Your post will be removed soon', 'pwdomain'); // "Twój wpis niedługo zostanie usunięty";
		$tresc = __('Hello,', 'pwdomain')."

".__('Soon your post', 'pwdomain')." '".$value->post_title."' ".__('will be removed', 'pwdomain')."

".__('Do you want to extend publication?', 'pwdomain')."

".__('Go to page', 'pwdomain')." " . get_permalink($pageID) . "".__('and add it again', 'pwdomain')."";

		if (wp_mail($email, $tytul, $tresc)) {
			add_post_meta($value->ID, 'czywyslano', 'tak');
		}


	}


	// zdejmij wpisy przeterminowane

	$args = array(
		'post_type' => $posttype,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => 'koniecpublikacji',
				'value' => time(),
				'compare' => '<'
				)
			)
		);
	$dozdjecia = new WP_Query($args);

	$dozdjeciawpisy = $dozdjecia->posts;

	foreach ($dozdjeciawpisy as $value) {
		$email = get_post_meta($value->ID, 'emailpowiadomien', true);

		wp_update_post(array(
			'ID' => $value->ID,
			'post_status' => 'draft'
			));

		$tytul = __('Your post has beeen removed', 'pwdomain'); // "Twój wpis został zdjęty";
		$tresc = __('Hello,', 'pwdomain')."

".__('Your post', 'pwdomain')." '".$value->post_title."' ".__('is removed.', 'pwdomain')."

".__('Do you want to extend publication?', 'pwdomain')."

".__('Go to page', 'pwdomain')." " . get_permalink($pageID) . "".__('and add it again', 'pwdomain')."

".__('This is content of your post:', 'pwdomain')."

" . $value->post_content . "

".__('(End)', 'pwdomain')."

".__('Thanks for publishing!', 'pwdomain')."
";

		if (wp_mail($email, $tytul, $tresc)) {
			wp_mail(get_option('admin_email'), $tytul, $tresc);
		}


	}


}

add_filter('the_content', 'pw_wstaw_zdjecie');

function pw_wstaw_zdjecie($content) {

	$setting = (array) get_option( 'pw-setting' );

	if ($setting['enableAttachment'] == 'enable') {
		$post_id = get_the_ID();

		$id_pliku = get_post_meta($post_id, 'idpliku', true);

		if ($id_pliku) {
			$image_html = wp_get_attachment_image($id_pliku, 'large');

			$content .= $image_html;
		}

	}

	return $content;
}