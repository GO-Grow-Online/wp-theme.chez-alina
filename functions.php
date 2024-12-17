<?php
/**
 * Timber starter-theme
 * https://github.com/timber/starter-theme
 */

// Load Composer dependencies.
require_once __DIR__ . '/vendor/autoload.php';


//////////////////////
///// ACF BLOCKS /////
//////////////////////

// By default, block's styles and scripts are all loaded wether or not the block is loaded.
// This line load scripts and styles ONLY if the block is used on the page.
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

// Register blocks
function register_acf_blocks() {
	// General blocks
	$acf_blocks = array(
		'heading_fp',
	);
	foreach($acf_blocks as $acf_block_name) {
		register_block_type( __DIR__ . '/assets/acf-blocks/' . $acf_block_name );
	}

}
add_action( 'init', 'register_acf_blocks' );




/////////////////////
///// FUNCTIONS /////
/////////////////////

// Call menu ACF block
function insert_menu() {
    $block_id = get_field('menu_id', 'options');
    $block_content = get_post($block_id)->post_content;
	$parsed_blocks = parse_blocks( $block_content );

	if ( $parsed_blocks ) {
		foreach ( $parsed_blocks as $block ) {
			echo render_block( $block );
		}
	}
}



function clean_wysiwyg_content($content) {
    return wp_kses($content, array(
		'h3' => array(),
		'h4' => array(),
		'h5' => array(),
		'h6' => array(),
		'p' => array(),
        'br' => array(),

		'em' => array(),
		'i' => array(),
		'strong' => array(),
		'b' => array(),
		's' => array(),

		'span' => array(
			'class' => array(),
			'id' => array(),
			'style' => array(),
			'data-*' => array(),
		),

		'a' => array(
            'href' => array(),
            'title' => array(),
            'target' => array(),
            'rel' => array(),
            'class' => array(),
            'id' => array(),
            'name' => array(),
            'style' => array(),
            'data-*' => array(),
        ),

        'ul' => array(),
        'ol' => array(),
        'li' => array(),
    ));
}


function acf_wysiwyg_overhaul( $toolbars ) {	

    // Add a new toolbar called "Very Simple"
    $toolbars['Restricted' ] = array();

    // - this toolbar has only 1 row of buttons
    $toolbars['Restricted' ][1] = array(
		'bold' , 
		'italic', 
		'bullist' ,
		'link' ,
	);
	
	// "Add media" button has been hidden in css trough admin-styles.scss

    return $toolbars;
}
add_filter( 'acf/fields/wysiwyg/toolbars' , 'acf_wysiwyg_overhaul'  );



// Remove gutenberg block library CSS from loading on the frontend
function smartwp_remove_wp_block_library_css(){
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
} 
add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );

   
// Add some security to password field
function acf_encrypt_passwords($value, $post_id, $field) {
    $value = wp_hash_password($value);
    return $value;
}
add_filter('acf/update_value/type=password', 'acf_encrypt_passwords', 10, 3);


// Easily get an archive link
function get_archive_link($slug) {
	return get_post_type_archive_link($slug);
}

// Get svg code of an svg file
function get_svg($media_file) {

	$html = "";

	// Allow to get svg even without a ssl certificate
	$context = stream_context_create([
		"ssl" => [
			"verify_peer" => false,
			"verify_peer_name" => false,
		],
	]);	

	if ($media_file) {
		if ($media_file['mime_type'] != "image/svg+xml" && is_user_logged_in()) {
			$html = '<p class="admin-msg">Attached file is not an ".svg" file. Please select an ".svg" file.</p>';
		}else if ($media_file['mime_type'] == "image/svg+xml") {
			$html = file_get_contents($media_file['url'], false, $context);
		}
	}else if (!$media_file && is_user_logged_in()) {
		$html = "<p class='admin-msg'>No file attached.</p>";
	}

    return $html;
}

// Add "reusable" block section in menu
function add_reusable_blocks_admin_menu() {
    add_menu_page( __('Reusable Blocks', 'Non-editable strings'), __('Reusable Blocks', 'Non-editable strings'), 'edit_posts', 'edit.php?post_type=wp_block', '', 'dashicons-editor-table', 22 );
}
add_action( 'admin_menu', 'add_reusable_blocks_admin_menu' );


//////////////////////
///// SHORTCODES /////
//////////////////////


// String replacement shortcodes (mostly for legal pages)
function phone_shortcode($atts) {
	$text = get_field('phone', 'option');
	$text_fallback = $text ? $text : "";
    return $text_fallback;
}
add_shortcode('phone', 'phone_shortcode');


function email_shortcode($atts) {
	$text = get_field('email', 'option');
	$text_fallback = $text ? $text : "";
    return $text_fallback;
}
add_shortcode('email', 'email_shortcode');


function address_shortcode($atts) {
	$text = get_field('address', 'option');
	$text_fallback = $text ? $text : "";
    return $text_fallback;
}
add_shortcode('address', 'address_shortcode');


function company_shortcode($atts) {
	$text = get_field('name', 'option');
	$text_fallback = $text ? $text : "";
    return $text_fallback;
}
add_shortcode('company', 'company_shortcode');



//////////////////////////////////
///// THEME FUNCTIONNALITIES /////
//////////////////////////////////

// Disactivate emojis scripts (styles, cookies and such)
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');


function get_polylang_languages() {
    if (function_exists('pll_the_languages')) {
        $languages = pll_the_languages([
            'raw' => true,
            'show_flags' => 1,
            'show_names' => 1,
            'hide_if_empty' => 0
        ]);
        return $languages;
    }
    return [];
}

// Rendre la fonction accessible dans Timber
add_filter('timber/context', function($context) {
    $context['polylang_languages'] = get_polylang_languages();
    return $context;
});

function get_current_language() {
    if (function_exists('pll_current_language')) {
        return pll_current_language('name'); // 'name' retourne le nom complet, 'slug' retourne le code
    }
    return '';
}

add_filter('timber/context', function($context) {
    $context['current_language'] = get_current_language();
    return $context;
});


add_filter('get_twig', function($twig) {
    $twig->addFunction(new \Twig\TwigFunction('pll', function ($string) {
        return pll__($string);
    }));
    return $twig;
});


// Remove ALL inline styles from front-end
add_action( 'wp_enqueue_scripts', function() {
  // https://github.com/WordPress/gutenberg/issues/36834
  wp_dequeue_style( 'wp-block-library' );
  wp_dequeue_style( 'wp-block-library-theme' );

  // https://stackoverflow.com/a/74341697/278272
  wp_dequeue_style( 'classic-theme-styles' );

  // Or, go deep: https://fullsiteediting.com/lessons/how-to-remove-default-block-styles
} );

add_filter( 'should_load_separate_core_block_assets', '__return_true' ); 

remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );


// Remove ALL scripts from footer when disconnected
function remove_all_scripts() {
    if (!is_admin()) { 
        global $wp_scripts;
        $wp_scripts->queue = array();
    }
}
add_action('wp_enqueue_scripts', 'remove_all_scripts');


// Allow svg files in media
function cc_mime_types($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');


// Give "Editor" role menu edition capability
function give_editor_access_to_menus() {
	$role = get_role( 'editor' );
	$role->add_cap( 'edit_menus' );

	$current_user = wp_get_current_user();
    if (in_array('editor', $current_user->roles)) {
		global $submenu;
		
		// Remove "themes"
        unset($submenu['themes.php'][5]);
		// Remove "customize"
        unset($submenu['themes.php'][6]);
    }
}
add_action( 'admin_init', 'give_editor_access_to_menus' );
 

// Image formats
add_image_size( 'card-thumbnail', 550, 550, false );


// Remove unused image sizes
add_filter('intermediate_image_sizes', function($sizes) {
  return array_diff($sizes, ['medium_large']);  // Medium Large (768 x 0)
  return array_diff($sizes, ['full']);  // Medium Large (768 x 0)
});

function remove_large_image_sizes() {
	remove_image_size( '1536x1536' );  // 2 x Medium Large (1536 x 1536)
	remove_image_size( '2048x2048' );  // 2 x Large (2048 x 2048)
}
add_action( 'init', 'remove_large_image_sizes' );


// Scale original image to limit hosting weight



function redimensionner_image_originale($image_id) {
    // In some cases, this function cause troubles with files such as PDFs, so we check it the uploaded file is an image
    if (wp_attachment_is_image($image_id)) {
        $image_path = get_attached_file($image_id);
        $max_width = 2560;
        $resized = wp_get_image_editor($image_path);

        if (!is_wp_error($resized)) {
            $resized->resize($max_width, 0); // Resize while keeping proportions
            $resized->save($image_path);
        }
    }
}
add_action('add_attachment', 'redimensionner_image_originale');





///////////////////////////////////////////////////
///////// ADMIN CUSTOMIZATION & SCRIPTS ///////////
///////////////////////////////////////////////////

// Admin Wordpress customisation
function custom_login_logo() {
	$logo = !empty(get_field('logo_solo', 'option')) ? get_field('logo_solo', 'option') : get_field('logo', 'option');
    echo '<style type="text/css">
        .login h1 a {
            background-image: url(' . $logo['url'] . ') !important;
            background-size: contain !important;
            width: 100% !important;
        }
    </style>';
}

add_action('login_head', 'custom_login_logo');


// Admin styles
function admin_styles() {
	if (!current_user_can('administrator')) {
		wp_enqueue_style( 'wp-editor-ui', get_template_directory_uri() . '/assets/css/wp-editor-ui.css' );
	} 
}
add_action( 'admin_enqueue_scripts', 'admin_styles' );


// Gutenberg styles & scripts
function gutenberg_assets() {
	if (is_admin()) { 
		// Load general styles and scripts
		wp_enqueue_script( 'splide', get_template_directory_uri() . '/assets/js/api/splide.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'script', get_template_directory_uri() . '/assets/js/app.js', array( 'jquery' ), null, true );
		wp_enqueue_style( 'gutenberg-css', get_template_directory_uri() . '/assets/css/gutenberg-styles.css' );
    }
}
add_action( 'enqueue_block_editor_assets', 'gutenberg_assets' );


// Admin styles
function admin_assets() {
	if (is_admin()) { 
		wp_enqueue_style( 'admin-css', get_template_directory_uri() . '/assets/css/admin-style.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'admin_assets' );





///////////////////////
///// INIT TIMBER /////
///////////////////////

use Timber\Site;

class StarterSite extends Site {
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );

		add_filter( 'timber/context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
		add_filter( 'timber/twig/environment/options', [ $this, 'update_twig_environment_options' ] );

		parent::__construct();
	}

	/**
	 * This is where you add some context
	 *
	 * @param string $context context['this'] Being the Twig's {{ this }}.
	 */
	public function add_to_context( $context ) {
		$context['nav']  = Timber::get_menu();
		$context['g'] = get_fields('options');
		$context['menu'] = !empty(get_fields('options')['categories']) ? get_fields('options')['categories'] : null;
		$context['schedules'] = get_fields('options')['schedules'];
		$context['schedules']['is_closed'] = get_fields('options')['closed'];
		$context['schedules']['is_closed_text'] = get_fields('options')['closed_text'];


		// Cookie consent settings
		$context['cookies_cfg'] = array(
			// Required since consent mode v2
			array('slug' => 'ad_storage', 'function' => "consentGrantedAdStorage", "type" => "marketing", 'active' => true ),
			array('slug' => 'ad_user_data', 'function' => "consentGrantedAdUserData", "type" => "marketing", 'active' => true ),
			array('slug' => 'ad_personalization', 'function' => "consentGrantedAdPersonnalisation", "type" => "marketing", 'active' => true ),
			array('slug' => 'analytics_storage', 'function' => "consentGrantedAnaliticsStorage", "type" => "analytics", 'active' => true ),

			// Additionnal
			array('slug' => 'functionality_storage', 'function' => "consentGrantedFunctionalityStorage", "type" => "necessary", 'active' => true ),
			array('slug' => 'personalization_storage', 'function' => "consentGrantedPersonnalisationStorage", "type" => "necessary", 'active' => true ),
			array('slug' => 'security_storage', 'function' => "consentGrantedSecurityStorage", "type" => "necessary", 'active' => true ),
		);

		// Cookie consent types
		$context['cookies_types'] = array(
			array(
				'slug' => 'necessary', 
				'title' => __("Nécessaire", "Cookie-banner"), 
				'infos' => __("Comprend les cookies relatifs au fonctionnement et à la sécurité du site internet. Sans ces cookies, le site ne pourra pas fonctionner correctement.", 'Cookie-banner'),
			),
			array(
				'slug' => 'analytics', 
				'title' => __("Analitics", "Cookie-banner"), 
				'infos' => __("Permet d'enregistrer le trafic des utilisateurs naviguant sur le site internet.", 'Cookie-banner'),
			),
			array(
				'slug' => 'marketing', 
				'title' => __("Marketing", "Cookie-banner"), 
				'infos' => __("Catégorie de cookies utilisée à des fins purement commerciales et publicitaires.", 'Cookie-banner'),
			),
		);

		$context['site']  = $this;

		return $context;
	}

	public function theme_supports() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		add_theme_support( 'menus' );
	}

	/**
	 * his would return 'foo bar!'.
	 *
	 * @param string $text being 'foo', then returned 'foo bar!'.
	 */
	public function myfoo( $text ) {
		$text .= ' bar!';
		return $text;
	}

	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @param Twig\Environment $twig get extension.
	 */
	public function add_to_twig( $twig ) {
		/**
		 * Required when you want to use Twig’s template_from_string.
		 * @link https://twig.symfony.com/doc/3.x/functions/template_from_string.html
		 */
		// $twig->addExtension( new Twig\Extension\StringLoaderExtension() );

		$twig->addFilter( new Twig\TwigFilter( 'myfoo', [ $this, 'myfoo' ] ) );

		// Get svg for svg files
		$twig->addFunction( new Twig\TwigFunction( 'get_svg', 'get_svg' ) );

		// Clean wysiwyg content and prevent unwanted html tags
		$twig->addFunction( new Twig\TwigFunction( 'clean_wysiwyg_content', 'clean_wysiwyg_content' ) );
		
		// Easily get an archive link
		$twig->addFunction( new Twig\TwigFunction( 'get_archive_link', 'get_archive_link' ) );
		

		return $twig;
	}

	/**
	 * Updates Twig environment options.
	 *
	 * @link https://twig.symfony.com/doc/2.x/api.html#environment-options
	 *
	 * \@param array $options An array of environment options.
	 *
	 * @return array
	 */
	function update_twig_environment_options( $options ) {
	    // $options['autoescape'] = true;

	    return $options;
	}
}
Timber\Timber::init();

// Sets the directories (inside your theme) to find .twig files.
Timber::$dirname = [ 'twig' ];

new StarterSite();
