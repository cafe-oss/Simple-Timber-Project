<?php
/**
 * Email Styles — Tonal brand override
 *
 * Overrides woocommerce/templates/emails/email-styles.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.5.0
 */

defined( 'ABSPATH' ) || exit;

// Brand palette
$brand_dark     = '#1c1c1a';
$brand_light    = '#f4f4f4';
$brand_white    = '#ffffff';
$brand_accent   = '#11ddc4';
$brand_muted    = '#6b7280';
$brand_border   = '#cbcccf';
$brand_body_bg  = '#f4f4f4';
?>
body {
	background-color: <?php echo $brand_body_bg; ?>;
	padding: 0;
	margin: 0;
	text-align: center;
}

#outer_wrapper {
	background-color: <?php echo $brand_body_bg; ?>;
}

#wrapper {
	margin: 0 auto;
	padding: 32px 0 48px;
	-webkit-text-size-adjust: none !important;
	width: 100%;
	max-width: 600px;
}

/* ── Container ── */
#inner_wrapper {
	background-color: <?php echo $brand_white; ?>;
	border-radius: 0;
}

#template_container {
	background-color: <?php echo $brand_white; ?>;
	border: 1px solid <?php echo $brand_border; ?>;
	border-radius: 0 !important;
	box-shadow: none !important;
}

/* ── Header ── */
#template_header_image {
	padding: 28px 40px 0;
	background-color: <?php echo $brand_dark; ?>;
}

#template_header_image p {
	margin: 0;
	text-align: left;
}

#template_header_image img {
	width: 120px;
	height: auto;
	vertical-align: middle;
}

.email-logo-text {
	color: <?php echo $brand_light; ?>;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 22px;
	font-weight: 700;
	letter-spacing: 6px;
	text-transform: uppercase;
	margin: 0;
	line-height: 1;
}

#template_header {
	background-color: <?php echo $brand_dark; ?>;
	border-radius: 0 !important;
	border-bottom: 3px solid <?php echo $brand_accent; ?>;
	color: <?php echo $brand_light; ?>;
	font-weight: 400;
	line-height: 100%;
	vertical-align: middle;
	font-family: Helvetica, Arial, sans-serif;
}

#template_header h1,
#template_header h1 a {
	color: <?php echo $brand_light; ?>;
	background-color: inherit;
}

#header_wrapper {
	padding: 24px 40px 28px;
	display: block;
}

#header_wrapper h1 {
	text-align: left;
}

/* ── Divider ── */
.hr {
	border: 0;
	border-bottom: 1px solid <?php echo $brand_border; ?>;
	margin: 20px 0;
}

/* ── Body ── */
#body_content {
	background-color: <?php echo $brand_white; ?>;
}

#body_content table td {
	padding: 32px 40px;
}

#body_content table td td {
	padding: 10px 12px;
}

#body_content table td th {
	padding: 10px 12px;
}

#body_content_inner {
	color: <?php echo $brand_dark; ?>;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 15px;
	line-height: 160%;
	text-align: left;
}

/* ── Order table ── */
.email-order-detail-heading {
	color: <?php echo $brand_dark; ?>;
	font-size: 13px;
	font-weight: 400;
	letter-spacing: 2px;
	text-transform: uppercase;
}

.email-order-detail-heading span {
	color: <?php echo $brand_muted; ?>;
	display: block;
	font-size: 13px;
	font-weight: 400;
	letter-spacing: 0;
	text-transform: none;
}

#body_content table .email-order-details td,
#body_content table .email-order-details th {
	padding: 10px 12px;
}

#body_content table .email-order-details td:first-child,
#body_content table .email-order-details th:first-child {
	padding-left: 0;
}

#body_content table .email-order-details td:last-child,
#body_content table .email-order-details th:last-child {
	padding-right: 0;
}

#body_content .email-order-details tbody tr:last-child td {
	border-bottom: 1px solid <?php echo $brand_border; ?>;
	padding-bottom: 20px;
}

#body_content .email-order-details tfoot tr:first-child td,
#body_content .email-order-details tfoot tr:first-child th {
	padding-top: 20px;
}

#body_content .email-order-details .order-totals td,
#body_content .email-order-details .order-totals th {
	font-weight: normal;
	padding-bottom: 4px;
	padding-top: 4px;
}

#body_content .email-order-details .order-totals-total th {
	font-weight: 700;
}

#body_content .email-order-details .order-totals-total td {
	font-weight: 700;
	font-size: 18px;
	color: <?php echo $brand_dark; ?>;
}

#body_content .email-order-details .order-totals-last td,
#body_content .email-order-details .order-totals-last th {
	border-bottom: 1px solid <?php echo $brand_border; ?>;
	padding-bottom: 20px;
}

.td {
	color: <?php echo $brand_dark; ?>;
	border: 0;
	vertical-align: middle;
}

.address {
	color: <?php echo $brand_dark; ?>;
	font-style: normal;
	padding: 8px 0;
	word-break: break-all;
}

#addresses td + td {
	padding-left: 10px !important;
}

/* ── Introduction block ── */
.email-introduction {
	padding-bottom: 24px;
}

.email-introduction p {
	color: <?php echo $brand_dark; ?>;
	font-size: 15px;
	line-height: 160%;
}

/* ── Additional content ── */
#body_content table td td.email-additional-content {
	color: <?php echo $brand_muted; ?>;
	font-family: Helvetica, Arial, sans-serif;
	padding: 28px 0 0;
}

.email-additional-content p {
	text-align: center;
	font-size: 13px;
}

/* ── Order item meta ── */
.email-order-item-meta {
	color: <?php echo $brand_muted; ?>;
	font-size: 13px;
	line-height: 140%;
}

.order-item-data td {
	border: 0 !important;
	padding: 0 !important;
	vertical-align: middle;
}

.order-customer-note td {
	border-bottom: 1px solid <?php echo $brand_border; ?>;
	padding-bottom: 20px;
	padding-top: 20px;
}

/* ── Footer ── */
#template_footer td {
	padding: 0;
	border-radius: 0;
}

#template_footer #credit {
	border: 0;
	border-top: 1px solid <?php echo $brand_border; ?>;
	color: <?php echo $brand_muted; ?>;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 12px;
	line-height: 160%;
	text-align: center;
	padding: 28px 40px;
	background-color: <?php echo $brand_body_bg; ?>;
}

#template_footer #credit p {
	margin: 0 0 8px;
}

#template_footer #credit,
#template_footer #credit a {
	color: <?php echo $brand_muted; ?>;
}

/* ── Typography ── */
h1 {
	color: <?php echo $brand_light; ?>;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 22px;
	font-weight: 400;
	letter-spacing: 4px;
	text-transform: uppercase;
	line-height: 130%;
	margin: 0;
	text-align: left;
}

h2 {
	color: <?php echo $brand_dark; ?>;
	display: block;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 16px;
	font-weight: 700;
	line-height: 150%;
	margin: 0 0 16px;
	text-align: left;
}

h3 {
	color: <?php echo $brand_dark; ?>;
	display: block;
	font-family: Helvetica, Arial, sans-serif;
	font-size: 14px;
	font-weight: 700;
	line-height: 150%;
	margin: 16px 0 8px;
	text-align: left;
}

a {
	color: <?php echo $brand_dark; ?>;
	font-weight: normal;
	text-decoration: underline;
}

img {
	border: none;
	display: inline-block;
	height: auto;
	outline: none;
	text-decoration: none;
	vertical-align: middle;
	max-width: 100%;
}

p {
	margin: 0 0 16px;
}

.text,
.address-title,
.order-item-data {
	color: <?php echo $brand_dark; ?>;
	font-family: Helvetica, Arial, sans-serif;
}

.link {
	color: <?php echo $brand_dark; ?>;
}

.text-align-left  { text-align: left; }
.text-align-right { text-align: right; }
.font-family      { font-family: Helvetica, Arial, sans-serif; }

/* ── Item list ── */
#body_content td ul.wc-item-meta {
	font-size: 13px;
	margin: 8px 0 0;
	padding: 0;
	list-style: none;
}

#body_content td ul.wc-item-meta li {
	margin: 4px 0 0;
	padding: 0;
}

#body_content td ul.wc-item-meta li p {
	margin: 0;
}

.wc-item-meta-label {
	float: left;
	font-weight: normal;
	margin-right: 4px;
}

/* ── Responsive ── */
@media screen and (max-width: 600px) {
	#template_header_image {
		padding: 20px 20px 0 !important;
	}

	#header_wrapper {
		padding: 18px 20px 22px !important;
	}

	#header_wrapper h1 {
		font-size: 18px !important;
		letter-spacing: 3px !important;
	}

	#body_content table td {
		padding: 24px 20px !important;
	}

	#body_content_inner {
		font-size: 14px !important;
	}

	.email-order-item-meta {
		font-size: 12px !important;
	}

	#body_content .email-order-details .order-totals-total td {
		font-size: 16px !important;
	}

	#template_footer #credit {
		padding: 24px 20px !important;
	}
}
