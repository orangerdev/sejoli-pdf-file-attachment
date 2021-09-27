<?php
namespace Sejoli_Pdf_File_Attachment\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Product {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version 	   = $version;

	}

    /**
	 * Setup PDF File Attachment Email in product setting
	 * Hooked via filter sejoli/product/fields, priority 42
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_pdf_file_attachment_setting_fields(array $fields) {

        $fields['pdf-attachment']   = array(
            'title'     => __('PDF', 'sejoli-pdf-file-attachment'),
            'fields'    => array(
                Field::make( 'separator', 'sep_pdf_attachment' , __('PDF File Attachment', 'sejoli-pdf-file-attachment'))
                    ->set_classes('sejoli-with-help')
                    ->set_help_text('<a href="' . sejolisa_get_admin_help('shipping') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make('checkbox', 'pdf_file_menunggu_pembayaran', __('Attach PDF Invoice - Email Menunggu Pembayaran', 'sejoli-pdf-file-attachment'))
                    ->set_option_value('yes')
                    ->set_default_value(false),

                Field::make('checkbox', 'pdf_file_pesanan_selesai', __('Attach PDF Invoice - Email Pesanan Selesai', 'sejoli-pdf-file-attachment'))
                    ->set_option_value('yes')
                    ->set_default_value(false),
            )
        );

        return $fields;

    }

}
