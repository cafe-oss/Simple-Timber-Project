<?php
/**
 * Email Header — Tonal brand override
 *
 * Overrides woocommerce/templates/emails/email-header.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

defined( 'ABSPATH' ) || exit;

$store_name = get_bloginfo( 'name', 'display' );
$logo_img   = get_option( 'woocommerce_email_header_image' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<title><?php echo esc_html( $store_name ); ?></title>
	<style type="text/css">
		<?php echo apply_filters( 'woocommerce_email_styles', wc_get_template_html( 'emails/email-styles.php' ), $email ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</style>
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">

<table width="100%" id="outer_wrapper" role="presentation" cellpadding="0" cellspacing="0">
	<tr>
		<td><!-- spacer --></td>
		<td width="600">
			<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
				<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="inner_wrapper" role="presentation">
					<tr>
						<td align="center" valign="top">

							<!-- Main container -->
							<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_container" role="presentation">
								<tr>
									<td align="center" valign="top">

										<!-- Header (dark brand bar) -->
										<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" role="presentation">
											<tr>
												<td id="header_wrapper">

													<!-- Logo -->
													<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin-bottom:20px;">
														<tr>
															<td id="template_header_image">
																<?php if ( $logo_img ) : ?>
																	<img src="<?php echo esc_url( $logo_img ); ?>"
																	     alt="<?php echo esc_attr( $store_name ); ?>"
																	     style="width:120px;height:auto;display:block;" />
																<?php else : ?>
																	<p class="email-logo-text"><?php echo esc_html( strtoupper( $store_name ) ); ?></p>
																<?php endif; ?>
															</td>
														</tr>
													</table>

													<!-- Email heading -->
													<h1 style="text-align: center;"><?php echo esc_html( $email_heading ); ?></h1>

												</td>
											</tr>
										</table>
										<!-- /Header -->

									</td>
								</tr>
								<tr>
									<td align="center" valign="top">
										<!-- Body -->
										<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body" role="presentation">
											<tr>
												<td valign="top" id="body_content">
													<!-- Content -->
													<table border="0" cellpadding="20" cellspacing="0" width="100%" role="presentation">
														<tr>
															<td valign="top" id="body_content_inner_cell">
																<div id="body_content_inner">
