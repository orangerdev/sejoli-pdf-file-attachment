<?php
namespace Sejoli_Pdf_File_Attachment\Admin;

use Dompdf\Dompdf;
use Dompdf\Options;

class Invoice {

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
     * Get subdistrict detail
     * @since   1.2.0
     * @since   1.5.0       Add conditional to check if subdistrict_id is 0
     * @param   integer     $subdistrict_id     District ID
     * @return  array|null  District detail
     */
    public function get_subdistrict_detail($subdistrict_id) {

        if( 0 !== intval($subdistrict_id) ) :

            ob_start();

            require SEJOLISA_DIR . 'json/subdistrict.json';
            $json_data = ob_get_contents();

            ob_end_clean();

            $subdistricts        = json_decode($json_data, true);
            $key                 = array_search($subdistrict_id, array_column($subdistricts, 'subdistrict_id'));
            $current_subdistrict = $subdistricts[$key];

            return $current_subdistrict;

        endif;

        return  NULL;

    }

    /**
     * Generate Invoice Data Order On-Hold
     * 
     * Hooked via action sejoli/notification/order/on-hold, priority 100
     * Hooked via action sejoli/order/set-status/on-hold, priority 100
     * 
     * @since   1.0.0
     * @return  pdf file attachment
     */
    public function generate_invoice_data_order_on_hold( array $order_data ){
        
        $response = $order_data;
        $pdf_file_menunggu_pembayaran = carbon_get_post_meta( $response['product_id'], 'pdf_file_menunggu_pembayaran' );

        if( true === boolval( $pdf_file_menunggu_pembayaran ) ) :

            $attachments = array(WP_CONTENT_DIR . '/uploads/invoice/INV-#87-on-hold-2021-08-27 05:42:21pm.pdf');

            require_once( SEJOLISA_DIR . 'notification/main.php' );
            require_once( SEJOLISA_DIR . 'notification/on-hold.php' );

            $this->libraries = [
                'on-hold' => new \SejoliSA\Notification\OnHold
            ];

            $this->libraries = apply_filters( 'sejoli/notification/libraries', $this->libraries );
            $this->libraries['on-hold']->trigger( $order_data );

            if( null !== $response ) :
                
                require_once( plugin_dir_path( __FILE__ ) . '../templates/invoice/sejoli-pdf-file-invoice-template.php' );

                $options = new Options();
                $options->set( 'isRemoteEnabled', true );

                $dompdf = new Dompdf( $options );
                $dompdf->load_html( $html );
                $dompdf->setPaper( 'P', 'A4', 'portrait' );
                $dompdf->render();
                $output = $dompdf->output();

                wp_mkdir_p( SEJOLI_PDF_UPLOAD_DIR );
                $file_name = 'INV-#'.$response['ID'].'-'.$response['status'].'-'.date("Y-m-d h:i:sa").'.pdf';
                $file_path = SEJOLI_PDF_UPLOAD_DIR.'/'.$file_name;
                file_put_contents( $file_path, $output );
                $invoice_url = SEJOLI_PDF_UPLOAD_URL.'/'.$file_name;

                return wp_send_json( $invoice_url );

            endif;

        endif;
    
    }

    /**
     * Generate Invoice Data Order Completed
     * 
     * Hooked via action sejoli/notification/order/completed, priority 300
     * Hooked via action sejoli/order/set-status/completed, priority 300
     * 
     * @since   1.0.0
     * @return  pdf file attachment
     */
    public function generate_invoice_data_order_completed( array $order_data ){
        
        $response = $order_data;
        $pdf_file_pesanan_selesai = carbon_get_post_meta( $response['product_id'], 'pdf_file_pesanan_selesai' );

        if( true === boolval( $pdf_file_pesanan_selesai) ) :

            require_once( SEJOLISA_DIR . 'notification/main.php' );
            require_once( SEJOLISA_DIR . 'notification/completed.php' );

            $this->libraries = [
                'completed' => new \SejoliSA\Notification\Completed
            ];

            $this->libraries = apply_filters( 'sejoli/notification/libraries', $this->libraries );
            $this->libraries['completed']->trigger( $order_data );

            if( null !== $response ) :
                
                require_once( plugin_dir_path( __FILE__ ) . '../templates/invoice/sejoli-pdf-file-invoice-template.php' );

                $options = new Options();
                $options->set( 'isRemoteEnabled', true );

                $dompdf = new Dompdf( $options );
                $dompdf->load_html( $html );
                $dompdf->setPaper( 'P', 'A4', 'portrait' );
                $dompdf->render();
                $output = $dompdf->output();

                wp_mkdir_p( SEJOLI_PDF_UPLOAD_DIR );
                $file_name = 'INV-#'.$response['ID'].'-'.$response['status'].'-'.date("Y-m-d h:i:sa").'.pdf';
                $file_path = SEJOLI_PDF_UPLOAD_DIR.'/'.$file_name;
                file_put_contents( $file_path, $output );
                $invoice_url = SEJOLI_PDF_UPLOAD_URL.'/'.$file_name;
     
                return wp_send_json( $invoice_url );

            endif;
            
        endif;
    
    }

}
