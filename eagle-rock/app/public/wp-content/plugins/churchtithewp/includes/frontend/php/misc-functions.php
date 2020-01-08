<?php
/**
 * Church Tithe WP
 *
 * @package     Church Tithe WP
 * @subpackage  Classes/Church Tithe WP
 * @copyright   Copyright (c) 2018, Church Tithe WP
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate the nonces
 *
 * @access   public
 * @since    1.0.0
 * @return   array
 */
function church_tithe_wp_refresh_and_get_frontend_nonces() {

	return array(
		'payment_intent_nonce'                            => wp_create_nonce( 'church_tithe_wp_payment_intent_nonce' ),
		'church_tithe_wp_email_transaction_receipt_nonce' => wp_create_nonce( 'church_tithe_wp_email_transaction_receipt_nonce' ),
		'note_with_tithe_nonce'                           => wp_create_nonce( 'church_tithe_wp_note_with_tithe' ),
		'email_login_nonce'                               => wp_create_nonce( 'church_tithe_wp_email_login_nonce' ),
		'login_nonce'                                     => wp_create_nonce( 'church_tithe_wp_login_nonce' ),
		'get_transactions_nonce'                          => wp_create_nonce( 'church_tithe_wp_get_transactions_nonce' ),
		'get_transaction_nonce'                           => wp_create_nonce( 'church_tithe_wp_get_transaction_nonce' ),
		'get_arrangements_nonce'                          => wp_create_nonce( 'church_tithe_wp_get_arrangements_nonce' ),
		'get_arrangement_nonce'                           => wp_create_nonce( 'church_tithe_wp_get_arrangement_nonce' ),
		'cancel_arrangement_nonce'                        => wp_create_nonce( 'church_tithe_wp_cancel_arrangement_nonce' ),
	);

}

/**
 * Force the update login cookie upon login.
 *
 * @access   public
 * @since    1.0.0
 * @param    string $logged_in_cookie The logged in cookie.
 * @return   void
 */
function church_tithe_wp_force_update_login_cookie( $logged_in_cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
}
add_action( 'set_logged_in_cookie', 'church_tithe_wp_force_update_login_cookie' );

/**
 * Create/Assemble all of the parts used on the frontend in reference to the "current_transaction_info".
 *
 * @since 1.0
 * @param Church_Tithe_WP_Transaction $transaction A transaction object.
 * @return array An formatted/predicatable array which can be used to pass a transaction to frontend endpoints
 */
function church_tithe_wp_transaction_info_format_for_endpoint( $transaction ) {

	// Get the Arrangement that this Transaction is linked with.
	$arrangement = new Church_Tithe_WP_Arrangement( $transaction->arrangement_id );
	$user        = get_user_by( 'id', $transaction->user_id );

	return array(
		'transaction_id'                       => $transaction->id,
		'transaction_date_created'             => $transaction->date_created,
		'transaction_date_paid'                => $transaction->date_paid,
		'transaction_period_start_date'        => $transaction->period_start_date,
		'transaction_period_end_date'          => $transaction->period_end_date,
		'transaction_charged_amount'           => $transaction->charged_amount,
		'transaction_charged_currency'         => strtoupper( $transaction->charged_currency ),
		'transaction_currency_symbol'          => html_entity_decode( church_tithe_wp_currency_symbol( $transaction->charged_currency ) ),
		'transaction_currency_is_zero_decimal' => church_tithe_wp_is_a_zero_decimal_currency( $transaction->charged_currency ),
		'transaction_note_with_tithe'          => $transaction->note_with_tithe,
		'arrangement_info'                     => church_tithe_wp_arrangement_info_format_for_endpoint( $arrangement ),
		'email'                                => $user->user_email,
		'payee_name'                           => get_bloginfo( 'name' ),
		'statement_descriptor'                 => $transaction->statement_descriptor,
	);
}

/**
 * Create/Assemble all of the parts used on the frontend in reference to the "current_arrangement_info".
 *
 * @since 1.0
 * @param Church_Tithe_WP_Arrangement $arrangement An arrangement object.
 * @return array An formatted/predicatable array which can be used to pass a transaction to frontend endpoints
 */
function church_tithe_wp_arrangement_info_format_for_endpoint( $arrangement ) {

	if (
		! empty( $arrangement->current_period_end ) &&
		'0000-00-00 00:00:00' !== $arrangement->current_period_end
	) {
		$webhook_succeeded  = true;
		$maybe_renewal_date = $arrangement->current_period_end;
	} else {
		$webhook_succeeded  = false;
		$maybe_renewal_date = __( 'Webhook failed!', 'church-tithe-wp' );
	}

	return array(
		'id'                       => $arrangement->id,
		'date_created'             => $arrangement->date_created,
		'amount'                   => $arrangement->renewal_amount,
		'currency'                 => $arrangement->currency,
		'is_zero_decimal_currency' => church_tithe_wp_is_a_zero_decimal_currency( $arrangement->currency ),
		'string_after'             => ' ' . __( 'per', 'church-tithe-wp' ) . ' ' . $arrangement->interval_string,
		'recurring_status'         => $arrangement->recurring_status,
		'renewal_date'             => $maybe_renewal_date,
		'webhook_succeeded'        => $webhook_succeeded,
	);

}

/**
 * Create/Assemble all of the values used to generate the default tithe form, passed to the react component (Church_Tithe_WP_Form)
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function church_tithe_wp_tithe_form_vars() {

	global $wp_query;

	$saved_settings = get_option( 'church_tithe_wp_settings' );

	$featured_image = church_tithe_wp_aq_resize( church_tithe_wp_get_saved_setting( $saved_settings, 'tithe_form_image' ), 100, 100 );

	// Get the default state from the URL variables.
	// If this is a bookmarked URL. Nonce is not checked here because this is not a form submission, but a URL.
	foreach ( $_GET as $url_variable => $url_variable_value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Skip any URL vars that aren't relevant to ctwp. Skip the modal vars too.
		if ( false === strpos( $url_variable, 'ctwp' ) || true === strpos( $url_variable, 'ctwpmodal' ) ) {
			continue;
		}

		// These values came from the $_GET array, meaning they need to be sanitized.
		$visual_state_key                        = sanitize_text_field( wp_unslash( $url_variable ) );
		$visual_state_value                      = sanitize_text_field( wp_unslash( $url_variable_value ) );
		$ctwp_url_variables[ $visual_state_key ] = $visual_state_value;

	}

	// Level 1 - Eventually we'll make this more robust, but for now 3 levels is as deep as has been needed.
	if ( isset( $ctwp_url_variables['ctwp1'] ) ) {
		$all_current_visual_states                                 = array();
		$all_current_visual_states[ $ctwp_url_variables['ctwp1'] ] = array();
		// Level 2.
		if ( isset( $ctwp_url_variables['ctwp2'] ) ) {
			$all_current_visual_states[ $ctwp_url_variables['ctwp1'] ][ $ctwp_url_variables['ctwp2'] ] = array();
			// Level 3.
			if ( isset( $ctwp_url_variables['ctwp3'] ) ) {
				$all_current_visual_states[ $ctwp_url_variables['ctwp1'] ][ $ctwp_url_variables['ctwp2'] ][ $ctwp_url_variables['ctwp3'] ] = array();
			}
		}
	} else {
		$all_current_visual_states = 'inherit';
	}

	// Now we will handle the modal vars.
	if ( isset( $_GET['ctwpmodal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ctwp_modal_value = sanitize_text_field( wp_unslash( $_GET['ctwpmodal'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Add that modal value to the modal visual state, which will be passed to our react app.
		$modal_visual_state[ $ctwp_modal_value ] = array();
	} else {
		$modal_visual_state = false;
	}

	$currency_code = church_tithe_wp_get_saved_setting( $saved_settings, 'default_currency', 'usd' );

	$user = wp_get_current_user();

	// Set the user's card name if they are logged in.
	if (
		isset( $user->first_name ) &&
		! empty( $user->first_name ) &&
		isset( $user->last_name ) &&
		! empty( $user->last_name )
	) {
		$user_card_name = $user->first_name . ' ' . $user->last_name;
	} else {
		$user_card_name = '';
	}

	$permalink = get_the_permalink( $wp_query->queried_object_id );

	// If no permalink found, for example, a category page has no permalink...
	if ( empty( $permalink ) || ! $permalink ) {

		// Attempt to get the current URL from the the $_SERVER variable.
		if (
			isset( $_SERVER['SERVER_NAME'] ) &&
			isset( $_SERVER['REQUEST_URI'] )
		) {
			$permalink = 'https://' . sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}
	}

	// If permalink is still empty, default to the base URL of the site.
	if ( empty( $permalink ) || ! $permalink ) {
		$permalink = get_bloginfo( 'url' );
	}

	$church_tithe_wp_vars = array(
		'mode'                        => 'form',
		'open_style'                  => 'in_place',
		'locale'                      => get_user_locale(),
		'date_format'                 => get_option( 'date_format' ),
		'time_format'                 => get_option( 'time_format' ),
		'currency_code'               => strtoupper( $currency_code ),
		'currency_symbol'             => html_entity_decode( church_tithe_wp_currency_symbol( strtolower( $currency_code ) ) ),
		'currency_type'               => church_tithe_wp_is_a_zero_decimal_currency( $currency_code ) ? 'zero_decimal' : 'decimal',
		'stripe_account_country_code' => church_tithe_wp_stripe_get_account_country_code(),
		'blank_flag_url'              => CHURCH_TITHE_WP_PLUGIN_URL . '/assets/images/flags/blank.gif',
		'flag_sprite_url'             => CHURCH_TITHE_WP_PLUGIN_URL . '/assets/images/flags/flags.png',
		'wordpress_permalink_only'    => $permalink,
		'all_default_visual_states'   => $all_current_visual_states,
		'modal_visual_state'          => $modal_visual_state,
		'default_amount'              => church_tithe_wp_get_saved_setting( $saved_settings, 'default_amount', 500 ),
		'has_featured_image'          => $featured_image ? $featured_image : false,
		'featured_image_url'          => $featured_image,
		'stripe_api_key'              => church_tithe_wp_get_stripe_publishable_key(),
		'close_button_url'            => CHURCH_TITHE_WP_PLUGIN_URL . '/assets/images/closebtn.png',
		'setup_link'                  => admin_url( 'admin.php?page=church-tithe-wp&mpwpadmin1=welcome&mpwpadmin_lightbox=do_wizard_health_check' ),
		'recurring_options'           => array(
			'never'   => array(
				'selected'     => true,
				'after_output' => __( 'One time only', 'church-tithe-wp' ),
			),
			'weekly'  => array(
				'selected'     => false,
				'after_output' => __( 'Every week', 'church-tithe-wp' ),
			),
			'monthly' => array(
				'selected'     => false,
				'after_output' => __( 'Every month', 'church-tithe-wp' ),
			),
			'yearly'  => array(
				'selected'     => false,
				'after_output' => __( 'Every year', 'church-tithe-wp' ),
			),
		),
		'strings'                     => array(
			'current_user_email'                 => isset( $user->user_email ) && ! empty( $user->user_email ) ? $user->user_email : '',
			'current_user_name'                  => $user_card_name,
			'link_text'                          => __( 'Give', 'church-tithe-wp' ),
			'complete_payment_button_error_text' => __( 'Check info and try again', 'church-tithe-wp' ),
			'payment_verb'                       => church_tithe_wp_get_saved_setting( $saved_settings, 'payment_verb', __( 'Pay', 'church-tithe-wp' ) ),
			'payment_request_label'              => get_bloginfo( 'name' ),
			'form_has_an_error'                  => __( 'Please check and fix the errors above', 'church-tithe-wp' ),
			'general_server_error'               => __( "Something isn't working right at the moment. Please try again.", 'church-tithe-wp' ),
			'form_title'                         => church_tithe_wp_get_saved_setting( $saved_settings, 'tithe_form_title', __( 'Give Online', 'church-tithe-wp' ) ),
			'form_subtitle'                      => church_tithe_wp_get_saved_setting( $saved_settings, 'tithe_form_subtitle' ),
			'currency_search_text'               => __( 'Country or Currency here', 'church-tithe-wp' ),
			'other_payment_option'               => __( 'Other payment option', 'church-tithe-wp' ),
			'manage_payments_button_text'        => __( 'Manage your payments', 'church-tithe-wp' ),
			'thank_you_message'                  => church_tithe_wp_get_saved_setting( $saved_settings, 'tithe_form_completed_message', __( 'Your payment has been successfully processed. Thank you.', 'church-tithe-wp' ) ),
			'payment_confirmation_title'         => get_bloginfo( 'name' ),
			'receipt_title'                      => __( 'Your Receipt', 'church-tithe-wp' ),
			'print_receipt'                      => __( 'Print Receipt', 'church-tithe-wp' ),
			'email_receipt'                      => __( 'Email Receipt', 'church-tithe-wp' ),
			'email_receipt_sending'              => __( 'Sending receipt...', 'church-tithe-wp' ),
			'email_receipt_success'              => __( 'Email receipt successfully sent', 'church-tithe-wp' ),
			'email_receipt_failed'               => __( 'Email receipt failed to send. Please try again.', 'church-tithe-wp' ),
			'receipt_payee'                      => __( 'Paid to', 'church-tithe-wp' ),
			'receipt_statement_descriptor'       => __( 'This will show up on your statement as', 'church-tithe-wp' ),
			'receipt_date'                       => __( 'Date', 'church-tithe-wp' ),
			'receipt_transaction_id'             => __( 'Transaction ID', 'church-tithe-wp' ),
			'receipt_transaction_amount'         => __( 'Amount', 'church-tithe-wp' ),
			'refund_payer'                       => __( 'Refund from', 'church-tithe-wp' ),
			'login'                              => __( 'Log in to manage your payments', 'church-tithe-wp' ),
			'manage_payments'                    => __( 'Manage Payments', 'church-tithe-wp' ),
			'transactions_title'                 => __( 'Your Transactions', 'church-tithe-wp' ),
			'transaction_title'                  => __( 'Transaction Receipt', 'church-tithe-wp' ),
			'transaction_period'                 => __( 'Plan Period', 'church-tithe-wp' ),
			'arrangements_title'                 => __( 'Your Plans', 'church-tithe-wp' ),
			'arrangement_title'                  => __( 'Manage Plan', 'church-tithe-wp' ),
			'arrangement_details'                => __( 'Plan Details', 'church-tithe-wp' ),
			'arrangement_id_title'               => __( 'Plan ID', 'church-tithe-wp' ),
			'arrangement_amount_title'           => __( 'Plan Amount', 'church-tithe-wp' ),
			'arrangement_renewal_title'          => __( 'Next renewal date', 'church-tithe-wp' ),
			'arrangement_action_cancel'          => __( 'Cancel Plan', 'church-tithe-wp' ),
			'arrangement_action_cant_cancel'     => __( 'Cancelling is currently not available.', 'church-tithe-wp' ),
			'arrangement_action_cancel_double'   => __( 'Are you sure you\'d like to cancel?', 'church-tithe-wp' ),
			'arrangement_cancelling'             => __( 'Cancelling Plan...', 'church-tithe-wp' ),
			'arrangement_cancelled'              => __( 'Plan Cancelled', 'church-tithe-wp' ),
			'arrangement_failed_to_cancel'       => __( 'Failed to cancel plan', 'church-tithe-wp' ),
			'login_button_text'                  => __( 'Log in', 'church-tithe-wp' ),
			'login_form_has_an_error'            => __( 'Please check and fix the errors above', 'church-tithe-wp' ),
			'uppercase_search'                   => __( 'Search', 'church-tithe-wp' ),
			'lowercase_search'                   => __( 'search', 'church-tithe-wp' ),
			'uppercase_page'                     => __( 'Page', 'church-tithe-wp' ),
			'lowercase_page'                     => __( 'page', 'church-tithe-wp' ),
			'uppercase_items'                    => __( 'Items', 'church-tithe-wp' ),
			'lowercase_items'                    => __( 'items', 'church-tithe-wp' ),
			'uppercase_per'                      => __( 'Per', 'church-tithe-wp' ),
			'lowercase_per'                      => __( 'per', 'church-tithe-wp' ),
			'uppercase_of'                       => __( 'Of', 'church-tithe-wp' ),
			'lowercase_of'                       => __( 'of', 'church-tithe-wp' ),
			'back'                               => __( 'Back to plans', 'church-tithe-wp' ),
			'zip_code_placeholder'               => __( 'Zip/Postal Code', 'church-tithe-wp' ),
			'input_field_instructions'           => array(
				'tithe_amount'         => array(
					'placeholder_text' => church_tithe_wp_get_saved_setting( $saved_settings, 'amount_title', __( 'How much would you like to give?', 'church-tithe-wp' ) ),
					'initial'          => array(
						'instruction_type'    => 'normal',
						'instruction_message' => church_tithe_wp_get_saved_setting( $saved_settings, 'amount_title', __( 'How much would you like to give?', 'church-tithe-wp' ) ),
					),
					'empty'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => church_tithe_wp_get_saved_setting( $saved_settings, 'amount_title', __( 'How much would you like to give?', 'church-tithe-wp' ) ),
					),
				),
				'recurring'            => array(
					'placeholder_text' => __( 'Recurring', 'church-tithe-wp' ),
					'initial'          => array(
						'instruction_type'    => 'normal',
						'instruction_message' => church_tithe_wp_get_saved_setting( $saved_settings, 'recurring_title', __( 'How often would you like to give this?', 'church-tithe-wp' ) ),
					),
					'success'          => array(
						'instruction_type'    => 'success',
						'instruction_message' => church_tithe_wp_get_saved_setting( $saved_settings, 'recurring_title', __( 'How often would you like to give this?', 'church-tithe-wp' ) ),
					),
					'empty'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => church_tithe_wp_get_saved_setting( $saved_settings, 'recurring_title', __( 'How often would you like to give this?', 'church-tithe-wp' ) ),
					),
				),
				'name'                 => array(
					'placeholder_text' => __( 'Name on Credit Card', 'church-tithe-wp' ),
					'initial'          => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter the name on your card.', 'church-tithe-wp' ),
					),
					'success'          => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Enter the name on your card.', 'church-tithe-wp' ),
					),
					'empty'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Please enter the name on your card.', 'church-tithe-wp' ),
					),
				),
				'privacy_policy'       => array(
					'terms_title'     => __( 'Terms and conditions', 'church-tithe-wp' ),
					'terms_body'      => church_tithe_wp_get_saved_setting( $saved_settings, 'tithe_form_terms' ),
					'terms_show_text' => __( 'View Terms', 'church-tithe-wp' ),
					'terms_hide_text' => __( 'Hide Terms', 'church-tithe-wp' ),

					'initial'         => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'I agree to the terms.', 'church-tithe-wp' ),
					),
					'unchecked'       => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Please agree to the terms before completing your purchase.', 'church-tithe-wp' ),
					),
					'checked'         => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'I agree to the terms.', 'church-tithe-wp' ),
					),
				),
				'email'                => array(
					'placeholder_text'     => __( 'Your email address', 'church-tithe-wp' ),
					'initial'              => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter your email address', 'church-tithe-wp' ),
					),
					'success'              => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Enter your email address', 'church-tithe-wp' ),
					),
					'blank'                => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Enter your email address', 'church-tithe-wp' ),
					),
					'not_an_email_address' => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Make sure you have entered a valid email address', 'church-tithe-wp' ),
					),
				),
				'note_with_tithe'      => array(
					'placeholder_text'  => __( 'Your note here...', 'church-tithe-wp' ),
					'initial'           => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Attach a note (optional)', 'church-tithe-wp' ),
					),
					'empty'             => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Attach a note (optional)', 'church-tithe-wp' ),
					),
					'not_empty_initial' => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Attach a note (optional)', 'church-tithe-wp' ),
					),
					'saving'            => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Saving note...', 'church-tithe-wp' ),
					),
					'success'           => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Note successfully saved!', 'church-tithe-wp' ),
					),
					'error'             => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Unable to save note note at this time. Please try again.', 'church-tithe-wp' ),
					),
				),
				'email_for_login_code' => array(
					'placeholder_text' => __( 'Your email address', 'church-tithe-wp' ),
					'initial'          => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter your email to log in.', 'church-tithe-wp' ),
					),
					'success'          => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Enter your email to log in.', 'church-tithe-wp' ),
					),
					'blank'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Enter your email to log in.', 'church-tithe-wp' ),
					),
					'empty'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Enter your email to log in.', 'church-tithe-wp' ),
					),
				),
				'login_code'           => array(
					'initial' => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Check your email and enter the login code.', 'church-tithe-wp' ),
					),
					'success' => array(
						'instruction_type'    => 'success',
						'instruction_message' => __( 'Check your email and enter the login code.', 'church-tithe-wp' ),
					),
					'blank'   => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Check your email and enter the login code.', 'church-tithe-wp' ),
					),
					'empty'   => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Check your email and enter the login code.', 'church-tithe-wp' ),
					),
				),
				'stripe_all_in_one'    => array(
					'initial'                  => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter your credit card details here.', 'church-tithe-wp' ),
					),
					'empty'                    => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Enter your credit card details here.', 'church-tithe-wp' ),
					),
					'success'                  => array(
						'instruction_type'    => 'normal',
						'instruction_message' => __( 'Enter your credit card details here.', 'church-tithe-wp' ),
					),
					'invalid_number'           => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card number is not a valid credit card number.', 'church-tithe-wp' ),
					),
					'invalid_expiry_month'     => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s expiration month is invalid.', 'church-tithe-wp' ),
					),
					'invalid_expiry_year'      => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s expiration year is invalid.', 'church-tithe-wp' ),
					),
					'invalid_cvc'              => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s security code is invalid.', 'church-tithe-wp' ),
					),
					'incorrect_number'         => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card number is incorrect.', 'church-tithe-wp' ),
					),
					'incomplete_number'        => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card number is incomplete.', 'church-tithe-wp' ),
					),
					'incomplete_cvc'           => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s security code is incomplete.', 'church-tithe-wp' ),
					),
					'incomplete_expiry'        => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s expiration date is incomplete.', 'church-tithe-wp' ),
					),
					'incomplete_zip'           => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s zip code is incomplete.', 'church-tithe-wp' ),
					),
					'expired_card'             => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card has expired.', 'church-tithe-wp' ),
					),
					'incorrect_cvc'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s security code is incorrect.', 'church-tithe-wp' ),
					),
					'incorrect_zip'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s zip code failed validation.', 'church-tithe-wp' ),
					),
					'invalid_expiry_year_past' => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card\'s expiration year is in the past', 'church-tithe-wp' ),
					),
					'card_declined'            => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The card was declined.', 'church-tithe-wp' ),
					),
					'missing'                  => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'There is no card on a customer that is being charged.', 'church-tithe-wp' ),
					),
					'processing_error'         => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'An error occurred while processing the card.', 'church-tithe-wp' ),
					),
					'invalid_request_error'    => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'Unable to process this payment, please try again or use alternative method.', 'church-tithe-wp' ),
					),
					'invalid_sofort_country'   => array(
						'instruction_type'    => 'error',
						'instruction_message' => __( 'The billing country is not accepted by SOFORT. Please try another country.', 'church-tithe-wp' ),
					),
				),
			),
		),
	);

	return $church_tithe_wp_vars;
}
