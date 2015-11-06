<?php
/**
 * Plugin Name: WP Organize Media Uploads
 * Description: Organizes media uploads into directories based on file type
 * Author: Andrew Taylor
 * Version: 1.0.1
 *
 */

namespace ataylorme\plugins;

/*
 * No Direct Access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}


/**
 * Class WPOrganizeMediaUploads
 * @package ataylorme\plugins
 */
class WPOrganizeMediaUploads {

	/**
	 * Instance
	 * @var object $this
	 */
	private static $instance = null;

	/**
	 * Singleton enforcement
	 * @return object $this
	 */
	public static function get_instance() {
		if ( ! isset( WPOrganizeMediaUploads::$instance ) ):
			WPOrganizeMediaUploads::$instance = new WPOrganizeMediaUploads;
		endif;

		return WPOrganizeMediaUploads::$instance;
	}


	/**
	 * Actions and filters in construct
	 */
	function __construct() {

		add_filter( 'wp_handle_upload_prefilter', array( $this, 'upload_prefilter' ) );

		add_filter( 'wp_handle_upload', array( $this, 'upload' ) );

		if ( ! defined( 'UPLOADS' ) ) {
			define( 'UPLOADS', trailingslashit( ABSPATH ) . 'media' );
		}

		update_option( 'upload_path', UPLOADS );

		update_option( 'upload_url_path', get_bloginfo( 'url' ) );

	}


	/**
	 * @param $file
	 *
	 * @return mixed
	 */
	function upload_prefilter( $file ) {

		add_filter( 'upload_dir', array( $this, 'custom_upload_dir' ) );

		return $file;

	}


	/**
	 * @param $fileinfo
	 *
	 * @return mixed
	 */
	function upload( $fileinfo ) {

		remove_filter( 'upload_dir', array( $this, 'custom_upload_dir' ) );

		return $fileinfo;

	}


	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	function custom_upload_dir( $path ) {

		// Bail if there is an error
		if ( ! empty( $path['error'] ) ) {
			return $path;
		}

		$file_type = 'media';

		$extension = strtolower( substr( strrchr( $_POST['name'], '.' ), 1 ) );

		switch ( $extension ):
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'svg':
				$customdir = '/images';
				$file_type = 'images';
				break;
			case 'mp4':
			case 'm4v':
			case 'mov':
			case 'wmv':
			case 'avi':
			case 'mpg':
			case 'ogv':
			case '3gp':
			case '3g2':
				$customdir = '/videos';
				$file_type = 'videos';
				break;
			case 'doc':
			case 'docx':
			case 'txt':
				$customdir = '/documents';
				$file_type = 'documents';
				break;
			case 'pdf':
				$customdir = '/pdf';
				$file_type = 'pdf';
				break;
			case 'mp3':
			case 'm4a':
			case 'ogg':
			case 'wav':
				$customdir = '/audio';
				$file_type = 'audio';
				break;
			default:
				$customdir = '';
				break;
		endswitch;

		$path['path'] = UPLOADS;

		$path['url'] = trailingslashit( get_bloginfo( 'url' ) );

		$use_year_month_folders = get_option( 'uploads_use_yearmonth_folders' );

		if ( $use_year_month_folders ) {
			$customdir .= date( '/Y/m' );
		}

		$path['subdir'] = $customdir;

		$path['path'] .= $customdir;

		$path['url'] .= $customdir;

		add_filter( 'as3cf_setting_object-prefix', array( $this, 'dynamic_path_' . $file_type ), 10, 1 );

		return $path;

	}

	function dynamic_path_media() {
		return 'media';
	}

	function dynamic_path_images() {
		return 'images';
	}

	function dynamic_path_videos() {
		return 'videos';
	}

	function dynamic_path_documents() {
		return 'documents';
	}

	function dynamic_path_pdf() {
		return 'pdf';
	}

	function dynamic_path_audio() {
		return 'audio';
	}

}


/**
 * Bootstrap the plugin
 */
function WPOrganizeMediaUploads_init() {
	WPOrganizeMediaUploads::get_instance();
}

add_action( 'plugins_loaded', 'ataylorme\plugins\WPOrganizeMediaUploads_init', 1 );