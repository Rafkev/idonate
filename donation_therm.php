<?php
/*
Plugin Name: idonate
Plugin URI: https://mywphost.com/
Description: Displays the amount raised in donation <code>[thermometer raised=?? target=??]</code>. Shortcodes for raised/target/percentage text values are also available for posts/pages/text widgets: <code>[therm_r]</code> / <code>[therm_t]</code> / <code>[therm_%]</code>.
Version: 0.0.1
Author: RK. Oluwasanmi
Text Domain: idonate-amount
Author URI: https://mywphost.com/
License: GPL3

Copyright 2024  RK. Oluwasanmi  (email : raphaelosanmi@gmail.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

*/

defined('ABSPATH') or die('Access denied');

define('THERMFOLDER', basename( dirname(__FILE__) ) );
define('THERM_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . THERMFOLDER ) ) );
require_once plugin_dir_path(__FILE__) . 'includes/therm_svg.php';
require_once plugin_dir_path(__FILE__) . 'includes/therm_widget_setup.php';

// Specify Hooks/Filters
add_action('admin_init', 'thermometer_init_fn' );
add_action('admin_menu', 'thermometer_add_page_fn');
add_action( 'wp_dashboard_setup', array('Thermometer_dashboard_widget', 'therm_widget_init'));

$thermDefaults = array('colour_picker1'=>'#d7191c', 'colour_picker6'=>'#eb7e80', 'therm_shadow'=>'false', 'therm_filltype'=>'uniform', 'therm_orientation'=>'portrait', 'chkbox1'=>'true', 'colour_picker2'=>'#000000', 'chkbox2'=>'true', 'colour_picker3'=>'#000000', 'chkbox3'=>'true', 'colour_picker4'=>'#000000', 'currency'=>'£','target_string'=>'500', 'raised_string'=>'250', 'thousands'=>', (comma)', 'decsep'=>'. (point)', 'decimals'=>'0', 'trailing'=>'false', 'tick_align'=>'right', 'color_ramp'=>'#d7191c; #fdae61; #abd9e9; #2c7bb6', 'targetlabels'=>'true', 'colour_picker5'=>'#8a8a8a', 'swapValues'=>'0');

$thermDefaultStyle = array('thermometer_svg'=>'', 'therm_target_style'=>'font-size: 16px; font-family: sans-serif;','therm_raised_style'=>'font-size: 14px; font-family: sans-serif;', 'therm_subTarget_style'=>'font-size: 14px; font-family: sans-serif;', 'therm_percent_style'=>'font-family: sans-serif; text-anchor: middle; font-weight: bold;','therm_legend_style'=>'font-size: 12px; font-family: sans-serif;','therm_majorTick_style'=>'stroke-width: 2.5px; stroke: #000;','therm_minorTick_style'=>'stroke-width: 2.5px; stroke: #000;', 'therm_border_style'=>'stroke-width: 1.5px; stroke: #000; fill: transparent;','therm_fill_style'=>'fill: transparent;','therm_arrow_style'=>'stroke: #000; stroke-width: 0.2px; fill: #000;','therm_subArrow_style'=>'stroke: #8a8a8a; stroke-width: 0.2px; fill: #8a8a8a;','therm_raisedLevel_style'=>'stroke-width: 1px; stroke: #000;','therm_subRaisedLevel_style'=>'stroke-width: 1px; stroke: #000;','therm_subTargetLevel_style'=>'stroke-width: 2.5px; stroke: #8a8a8a;');

function set_plugin_meta_dt($links, $file) {
    $plugin = plugin_basename(__FILE__);
    // create link
    if ($file == $plugin) {
        return array_merge(
            $links,
            array( (sprintf( '<a href="options-general.php?page=%s">%s</a>', 'thermometer-settings', __('Settings', 'idonate') ) ),
                (sprintf( '<a href="https://wordpress.org/support/plugin/idonate/" target="_blank">%s</a>', __('Support Forum', 'donation-thermometer') ) ),
                sprintf('<a href="#" target="_blank">%s</a>', __('Buy Raphael a coffee', 'donation-thermometer') ) )
        );
    }
    return $links;
}
add_filter( 'plugin_row_meta', 'set_plugin_meta_dt', 10, 2 );

/*function load_therm_textdomain() {
    $domain = 'donation-thermometer';
    $mo_file = WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . get_locale() . '.mo';

    load_textdomain( $domain, $mo_file );
    load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'admin_init', 'load_therm_textdomain');*/

// Register settings
function thermometer_init_fn(){
    if( false == get_option( 'thermometer_options' ) ) {
        add_option( 'thermometer_options' );
    }
    global $thermDefaults;

    add_settings_section('intro_section', '', 'section_text_fn', 'thermometer_options');

    add_settings_section('thermometer_section', __('Thermometer values', 'donation-thermometer'), '', 'thermometer_options');
    register_setting('thermometer_options', 'thermometer_options', 'thermometer_options_validate' );
    add_settings_field('target_string', __('Target value', 'donation-thermometer'), 'target_string_fn', 'thermometer_options', 'thermometer_section', array( 'default' => $thermDefaults['target_string'], 'type' => 'target_string'));
    add_settings_field('raised_string', __('Raised value', 'donation-thermometer'), 'raised_string_fn', 'thermometer_options', 'thermometer_section', array( 'default' => $thermDefaults['raised_string'], 'type' => 'raised_string'));

    add_settings_section('colours', __('Fill/text colours', 'donation-thermometer'), '', 'thermometer_options');
    add_settings_field('colour_picker1', __('Fill colour', 'donation-thermometer'), 'fill_colour_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['colour_picker1'], 'type' => 'colour_picker1'));
    add_settings_field('therm_filltype', __('Fill style', 'donation-thermometer'), 'therm_filltype_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['therm_filltype'], 'type' => 'therm_filltype'));
    add_settings_field('colour_picker6', __('Second fill colour', 'donation-thermometer'), 'fill2_colour_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['colour_picker6'], 'type' => 'colour_picker6'));
    add_settings_field('colour_picker2', __('Percentage text colour', 'donation-thermometer'), 'text_colour_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['colour_picker2'], 'type' => 'colour_picker2'));
    add_settings_field('colour_picker3', __('Target text colour', 'donation-thermometer'), 'target_colour_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['colour_picker3'], 'type' => 'colour_picker3'));
    add_settings_field('colour_picker5', __('Sub-target text colour', 'donation-thermometer'), 'subtarget_colour_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['colour_picker5'], 'type' => 'colour_picker5'));
    add_settings_field('colour_picker4', __('Raised text colour', 'donation-thermometer'), 'raised_colour_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['colour_picker4'], 'type' => 'colour_picker4'));
    add_settings_field('therm_shadow', __('Thermometer shadow', 'donation-thermometer'), 'therm_shadow_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['therm_shadow'], 'type' => 'therm_shadow'));
    add_settings_field('color_ramp', __('Colour ramp', 'donation-thermometer'), 'color_ramp_fn', 'thermometer_options', 'colours', array( 'default' => $thermDefaults['color_ramp'], 'type' => 'color_ramp'));

    add_settings_section('elements', __('Visible elements', 'donation-thermometer'), '', 'thermometer_options');
    add_settings_field('therm_orientation', __('Thermometer orientation', 'donation-thermometer'), 'therm_orientation_fn', 'thermometer_options', 'elements', array( 'default' => $thermDefaults['therm_orientation'], 'type' => 'therm_orientation'));
    add_settings_field('chkbox1', __('Show percentage', 'donation-thermometer'), 'setting_chk1_fn', 'thermometer_options', 'elements', array( 'default' => $thermDefaults['chkbox1'], 'type' => 'chkbox1'));
    add_settings_field('chkbox2', __('Show target', 'donation-thermometer'), 'setting_chk2_fn', 'thermometer_options', 'elements', array( 'default' => $thermDefaults['chkbox2'], 'type' => 'chkbox2'));
    add_settings_field('targetlabels', __('Show sub-targets', 'donation-thermometer'), 'setting_chk4_fn', 'thermometer_options', 'elements', array( 'default' => $thermDefaults['targetlabels'], 'type' => 'targetlabels'));
    add_settings_field('chkbox3', __('Show amount raised', 'donation-thermometer'), 'setting_chk3_fn', 'thermometer_options', 'elements', array( 'default' => $thermDefaults['chkbox3'], 'type' => 'chkbox3'));
    add_settings_field('tick_align', __('Tick alignment', 'donation-thermometer'), 'tick_align_fn', 'thermometer_options', 'elements', array( 'default' => $thermDefaults['tick_align'], 'type' => 'tick_align'));

    add_settings_section('values', __('Number formatting', 'donation-thermometer'), '', 'thermometer_options');
    add_settings_field('currency', __('Currency', 'donation-thermometer'), 'setting_dropdown_fn', 'thermometer_options', 'values', array( 'default' => $thermDefaults['currency'], 'type' => 'currency'));
    add_settings_field('trailing', __('Currency symbol follows value', 'donation-thermometer'), 'setting_trailing_fn', 'thermometer_options', 'values', array( 'default' => $thermDefaults['trailing'], 'type' => 'trailing'));
    add_settings_field('thousands', __('Thousands separator', 'donation-thermometer'), 'setting_thousands_fn', 'thermometer_options', 'values', array( 'default' => $thermDefaults['thousands'], 'type' => 'thousands'));
    add_settings_field('decsep', __('Decimal separator', 'donation-thermometer'), 'setting_decsep_fn', 'thermometer_options', 'values', array( 'default' => $thermDefaults['decsep'], 'type' => 'decsep'));
    add_settings_field('decimals', __('Decimal places', 'donation-thermometer'), 'setting_decimals_fn', 'thermometer_options', 'values', array( 'default' => $thermDefaults['decimals'], 'type' => 'decimals'));


}

// Add sub-page to the Settings Menu
function thermometer_add_page_fn() {
    $page = add_options_page(__('Thermometer settings','donation-thermometer'), __('Thermometer', 'donation-thermometer'), 'administrator', 'thermometer-settings', 'options_page_fn');
    add_action( 'admin_print_styles-' . $page, 'my_admin_scripts' );
}

function thermometer_help_init() {
    add_settings_section('therm_help_section','','help_section_text_fn','thermometer-help');
}
add_action( 'admin_init', 'thermometer_help_init' );
function thermometer_preview_init() {
    add_settings_section('therm_preview_section','','preview_section_text_fn','thermometer-preview');
}
add_action( 'admin_init', 'thermometer_preview_init' );



function thermometer_style_init() {
    global $thermDefaultStyle;

    if( false == get_option( 'thermometer_style' ) ) {
        add_option( 'thermometer_style' );
    }
    add_settings_section('therm_style_section',__('Default CSS values','donation-thermometer'),'style_section_text_fn','thermometer_style');
    register_setting('thermometer_style', 'thermometer_style', 'thermometer_options_validate' );
    add_settings_field('thermometer_svg', __('SVG image','donation-thermometer').' <code>class="thermometer_svg"</code>', 'svg_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['thermometer_svg'], 'type' => 'thermometer_svg'));
    add_settings_field('therm_target_style', __('Target value','donation-thermometer').' <code>class="therm_target"</code>', 'target_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_target_style'], 'type' => 'therm_target_style'));
    add_settings_field('therm_raised_style', __('Raised value','donation-thermometer').' <code>class="therm_raised"</code>', 'raised_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_raised_style'], 'type' => 'therm_raised_style'));
    add_settings_field('therm_percent_style', __('Percent value','donation-thermometer').' <code>class="therm_percent"</code>', 'percent_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_percent_style'], 'type' => 'therm_percent_style'));
    add_settings_field('therm_subTarget_style', __('Sub-target value','donation-thermometer').' <code>class="therm_subTarget"</code>', 'subTarget_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_subTarget_style'], 'type' => 'therm_subTarget_style'));
    add_settings_field('therm_legend_style', __('Legend entries','donation-thermometer').' <code>class="therm_legend"</code>', 'legend_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_legend_style'], 'type' => 'therm_legend_style'));
    add_settings_field('therm_border_style', __('Border graphic','donation-thermometer').' <code>class="therm_border"</code>', 'border_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_border_style'], 'type' => 'therm_border_style'));
    add_settings_field('therm_fill_style', __('Thermometer background','donation-thermometer').' <code>class="therm_fill"</code>', 'fill_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_fill_style'], 'type' => 'therm_fill_style'));
    add_settings_field('therm_arrow_style', __('Raised arrow','donation-thermometer').' <code>class="therm_arrow"</code>', 'arrow_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_arrow_style'], 'type' => 'therm_arrow_style'));
    add_settings_field('therm_raisedLevel_style', __('Raised bar','donation-thermometer').' <code>class="therm_raisedLevel"</code>', 'raisedLevel_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_raisedLevel_style'], 'type' => 'therm_raisedLevel_style'));
    add_settings_field('therm_subRaisedLevel_style', __('Sub-raised bar','donation-thermometer').' <code>class="therm_subRaisedLevel"</code>', 'subRaisedLevel_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_subRaisedLevel_style'], 'type' => 'therm_subRaisedLevel_style'));
    add_settings_field('therm_subArrow_style', __('Sub-target arrow','donation-thermometer').' <code>class="therm_subArrow"</code>', 'subArrow_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_subArrow_style'], 'type' => 'therm_subArrow_style'));
    add_settings_field('therm_subTargetLevel_style', __('Sub-target bar','donation-thermometer').' <code>class="therm_subTargetLevel"</code>', 'subTargetLevel_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_subTargetLevel_style'], 'type' => 'therm_subTargetLevel_style'));
    add_settings_field('therm_majorTick_style', __('Major ticks','donation-thermometer').' <code>class="therm_majorTick"</code>', 'majorTick_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_majorTick_style'], 'type' => 'therm_majorTick_style'));
    add_settings_field('therm_minorTick_style', __('Minor ticks','donation-thermometer').' <code>class="therm_minorTick"</code>', 'minorTick_style_fn', 'thermometer_style', 'therm_style_section', array( 'default' => $thermDefaultStyle['therm_minorTick_style'], 'type' => 'therm_minorTick_style'));
}
add_action( 'admin_init', 'thermometer_style_init' );


// Define default option settings when activate
function therm_activation() {
    set_transient( 'therm_activation_notice', true, 5 );
}
//add_action('admin_notices', 'therm_shortcode_notice');


/*function therm_deactivation(){
}
*/
function therm_uninstall(){
    delete_option('thermometer_options');
    delete_option('thermometer_style');
}

//register_activation_hook(__FILE__, 'therm_activation');
//register_deactivation_hook(__FILE__, 'therm_deactivation');
register_uninstall_hook(__FILE__, 'therm_uninstall');

function my_admin_scripts() {
    wp_enqueue_style( 'farbtastic' );
    wp_enqueue_script( 'farbtastic' );
    $coloursjs = plugin_dir_url( __FILE__ ) . 'colours.js?v=0.2';
    wp_enqueue_script( 'options_page_fn', $coloursjs , array( 'farbtastic', 'jquery' ) );
}

function remove_footer_admin () {
    printf(/*translators: 1: hyperlinked ***** text */__('If you have found this plugin useful please consider helping spread the word by leaving a %s rating. Thanks in advance!', 'donation-thermometer'),'<a href="https://wordpress.org/support/plugin/donation-thermometer/reviews/" style="text-decoration: none;">★★★★★</a>');
}

if (!is_admin())
    add_filter('widget_text', 'do_shortcode', 11);

// ************************************************************************************************************


// Display the admin options page
function options_page_fn() {
    require_once plugin_dir_path(__FILE__) . 'includes/therm_settings.php';

    echo '<div class="wrap">
        <div class="icon32" id="icon-options-general"><br></div>';

    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';

    echo '<h1>'.__('Donation Thermometer settings','donation-thermometer').'</h1>';
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a class="nav-tab '.($active_tab == 'settings' ? 'nav-tab-active' : '').'" href="'.admin_url('options-general.php?page=thermometer-settings&tab=settings').'">'.__('Settings','donation-thermometer').'</a>';
    echo '<a class="nav-tab '.($active_tab == 'style' ? 'nav-tab-active' : '').'" href="'.admin_url('options-general.php?page=thermometer-settings&tab=style').'">'.__('Custom CSS','donation-thermometer').'</a>';
    echo '<a class="nav-tab '.($active_tab == 'preview' ? 'nav-tab-active' : '').'" href="'.admin_url('options-general.php?page=thermometer-settings&tab=preview').'">'.__('Preview','donation-thermometer').'</a>';
    echo '<a class="nav-tab '.($active_tab == 'help' ? 'nav-tab-active' : '').'" href="'.admin_url('options-general.php?page=thermometer-settings&tab=help').'">'.__('Help','donation-thermometer').'</a>';
    echo '</h2>';

    if( $active_tab == 'settings' ) {
        echo '<form action="options.php" method="post">';
        settings_fields('thermometer_options');
        do_settings_sections('thermometer_options');
        submit_button();
        add_filter('admin_footer_text', 'remove_footer_admin');
        echo '</form>';
    }
    elseif ( $active_tab == 'style' ) {
        echo '<form action="options.php" method="post">';
        settings_fields( 'thermometer_style' );
        do_settings_sections( 'thermometer_style' );
        submit_button();
        add_filter('admin_footer_text', 'remove_footer_admin');
        echo '</form>';
    }
    elseif ( $active_tab == 'preview' ) {
        settings_fields( 'thermometer-preview' );
        do_settings_sections( 'thermometer-preview' );
        add_filter('admin_footer_text', 'remove_footer_admin');
    }
    else{
        settings_fields( 'thermometer-help' );
        do_settings_sections( 'thermometer-help' );
        add_filter('admin_footer_text', 'remove_footer_admin');
    }
    echo '</div>';
}

require_once plugin_dir_path(__FILE__) . 'includes/therm_shortcode.php';

function hexValidate($hex_color) {
    if( preg_match('/^#[a-f0-9]{6}$/i', $hex_color) ){
        return true;
    }
    else{
        return false;
    }
}

// Validate user data
function thermometer_options_validate($input) {
    $output = array();
    global $thermDefaults;

    foreach( $input as $key => $value ) {
        if( isset( $value ) ) {
            if ( substr($key,0,13) === 'colour_picker'){
                if ( hexValidate( $value ) ){
                    $output[$key] = strip_tags( stripslashes( $value ) );
                }
                else{
                    $output[$key] = $thermDefaults[$key];
                }
            }
            else{
                $output[$key] = strip_tags( stripslashes( $value ) );
            }
        }
    }

    if ($input['decimals']){
        if (! (is_numeric($input['decimals']))){
            $output['decimals'] = $thermDefaults['decimals'];
        }
    }

    return apply_filters( 'thermometer_options_validate', $output, $input );
}

/* Display a notice that can be dismissed */

/*function therm_shortcode_notice() {
    if( get_transient( 'therm_activation_notice' ) ){
        ?>
        <div class="notice-info notice is-dismissible"><p>
        <p>Thanks for upgrading to the latest version of the Donation Thermometer plugin.
            This major update brings many new features, including a switch from raster to vector-based (SVG) graphics,
            options for multiple categories/targets, and a new shortcode (<code>therm_&#37;</code>).
            These changes will help your pages load faster and provide a better-looking thermometer image.</p>
        <p>Check out the help section on the

            <?php echo sprintf( '<a href="options-general.php?page=%s">%s</a>', 'thermometer-settings', __('settings'));?>

            page for more info.
            Technical issues can be raised with me at the <a href="https://wordpress.org/support/plugin/donation-thermometer" target="_blank">
                plugin help forum</a>.</p>
        </p></div><?php
        delete_transient( 'therm_activation_notice' );
    }
}*/
