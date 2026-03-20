<?php
/**
 * Email Footer — Tonal brand override
 *
 * Overrides woocommerce/templates/emails/email-footer.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

defined( 'ABSPATH' ) || exit;

$email            = $email ?? null;
$store_name       = get_bloginfo( 'name', 'display' );
$email_footer_text = get_option( 'woocommerce_email_footer_text' );

?>
																</div>
															</td>
														</tr>
													</table>
													<!-- /Content -->
												</td>
											</tr>
										</table>
										<!-- /Body -->
									</td>
								</tr>
							</table>

							<!-- Footer -->
							<table border="0" cellpadding="10" cellspacing="0" width="100%" id="template_footer" role="presentation">
								<tr>
									<td valign="top">
										<table border="0" cellpadding="10" cellspacing="0" width="100%" role="presentation">
											<tr>
												<td colspan="2" valign="middle" id="credit">

													<!-- Divider accent line -->
													<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin-bottom:20px;">
														<tr>
															<td style="height:2px;background-color:#11ddc4;"></td>
														</tr>
													</table>

													<!-- Store name -->
													<p style="font-size:13px;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:#1c1c1a;margin:0 0 12px;">
														<?php echo esc_html( strtoupper( $store_name ) ); ?>
													</p>

													<!-- Footer text from WC settings -->
													<?php
													if ( $email_footer_text ) {
														echo wp_kses_post(
															wpautop(
																wptexturize(
																	apply_filters( 'woocommerce_email_footer_text', $email_footer_text, $email )
																)
															)
														);
													}
													?>

													<!-- Links -->
													<p style="margin:16px 0 0;">
														<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
														   style="color:#6b7280;text-decoration:none;font-size:12px;margin:0 8px;">Shop</a>
														&nbsp;&middot;&nbsp;
														<a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"
														   style="color:#6b7280;text-decoration:none;font-size:12px;margin:0 8px;">Privacy Policy</a>
														&nbsp;&middot;&nbsp;
														<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
														   style="color:#6b7280;text-decoration:none;font-size:12px;margin:0 8px;"><?php echo esc_html( parse_url( home_url(), PHP_URL_HOST ) ); ?></a>
													</p>

												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							<!-- /Footer -->

						</td>
					</tr>
				</table>
			</div>
		</td>
		<td><!-- spacer --></td>
	</tr>
</table>

</body>
</html>
