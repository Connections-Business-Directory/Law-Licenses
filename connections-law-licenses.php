<?php
/**
 * An extension for the Connections Business Directory which add a repeatable field for entering the law licenses of a lawyer.
 *
 * @package   Connections Business Directory Law Licenses
 * @category  Extension
 * @author    Steven A. Zahm
 * @license   GPL-2.0+
 * @link      http://connections-pro.com
 * @copyright 2017 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Connections Business Directory Law Licenses
 * Plugin URI:        http://connections-pro.com
 * Description:       An extension for the Connections Business Directory which add a repeatable field for entering the law licenses of a lawyer.
 * Version:           1.0
 * Author:            Steven A. Zahm
 * Author URI:        http://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       connections-law-licenses
 * Domain Path:       /languages
 */

if ( ! class_exists( 'Connections_Law_Licenses' ) ) {

	final class Connections_Law_Licenses {

		const VERSION = '1.0';

		/**
		 * @var string The absolute path this this file.
		 *
		 * @access private
		 * @since 1.0
		 */
		private static $file = '';

		/**
		 * @var string The URL to the plugin's folder.
		 *
		 * @access private
		 * @since 1.0
		 */
		private static $url = '';

		/**
		 * @var string The absolute path to this plugin's folder.
		 *
		 * @access private
		 * @since 1.0
		 */
		private static $path = '';

		/**
		 * @var string The basename of the plugin.
		 *
		 * @access private
		 * @since 1.0
		 */
		private static $basename = '';

		/**
		 * Stores the instance of this class.
		 *
		 * @var $instance Connections_Law_Licenses
		 *
		 * @access private
		 * @static
		 * @since  1.0
		 */
		private static $instance;

		/**
		 * A dummy constructor to prevent the class from being loaded more than once.
		 *
		 * @access public
		 * @since  1.0
		 */
		public function __construct() { /* Do nothing here */ }

		/**
		 * The main plugin instance.
		 *
		 * @access  private
		 * @static
		 * @since   1.0
		 * @return object self
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Connections_Law_Licenses ) ) {

				self::$file       = __FILE__;
				self::$url        = plugin_dir_url( self::$file );
				self::$path       = plugin_dir_path( self::$file );
				self::$basename   = plugin_basename( self::$file );

				self::$instance = new Connections_Law_Licenses;

				// This should run on the `plugins_loaded` action hook. Since the extension loads on the
				// `plugins_loaded action hook, call immediately.
				self::loadTextdomain();

				// Register CSS and JavaScript.
				add_action( 'init', array( __CLASS__ , 'registerScripts' ) );

				// Register the metabox and fields.
				add_action( 'cn_metabox', array( __CLASS__, 'registerMetabox') );

				// Law License uses a custom field type, so let's add the action to add it.
				add_action( 'cn_meta_field-law_licenses', array( __CLASS__, 'field' ), 10, 2 );

				// Since we're using a custom field, we need to add our own sanitization method.
				add_filter( 'cn_meta_sanitize_field-law_licenses', array( __CLASS__, 'sanitize') );

				// Add the business hours option to the admin settings page.
				add_filter( 'cn_content_blocks', array( __CLASS__, 'registerContentBlockOptions') );

				// Add the action that'll be run when calling $entry->getContentBlock( 'law_licenses' ) from within a template.
				add_action( 'cn_output_meta_field-law_licenses', array( __CLASS__, 'block' ), 10, 4 );
			}

			return self::$instance;
		}

		/**
		 * Load the plugin translation.
		 *
		 * Credit: Adapted from Ninja Forms / Easy Digital Downloads.
		 *
		 * @access private
		 * @since  1.0
		 */
		public static function loadTextdomain() {

			// Plugin textdomain. This should match the one set in the plugin header.
			$domain = 'connections-law-licenses';

			// Set filter for plugin's languages directory
			$languagesDirectory = apply_filters( "cn_{$domain}_languages_directory", CN_DIR_NAME . '/languages/' );

			// Traditional WordPress plugin locale filter
			$locale   = apply_filters( 'plugin_locale', get_locale(), $domain );
			$fileName = sprintf( '%1$s-%2$s.mo', $domain, $locale );

			// Setup paths to current locale file
			$local  = $languagesDirectory . $fileName;
			$global = WP_LANG_DIR . "/{$domain}/" . $fileName;

			if ( file_exists( $global ) ) {

				// Look in global `../wp-content/languages/{$domain}/` folder.
				load_textdomain( $domain, $global );

			} elseif ( file_exists( $local ) ) {

				// Look in local `../wp-content/plugins/{plugin-directory}/languages/` folder.
				load_textdomain( $domain, $local );

			} else {

				// Load the default language files
				load_plugin_textdomain( $domain, FALSE, $languagesDirectory );
			}
		}

		public static function registerScripts() {

			// If SCRIPT_DEBUG is set and TRUE load the non-minified JS files, otherwise, load the minified files.
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
			$min = '';

			$requiredCSS = class_exists( 'Connections_Form' ) ? array( 'cn-public', 'cn-form-public' ) : array( 'cn-public' );

			// Register CSS.
			//wp_register_style( 'cnbh-admin' , CNBH_URL . "assets/css/cnbh-admin$min.css", array( 'cn-admin', 'cn-admin-jquery-ui' ) , CNBH_CURRENT_VERSION );
			//wp_register_style( 'cnbh-public', CNBH_URL . "assets/css/cnbh-public$min.css", $requiredCSS, CNBH_CURRENT_VERSION );

			// Register JavaScript.
			//wp_register_script( 'jquery-timepicker' , CNBH_URL . "assets/js/jquery-ui-timepicker-addon$min.js", array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ) , '1.4.3' );
			wp_register_script( 'cnll-ui-js' , self::$url . "assets/js/cnll-common$min.js", array( 'jquery-ui-sortable' ) , self::VERSION, true );

			//wp_localize_script( 'cnbh-ui-js', 'cnbhDateTimePickerOptions', Connections_Business_Hours::dateTimePickerOptions() );
		}

		public static function registerMetabox() {

			$atts = array(
				'id'       => 'law-licences',
				'title'    => __( 'Law Licenses', 'connections-law-licenses' ),
				'context'  => 'normal',
				'priority' => 'core',
				'fields'   => array(
					array(
						'id'    => 'law_licenses',
						'type'  => 'law_licenses',
					),
				),
			);

			cnMetaboxAPI::add( $atts );
		}

		/**
		 * Callback for the `cn_content_blocks` filter.
		 *
		 * Add the custom meta as an option in the content block settings in the admin.
		 * This is required for the output to be rendered by $entry->getContentBlock().
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @param array $blocks An associative array containing the registered content block settings options.
		 *
		 * @return array
		 */
		public static function registerContentBlockOptions( $blocks ) {

			$blocks['law_licenses'] = __( 'Licenses', 'connections-law-licenses' );

			return $blocks;
		}

		public static function field( $field, $value ) {

			// Setup a default value if no licenses exist so the first license row is rendered.
			if ( empty( $value ) ) {

				$value = array(
					array(
						'state'  => '',
						'number' => '',
						'year'   => '',
						'status' => '',
					)
				);
			}

			?>
			<style type="text/css" scoped>
				#cn-law-licenses thead td {
					vertical-align: bottom;
				}
				i.fa.fa-sort {
					cursor: move;
					padding-bottom: 4px;
					padding-right: 4px;
					vertical-align: middle;
				}
				i.cnll-clearable__clear {
					display: none;
					position: absolute;
					right: 0;
					top: 0;
					font-style: normal;
					user-select: none;
					cursor: pointer;
					font-size: 1.5em;
					padding: 0 8px;
				}
				@media screen and ( max-width: 782px ) {
					i.cnll-clearable__clear {
						font-size: 2.15em;
						/*padding: 7px 8px;*/
					}
				}
			</style>
			<table id="cn-law-licenses" data-count="<?php echo count( $value ) ?>">

				<thead>
				<tr>
					<td>&nbsp;</td>
					<td><?php _e( 'State', 'connections-law-licenses' ); ?></td>
					<td><?php _e( 'Number', 'connections-law-licenses' ); ?></td>
					<td><?php _e( 'Year', 'connections-law-licenses' ); ?></td>
					<td><?php _e( 'Status', 'connections-law-licenses' ); ?></td>
					<td><?php _e( 'Add / Remove', 'connections-law-licenses' ); ?></td>
				</tr>
				</thead>

				<!--<tfoot>-->
				<!--<tr>-->
				<!--	<td>--><?php //_e( 'State', 'connections-law-licenses' ); ?><!--</td>-->
				<!--	<td>--><?php //_e( 'Number', 'connections-law-licenses' ); ?><!--</td>-->
				<!--	<td>--><?php //_e( 'Year', 'connections-law-licenses' ); ?><!--</td>-->
				<!--	<td>--><?php //_e( 'Status', 'connections-law-licenses' ); ?><!--</td>-->
				<!--	<td>--><?php //_e( 'Add / Remove', 'connections-law-licenses' ); ?><!--</td>-->
				<!--</tr>-->
				<!--</tfoot>-->

				<tbody>

				<?php foreach ( $value as $license ) : ?>

				<tr class="widget">
					<td><i class="fa fa-sort"></i></td>
					<td>
						<?php

						cnHTML::field(
							array(
								'type'     => 'select',
								'class'    => '',
								'id'       => $field['id'] . '[0][state]',
								'required' => false,
								'label'    => '',
								'before'   => '',
								'after'    => '',
								'options'  => self::getLicenseStateOptions(),
								'return'   => false,
							),
							cnArray::get( $license, 'state', NULL )
						);

						?>
					</td>
					<td>
						<span class="cnll-clearable" style="display: inline-block; position: relative">
						<?php

						cnHTML::field(
							array(
								'type'     => 'text',
								'class'    => 'clearable',
								'id'       => $field['id'] . '[0][number]',
								'style'    => array(
									'box-sizing'    => 'border-box',
									'padding-right' => '24px',
									'width'         => '100%',
								),
								'required' => false,
								'label'    => '',
								'before'   => '',
								'after'    => '',
								'return'   => false,
							),
							cnArray::get( $license, 'number', NULL )
						);

						?>
						<i class="cnll-clearable__clear">&times;</i>
						</span>
					</td>
					<td>
						<?php

						cnHTML::field(
							array(
								'type'     => 'select',
								'class'    => '',
								'id'       => $field['id'] . '[0][year]',
								'required' => false,
								'label'    => '',
								'before'   => '',
								'after'    => '',
								'options'  => self::getLicenseYearOptions(),
								'return'   => false,
							),
							cnArray::get( $license, 'year', NULL )
						);

						?>
					</td>
					<td>
						<?php

						cnHTML::field(
							array(
								'type'     => 'select',
								'class'    => '',
								'id'       => $field['id'] . '[0][status]',
								'required' => false,
								'label'    => '',
								'before'   => '',
								'after'    => '',
								'options'  => self::getLicenseStatusOptions(),
								'return'   => false,
							),
							cnArray::get( $license, 'status', NULL )
						);

						?>
					</td>
					<td>
						<span class="button disabled cnll-remove-license">&ndash;</span><span class="button cnll-add-license">+</span>
					</td>
				</tr>

				<?php endforeach; ?>

				</tbody>
			</table>

			<?php

			// Enqueue the JS required for the metabox.
			wp_enqueue_script( 'cnll-ui-js' );
		}

		/**
		 * Sanitize the times as a text input using the cnSanitize class.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @param array $value
		 *
		 * @return array
		 */
		public static function sanitize( $value ) {

			if ( empty( $value ) ) return $value;

			//var_dump( $value ); die();
			//$licences = array();

			$states = self::getLicenseStateOptions();
			$status = self::getLicenseStatusOptions();

			foreach ( $value as $key => &$licence ) {

				if ( 0 < strlen( $licence['number'] ) ) {

					$licence['state']  = array_key_exists( $licence['state'], $states ) ? $licence['state'] : 'AL';
					$licence['number'] = sanitize_text_field( $licence['number'] );
					$licence['year']   = absint( $licence['year'] );
					$licence['status'] = array_key_exists( $licence['status'], $status ) ? $licence['status'] : 'other';

				} else {

					unset( $value[ $key ] );
				}
			}

			return $value;
		}

		protected static function getLicenseStatusOptions() {

			$status = array(
				'active'     => 'Active',
				'inactivate' => 'Inactivate',
				'other'      => 'Other',
			);

			return apply_filters( 'cnll_law_status_options', $status );
		}

		protected static function getLicenseStateOptions() {

			$states = cnGeo::US_Regions();

			unset(
				$states['AS'],
				$states['CZ'],
				$states['CM'],
				$states['FM'],
				$states['GU'],
				$states['MH'],
				$states['MP'],
				$states['PW'],
				$states['PI'],
				$states['PR'],
				$states['TT'],
				$states['VI'],
				$states['AA'],
				$states['AE'],
				$states['AP']
			);

			return apply_filters( 'cnll_state_options', $states );
		}

		protected static function getLicenseYearOptions() {

			$year  = date( 'Y' );
			$range = range( $year, $year - 100 );
			$years = array_combine( $range, $range );

			return apply_filters( 'cnll_year_options', $years );
		}

		/**
		 * The output of the license data.
		 *
		 * Called by the cn_meta_output_field-law_licenses action in cnOutput->getMetaBlock().
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @param string  $id    The field id.
		 * @param array   $value The license data.
		 * @param cnEntry $object
		 * @param array   $atts  The shortcode atts array passed from the calling action.
		 */
		public static function block( $id, $value, $object = NULL, $atts ) {

			$states   = self::getLicenseStateOptions();
			$statuses = self::getLicenseStatusOptions();
			?>

			<div class="cn-licenses">

			<?php

			foreach ( $value as $key => &$licence ) {

				$state  = array_key_exists( $licence['state'], $states ) ? $states[ $licence['state'] ] : '';
				$status = array_key_exists( $licence['status'], $statuses ) ? $statuses[ $licence['status'] ] : '';

				?>

				<ul class="cn-license">
					<li class="cn-license cn-state"><span class="cn-label"><?php _e( 'State:', 'connections-law-licenses' ) ?></span> <span class="cn-value"><?php echo esc_html( $state ); ?></span></li>
					<li class="cn-license cn-number"><span class="cn-label"><?php _e( 'Number:', 'connections-law-licenses' ) ?></span> <span class="cn-value"><?php echo esc_html( $licence['number'] ); ?></span></li>
					<li class="cn-license cn-year"><span class="cn-label"><?php _e( 'Year:', 'connections-law-licenses' ) ?></span> <span class="cn-value"><?php echo absint( $licence['year'] ); ?></span></li>
					<li class="cn-license cn-status"><span class="cn-label"><?php _e( 'Status:', 'connections-law-licenses' ) ?></span> <span class="cn-value"><?php echo esc_html( $status ); ?></span></li>
				</ul>

				<?php
			}

			?>

			</div>

			<?php
		}
	}

	/**
	 * Start up the extension.
	 *
	 * @access                public
	 * @since                 1.0
	 * @return mixed (object)|(bool)
	 */
	function Connections_Law_Licenses() {

		if ( class_exists( 'connectionsLoad' ) ) {

			return Connections_Law_Licenses::instance();

		} else {

			add_action(
				'admin_notices',
				create_function(
					'',
					'echo \'<div id="message" class="error"><p><strong>ERROR:</strong> Connections must be installed and active in order use Connections Law Licenses.</p></div>\';'
				)
			);

			return FALSE;
		}
	}

	/**
	 * We'll load the extension on `plugins_loaded` so we know Connections will be loaded and ready first.
	 */
	add_action( 'plugins_loaded', 'Connections_Law_Licenses' );
}
