<?php
/*
 * Author: Jason Faulkner
 * Company: biNu
 *
 * Created on 13/01/2011
 *
 * Copyright: biNu 13/01/2011 All rights reserved
 *
 * Description: PHP helper Class for constructing biNu pages
 *
 * Disclaimer: this file is provided to assist in developing basic biNu applications.
 * 		It is provided as an educational resource for our developers.
 *
 * Suggestions / Comments : developer@binu.com
 * 
 * Please feel free to fork and improve this script, extend it's functionality, as well as adapt for different scripting languages
 * 
 */

// Helper constants
define('CR_LF',chr(13).chr(10));

/*
 * Time to live constants
 */
define('TTL_TESTING',1);
define('TTL_IMMEDIATE',1);
define('TTL_6_HOURS',21600);
define('TTL_12_HOURS',43200);
define('TTL_24_HOURS',86400);

class biNu_app {
	/*
	 * Developer ID as assigned by DevCentral
	 */
	public $developer_id = 0;

	/*
	 * Application ID as assigned by DevCentral
	 */
	public $application_id = 0;
	public $application_name = 'biNu application';
	public $application_URL = '';

	public $device_id = 0;
	public $device_ip = '0.0.0.0';
	public $binu_client_version = '0.0';
	public $user_agent = 'No UA';

	/*
	 * Width of client screen
	 */
	public $screen_width = 0;

	/*
	 * Height of client screen
	 */
	public $screen_height = 0;

	/*
	 * Height of screen display areas
	 */
	public $header_height = 0;
	public $body_height = 0;
	public $footer_height = 0;

	/*
	 * Indicates if handset is touch or J2ME
	 * Default: FALSE
	 */
	public $touch_client = FALSE;

	/*
	 * Page orientation either 'portrait' or 'landscape'
	 * Default: portrait
	 */
	public $orientation = 'portrait';

	/*
	 * Generic application screen size
	 * Default: S
	 * Optional values:
	 * 		S - small
	 * 		M - medium
	 * 		L - large
	 */
	public $app_size = 'S';

	/*
	 * BML font and display variables
	 * Default: Preset to small screen values
	 */
	public $font_size = 12;
	public $font_color = '#FFFFFFFF';
	public $line_height = 14;
	public $title_indent = 15;
	public $indent = 2;
	public $base_font = 'Arial Unicode MS';

	/*
	 * Time to live, in seconds, tells proxy how long
	 * to cache content before refreshing to client.
	 *
	 * Default: 21600 seconds (ie 6 hours, note TTL constant)
	 */
	public $time_to_live = TTL_TESTING;

	/*
	 * Boolean flag to toggle display of Back link
	 */
	public $show_back_link = TRUE;

	/*
	 * An array of styles
	 */
	public $styles = array();

	/*
	 * An array of actions
	 */
	public $actions = array();

	/*
	 * An array of text to display on the screen
	 */
	public $text_area = array();
	/*
	 * An array of menus
	 */
	public $menus = array();
	/*
	 * An array of list items
	 */
	public $list_items = array();
	/*
	 * Global last error message string
	 */
	 public $function_error_msg = '';

	/*
	 * biNu Markup Language
	 */
	public $BML = '';
	public $header_BML = '';
	public $body_BML = '';
	public $footer_BML = '';
	public $footer_actions_BML = '';
	public $listings_BML = '';

	/*
	 * Utility functions
	 */
	public function xmlentities ( $string ) {
		return str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $string );
	}

	public function handle_error( $error_message ) {
		$this->reset_styles();
		$this->reset_actions();
		$this->reset_header();
		$this->reset_body();
		$this->reset_footer();

		$this->add_style( array( 'name' => 'error_style', 'color' => '#ff000000' ) );
		$this->add_header( $this->application_name, '#ffffffff' );

		$this->body_BML .= '<pageSegment x="0" y="30" w="width">'.CR_LF;
		$this->body_BML .= '	<panning>'.CR_LF;
		$this->body_BML .= '		<text x="2" y="y" w="width" mode="wrap" align="center" style="error_style">'.trim($error_message).'</text>'.CR_LF;
		$this->body_BML .= '		<text x="4" y="y + 4" w="width" mode="wrap" align="left" style="error_style">Key 1. Back</text>'.CR_LF;
		$this->body_BML .= '		<text x="4" y="y" w="width" mode="wrap" align="left" style="error_style">Key 2. Home</text>'.CR_LF;
		$this->body_BML .= '		<text x="4" y="y" w="width" mode="wrap" align="left" style="error_style">Key 3. Exit</text>'.CR_LF;
		$this->body_BML .= '	</panning>'.CR_LF;
		$this->body_BML .= '</pageSegment>'.CR_LF;

		$this->add_footer( 'Restart biNu' );
		// $this->addMenu( 'error_menu', )
		$this->add_action( '1', 'N', 'Back', '', 'back' );
		$this->add_action( '2', 'N', 'Home', '', 'home' );
		$this->add_action( '3', 'N', 'Exit', '', 'exit' );

		return TRUE;
	}
	public function __construct($init_params = array()) {

		// Initialise BML application settings

		if (isset($init_params['dev_id'])) {
			$this->developer_id = intval($init_params['dev_id']);
		}

		if (isset($init_params['app_id'])) {
			$this->application_id = intval($init_params['app_id']);
		}

		if (isset($init_params['app_name'])) {
			$this->application_name = $init_params['app_name'];
		}

		if (isset($init_params['app_home'])) {
			$this->application_URL = $init_params['app_home'];
		}

		if (isset($init_params['ttl'])) {
			$this->time_to_live = intval($init_params['ttl']);
		} else {
			$this->time_to_live = TTL_TESTING;
		}

		// Initialise biNu cookie values and client information
		$this->device_id = (isset($_COOKIE['binusys_device_id']) ? $_COOKIE['binusys_device_id'] : '0000' );
		$this->device_ip = (isset($_COOKIE['binusys_ip_addr']) ? $_COOKIE['binusys_ip_addr'] : ( isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0'));
		$this->binu_client_version = (isset($_COOKIE['binusys_client_version']) ? $_COOKIE['binusys_client_version'] : '0.0');
		$this->user_agent = (isset($_COOKIE['binusys_user_agent']) ? $_COOKIE['binusys_user_agent'] : ( isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'No UA'));

		// Initialise BML font and screen size

		if ( isset($_COOKIE['binusys_size']) ) {
		  $screen_dimensions = explode('x', $_COOKIE['binusys_size']);
		  $this->screen_width = intval($screen_dimensions[0]);
		  $this->screen_height = intval($screen_dimensions[1]);
		} else {
			if ((isset($_COOKIE['screen_width']) AND isset($_COOKIE['screen_height']))
					AND (((intval($_COOKIE['screen_width']) > 0) AND (intval($_COOKIE['screen_height']) > 0)) )) {
				$this->screen_width = intval($_COOKIE['screen_width']);
			 	$this->screen_height = intval($_COOKIE['screen_height']);
		  	} else {
				$this->screen_width = 120;
			  	$this->screen_height = 120;
		  	}
		}

		if ( $this->screen_width * $this->screen_height <= 26000 ) {
			$this->app_size = 'S';
			$this->font_size = 12;
			$this->line_height = 14;
			$this->title_indent = 15;
			$this->indent = 2;
		} elseif ( $this->screen_width * $this->screen_height <= 40000) {
			$this->app_size = 'M';
			$this->font_size = 15;
			$this->line_height = 17;
			$this->title_indent = 18;
			$this->indent = 3;
		} elseif ( $this->screen_width * $this->screen_height <= 100000) {
			$this->app_size = 'L';
			$this->font_size = 18;
			$this->line_height = 20;
			$this->title_indent = 21;
			$this->indent = 3;
		} elseif ( $this->screen_width * $this->screen_height <= 185000) {
			$this->app_size = 'XL';
			$this->font_size = 23;
			$this->line_height = 27;
			$this->title_indent = 21;
			$this->indent = 3;
		} else {
			$this->app_size = 'XXL';
			$this->font_size = 30;
			$this->line_height = 40;
			$this->title_indent = 21;
			$this->indent = 3;
		}

		if (($this->screen_height > 0) AND ($this->screen_width > 0) ) {
			if ($this->screen_width > $this->screen_height) {
				$this->orientation = 'landscape';
			}
			$this->body_height = intval($this->screen_height);
		}
	}

	public function add_style( $new_style = array() ) {
		if (count($new_style) > 0) {
			if (empty($new_style['name'])) {
				// Styles must have a name
				$this->function_error_msg = 'All Styles must have a name';
				return FALSE;
			} else {
				$this->styles[] = $new_style;
			}
		} else {
			$this->function_error_msg = 'Styles is empty';
			return FALSE;
		}
	}

	private function get_styles() {
		$bml = '<styles>'.CR_LF;

		foreach ($this->styles as $style_array) {
			$bml .= '  <style name="'.trim($style_array['name']).'">'.CR_LF;

			// Build style elements
			if (! empty($style_array['color'])) {
				$bml .= '  <color value="'.trim($style_array['color']).'"/>'.CR_LF;
			} else {
				$bml .= '  <color value="'.$this->font_color.'"/>'.CR_LF;
			}
			if (! empty($style_array['font'])) {
				$bml .= '  <font face="'.trim($style_array['font']).'" size="'.((isset($style_array['font_size']) AND (intval($style_array['font_size']) > 0)) ? intval($style_array['font_size']) : $this->font_size).'"/>'.CR_LF;
			} else {
				$bml .= '  <font face="'.$this->base_font.'" size="'.$this->font_size.'"/>'.CR_LF;
			}
			$bml .= '  </style>'.CR_LF;
		}

		$bml .= '</styles>'.CR_LF;
		return trim($bml);
	}

	private function reset_styles() {
		$this->styles = array();
	}

	/*
	 * Function to create BML for header section of page
	 */
	public function add_header( $app_name = '', $background_colour = '#00000000', $height = 0, $width = 0, $text_align = 'center' ) {
		$this->add_style( array( 'name' => 'header_style', 'color' => $background_colour ) );

		if ($height == 0) {
			// 2 pixel padding
			$height = $this->line_height + 4;
		}

		if ($width == 0) {
			$width = $this->screen_width;
		}

		$this->header_BML = '<pageSegment x="0" y="0">'.CR_LF;
		$this->header_BML .= '	<fixed>'.CR_LF;
		$this->header_BML .= '		<rectangle x="0" y="0" h="'.intval($height).'" radius="0" style="header_style"/>'.CR_LF;
		$this->header_BML .= '		<text y="4" w="width" mode="wrap" align="'.$text_align.'" style="header_text">'.(empty($app_name) ? $this->application_name : $app_name).'</text>'.CR_LF;
		$this->header_BML .= '	</fixed>'.CR_LF;
		$this->header_BML .= '</pageSegment>'.CR_LF;

		// Set screen display height values
		$this->header_height = intval($height);
		// We may have already added a footer, so use $this->body_height instead of screen_height
		$this->body_height = ($this->body_height - intval($height));

	}

	private function reset_header() {
		$this->header_BML = '';
	}

	private function reset_body() {
		$this->body_BML = '';
	}

	/*
	 * Function to create BML for footer section of page
	 */
	public function add_footer( $footer_text = '', $background_colour = '#00000000', $height = 0, $width = 0, $text_align = 'center' ) {
		$this->add_style( array( 'name' => 'footer_style', 'color' => $background_colour ) );

		if ($height == 0) {
			// Set Header height to 1/6th of screen height?
			// $height = $this->screen_height / 6;
			$height = $this->line_height + 4;
		}

		if ($width == 0) {
			$width = $this->screen_width;
		}

//		$this->footer_BML = '<pageSegment x="0" y="-'.intval($height).'">'.CR_LF;
//		$this->footer_BML .= '	<fixed>'.CR_LF;
//		$this->footer_BML .= '		<rectangle x="0" y="0" h="'.intval($height).'" radius="0" style="footer_style"/>'.CR_LF;
//
//		if ((! empty($footer_text)) OR (count($this->menus) > 0)) {
//			$this->add_style( array( 'name' => 'footer_text', 'color' => '#ffffffff' ) );
//
//			if (! empty($footer_text)) {
//				$this->footer_BML .= '		<text x="0" y="0" w="'.intval($width).'" mode="wrap" align="'.$text_align.'" style="footer_text">'.trim($footer_text).'</text>'.CR_LF;
//			}
//
//			if (count($this->menus) > 0) {
//				$this->footer_BML .= '		<link key="action" spider="N" actionType="menu" menu="action_menu" x="0" y="0" w="width / 3" align="left">'.CR_LF;
//				$this->footer_BML .= '			<text x="1" y="0" w="width / 3 - 2" mode="truncate" style="footer_text" align="left">Menu</text>'.CR_LF;
//				$this->footer_BML .= '		</link>'.CR_LF;
//				// Following line should be commented out in production
//				// $this->footer_BML .= '			<text x="0" y="0" w="width" mode="truncate" style="footer_text" align="center">('.$this->screen_width.'x'.$this->screen_height.'-'.$this->app_size.')</text>'.CR_LF;
//				if ($this->show_back_link) {
//					// $this->footer_BML .= '		<link key="navigate" spider="Y" actionType="back" x="width * 2/3" y="0" w="width / 3" align="right">'.CR_LF;
//					$this->footer_BML .= '			<text x="width * 2/3" y="0" w="width / 3 - 2" mode="truncate" style="footer_text" align="right">Back</text>'.CR_LF;
//					// $this->footer_BML .= '		</link>';
//				}
//			}
//		}
//		$this->footer_BML .= '	</fixed>'.CR_LF;
//		$this->footer_BML .= '</pageSegment>'.CR_LF;


		// Set screen display height values
		$this->footer_height = intval($height);
		// We may have already added a footer, so use $this->body_height instead of screen_height
		$this->body_height = ($this->body_height - intval($height));
	}

	private function reset_footer() {
		$this->footer_BML = '';
	}

	public function add_text( $text_string = '', $text_style = '', $text_align = 'left', $x_pos = '0', $y_pos = 'y', $mode = 'wrap' ) {
		$this->text_area[] = $text_array = array (
			'text' => $text_string,
			'style' => $text_style,
			'align' => $text_align,
			'x' => $x_pos,
			'y' => $y_pos,
			'mode' => $mode
		);
	}

	private function get_text() {
		if (count($this->text_area) > 0) {
			$the_text_area = '<pageSegment y="y">'.CR_LF;
			$the_text_area .= '	<fixed>'.CR_LF;
			foreach ($this->text_area as $text_section) {
				$the_text_area .= '		<text x="'.$text_section['x'].'" y="'.$text_section['y'].'" w="width" mode="'.$text_section['mode'].'" align="'.$text_section['align'].'" style="'.$text_section['style'].'">'.trim($text_section['text']).'</text>'.CR_LF;
			}
			$the_text_area .= '	</fixed>'.CR_LF;
			$the_text_area .= '</pageSegment>'.CR_LF;

			return trim($the_text_area);
		} else {
			return '';
		}

	}

	public function add_list_item( $list_name, $item_id, $type = 'Field', $name, $value = '', $url = '' ) {
		$this->list_items[$item_id][] = array(
			'list_name' => $list_name,
			'type' => ucfirst($type),
			'name' => $name,
			'value' => $value,
			'url' => $url
		);
	}

	public function get_listings() {
		if (count($this->list_items) > 0) {
			$first_item = TRUE;
			foreach ($this->list_items as $item) {
				if ($first_item) {
					$first_item = FALSE;
					$listings_BML = '<list name="'.$item[0]['list_name'].'">'.CR_LF;
				}
				$listings_BML .= '	<listItem>'.CR_LF;
				foreach ($item as $item_field) {
					$listings_BML .= '		<item'.$item_field['type'].' name="'.$item_field['name'].'" ';

					if (! empty($item_field['url']) AND (strlen(trim($item_field['url'])) > 0)) {
						$listings_BML .= 'url="'.$item_field['url'].'"/>'.CR_LF;
					} else {
						$listings_BML .= 'value="'.$item_field['value'].'"/>'.CR_LF;
					}
				}
				$listings_BML .= '	</listItem>'.CR_LF;
			}
			$listings_BML .= '</list>'.CR_LF;
			return trim($listings_BML);
		} else {
			return '';
		}
	}

	/*
	 * Add an action key
	 */
	public function add_action( $key = '', $spider = 'Y', $text = '', $url = '', $actionType = '', $linkType = '' ) {
		$action_array = array();

		if (! empty($key)) {
			$action_array['key'] = $key;
		}

		$action_array['spider'] = $spider;

		if (! empty($text)) {
			$action_array['text'] = $text;
		}
		if (! empty($url)) {
			$action_array['url'] = htmlspecialchars($url, ENT_COMPAT, 'UTF-8');
		}
		if (! empty($actionType)) {
			$action_array['actionType'] = $actionType;
		}
		if (! empty($linkType)) {
			$action_array['linkType'] = $linkType;
		}
		$this->actions[] = $action_array;
	}

	private function get_actions() {
		$this->footer_actions_BML = '<control textUTF8="true">'.CR_LF;
		$this->footer_actions_BML .= '<footer labelStyle="footer_text" barStyle="footer_style">'.CR_LF;
		// $this->footer_actions_BML .= '	<actions>'.CR_LF;

		// Check for Menus here?
		$bml_actions = '';

		if (count($this->menus) > 0) {
			$bml_actions .= $this->get_menu();

		}

		foreach ($this->actions as $action_array) {
			$bml_actions .= '  <action';
			// Build action elements
			if (! empty($action_array['key'])) {
				$bml_actions .= ' key="'.trim($action_array['key']).'"';
			}
			if (! empty($action_array['spider'])) {
				$bml_actions .= ' spider="'.trim($action_array['spider']).'"';
			} else {
				$bml_actions .= ' spider="Y"';
			}
			if (! empty($action_array['text'])) {
				$bml_actions .= ' text="'.trim($action_array['text']).'"';
			}
			if (! empty($action_array['actionType'])) {
				$bml_actions .= ' actionType="'.trim($action_array['actionType']).'"';
			}
			if (! empty($action_array['linkType'])) {
				$bml_actions .= ' linkType="'.trim($action_array['linkType']).'"';
			}
			$bml_actions .= '>';
			if (! empty($action_array['url'])) {
				$bml_actions .= trim($action_array['url']);
			}
			$bml_actions .= '</action>'.CR_LF;
		}

		$this->footer_actions_BML .= $bml_actions;

		// $this->footer_actions_BML .= '	</actions>'.CR_LF;
		$this->footer_actions_BML .= '</footer>'.CR_LF;
		$this->footer_actions_BML .= '</control>'.CR_LF;
		return trim($this->footer_actions_BML);
	}

	private function reset_actions() {
		$this->actions = array();
	}

	public function add_menu_item( $key = '', $text = '', $url = '', $actionType = ''  ) {
		$menu_array = array();

		if (empty($text)) {
			return FALSE;
		}

		$menu_array['key'] = $key;

		if (! empty($text)) {
			$menu_array['text'] = $text;
		}
		if (! empty($url)) {
			$menu_array['url'] = htmlspecialchars($url, ENT_COMPAT, 'UTF-8');
		} else {
			if (! empty($actionType)) {
				$menu_array['actionType'] = $actionType;
			}
		}

		$this->menus[] = $menu_array;
		if (count($this->menus) == 1) {
			$this->add_footer( '', '#00000000', ($this->line_height + 2), $this->screen_width );
		}
	}

	public function get_menu() {
		$bml_menu = '';
		if (count($this->menus) > 0) {
			$key_counter = 1;
			// $bml_menu .= '<menu name="action_menu" align="Left">'.CR_LF;
			$bml_menu .= '<menu text="Menu" key="action">'.CR_LF;
			foreach ($this->menus as $menu_array) {
				if (! empty($menu_array['key'])) {
					$action_key = intval($menu_array['key']);
				} else {
					$action_key = $key_counter;
					$key_counter++;
				}
				$bml_menu .= '  <action key="'.$action_key.'" ';

				// Build action elements
				if (! empty($menu_array['text'])) {
					$bml_menu .= ' text="'.trim($menu_array['text']).'"';
                                	if (strtolower($menu_array['text']) != 'exit') {
						$bml_menu .= ' spider="Y"';
                                	}
				}
				if (! empty($menu_array['actionType'])) {
					$bml_menu .= ' actionType="'.trim($menu_array['actionType']).'"';
				}
				if (! empty($menu_array['linkType'])) {
					$bml_menu .= ' linkType="'.trim($menu_array['linkType']).'"';
				}
				$bml_menu .= '>';
				if (! empty($menu_array['url'])) {
					$bml_menu .= trim($menu_array['url']);
				}
				$bml_menu .= '</action>'.CR_LF;
			}
			$bml_menu .= '</menu>'.CR_LF;
		}

		return trim($bml_menu);
	}

	private function reset_menu() {
		$this->menus = array();
	}

	/*
	 * This function generates the biNu Markup Language
	 */
	public function generate_BML() {

		// Check there are no errors
		if (! empty($this->function_error_msg)) {
			$this->handle_error($this->function_error_msg);
		}

		// Need to set screen width cookie as biNu Proxy server does not preserve this value
		// setcookie('screen_width', $this->screen_width, time()+86400*356);
		// setcookie('screen_height', $this->screen_height, time()+86400*356);
		setcookie('screen', $this->screen_width.'x'.$this->screen_height, time()+86400*356);
		header('Content-Type: text/xml; charset="utf-8"');
		$this->BML = '<?xml version="1.0" encoding="utf-8"?>'.CR_LF;
		$this->BML .= '<binu ttl="' . $this->time_to_live . '" developer="' . $this->developer_id . '" app="' . $this->application_id . '">' . CR_LF .
			$this->get_styles() . CR_LF .
			'<page>'.CR_LF .
				$this->header_BML . CR_LF .
				$this->body_BML . CR_LF .
				$this->get_text() . CR_LF .
				$this->footer_BML . CR_LF .
			'</page>' . CR_LF .
				$this->get_actions() . CR_LF .
				$this->get_listings() . CR_LF .
		'</binu>';

		// Display page
		echo trim($this->BML);
	}
}

/*
 * This class extends from the biNu base class whilst providing methods and properties only available on text entry screens
 */
class binu_form extends biNu_app {
	public $form_name = 'biNu Form';
	public $form_message = '';
	public $form_URL = '';

	private $text_fields = array();
	private $fields_BML;
	private $form_BML = '';

	/*
	 * Add text entry field to array of fields
	 */
	public function add_field( $name, $value = '', $max_length = 125, $full_screen = 'false', $manditory = 'true', $hide_value = 'false'  ) {
		$fields_array = array();

		if (empty($name)) {
			return FALSE;
		}

		$fields_array['name'] = $name;
		$fields_array['value'] = trim($value);

		if (! empty($max_length)) {
			$fields_array['max_length'] = $max_length;
		}
		if (! empty($full_screen)) {
			$fields_array['full_screen'] = $full_screen;
		}
		if (! empty($manditory)) {
			$fields_array['manditory'] = $manditory;
		}
		if (! empty($hide_value)) {
			$fields_array['hide_value'] = $hide_value;
		}
		$this->text_fields[] = $fields_array;
	}

	private function get_fields() {
		$tmp_bml = '';

		$this->fields_BML = '	<textEntry title="'.parent::xmlentities($this->form_name).'" message="'.parent::xmlentities($this->form_message).'">'.CR_LF;

		foreach ($this->text_fields as $field_array) {
			$tmp_bml .= '		<textEntryField name="'.$field_array['name'].'"';

			// Build textEntryField attributes
			if (! empty($field_array['value'])) {
				$tmp_bml .= ' value="'.trim($field_array['value']).'"';
			}
			if (! empty($field_array['max_length'])) {
				$tmp_bml .= ' maxLength="'.trim($field_array['max_length']).'"';
			}
			if (! empty($field_array['full_screen'])) {
				$tmp_bml .= ' fullScreen="'.trim($field_array['full_screen']).'"';
			}
			if (! empty($field_array['manditory'])) {
				$tmp_bml .= ' manditory="'.trim($field_array['manditory']).'"';
			}
			if (! empty($field_array['hide_value'])) {
				$tmp_bml .= ' hideValue="'.trim($field_array['hide_value']).'"';
			}
			$tmp_bml .= '/>'.CR_LF;
		}

		$this->fields_BML .= $tmp_bml;
		$this->fields_BML .= '	</textEntry>'.CR_LF;
		return trim($this->fields_BML);
	}
	/*
	 * This function generates the biNu Markup Language
	 * for a BML form
	 */
	public function generate_BML() {
		// Need to set screen width cookie as biNu Proxy server does not preserve this value
		// setcookie('screen_width', parent::screen_width, time()+86400*356);
		// setcookie('screen_height', parent::screen_height, time()+86400*356);
		header('Content-Type: text/xml; charset="utf-8"');
		$this->form_BML = '<?xml version="1.0" encoding="utf-8"?>'.CR_LF;
		$this->form_BML .= '<binu ttl="' . $this->time_to_live . '" developer="' . $this->developer_id . '" app="' . $this->application_id . '">' . CR_LF .
			'<page>'.CR_LF .
			'	<pageSegment x="0" y="0">'.CR_LF .
			$this->get_fields() . CR_LF .
			'	</pageSegment>' . CR_LF .
			'</page>' . CR_LF .
			'<control>' . CR_LF .
			'	<actions>' . CR_LF .
			'		<action key="action" spider="N">'.parent::xmlentities($this->form_URL).'</action>' . CR_LF .
			'	</actions>' . CR_LF .
			'</control>' . CR_LF .
		'</binu>';

		// Display page
		echo trim($this->form_BML);
	}

}
?>
