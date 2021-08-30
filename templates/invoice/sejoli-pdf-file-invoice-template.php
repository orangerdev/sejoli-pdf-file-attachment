<?php ob_start(); ?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>A simple, clean, and responsive HTML invoice template</title>

		<style>
			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 16px;
				line-height: 24px;
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				color: #000000;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}

			/** RTL **/
			.invoice-box.rtl {
				direction: rtl;
				font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
			}

			.invoice-box.rtl table {
				text-align: right;
			}

			.invoice-box.rtl table tr td:nth-child(2) {
				text-align: left;
			}
		</style>
	</head>

	<body>
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="3">
						<table>
							<tr>
								<td class="title">
									<?php
								        $upload_logo = carbon_get_theme_option('notification_email_logo');
								        if($upload_logo) :
								            $image = wp_get_attachment_image_src($upload_logo, 'medium');
								            if($image) :
								                echo '<img src="'.$image[0].'" alt="'.get_bloginfo('name').'" style="width: 100%; max-width: 150px" />';
								            endif;
								        endif;
									?>		
								</td>

								<td>
									<?php
										$status = $response['status'];
										if($status === 'on-hold'){
											$status_label = 'Menunggu Pembayaran';
										} else {
											$status_label = 'Pesanan Selesai';
										}
									?>
									<b><?php echo __('INV #', 'sejoli-pdf-file-attachment').$response['ID'].' - '.$status_label; ?></b><br />
									<?php echo __('Tanggal Dibuat: ', 'sejoli-pdf-file-attachment').'<br /><b>'.date("d F, Y").'</b>'; ?><br />
									<?php echo __('Tanggal Jatuh Tempo: ', 'sejoli-pdf-file-attachment').'<br /><b>'.date("d F, Y").'</b>'; ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<?php
					$shipper_origin_id   = $response['product']->shipping['origin'];
					$shipper_origin_city = $this->get_subdistrict_detail($shipper_origin_id);
				?>
				<tr class="information">
					<td colspan="3">
						<table>
							<tr>
								<td>
									<?php echo get_bloginfo('name'); ?><br />
								</td>
								<td>
									<?php echo $shipper_origin_city['type'].' '.$shipper_origin_city['city'].', '.$shipper_origin_city['subdistrict_name'].', '.$shipper_origin_city['province']; ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="heading">
					<td><?php _e('Metode Pembayaran', 'sejoli-pdf-file-attachment'); ?></td>
					<td>&nbsp;</td>
					<td>
					<?php 
						if( $response['payment_gateway'] === 'manual' ): 
							_e('Informasi Akun', 'sejoli-pdf-file-attachment');
						else:
							echo '&nbsp;';
						endif;
					?>
					</td>
				</tr>

				<tr class="details">
					<td><?php echo ucfirst($response['payment_gateway']); ?></td>
					<td>&nbsp;</td>
					<?php if( $response['payment_gateway'] === 'manual' ): ?>
						<td><?php echo $response['payment_info']['bank'].' - '.$response['payment_info']['account_number'].'<br /> (a/n. '.$response['payment_info']['owner'].')'; ?></td>
					<?php else: ?>
						<td>&nbsp;</td>
					<?php endif; ?>
				</tr>

				<tr class="heading">
					<td><?php _e('Item', 'sejoli-pdf-file-attachment'); ?></td>
					<td><?php _e('QTY', 'sejoli-pdf-attachment'); ?></td>
					<td><?php _e('Subtotal', 'sejoli-pdf-file-attachment'); ?></td>
				</tr>

				<tr class="item">
					<td>
						<?php echo $response['product']->post_title; ?><br />
						<?php 
						if(isset($response['meta_data']['variants'])){
							foreach ($response['meta_data']['variants'] as $variants):
								echo $variants['type'] .' : '. $variants['label']. '<br />';
				        	endforeach;
				        }
				        ?>
					</td>
					<td><?php echo $response['quantity']; ?></td>
					<td><?php echo sejolisa_price_format($response['grand_total']); ?></td>
				</tr>

				<tr class="total">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><b><?php echo __('TOTAL', 'sejoli-pdf-file-attachment').' : '.sejolisa_price_format($response['grand_total']); ?></b></td>
				</tr>
			</table>
		</div>
	</body>
</html>

<?php
	$html = ob_get_contents();
	ob_end_clean();
?>	