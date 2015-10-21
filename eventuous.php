<?php
/*
Plugin Name: Eventuous
Plugin URI: http://www.eventuo.us
Description: Eventuous Event Engine Plugin.
Version: 0.1
Author: Event Software
Author URI: http://www.eventsoft.com
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

if ( ! class_exists( 'Eventuous' ) ) {
	class Eventuous
	{

		protected $tag = 'eventuous';

		protected $name = 'Eventuous';

		protected $version = '0.1';

		protected $options = array();

		protected $settings = array(
			'Identity_Token' => array(
				'description' => 'Token for your identity.'
			),
			'Access_Token' => array(
				'description' => 'Token for your venue/organization.'
			)
		);

		public function __construct()
		{
			if ( $options = get_option( $this->tag ) ) {
				$this->options = $options;
			}
			add_shortcode( $this->tag, array( &$this, 'shortcode' ) );
			if ( is_admin() ) {
				add_action( 'admin_init', array( &$this, 'settings' ) );
			}
		}

	private function render( $type, $desc, $width, $height, $border, $scroll, $template ) {
		$width  = preg_replace( '/[^0-9%px]/', '', $width );
		$height = preg_replace( '/[^0-9%px]/', '', $height );

		$border = ( $border == 'yes' ) ? 'border: 1px solid #999' : '';

		if ( !in_array( $scroll, array( 'auto', 'yes', 'no' ) ) )
			$scroll = 'no';

		if ($type == 'events') {
			$url = 'http://www.eventuo.us/api/v1/events';
		}

		if (!empty($url)) {
			if ( isset( $_GET['start_date'] ) && !empty( $_GET['start_date'] ) && isset( $_GET['end_date'] ) && !empty( $_GET['end_date'] ) ) {
				$url = $url . '?start_date=' . $_GET['start_date'] . '&' . 'end_date=' . $_GET['end_date'];
			}

		}
		


		ob_start();

		//echo 'URL = '. $url;

		//vars
		$my_access_token = $this->options['Access_Token']; 
		$my_identity_token = $this->options['Identity_Token'];
		$my_token = $my_identity_token . '_' . $my_access_token;  

		// $startdate = DateTime::createFromFormat('d-m-Y', $_GET['start_date']); //get_query_var('start_date') );

		//get json data
		$opts = array(
		  'http'=>array(
		    'method'=>"GET",
		    'header'=>'Authorization: Token token="' . $my_token . '"'
		  )
		);

		$context = stream_context_create($opts);
	    $raw_text = file_get_contents( $url, false, $context); 
	    $json = json_decode($raw_text);
	    
	    //output format

	    if ($template > '') {
	    	$file_path = WP_PLUGIN_DIR . '/eventuous/templates/' . $template . '/';

	    	$output_header = file_get_contents($file_path . 'header.txt');
			$output_footer = file_get_contents($file_path . 'footer.txt');
	    	$output_body   = file_get_contents($file_path . 'body.txt');
	    	$style_body    = file_get_contents($file_path . 'style.css');
	    	include( plugin_dir_path( __FILE__ ) . 'templates/example/settings.php');

	    	echo '<style>';
	    	echo $style_body;
	    	echo '</style>';

	    	//function add_my_styles() {
	    	//	wp_enqueue_style('eventuous-template-style', plugin_dir_url( __FILE__ ) . 'templates/example/style.css' );
	    		//wp_enqueue_style('eventuous-template-style', $file_path . 'style.css');
	    	//}
	    	//add_action( 'wp_enqueue_scripts', 'add_my_styles');

	    	//echo plugin_dir_url( __FILE__ ) . 'templates/example/style.css';


	    } else {
		    $output_body = '<div class="event">[event.name]</div><div class="location">[event.location]</div>';
		    $output_header = '<h3>All My Events</h3>'; 
		    $output_footer = '<h4>Come and see us soon</h4>';		    	
	    }


	    echo '<div class="eventuous-frame">';

	    echo $output_header . "\r\n";


		$fields = array('[event.name]','[event.description]','[event.location]','[event.client]', '[event.startdate]', '[event.longitude]', '[event.latitude]','[event.id]');
	    foreach($json as $obj){
	    	$fieldvalues = array($obj->name, $obj->description, $obj->location, $obj->client, $obj->startdate, $obj->longitude, $obj->latitude, $obj->id);
	    	$output = str_replace($fields, $fieldvalues, $output_body);
	    	echo $output. "\r\n";
	    }

	    echo $output_footer . "\r\n";

	    echo '</div>' . "\r\n";

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;

	}

	public function shortcode( $attrs, $content = null, $code = '' ) {
		//$tit le  = $desc = $url = '';
		$height = '400px';
		$width  = '100%';
		$border = 'no';
		$scrolling = 'no';
		$template = '';

		if ( isset( $attrs['type'] ) ) {
			// New style
			foreach ( array( 'type', 'desc', 'height', 'width', 'border', 'scrolling', 'template' ) AS $attr ) {
				if ( isset( $attrs[$attr] ) )
					$$attr = $attrs[$attr];
			}
		}
		else {
			// Old style
			$type   = $attrs[0];
			//$tit le = $desc = '';

			// if ( isset( $attrs[1] ) )
			// 	$tit le = $attrs[1];

			if ( count( $attrs ) > 1 )
				$desc = implode( ' ', array_slice( $attrs, 2 ) );
		}

		if ( $type )
			return $this->render( $type, $desc, $width, $height, $border, $scrolling, $template );
		return '';
	}

		public function settings()
		{
			$section = 'reading';
			add_settings_section(
				$this->tag . '_settings_section',
				$this->name . ' Settings',
				function () {
					echo '<p>Configuration options for the ' . esc_html( array(&$this, 'get_Name') ) . ' plugin.</p>';
				},
				$section
			);
			foreach ( $this->settings AS $id => $options ) {
				$options['id'] = $id;
				add_settings_field(
					$this->tag . '_' . $id . '_settings',
					$id,
					array( &$this, 'settings_field' ),
					$section,
					$this->tag . '_settings_section',
					$options
				);
			}
			register_setting(
				$section,
				$this->tag,
				array( &$this, 'settings_validate' )
			);
		}

		public function settings_field( array $options = array() )
		{
			$atts = array(
				'id' => $this->tag . '_' . $options['id'],
				'name' => $this->tag . '[' . $options['id'] . ']',
				'type' => ( isset( $options['type'] ) ? $options['type'] : 'text' ),
				'class' => 'regular-text',
				'value' => ( array_key_exists( 'default', $options ) ? $options['default'] : null )
			);
			if ( isset( $this->options[$options['id']] ) ) {
				$atts['value'] = $this->options[$options['id']];
			}
			if ( isset( $options['placeholder'] ) ) {
				$atts['placeholder'] = $options['placeholder'];
			}
			if ( isset( $options['type'] ) && $options['type'] == 'checkbox' ) {
				if ( $atts['value'] ) {
					$atts['checked'] = 'checked';
				}
				$atts['value'] = true;
			}
			array_walk( $atts, function( &$item, $key ) {
				$item = esc_attr( $key ) . '="' . esc_attr( $item ) . '"';
			} );
			?>
			<label>
				<input <?php echo implode( ' ', $atts ); ?> />
				<?php if ( array_key_exists( 'description', $options ) ) : ?>
				<?php esc_html_e( $options['description'] ); ?>
				<?php endif; ?>
			</label>
			<?php
		}

		public function settings_validate( $input )
		{
			$errors = array();
			foreach ( $input AS $key => $value ) {
				if ( $value == '' ) {
					unset( $input[$key] );
					continue;
				}
				$validator = false;
				if ( isset( $this->settings[$key]['validator'] ) ) {
					$validator = $this->settings[$key]['validator'];
				}
				switch ( $validator ) {
					case 'numeric':
						if ( is_numeric( $value ) ) {
							$input[$key] = intval( $value );
						} else {
							$errors[] = $key . ' must be a numeric value.';
							unset( $input[$key] );
						}
					break;
					default:
						 $input[$key] = strip_tags( $value );
					break;
				}
			}
			if ( count( $errors ) > 0 ) {
				add_settings_error(
					$this->tag,
					$this->tag,
					implode( '<br />', $errors ),
					'error'
				);
			}
			return $input;
		}

		protected function _enqueue()
		{
	 // Define the URL path to the plugin...
			$plugin_path = plugin_dir_url( __FILE__ );
	 // Enqueue the styles in they are not already...
			if ( !wp_style_is( $this->tag, 'enqueued' ) ) {
				wp_enqueue_style(
					$this->tag,
					$plugin_path . 'eventuous.css',
					array(),
					$this->version
				);
			}
	 // Enqueue the scripts if not already...
			if ( !wp_script_is( $this->tag, 'enqueued' ) ) {
				wp_enqueue_script( 'jquery' );

	 // Make the options available to JavaScript...
	 			$options = array_merge( array(
					'selector' => '.' . $this->tag
				), $this->options );
				wp_localize_script( $this->tag, $this->tag, $options );
				wp_enqueue_script( $this->tag );
			}
		}

		public function get_Name() {
      		echo $this->name;
    	}

	}
	new Eventuous;
}
