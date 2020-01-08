var church_tithe_wp_vars = church_tithe_wp_js_vars.tithe_form_vars;

window.Church_Tithe_WP_Arrangement = class Church_Tithe_WP_Arrangement extends React.Component{

	constructor( props ){
		super(props);

		this.state = {
			arrangement_cancel_intention_count: 0,
		};

	}

	cancel_arrangement() {

		// Double check with the user to make sure they meant to cancel
		if ( 0 == this.state.arrangement_cancel_intention_count ) {

			this.setState( {
				arrangement_cancel_intention_count: 1
			} );

			return;
		}

		// Get the current arrangement
		var current_arrangement_info = this.props.main_component.state.current_arrangement_info;

		// Update the recurring status of that arrangement to "cancelling"
		current_arrangement_info.recurring_status = 'cancelling';

		// Set the state to be cancelling
		this.props.main_component.setState( {
			current_arrangement_info: current_arrangement_info
		} );

		var this_component = this;

		var postData = new FormData();
		postData.append('action', 'church_tithe_wp_cancel_arrangement' );
		postData.append('church_tithe_wp_arrangement_id', this.props.main_component.state.current_arrangement_info.id );
		postData.append('church_tithe_wp_cancel_arrangement_nonce', this.props.main_component.state.frontend_nonces.cancel_arrangement_nonce );

		// Cancel the arrangement in question
		fetch( church_tithe_wp_js_vars.ajaxurl + '?church_tithe_wp_cancel_arrangement', {
			method: "POST",
			mode: "same-origin",
			credentials: "same-origin",
			body: postData
		} ).then(
			function( response ) {
				if ( response.status !== 200 ) {

					// We were unable to cancel the arrangement

					// Get the current arrangement
					current_arrangement_info = this_component.props.main_component.state.current_arrangement_info;

					// Update the recurring status of that arrangement to "cancelling"
					current_arrangement_info.recurring_status = 'failed_to_cancel';

					// Update the arrangement from the fetched data
					this_component.props.main_component.setState( {
						current_arrangement_info: data.current_arrangement_info
					} );

					console.log('Looks like there was a problem. Status Code: ' + response.status);

					return;
				}

				// Examine the text in the response
				response.json().then(
					function( data ) {
						if ( data.success ) {

							// Reset the check user nonce
							this_component.props.main_component.setState( {
								user_logged_in: data.user_logged_in,
								frontend_nonces: data.frontend_nonces ? data.frontend_nonces : this_component.props.main_component.state.frontend_nonces
							}, () => {
								// Set the current arrangement info based on the updated data
								this_component.props.main_component.setState( {
									current_arrangement_info: data.arrangement_info,
									arrangement_cancel_intention_count: 0
								} );

								// Reload the arrangements so that "cancelled" shows up in the column for this arrangement
								this_component.props.main_component.setState( {
									reload_arrangements: true
								} );

							} );

						} else {

							// We were unable to cancel the arrangement

							// Get the current arrangement
							current_arrangement_info = this_component.props.main_component.state.current_arrangement_info;

							// Update the recurring status of that arrangement to "cancelling"
							current_arrangement_info.recurring_status = 'failed_to_cancel';

							// Set the state to be cancelling
							this_component.props.main_component.setState( {
								current_arrangement_info: current_arrangement_info
							} );

							if ( 'not_logged_in' == data.error_code ) {

								// Remove the user ID from the main state and set the state to be login
								this_component.props.main_component.setState( {
									user_logged_in: null,
									frontend_nonces: data.frontend_nonces ? data.frontend_nonces : this_component.props.main_component.state.frontend_nonces
								} );
							}
						}
					}
				).catch(
					function( err ) {

						// We were unable to cancel the arrangement

						// Get the current arrangement
						current_arrangement_info = this_component.props.main_component.state.current_arrangement_info;

						// Update the recurring status of that arrangement to "cancelling"
						current_arrangement_info.recurring_status = 'failed_to_cancel';

						// Set the state to be cancelling
						this_component.props.main_component.setState( {
							current_arrangement_info: current_arrangement_info
						} );

						console.log('Fetch Error: ', err);
					}
				);
			}
		).catch(
			function( err ) {

				// We were unable to cancel the arrangement

				// Get the current arrangement
				current_arrangement_info = this_component.props.main_component.state.current_arrangement_info;

				// Update the recurring status of that arrangement to "cancelling"
				current_arrangement_info.recurring_status = 'failed_to_cancel';

				// Set the state to be cancelling
				this_component.props.main_component.setState( {
					current_arrangement_info: current_arrangement_info
				} );

				console.log('Fetch Error :-S', err);
			}
		);



	}

	render_cancel_button() {

		var button_text;

		// If the webhook has not arrived, show "Unable to cancel, webhook failed" on the button.
		if ( ! this.props.main_component.state.current_arrangement_info.webhook_succeeded ) {
			return ( <button className="church-tithe-wp-receipt-line-item-action church-tithe-wp-arrangement-action-cancel">{ this.props.main_component.state.unique_settings.strings.arrangement_action_cant_cancel }</button> );
		}

		if ( 0 == this.state.arrangement_cancel_intention_count ) {
			button_text = this.props.main_component.state.unique_settings.strings.arrangement_action_cancel;
		}

		if ( 1 == this.state.arrangement_cancel_intention_count ) {
			button_text = this.props.main_component.state.unique_settings.strings.arrangement_action_cancel_double;
		}

		if ( ! this.props.main_component.state.current_arrangement_info.recurring_status || 'on' == this.props.main_component.state.current_arrangement_info.recurring_status ) {
			return(
				<button className="church-tithe-wp-receipt-line-item-action church-tithe-wp-arrangement-action-cancel" onClick={ this.cancel_arrangement.bind( this ) }>{ button_text }</button>
			);
		}

		if ( 'failed_to_cancel' == this.props.main_component.state.current_arrangement_info.recurring_status ) {
			return(
				<button className="church-tithe-wp-receipt-line-item-action church-tithe-wp-arrangement-action-cancel">{ this.props.main_component.state.unique_settings.strings.arrangement_failed_to_cancel }</button>
			);
		}

		if ( 'cancelling' == this.props.main_component.state.current_arrangement_info.recurring_status ) {
			return(
				<button className="church-tithe-wp-receipt-line-item-action church-tithe-wp-arrangement-action-cancel">{ this.props.main_component.state.unique_settings.strings.arrangement_cancelling }</button>
			);
		}

		if ( 'cancelled' == this.props.main_component.state.current_arrangement_info.recurring_status ) {
			return(
				<button className="church-tithe-wp-receipt-line-item-action church-tithe-wp-arrangement-action-cancel">{ this.props.main_component.state.unique_settings.strings.arrangement_cancelled }</button>
			);
		}
	}

	render_renewal_date() {

		if ( 'cancelled' == this.props.main_component.state.current_arrangement_info.recurring_status ) {
			return '';
		}

		return (
			<div className="church-tithe-wp-arrangement-renewal-date">
				<span className="church-tithe-wp-receipt-line-item-title church-tithe-wp-arrangement-renewal-date-title">{ this.props.main_component.state.unique_settings.strings.arrangement_renewal_title + ': ' }</span>
				<span className="church-tithe-wp-receipt-line-item-value church-tithe-wp-arrangement-renewal-date-value">{ church_tithe_wp_format_date( this.props.main_component.state.current_arrangement_info.renewal_date ) }</span>
			</div>
		);

	}

	format_amount() {

		var cents = this.props.main_component.state.current_arrangement_info.amount;
		var currency = this.props.main_component.state.current_arrangement_info.currency;
		var is_zero_decimal_currency = this.props.main_component.state.current_arrangement_info.is_zero_decimal_currency;
		var locale = this.props.main_component.state.unique_settings.locale;
		var string_after = this.props.main_component.state.current_arrangement_info.string_after + ' (' + currency.toUpperCase() + ')';

		return church_tithe_wp_format_money( cents, currency, is_zero_decimal_currency, locale, string_after );

	}

	render() {

		if ( ! this.props.main_component.state.current_arrangement_info ) {
			return ( <Church_Tithe_WP_Spinner /> );
		}

		return (
			<div className="church-tithe-wp-arrangement">
					<div className={ 'church-tithe-wp-receipt-title' }>{ this.props.main_component.state.unique_settings.strings.arrangement_details }</div>
					<div className="church-tithe-wp-arrangement-id">
						<span className="church-tithe-wp-receipt-line-item-title church-tithe-wp-arrangement-id-title">{ this.props.main_component.state.unique_settings.strings.arrangement_id_title + ': ' }</span>
						<span className="church-tithe-wp-receipt-line-item-value church-tithe-wp-arrangement-id-value">{ this.props.main_component.state.current_arrangement_info.id }</span>
					</div>
					<div className="church-tithe-wp-arrangement-interval">
						<span className="church-tithe-wp-receipt-line-item-title church-tithe-wp-arrangement-plan-title">{ this.props.main_component.state.unique_settings.strings.arrangement_amount_title + ': ' }</span>
						<span className="church-tithe-wp-receipt-line-item-value church-tithe-wp-arrangement-plan-value">{ this.format_amount() }</span>
					</div>
					{ this.render_renewal_date() }
					<div className="church-tithe-wp-arrangement-actions">
						{ this.render_cancel_button() }
					</div>

			</div>
		)
	}

}
export default Church_Tithe_WP_Arrangement;
