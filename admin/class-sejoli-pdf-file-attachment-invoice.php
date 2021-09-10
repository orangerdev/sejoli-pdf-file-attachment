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

    protected $blacklist_extension_for_email = array('zip', 'exe');

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
     * Add pdf attchment files
     * Hooked via filter sejoli/notification/email/attachments, priority 10
     * @since 1.0.0
     * @param array $attachments
     * @param array $invoice_data
     * @return array
     */
    public function set_pdf_email_attachments($attachments = array(), array $invoice_data) {
        
        if(isset($invoice_data['order_data']) && $invoice_data['order_data']['status'] === 'on-hold' || isset($invoice_data['order_data']) && $invoice_data['order_data']['status'] === 'completed'):

            $pdf_file_menunggu_pembayaran = carbon_get_post_meta( $invoice_data['product_data']->ID, 'pdf_file_menunggu_pembayaran' );
            $pdf_file_pesanan_selesai     = carbon_get_post_meta( $invoice_data['product_data']->ID, 'pdf_file_pesanan_selesai' );

            if( true === boolval( $pdf_file_menunggu_pembayaran ) || true === boolval( $pdf_file_pesanan_selesai ) || true === boolval( $pdf_file_menunggu_pembayaran ) && true === boolval( $pdf_file_pesanan_selesai  ) ) :

                $invoice_data['product_data']->files = [];

                $file_name   = 'INV-'.$invoice_data['order_data']['ID'].'-'.$invoice_data['order_data']['status'].'-'.date("Y-m-d").'.pdf';
                $file_path   = SEJOLI_PDF_UPLOAD_DIR.'/'.$file_name;
                $invoice_url = SEJOLI_PDF_UPLOAD_URL.'/'.$file_name;

                $invoice_data['product_data']->files[] = [
                    // 'ID'    => $file_id,
                    'path' => $file_path,
                    'link' => $invoice_url
                ];

                $files = $invoice_data['product_data']->files;

                foreach( (array) $files as $file ) :

                    $file_parts = pathinfo($file['path']);

                    if(!in_array($file_parts['extension'], $this->blacklist_extension_for_email)) :
                    
                        $attachments[] = $file['path'];
                    
                    endif;

                endforeach;
                
                return $attachments;

            endif;

        endif;

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
        
        // $response = $order_data;
        $response = sejolisa_get_orders(['ID' => $order_data['ID'] ]);
        
        require_once ( SEJOLISA_DIR . 'payments/main.php' );
        require_once ( SEJOLISA_DIR . 'payments/manual.php' );

        $payments['manual'] = new \SejoliSA\Payment\Manual();

        $this->payments = apply_filters('sejoli/payment/available-libraries', $payments);

        $used_module = $response['orders'][0]->payment_gateway;

        if( isset($this->payments[$used_module]) ) :

            $response['orders'][0]->payment_info = $this->payments[$used_module]->set_payment_info($order_data);

        endif;

        $pdf_file_menunggu_pembayaran = carbon_get_post_meta( $response['orders'][0]->product_id, 'pdf_file_menunggu_pembayaran' );

        if( true === boolval( $pdf_file_menunggu_pembayaran ) ) :

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
                $file_name = 'INV-'.$response['orders'][0]->ID.'-'.$response['orders'][0]->status.'-'.date("Y-m-d").'.pdf';
                $file_path = SEJOLI_PDF_UPLOAD_DIR.'/'.$file_name;
                file_put_contents( $file_path, $output );
                $invoice_url = SEJOLI_PDF_UPLOAD_URL.'/'.$file_name;

                return $order_data; //wp_send_json( $invoice_url );

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

        // $response = $order_data;
        $response = sejolisa_get_orders(['ID' => $order_data['ID'] ]);

        require_once ( SEJOLISA_DIR . 'payments/main.php' );
        require_once ( SEJOLISA_DIR . 'payments/manual.php' );

        $payments['manual'] = new \SejoliSA\Payment\Manual();

        $this->payments = apply_filters('sejoli/payment/available-libraries', $payments);

        $used_module = $response['orders'][0]->payment_gateway;

        if( isset($this->payments[$used_module]) ) :

            $response['orders'][0]->payment_info = $this->payments[$used_module]->set_payment_info($order_data);

        endif;

        $pdf_file_pesanan_selesai = carbon_get_post_meta( $response['orders'][0]->product_id, 'pdf_file_pesanan_selesai' );

        if( true === boolval( $pdf_file_pesanan_selesai ) ) :

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
                $file_name = 'INV-'.$response['orders'][0]->ID.'-'.$response['orders'][0]->status.'-'.date("Y-m-d").'.pdf';
                $file_path = SEJOLI_PDF_UPLOAD_DIR.'/'.$file_name;
                file_put_contents( $file_path, $output );
                $invoice_url = SEJOLI_PDF_UPLOAD_URL.'/'.$file_name;

                return $order_data; //wp_send_json( $invoice_url );

            endif;

        endif;
    
    }

}
