<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Pdf_File_Attachment
 * @subpackage Sejoli_Pdf_File_Attachment/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sejoli_Pdf_File_Attachment
 * @subpackage Sejoli_Pdf_File_Attachment/includes
 * @author     Sejoli Team <developer@sejoli.co.id>
 */
class Sejoli_Pdf_File_Attachment {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sejoli_Pdf_File_Attachment_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'SEJOLI_PDF_FILE_ATTACHMENT_VERSION' ) ) {

			$this->version = SEJOLI_PDF_FILE_ATTACHMENT_VERSION;

		} else {

			$this->version = '1.0.0';

		}

		$this->plugin_name = 'sejoli-pdf-file-attachment';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sejoli_Pdf_File_Attachment_Loader. Orchestrates the hooks of the plugin.
	 * - Sejoli_Pdf_File_Attachment_i18n. Defines internationalization functionality.
	 * - Sejoli_Pdf_File_Attachment_Admin. Defines all hooks for the admin area.
	 * - Sejoli_Pdf_File_Attachment_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SEJOLI_PDF_FILE_ATTACHMENT_DIR . 'includes/class-sejoli-pdf-file-attachment-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SEJOLI_PDF_FILE_ATTACHMENT_DIR . 'includes/class-sejoli-pdf-file-attachment-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SEJOLI_PDF_FILE_ATTACHMENT_DIR . 'admin/class-sejoli-pdf-file-attachment-admin.php';
		require_once SEJOLI_PDF_FILE_ATTACHMENT_DIR . 'admin/class-sejoli-pdf-file-attachment-product.php';
		require_once SEJOLI_PDF_FILE_ATTACHMENT_DIR . 'admin/class-sejoli-pdf-file-attachment-invoice.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SEJOLI_PDF_FILE_ATTACHMENT_DIR . 'public/class-sejoli-pdf-file-attachment-public.php';

		$this->loader = new Sejoli_Pdf_File_Attachment_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sejoli_Pdf_File_Attachment_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sejoli_Pdf_File_Attachment_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new Sejoli_Pdf_File_Attachment\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		$product = new Sejoli_Pdf_File_Attachment\Admin\Product( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/product/fields', $product, 'setup_pdf_file_attachment_setting_fields', 97);

		$invoice = new Sejoli_Pdf_File_Attachment\Admin\Invoice( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/notification/email/attachments', $invoice, 'set_pdf_email_attachments', 10, 2);
		$this->loader->add_action( 'sejoli/order/set-status/on-hold', $invoice, 'generate_invoice_data_order_on_hold', 100);
		$this->loader->add_action( 'sejoli/notification/order/on-hold', $invoice, 'generate_invoice_data_order_on_hold', 100);
		$this->loader->add_action( 'sejoli/order/set-status/completed', $invoice, 'generate_invoice_data_order_completed', 300);
		$this->loader->add_action( 'sejoli/notification/order/completed', $invoice, 'generate_invoice_data_order_completed', 300);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$public = new Sejoli_Pdf_File_Attachment\Front( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		$this->loader->run();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string The name of the plugin.
	 */
	public function get_plugin_name() {

		return $this->plugin_name;

	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Sejoli_Pdf_File_Attachment_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {

		return $this->loader;

	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {

		return $this->version;

	}

}