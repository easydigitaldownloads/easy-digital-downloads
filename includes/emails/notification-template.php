<?php
/**
 * Sale Notification Email Template
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Email Template Tags
 *
 * @since 1.7
 *
 * @param string $message Message with the template tags
 * @param array $payment_data Payment Data
 * @param int $payment_id Payment ID
 *
 * @return string $message Fully formatted message
 */
function edd_sale_notification_template_tags( $message, $payment_data, $payment_id ) {
	global $edd_options;

	$has_tags = ( strpos($message, '{' ) !== false );
	if ( ! $has_tags ) return $message;

	$user_info = maybe_unserialize( $payment_data['user_info'] );

	$fullname = '';
	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
		$user_data = get_userdata( $user_info['id'] );
		$name      = $user_info['first_name'];
		$fullname  = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$username  = $user_data->user_login;
	} elseif ( isset( $user_info['first_name'] ) ) {
		$name     = $user_info['first_name'];
		$fullname = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$username = $user_info['first_name'];
	} else {
		$name     = $user_info['email'];
		$username = $user_info['email'];
	}

	$file_urls     = '';
	$download_list = '<ul>';
	$cart_items     = edd_get_payment_meta_cart_details( $payment_id );

	if ( $cart_items ) {
		$show_names = apply_filters( 'edd_email_show_names', true );

		foreach ( $cart_items as $item ) {

			if ( edd_use_skus() )
				$sku = edd_get_download_sku( $item['id'] );

			$price_id = edd_get_cart_item_price_id( $item );

			if ( $show_names ) {

				$title = get_the_title( $item['id'] );

				if( ! empty( $sku ) )
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'edd' ) . ': ' . $sku;

				if( $price_id !== false )
					$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id );

				$download_list .= '<li>' . apply_filters( 'edd_email_receipt_download_title', $title, $item['id'], $price_id ) . '<br/>';
				$download_list .= '<ul>';
			}


			$files = edd_get_download_files( $item['id'], $price_id );

			if ( $files ) {
				foreach ( $files as $filekey => $file ) {
					$download_list .= '<li>';
					$file_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $filekey, $item['id'], $price_id );
					$download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
					$download_list .= '</li>';

					$file_urls .= esc_html( $file_url ) . '<br/>';
				}
			} elseif( edd_is_bundled_product( $item['id'] ) ) {

				$bundled_products = edd_get_bundled_products( $item['id'] );

				foreach( $bundled_products as $bundle_item ) {

					$download_list .= '<li class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></li>';

					$files = edd_get_download_files( $bundle_item );

					foreach ( $files as $filekey => $file ) {
						$download_list .= '<li>';
						$file_url = edd_get_download_file_url( $payment_data['key'], $payment_data['email'], $filekey, $bundle_item, $price_id );
						$download_list .= '<a href="' . esc_url( $file_url ) . '">' . $file['name'] . '</a>';
						$download_list .= '</li>';

						$file_urls .= esc_html( $file_url ) . '<br/>';
					}
				}
			}

			if ( $show_names ) {
				$download_list .= '</ul>';
			}

			if ( '' != edd_get_product_notes( $item['id'] ) )
				$download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';

			if ( $show_names ) {
				$download_list .= '</li>';
			}
		}
	}
	$download_list .= '</ul>';

	$subtotal   = isset( $payment_data['subtotal'] ) ? $payment_data['subtotal'] : $payment_data['amount'];
	$subtotal   = edd_currency_filter( edd_format_amount( $subtotal ) );
	$tax        = isset( $payment_data['tax'] ) ? $payment_data['tax'] : 0;
	$tax        = edd_currency_filter( edd_format_amount( $tax ) );
	$price      = edd_currency_filter( edd_format_amount( $payment_data['amount'] ) );
	$gateway    = edd_get_gateway_checkout_label( get_post_meta( $payment_id, '_edd_payment_gateway', true ) );
	$receipt_id = $payment_data['key'];
	$email		= $user_info['email'];

	$message = str_replace( '{name}', $name, $message );
	$message = str_replace( '{fullname}', $fullname, $message );
	$message = str_replace( '{username}', $username, $message );
	$message = str_replace( '{user_email}', $email, $message );
	$message = str_replace( '{download_list}', $download_list, $message );
	$message = str_replace( '{file_urls}', $file_urls, $message );
	$message = str_replace( '{date}', date_i18n( get_option( 'date_format' ), strtotime( $payment_data['date'] ) ), $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{subtotal}', $subtotal, $message );
	$message = str_replace( '{tax}', $tax, $message );
	$message = str_replace( '{price}', $price, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{receipt_id}', $receipt_id, $message );
	$message = str_replace( '{payment_id}', $payment_id, $message );

	$message = apply_filters( 'edd_sale_notification_template_tags', $message, $payment_data, $payment_id );

	return $message;
}

/**
 * Email Template Header
 *
 * @access private
 * @since 1.7
 * @return string Email template header
 */
function edd_get_sale_notification_body_header() {
	ob_start();
	?>
	<html>
	<head>
		<style type="text/css">#outlook a { padding: 0; }</style>
	</head>
	<body>
	<?php
	do_action( 'edd_sale_notification_body_header' );
	return ob_get_clean();
}

/**
 * Email Template Body
 *
 * @since 1.7
 * @param int $payment_id Payment ID
 * @param array $payment_data Payment Data
 * @return string $email_body Body of the email
 */
function edd_get_sale_notification_body_content( $payment_id = 0, $payment_data = array() ) {
	global $edd_options;

	$user_info = maybe_unserialize( $payment_data['user_info'] );
	$email = edd_get_payment_user_email( $payment_id );

	if( isset( $user_info['id'] ) && $user_info['id'] > 0 ) {
		$user_data = get_userdata( $user_info['id'] );
		$name = $user_data->display_name;
	} elseif( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $email;
	}

	$download_list = '';
	$downloads = maybe_unserialize( $payment_data['downloads'] );

	if( is_array( $downloads ) ) {
		foreach( $downloads as $download ) {
			$id		= isset( $payment_data['cart_details'] ) ? $download['id'] : $download;
			$title	= get_the_title( $id );
			if( isset( $download['options'] ) ) {
				if( isset( $download['options']['price_id'] ) ) {
					$title .= ' - ' . edd_get_price_option_name( $id, $download['options']['price_id'], $payment_id );
				}
			}
			$download_list .= html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) . "\n";
		}
	}

	$gateway = edd_get_gateway_admin_label( get_post_meta( $payment_id, '_edd_payment_gateway', true ) );

	$default_email_body = __( 'Hello', 'edd' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'edd' ), edd_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'edd' ), edd_get_label_plural() ) . "\n\n";
	$default_email_body .= $download_list . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'edd' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Amount: ', 'edd' ) . " " . html_entity_decode( edd_currency_filter( edd_format_amount( $payment_data['amount'] ) ), ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Payment Method: ', 'edd' ) . " " . $gateway . "\n\n";
	$default_email_body .= __( 'Thank you', 'edd' );

	$email = isset( $edd_options['sale_notification'] ) ? $edd_options['sale_notification'] : $default_email_body;

	$email_body = edd_sale_notification_template_tags( $email, $payment_data, $payment_id );

	return apply_filters( 'edd_purchase_receipt', $email_body, $payment_id, $payment_data );
}

/**
 * Email Template Footer
 *
 * @since 1.7
 * @return string Email template footer
 */
function edd_get_sale_notification_body_footer() {
	ob_start();
	do_action( 'edd_sale_notification_body_footer' );
	?>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

/**
 * Applies the Chosen Email Template
 *
 * @since 1.7
 * @param string $body The contents of the receipt email
 * @param int $payment_id The ID of the payment we are sending a receipt for
 * @param array $payment_data An array of meta information for the payment
 * @return string $email Formatted email with the template applied
 */
function edd_apply_sale_notification_template( $body, $payment_id, $payment_data ) {
	global $edd_options;

	$template_name = isset( $edd_options['email_template'] ) ? $edd_options['email_template'] : 'default';
	$template_name = apply_filters( 'edd_email_template', $template_name, $payment_id );

	if ( $template_name == 'none' ) {
		if ( is_admin() )
			$body = edd_email_preview_templage_tags( $body );

		return $body; // Return the plain email with no template
	}

	ob_start();

	do_action( 'edd_sale_notification_template_' . $template_name );

	$template = ob_get_clean();

	$body = apply_filters( 'edd_sale_notification_' . $template_name, $body );

	$email = str_replace( '{email}', $body, $template );

	return $email;
}
add_filter( 'edd_sale_notification', 'edd_apply_sale_notification_template', 20, 3 );

/**
 * Default Email Template
 *
 * @access private
 * @since 1.7
 */
function edd_default_sale_notification_template() {
	echo '<div style="width: 550px; border: 1px solid #ccc; background: #f0f0f0; padding: 8px 10px; margin: 0 auto;">';
	echo '<div id="edd-email-content" style="background: #fff; border: 1px solid #ccc; padding: 10px;">';
	echo '{email}'; // This tag is required in order for the contents of the email to be shown
	echo '</div>';
	echo '</div>';
}
add_action( 'edd_sale_notification_template_default', 'edd_default_sale_notification_template' );

/**
 * Default Email Template Styling Extras
 *
 * @since 1.7
 * @param string $email_body Email template without styling
 * @return string $email_body Email template with styling
 */
function edd_default_sale_notification_styling( $email_body ) {
	$first_p    = strpos( $email_body, '<p>' );
	$email_body = substr_replace( $email_body, '<p style="margin-top:0;">', $first_p, 3 );
	$email_body = str_replace( '<ul>', '<ul style="margin:0 0 10px 0; padding: 0;">', $email_body );
	$email_body = str_replace( '<li>', '<li style="display:block;margin:0 0 4px 0;">', $email_body );

	return $email_body;
}
add_filter( 'edd_sale_notification_default', 'edd_default_sale_notification_styling' );
