<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sejoli.co.id
 * @since             1.0.0
 * @package           Sejoli_Pdf_File_Attachment
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli - PDF File Attatchment
 * Plugin URI:        https://sejoli.co.id
 * Description:       Plugin Sejoli PDF File Attachment untuk Attach File PDF di Email.
 * Version:           1.0.0
 * Author:            Sejoli Team
 * Author URI:        https://sejoli.co.id
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sejoli-pdf-file-attachment
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {

	die;

}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEJOLI_PDF_FILE_ATTACHMENT_VERSION', '1.0.0' );
define( 'SEJOLI_PDF_FILE_ATTACHMENT_DIR', plugin_dir_path( __FILE__ ) );
define( 'SEJOLI_PDF_FILE_ATTACHMENT_URL', plugin_dir_url( __FILE__ ) );

// Set up paths to include DOMPDF
define( 'SEJOLI_PDF_FILE_ATTACHMENT_DOMPDF', SEJOLI_PDF_FILE_ATTACHMENT_DIR . 'vendor/dompdf/' );

// Set up directory to save PDF
$upload_dir = wp_upload_dir();

define( 'SEJOLI_PDF_UPLOAD_DIR', $upload_dir['basedir'] . '/invoice');
define( 'SEJOLI_PDF_UPLOAD_URL', $upload_dir['baseurl'] . '/invoice');

if(version_compare(PHP_VERSION, '7.2.1') < 0 && !class_exists( 'WP_CLI' )) :

	add_action('admin_notices', 'sejoli_pdf_file_attachment_error_php_message', 1);

	/**
	 * Display error message when PHP version is lower than 7.2.0
	 * Hooked via admin_notices, priority 1
	 * @return 	void
	 */
	function sejoli_pdf_file_attachment_error_php_message() {
		?>
		<div class="notice notice-error">
			<h2>SEJOLI TIDAK BISA DIGUNAKAN DI HOSTING ANDA</h2>
			<p>
				Versi PHP anda tidak didukung oleh SEJOLI dan HARUS diupdate. Update versi PHP anda ke versi yang terbaru. <br >
				Minimal versi PHP adalah 7.2.1 dan versi PHP anda adalah <?php echo PHP_VERSION; ?>
			</p>
			<p>
				Jika anda menggunakan cpanel, anda bisa ikuti langkah ini <a href='https://www.rumahweb.com/journal/memilih-versi-php-melalui-cpanel/' target="_blank" class='button'>Update Versi PHP</a>
			</p>
			<p>
				Jika anda masih kesulitan untuk update versi PHP anda, anda bisa meminta bantuan pada CS hosting anda.
			</p>
		</div>
		<?php
	}

else :

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-sejoli-pdf-file-attachment-activator.php
	 */
	function activate_sejoli_pdf_file_attachment() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-pdf-file-attachment-activator.php';

		Sejoli_Pdf_File_Attachment_Activator::activate();

	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-sejoli-pdf-file-attachment-deactivator.php
	 */
	function deactivate_sejoli_pdf_file_attachment() {

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-pdf-file-attachment-deactivator.php';

		Sejoli_Pdf_File_Attachment_Deactivator::deactivate();

	}

	register_activation_hook( __FILE__, 'activate_sejoli_pdf_file_attachment' );
	register_deactivation_hook( __FILE__, 'deactivate_sejoli_pdf_file_attachment' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-pdf-file-attachment.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_sejoli_pdf_file_attachment() {

		$plugin = new Sejoli_Pdf_File_Attachment();
		$plugin->run();

	}

	require_once(SEJOLI_PDF_FILE_ATTACHMENT_DIR . 'vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php');

	$update_checker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/orangerdev/sejoli-pdf-file-attachment',
		__FILE__,
		'sejoli-pdf-file-attachment'
	);

	$update_checker->setBranch('main');

	run_sejoli_pdf_file_attachment();

endif;
