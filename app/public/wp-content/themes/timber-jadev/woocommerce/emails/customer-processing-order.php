<?php
/**
 * Customer Processing Order Email — Tonal brand override
 *
 * Sent to the customer when an order is received/processing.
 * Overrides woocommerce/templates/emails/customer-processing-order.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header()
 */
do_action( 'woocommerce_email_header', $email_heading, $email );

$first_name = $order->get_billing_first_name();
?>

<!-- Greeting -->
<div class="email-introduction">
	<p>
		<?php
		if ( $first_name ) {
			/* translators: %s: customer first name */
			printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $first_name ) );
		} else {
			esc_html_e( 'Hi there,', 'woocommerce' );
		}
		?>
	</p>

	<p>
		<?php esc_html_e( 'Thank you for your order. We\'ve received it and it\'s now being processed.', 'woocommerce' ); ?>
	</p>

	<!-- Order confirmation badge -->
	<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:24px 0;">
		<tr>
			<td style="background-color:#f4f4f4;border-left:3px solid #11ddc4;padding:16px 20px;">
				<p style="margin:0;font-size:13px;letter-spacing:2px;text-transform:uppercase;color:#6b7280;">
					<?php esc_html_e( 'Order number', 'woocommerce' ); ?>
				</p>
				<p style="margin:4px 0 0;font-size:20px;font-weight:700;color:#1c1c1a;">
					#<?php echo esc_html( $order->get_order_number() ); ?>
				</p>
			</td>
		</tr>
	</table>

	<p>
		<?php esc_html_e( 'Here\'s a summary of what you ordered:', 'woocommerce' ); ?>
	</p>
</div>

<?php
/*
 * @hooked WC_Emails::order_details()
 * @hooked WC_Structured_Data::generate_order_data()
 * @hooked WC_Structured_Data::output_structured_data()
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta()
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details()
 * @hooked WC_Emails::email_address()
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

// Additional content from WooCommerce email settings
if ( $additional_content ) {
	echo '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content">';
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	echo '</td></tr></table>';
}

/*
 * @hooked WC_Emails::email_footer()
 */
do_action( 'woocommerce_email_footer', $email );
