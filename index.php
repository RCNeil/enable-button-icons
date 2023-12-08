<?php 
/**
 * Plugin Name:         Enable Button Icons
 * Plugin URI:          https://rcneil.com
 * Description:         Add icons to Button blocks. Based off Nick Diego's (@ndiego) original plugin example
 * Version:             1.0
 * Requires at least:   6.3
 * Requires PHP:        7.4
 * Author:              RCNeil
 * Author URI:          https://rcneil.com
 * License:             GPLv2
 * License URI:         https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:         enable-button-icons
 *
 * @package enable-button-icons
 */


//GET SVG ICONS AND THEIR MARKUP FROM DIRECTORY 
function generate_icons_data() {
	$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
    $directory = $plugin_path . '/icons';
    $svgFiles = glob("$directory/*.svg");
    $icons = [];

    foreach ($svgFiles as $svgFile) {
        $filename = pathinfo($svgFile, PATHINFO_FILENAME);
        $svgMarkup = file_get_contents($svgFile);
        $icons[$filename] = $svgMarkup;
    }
	
    return $icons;
}


//ENQUEUE EDITOR ASSETS AND GET ICON DATA TO LOCALIZE FOR SCRIPTS.js
function enable_button_icons_enqueue_block_editor_assets() {
    $iconsData = generate_icons_data();
	
	$plugin_path = untrailingslashit( plugin_dir_url( __FILE__ ) );

    wp_enqueue_script(
        'enable-button-icons-editor-scripts',
        $plugin_path . '/scripts.js',
        array('react', 'wp-block-editor', 'wp-components', 'wp-hooks', 'wp-i18n', 'wp-primitives'),
        '1.1'
    );
    // Pass the icon data to the script
    wp_localize_script('enable-button-icons-editor-scripts', 'iconData', $iconsData);

    wp_enqueue_style(
        'enable-button-icons-editor-styles',
        $plugin_path . '/editor.css'
    );
	
	wp_set_script_translations(
        'enable-button-icons-editor-scripts',
        'enable-button-icons',
        $plugin_path . '/languages'
    );
}
add_action('enqueue_block_editor_assets', 'enable_button_icons_enqueue_block_editor_assets');


//ENQUEUE BLOCK STYLES FOR BOTH FRONTEND/BACKEND
function enable_button_icons_block_styles() {
	$plugin_path = untrailingslashit( plugin_dir_url( __FILE__ ) );
	
    wp_enqueue_block_style(
        'core/button',
        array(
            'handle' => 'enable-button-icons-block-styles',
            'src'    => $plugin_path . '/style.css',
            'ver'    => wp_get_theme()->get( 'Version' ),
            'path'   => $plugin_path . '/style.css',
        )
    );
}
add_action( 'init', 'enable_button_icons_block_styles' );

// RENDER ICON ON THE FRONTEND
function enable_button_icons_render_block_button( $block_content, $block ) {
    if ( ! isset( $block['attrs']['icon'] ) ) {
		return $block_content;
	}

    $icon         = $block['attrs']['icon'];
    $positionLeft = isset( $block['attrs']['iconPositionLeft'] ) ? $block['attrs']['iconPositionLeft'] : false;
    
	//GET ICONS
	$icons = generate_icons_data();

    // Make sure the selected icon is in the array, otherwise bail.
    if ( ! array_key_exists( $icon, $icons ) ) {
        return $block_content;
    }

    // Append the icon class to the block.
    $p = new WP_HTML_Tag_Processor( $block_content );
    if ( $p->next_tag() ) {
        $p->add_class( 'has-icon__' . $icon );
    }
    $block_content = $p->get_updated_html();

    // Add the SVG icon either to the left of right of the button text.
    $block_content = $positionLeft 
        ? preg_replace( '/(<a[^>]*>)(.*?)(<\/a>)/i', '$1<span class="wp-block-button__link-icon" aria-hidden="true">' . $icons[ $icon ] . '</span>$2$3', $block_content )
        : preg_replace( '/(<a[^>]*>)(.*?)(<\/a>)/i', '$1$2<span class="wp-block-button__link-icon" aria-hidden="true">' . $icons[ $icon ] . '</span>$3', $block_content );

	return $block_content;
}
add_filter( 'render_block_core/button', 'enable_button_icons_render_block_button', 10, 2 );


//WRITE CSS FOR BACKEND EDITOR
function enhance_button_icons_backend_css() {
    // Check if in the admin area and on the block editor screen
    if (is_admin() && function_exists('get_current_screen') && get_current_screen()->base === 'post') {
		$icons = generate_icons_data();
		//Loop through each icon to write the correct CSS for the mask-image
        ?>
        <style type="text/css">
			<?php foreach ($icons as $key => $value) { ?>
            .wp-block-button[class*=has-icon__].has-icon__<?php echo $key; ?> .wp-block-button__link::after, 
			.wp-block-button[class*=has-icon__].has-icon__<?php echo $key; ?> .wp-block-button__link::before {
				-webkit-mask-image:url(<?php echo svgToDataUri($value); ?>);
				mask-image:url(<?php echo svgToDataUri($value); ?>);				
			}
			<?php } ?>
        </style>
        <?php
    }
}
add_action('admin_head', 'enhance_button_icons_backend_css');


function svgToDataUri($svgContent) {
    $base64Encoded = base64_encode($svgContent);
    $dataUri = 'data:image/svg+xml;base64,' . $base64Encoded;
    return $dataUri;
}



?>