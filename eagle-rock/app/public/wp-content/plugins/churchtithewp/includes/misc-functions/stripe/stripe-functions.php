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
 * Set the stripe customer key in the user meta.
 *
 * @access      public
 * @since       1.0.0.
 * @param       string $email An email address for a WordPress User that already exists.
 * @return      array A Stripe Customer Object in an array form
 */
function church_tithe_wp_set_stripe_customer_id( $email ) {

	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		return false;
	}

	$meta_key = church_tithe_wp_stripe_get_customer_key();

	// Create Stripe customer and use email to identify them in stripe.
	$s = new Church_Tithe_WP_Stripe(
		array(
			'url'    => 'https://api.stripe.com/v1/customers',
			'fields' => array(
				'email' => $email,
			),
		)
	);

	// Execute the call to Stripe.
	$customer = $s->call();

	$stripe_customer_id = $customer['id'];

	// Store the minimal amount of info we have to for this customer, just their Stripe Customer ID.
	$success = update_user_meta( $user->ID, $meta_key, $stripe_customer_id );

	if ( $success ) {
		return $customer;
	} else {
		return false;
	}
}

/**
 * Get the stripe customer key from the user meta.
 * If one doesn't exist at Stripe, we'll create one and save that.
 *
 * @access      public
 * @since       1.0.0.
 * @param       string $email An email address for a WordPress User that already exists.
 * @return      array A Stripe Customer Object in an array form
 */
function church_tithe_wp_get_stripe_customer( $email ) {

	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		return false;
	}

	$meta_key = church_tithe_wp_stripe_get_customer_key();

	$stripe_customer_id = get_user_meta( $user->ID, $meta_key, true );

	// If there isn't a stripe customer ID, create one.
	if ( empty( $stripe_customer_id ) ) {

		$stripe_customer = church_tithe_wp_set_stripe_customer_id( $email );

	} else {

		// Make sure the stripe customer id actually exists in Stripe.
		$s = new Church_Tithe_WP_Stripe(
			array(
				'url' => 'https://api.stripe.com/v1/customers/' . $stripe_customer_id,
			)
		);

		// Execute the call to Stripe.
		$customer = $s->call();

		if ( ! isset( $customer['id'] ) || ( isset( $customer['id'] ) && $customer['id'] !== $stripe_customer_id ) ) {
			$stripe_customer = church_tithe_wp_set_stripe_customer_id( $email );
		} else {
			$stripe_customer = $customer;
		}
	}

	return $stripe_customer;
}

/**
 * Get the meta key for storing Stripe customer IDs in
 *
 * @access      public
 * @since       1.0.0.
 * @return      string
 */
function church_tithe_wp_stripe_get_customer_key() {

	$key = '_church_tithe_wp_stripe_customer_id';
	if ( church_tithe_wp_stripe_is_test_mode() ) {
		$key .= '_test';
	}
	return $key;
}

/**
 * Return true if Stripe is in test mode. False if not.
 *
 * @access      public
 * @since       1.0.0.
 * @return      bool
 */
function church_tithe_wp_stripe_is_test_mode() {

	$saved_settings = get_option( 'church_tithe_wp_settings' );

	$test_mode = church_tithe_wp_get_saved_setting( $saved_settings, 'stripe_test_mode' );

	if ( 'true' === $test_mode ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Return true if Stripe is in live mode. False if not.
 *
 * @access      public
 * @since       1.0.0.
 * @return      bool
 */
function church_tithe_wp_stripe_is_live_mode() {

	$saved_settings = get_option( 'church_tithe_wp_settings' );

	$test_mode = church_tithe_wp_get_saved_setting( $saved_settings, 'stripe_test_mode' );

	if ( 'true' === $test_mode ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Get the Stripe API publishable key based on the test/live mode
 *
 * @access      public
 * @since       1.0.0.
 * @return      string
 */
function church_tithe_wp_get_stripe_publishable_key() {

	$settings = get_option( 'church_tithe_wp_settings' );

	if ( ! church_tithe_wp_stripe_is_test_mode() ) {
		$mode = 'live';
	} else {
		$mode = 'test';
	}

	return isset( $settings[ 'stripe_' . $mode . '_public_key' ] ) ? $settings[ 'stripe_' . $mode . '_public_key' ] : false;

}

/**
 * Get the Stripe account ID
 *
 * @access      public
 * @since       1.0.0.
 * @return      string
 */
function church_tithe_wp_get_stripe_account_id( $mode = 'live' ) {

	$settings = get_option( 'church_tithe_wp_settings' );

	return isset( $settings[ 'stripe_' . $mode . '_account_id' ] ) ? $settings[ 'stripe_' . $mode . '_account_id' ] : false;

}

/**
 * Get the Stripe API secret key based on the test/live mode
 *
 * @access      public
 * @since       1.0.0.
 * @param       string $mode If blank, get the secret key based on the current mode. Otherwise, get the "live" or "test" secret key.
 * @return      string
 */
function church_tithe_wp_get_stripe_secret_key( $mode = false ) {

	$settings = get_option( 'church_tithe_wp_settings' );

	if ( empty( $mode ) ) {
		if ( ! church_tithe_wp_stripe_is_test_mode() ) {
			$mode = 'live';
		} else {
			$mode = 'test';
		}
	}

	return isset( $settings[ 'stripe_' . $mode . '_secret_key' ] ) ? $settings[ 'stripe_' . $mode . '_secret_key' ] : false;

}

/**
 * Return true if Stripe test mode is successfully connected/stored. False if not.
 *
 * @access      public
 * @since       1.0.0.
 * @return      bool
 */
function church_tithe_wp_stripe_test_mode_connected() {

	$settings = get_option( 'church_tithe_wp_settings' );

	if (
		isset( $settings['stripe_test_public_key'] ) &&
		! empty( $settings['stripe_test_public_key'] ) &&
		isset( $settings['stripe_test_secret_key'] ) &&
		! empty( $settings['stripe_test_secret_key'] )
	) {
		return true;
	} else {
		return false;
	}

}

/**
 * Return true if Stripe live mode is successfully connected/stored. False if not.
 *
 * @access      public
 * @since       1.0.0.
 * @return      bool
 */
function church_tithe_wp_stripe_live_mode_connected() {

	$settings = get_option( 'church_tithe_wp_settings' );

	if (
		isset( $settings['stripe_live_public_key'] ) &&
		! empty( $settings['stripe_live_public_key'] ) &&
		isset( $settings['stripe_live_secret_key'] ) &&
		! empty( $settings['stripe_live_secret_key'] )
	) {
		return true;
	} else {
		return false;
	}

}

/**
 * Create the Apple Pay verification file in the site root
 *
 * @since  1.0.0
 * @return bool
 */
function church_tithe_wp_create_apple_verification_file() {

	$home_path       = ABSPATH;
	$well_known_path = $home_path . '.well-known/';
	$apple_ver_path  = $well_known_path . 'apple-developer-merchantid-domain-association';

	if ( file_exists( $apple_ver_path ) ) {
		return true;
	}

	// First lets check/make-sure that the /well-known directory exists. If not, create it.
	if ( ! is_dir( $well_known_path ) ) {

		if ( is_writable( $home_path ) ) {
			$well_known_dir_exists = mkdir( $well_known_path );
		}
	} else {
		$well_known_dir_exists = true;
	}

	// If the .well-known directory exists.
	if ( ! $well_known_dir_exists ) {
		return false;
	}

	// Create the apple developer verification file inside the .well-known directory.
	$apple_ver_file_created = file_put_contents( $well_known_path . 'apple-developer-merchantid-domain-association', '7B227073704964223A2239373943394538343346343131343044463144313834343232393232313734313034353044314339464446394437384337313531303944334643463542433731222C2276657273696F6E223A312C22637265617465644F6E223A313437313435343137313137362C227369676E6174757265223A2233303830303630393261383634383836663730643031303730326130383033303830303230313031333130663330306430363039363038363438303136353033303430323031303530303330383030363039326138363438383666373064303130373031303030306130383033303832303365363330383230333862613030333032303130323032303836383630663639396439636361373066333030613036303832613836343863653364303430333032333037613331326533303263303630333535303430333063323534313730373036633635323034313730373036633639363336313734363936663665323034393665373436353637373236313734363936663665323034333431323032643230343733333331323633303234303630333535303430623063316434313730373036633635323034333635373237343639363636393633363137343639366636653230343137353734363836663732363937343739333131333330313130363033353530343061306330613431373037303663363532303439366536333265333130623330303930363033353530343036313330323535353333303165313730643331333633303336333033333331333833313336333433303561313730643332333133303336333033323331333833313336333433303561333036323331323833303236303630333535303430333063316636353633363332643733366437303264363237323666366236353732326437333639363736653566353534333334326435333431346534343432346635383331313433303132303630333535303430623063306236393466353332303533373937333734363536643733333131333330313130363033353530343061306330613431373037303663363532303439366536333265333130623330303930363033353530343036313330323535353333303539333031333036303732613836343863653364303230313036303832613836343863653364303330313037303334323030303438323330666461626333396366373565323032633530643939623435313265363337653261393031646436636233653062316364346235323637393866386366346562646538316132356138633231653463333364646365386532613936633266366166613139333033343563346538376134343236636539353162313239356133383230323131333038323032306433303435303630383262303630313035303530373031303130343339333033373330333530363038326230363031303530353037333030313836323936383734373437303361326632663666363337333730326536313730373036633635326536333666366432663666363337333730333033343264363137303730366336353631363936333631333333303332333031643036303335353164306530343136303431343032323433303062396165656564343633313937613461363561323939653432373138323163343533303063303630333535316431333031303166663034303233303030333031663036303335353164323330343138333031363830313432336632343963343466393365346566323765366334663632383663336661326262666432653462333038323031316430363033353531643230303438323031313433303832303131303330383230313063303630393261383634383836663736333634303530313330383166653330383163333036303832623036303130353035303730323032333038316236306338316233353236353663363936313665363336353230366636653230373436383639373332303633363537323734363936363639363336313734363532303632373932303631366537393230373036313732373437393230363137333733373536643635373332303631363336333635373037343631366536333635323036663636323037343638363532303734363836353665323036313730373036633639363336313632366336353230373337343631366536343631373236343230373436353732366437333230363136653634323036333666366536343639373436393666366537333230366636363230373537333635326332303633363537323734363936363639363336313734363532303730366636633639363337393230363136653634323036333635373237343639363636393633363137343639366636653230373037323631363337343639363336353230373337343631373436353664363536653734373332653330333630363038326230363031303530353037303230313136326136383734373437303361326632663737373737373265363137303730366336353265363336663664326636333635373237343639363636393633363137343635363137353734363836663732363937343739326633303334303630333535316431663034326433303262333032396130323761303235383632333638373437343730336132663266363337323663326536313730373036633635326536333666366432663631373037303663363536313639363336313333326536333732366333303065303630333535316430663031303166663034303430333032303738303330306630363039326138363438383666373633363430363164303430323035303033303061303630383261383634386365336430343033303230333439303033303436303232313030646131633633616538626535663634663865313165383635363933376239623639633437326265393365616333323333613136373933366534613864356538333032323130306264356166626638363966336330636132373462326664646534663731373135396362336264373139396232636130666634303964653635396138326232346433303832303265653330383230323735613030333032303130323032303834393664326662663361393864613937333030613036303832613836343863653364303430333032333036373331316233303139303630333535303430333063313234313730373036633635323035323666366637343230343334313230326432303437333333313236333032343036303335353034306230633164343137303730366336353230343336353732373436393636363936333631373436393666366532303431373537343638366637323639373437393331313333303131303630333535303430613063306134313730373036633635323034393665363332653331306233303039303630333535303430363133303235353533333031653137306433313334333033353330333633323333333433363333333035613137306433323339333033353330333633323333333433363333333035613330376133313265333032633036303335353034303330633235343137303730366336353230343137303730366336393633363137343639366636653230343936653734363536373732363137343639366636653230343334313230326432303437333333313236333032343036303335353034306230633164343137303730366336353230343336353732373436393636363936333631373436393666366532303431373537343638366637323639373437393331313333303131303630333535303430613063306134313730373036633635323034393665363332653331306233303039303630333535303430363133303235353533333035393330313330363037326138363438636533643032303130363038326138363438636533643033303130373033343230303034663031373131383431396437363438356435316135653235383130373736653838306132656664653762616534646530386466633462393365313333353664353636356233356165323264303937373630643232346537626261303866643736313763653838636237366262363637306265633865383239383466663534343561333831663733303831663433303436303630383262303630313035303530373031303130343361333033383330333630363038326230363031303530353037333030313836326136383734373437303361326632663666363337333730326536313730373036633635326536333666366432663666363337333730333033343264363137303730366336353732366636663734363336313637333333303164303630333535316430653034313630343134323366323439633434663933653465663237653663346636323836633366613262626664326534623330306630363033353531643133303130316666303430353330303330313031666633303166303630333535316432333034313833303136383031346262623064656131353833333838396161343861393964656265626465626166646163623234616233303337303630333535316431663034333033303265333032636130326161303238383632363638373437343730336132663266363337323663326536313730373036633635326536333666366432663631373037303663363537323666366637343633363136373333326536333732366333303065303630333535316430663031303166663034303430333032303130363330313030363061326138363438383666373633363430363032306530343032303530303330306130363038326138363438636533643034303330323033363730303330363430323330336163663732383335313136393962313836666233356333353663613632626666343137656464393066373534646132386562656631396338313565343262373839663839386637396235393966393864353431306438663964653963326665303233303332326464353434323162306133303537373663356466333338336239303637666431373763326332313664393634666336373236393832313236663534663837613764316239396362396230393839323136313036393930663039393231643030303033313832303136303330383230313563303230313031333038313836333037613331326533303263303630333535303430333063323534313730373036633635323034313730373036633639363336313734363936663665323034393665373436353637373236313734363936663665323034333431323032643230343733333331323633303234303630333535303430623063316434313730373036633635323034333635373237343639363636393633363137343639366636653230343137353734363836663732363937343739333131333330313130363033353530343061306330613431373037303663363532303439366536333265333130623330303930363033353530343036313330323535353330323038363836306636393964396363613730663330306430363039363038363438303136353033303430323031303530306130363933303138303630393261383634383836663730643031303930333331306230363039326138363438383666373064303130373031333031633036303932613836343838366637306430313039303533313066313730643331333633303338333133373331333733313336333133313561333032663036303932613836343838366637306430313039303433313232303432303733343832623432653665366332323264616536643963303961346336663332316534656136653666326661626631356430376562333338643264613435646233303061303630383261383634386365336430343033303230343438333034363032323130306564333264376438616131623536623036626164623162396639396264643063653662363931316530623032393232633934333362663564326130656135353830323231303066393433353637663030323361643061343561373236663238376636303062656334666566373335383832383935633733313531383337336163383934383137303030303030303030303030227D' );

	if ( false !== $apple_ver_file_created ) {
		return true;
	} else {
		return false;
	}

}
add_action( 'admin_init', 'church_tithe_wp_create_apple_verification_file' );

/**
 * Cancel a subscription at Stripe
 *
 * @since  1.0.0
 * @param  object $arrangement An arrangement object.
 * @param  string $reason A sentence describing the reason for cancellation.
 * @return array An array containing success or failure
 */
function church_tithe_wp_cancel_stripe_subscription( $arrangement, $reason ) {

	// If no Stripe subscription ID was found for that arrangement, we don't have anything we can cancel.
	if ( ! $arrangement->gateway_subscription_id ) {
		return array(
			'success'    => false,
			'error_code' => 'no_subscription_id_attached_to_arrangement',
			'details'    => 'No subscription ID was attached to arrangement ' . $arrangement->id,
		);
	}

	// Send a call to Stripe to cancel this subscription.
	$s = new Church_Tithe_WP_Stripe_Delete(
		array(
			'url' => 'https://api.stripe.com/v1/subscriptions/' . $arrangement->gateway_subscription_id,
		)
	);

	// Execute the call to Stripe.
	$subscription = $s->call();

	// If you try and cancel a subscription that was already cancelled, this will be a "resource_missing" error, so it's not neccesarily a problem.
	if ( isset( $subscription['error'] ) ) {

		// If the sub was already deleted, you get a "resource_missing" from Stripe, so we'll count that as a success. Al other errors will be caught here.
		if ( 'resource_missing' !== $subscription['error']['code'] ) {

			// Email the admin to let them know about this error. They probably should know about this one.
			$admin_email = get_bloginfo( 'admin_email' );
			// translators: The url of the website.
			$subject   = sprintf( __( 'A user attempted to cancel their subscription but it failed on %s.', 'church-tithe-wp' ), get_bloginfo( 'url' ) );
			$body      = __( 'Please email support@churchtithewp.com with the following information for assistance.', 'church-tithe-wp' ) . ' ' . wp_json_encode( $subscription['error'] ) . "\n" . __( 'Data in request:', 'church-tithe-wp' ) . "\n" . wp_json_encode( $s->fields );
			$mail_sent = wp_mail( $admin_email, $subject, $body );

			return array(
				'success'      => false,
				'error_code'   => 'unable_to_cancel',
				'type'         => 'Subscription',
				'details'      => 'Unable to cancel subscription',
				'subscription' => $subscription,
			);

			// If "resource_missing" is the error from Stripe, we probably already deleted this, and this is a duplicate request.
			// Therefore, we won't update the reason it was cancelled.
		} else {

			$arrangement->update(
				array(
					'recurring_status' => 'cancelled',
				)
			);

		}
	} else {

		$arrangement->update(
			array(
				'recurring_status' => 'cancelled',
				'status_reason'    => $reason ? sanitize_text_field( $reason ) : 'general',
			)
		);
	}

	return array(
		'success'      => true,
		'subscription' => $subscription,
	);
}

/**
 * Get the the country code of this Stripe Account
 *
 * @since  1.0.0
 * @return string The 2 letter country code attached to this Stripe Account
 */
function church_tithe_wp_stripe_get_account_country_code() {

	// Check if we have fetched this before so it can be cached and prevent a call to Stripe.
	$stripe_account_country_code = get_option( 'church_tithe_wp_stripe_country_code' );

	if ( ! empty( $stripe_account_country_code ) ) {
		return $stripe_account_country_code;
	}

	$force_mode = false;

	// If we are in test mode, but no test key exists, use the live key, as the account country code is no different in live/test mode.
	if ( church_tithe_wp_stripe_is_test_mode() ) {
		$mode            = 'test';
		$test_secret_key = church_tithe_wp_get_stripe_secret_key();
		if ( empty( $test_secret_key ) ) {
			$force_mode = 'live';
			$mode       = 'live';
		}
	} else {
		$mode = 'live';
	}

	// Check if we have a Stripe Account ID or not yet.
	$account_id = church_tithe_wp_get_stripe_account_id( $mode );

	// Default to US.
	if ( ! $account_id ) {
		return 'US';
	}

	// Ping stripe to get the account data.
	$s = new Church_Tithe_WP_Stripe_Get(
		array(
			'force_mode' => $force_mode, // We force this to live mode if no test key exists.
			'url'        => 'https://api.stripe.com/v1/accounts/' . $account_id,
		)
	);

	// Execute the call to Stripe.
	$account_data = $s->call();

	if ( isset( $account_data['error'] ) || ! isset( $account_data['country'] ) ) {
		// Default to US for now.
		return 'US';
	}

	// Save it to the database so it is cached.
	update_option( 'church_tithe_wp_stripe_country_code', sanitize_text_field( $account_data['country'] ) );

	return $account_data['country'];

}

/**
 * Get the currencies available to this Stripe Account based on its location
 *
 * @since  1.0.0
 * @return array Array of currencies available to the connected account
 */
function church_tithe_wp_stripe_get_available_currencies() {

	// Check if we have fetched this before so it can be cached and prevent a call to Stripe.
	$cached_currencies = get_option( 'church_tithe_wp_stripe_available_currencies' );

	if ( ! empty( $cached_currencies ) ) {
		return $cached_currencies;
	}

	// Ping stripe to get the account data.
	$country_code = church_tithe_wp_stripe_get_account_country_code();

	$force_mode = false;

	// If we are in test mode, but no test key exists, use the live key, as currencies available are no different in live/test mode.
	if ( church_tithe_wp_stripe_is_test_mode() ) {
		$test_secret_key = church_tithe_wp_get_stripe_secret_key();
		if ( empty( $test_secret_key ) ) {
			$force_mode = 'live';
		}
	}

	// Ping stripe to get the data about that country, including the currencies it can charge.
	$s = new Church_Tithe_WP_Stripe_Get(
		array(
			'force_mode' => $force_mode, // We force this to live mode if no test key exists.
			'url' => 'https://api.stripe.com/v1/country_specs/' . $country_code,
		)
	);

	// Execute the call to Stripe.
	$country_data = $s->call();

	if ( isset( $country_data['error'] ) || ! isset( $country_data['supported_payment_currencies'] ) || ! is_array( $country_data['supported_payment_currencies'] ) ) {
		return array();
	}

	// Sanitize the values.
	$sanitized_stripe_currencies = array();

	// Loop through each currency from Stripe and sanitize it.
	foreach ( $country_data['supported_payment_currencies'] as $currency_code ) {
		$sanitized_stripe_currencies[] = sanitize_text_field( $currency_code );
	}

	update_option( 'church_tithe_wp_stripe_available_currencies', $sanitized_stripe_currencies );

	return $sanitized_stripe_currencies;
}

/**
 * Get the name of the connected Stripe Account.
 *
 * @since  1.0.7
 * @param  string $mode The mode to get the account for. Defaults to live.
 * @param  bool $use_cached_value If set to false, a call to Stripe will take place. If true, it will return a cached value from a monthly transient.
 */
function church_tithe_wp_stripe_account_name( $mode = 'live', $use_cached_value = false ) {

	if ( $use_cached_value ) {
		$cached_account_name = get_transient( 'ctwp_stripe_account_name_' . $mode . '_mode' );

		if ( ! empty( $cached_account_name ) ) {
			return $cached_account_name;
		}
	}

	// Check if we have a Stripe Account ID or not yet.
	$account_id = church_tithe_wp_get_stripe_account_id( $mode );

	if ( ! $account_id ) {
		return false;
	}

	// Ping stripe to get the data about that account.
	$s = new Church_Tithe_WP_Stripe_Get(
		array(
			'force_mode' => $mode,
			'url'        => 'https://api.stripe.com/v1/accounts/' . $account_id,
		)
	);

	// Execute the call to Stripe.
	$account_data = $s->call();

	if ( isset( $account_data['error'] ) ) {
		print_r(  $account_data['error'] );
		return false;
	}

	$account_name = $account_data['settings']['dashboard']['display_name'];
	set_transient( 'ctwp_stripe_account_name_' . $mode . '_mode', $account_name, MONTH_IN_SECONDS );

	return $account_name;
}
