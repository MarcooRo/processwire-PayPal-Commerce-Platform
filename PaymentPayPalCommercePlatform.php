<?php namespace ProcessWire;
/*
This is a sendbox vertion.
If you wan use live rember to change the URL in access_token() and  client_token();

PayPal documentatio start here:
https://developer.paypal.com/docs/platforms/
*/

class PaymentPayPalCommercePlatform extends PaymentModule {

	public static function getModuleInfo() {
		return array('title'=> 'PayPal Commerce Platform',
			'summary'=> 'PayPal Commerce Platform - server side, credit card, paypal',
			'author'=> 'Marco Romano',
			'version'=> '0.1',
			'singular'=> false,
			'autoload'=> true,
			'requires'=> 'PaymentModule',
		);
	}

	public function init() {
		require_once dirname(wire('modules')->getModuleFile('PadCart')) . '/PadOrderProcess.php';
	}

	public function getTitle() {
		return __("All Payment (Vai PayPal)");
	}

	public function access_token() {
		if ($this->enviroment=="sandbox") {
			$client_ID = $this->clientIdSandbox;
		} else {
			$client_ID = $this->clientIdLive;
		}

		$curl=curl_init();

		//API CREDENTIALS
		$clientId = $client_ID;
		$secret = $this->secretCode;

		curl_setopt_array($curl, array(CURLOPT_URL=> "https://api.sandbox.paypal.com/v1/oauth2/token", // Change URL for live
			CURLOPT_RETURNTRANSFER=> true,
			CURLOPT_SSL_VERIFYHOST=> false,
			CURLOPT_SSL_VERIFYPEER=> false,
			CURLOPT_CUSTOMREQUEST=> "POST",
			CURLOPT_POSTFIELDS=> "grant_type=client_credentials",
			CURLOPT_USERPWD=> $clientId .":". $secret,
			CURLOPT_HEADER=> false,
			CURLOPT_HTTPHEADER=> array("Accept: application/json",
				"Accept-Language: en_US",
			),
		));

		$response=curl_exec($curl);
		$err=curl_error($curl);

		$response=json_decode($response);
		$access_token=$response->access_token;
		return $access_token;
	}

	public function client_token() {
		$curl2=curl_init();
		curl_setopt_array($curl2, array(CURLOPT_URL=> "https://api.sandbox.paypal.com/v1/identity/generate-token", // Change URL for live
			CURLOPT_RETURNTRANSFER=> true,
			CURLOPT_SSL_VERIFYHOST=> false,
			CURLOPT_SSL_VERIFYPEER=> false,
			CURLOPT_CUSTOMREQUEST=> "POST",
			CURLOPT_HEADER=> false,
			CURLOPT_HTTPHEADER=> array("Accept: application/json",
				"Accept-Language: en_US",
				"Authorization: Bearer ". $this->access_token(),
			),
		));
		$response=curl_exec($curl2);
		$err=curl_error($curl2);

		curl_close($curl2);
		$response=json_decode($response);
		$client_token=$response->client_token;
		return $client_token;
	}

	public function render() {

		if ($this->enviroment=="sandbox") {
			$client_ID=$this->clientIdSandbox;
		}	else {
			$client_ID=$this->clientIdLive;
		}

		// original CSS
		echo "<style>
			/*
				.form-container {
					display: flex;
					background-color: #EEE;
					justify-content: center;
					align-items: center;
					height: 100%;
					flex-direction: column;
					border: 1em solid #fff;
					box-sizing: border-box;
					position: relative;
				}*/
			@media (max-width: 476px) {
				.form-container {
					border: none;
				}
			}

			.cardinfo-wrapper {
				display: flex;
				justify-content: space-around;
			}


			.card-shape,
			#card-form.visa,
			#card-form.master-card,
			#card-form.maestro,
			#card-form.american-express,
			#card-form.discover,
			#card-form.unionpay,
			#card-form.jcb,
			#card-form.diners-club {
				border-radius: 6px;
				padding: 2em 2em 1em;
			}

			@media (max-width: 476px) {

				.card-shape,
				#card-form.visa,
				#card-form.master-card,
				#card-form.maestro,
				#card-form.american-express,
				#card-form.discover,
				#card-form.unionpay,
				#card-form.jcb,
				#card-form.diners-club {
					padding: 2em 1.5em 1em;
				}
			}


			#pp-container {
				background-color: #FFF;
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
				padding: 8em 3em 2em;
				width: 20em;
				margin-bottom: 2em;
				transition: all 600ms cubic-bezier(0.2, 1.3, 0.7, 1);
				-webkit-animation: cardIntro 500ms cubic-bezier(0.2, 1.3, 0.7, 1);
				animation: cardIntro 500ms cubic-bezier(0.2, 1.3, 0.7, 1);
				z-index: 1;
			}




			#card-form {
				background-color: #FFF;
				box-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
				padding: 8em 3em 2em;
				width: 20em;
				margin-bottom: 2em;
				transition: all 600ms cubic-bezier(0.2, 1.3, 0.7, 1);
				-webkit-animation: cardIntro 500ms cubic-bezier(0.2, 1.3, 0.7, 1);
				animation: cardIntro 500ms cubic-bezier(0.2, 1.3, 0.7, 1);
				z-index: 1;
			}

			#card-form:hover {
				box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
			}

			@media (max-width: 476px) {
				#card-form {
					box-sizing: border-box;
					padding: 7em 2em 2em;
					width: 100%;
				}
			}

			#card-form.visa {
				color: #fff;
				background-color: #0D4AA2;
			}

			#card-form.master-card {
				color: #fff;
				background-color: #363636;
				background: linear-gradient(115deg, #d82332, #d82332 50%, #f1ad3d 50%, #f1ad3d);
			}

			#card-form.maestro {
				color: #fff;
				background-color: #363636;
				background: linear-gradient(115deg, #009ddd, #009ddd 50%, #ed1c2e 50%, #ed1c2e);
			}

			#card-form.american-express {
				color: #fff;
				background-color: #007CC3;
			}

			#card-form.discover {
				color: #fff;
				background-color: #ff6000;
				background: linear-gradient(#d14310, #f7961e);
			}

			#card-form.unionpay,
			#card-form.jcb,
			#card-form.diners-club {
				color: #fff;
				background-color: #363636;
			}

			.cardinfo-label {
				display: block;
				font-size: 11px;
				margin-bottom: 0.5em;
				text-transform: uppercase;
			}

			.cardinfo-exp-date {
				margin-right: 1em;
				width: 100%;
			}

			.cardinfo-cvv {
				width: 100%;
			}

			#button-pay {
				cursor: pointer;
				width: 16em;
				font-size: 15px;
				border: 0;
				padding: 1.2em 1em;
				color: #fff;
				background: #282c37;
				border-radius: 4px;
				z-index: 0;
				-webkit-transform: translateY(-100px);
				transform: translateY(-100px);
				transition: all 500ms cubic-bezier(0.2, 1.3, 0.7, 1);
				opacity: 0;
				-webkit-appearance: none;
			}

			#button-pay:hover {
				background: #535b72;
			}

			#button-pay:active {
				-webkit-animation: cardIntro 200ms cubic-bezier(0.2, 1.3, 0.7, 1);
				animation: cardIntro 200ms cubic-bezier(0.2, 1.3, 0.7, 1);
			}

			#button-pay.show-button {
				-webkit-transform: translateY(0);
				transform: translateY(0);
				opacity: 1;
			}

			.cardinfo-card-number {
				position: relative;
			}

			#card-image {
				position: absolute;
				top: 2em;
				right: 1em;
				width: 44px;
				height: 28px;
				background-image: url();
				background-size: 86px 458px;
				border-radius: 4px;
				background-position: -100px 0;
				background-repeat: no-repeat;
				margin-bottom: 1em;
			}

			#card-image.visa {
				background-position: 0 -398px;
			}

			#card-image.master-card {
				background-position: 0 -281px;
			}

			#card-image.american-express {
				background-position: 0 -370px;
			}

			#card-image.discover {
				background-position: 0 -163px;
			}

			#card-image.maestro {
				background-position: 0 -251px;
			}

			#card-image.jcb {
				background-position: 0 -221px;
			}

			#card-image.diners-club {
				background-position: 0 -133px;
			}

			/*--------------------
				Inputs
				--------------------*/
			.input-wrapper {
				border-radius: 2px;
				background: rgba(255, 255, 255, 0.86);
				height: 2.75em;
				border: 1px solid #eee;
				box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.06);
				padding: 5px 10px;
				margin-bottom: 1em;
			}

			.cardinfo-card-number,
			.cardinfo-exp-date,
			.cardinfo-cvv {
				transition: -webkit-transform 0.3s;
				transition: transform 0.3s;
				transition: transform 0.3s, -webkit-transform 0.3s;
			}



			#form-container {
				display: block;
			}

			#pp-container {
				display: none;
			}

		</style>";

		echo "<script src='https://www.paypal.com/sdk/js?components=hosted-fields,buttons&client-id=".$client_ID."&currency=EUR' data-client-token='".$this->client_token()."'></script>";
		echo "<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-alpha1/jquery.min.js'></script>";

		$createOrder = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
		if ($this->wire('user')->language) {
			$lang = $this->wire('user')->language;
			$landPath = $lang->name;
		} else {
			$landPath = '';
		}
		$capture = 'https://'.$_SERVER['HTTP_HOST'].'/'.$landPath.'/'.$this->capture;
		$successUrl = 'https://'.$_SERVER['HTTP_HOST'].'/'.$landPath.'/'.$this->successUrl;

		// Take Order information
		$order_id=$this->id;
		$order=$this->getOrder($order_id);

		$data = '
		let data =
			{
				"intent": "CAPTURE",
				"payer":{
					"name":{
						"given_name": "'.$order->pad_firstname.'",
						"surname": "'.$order->pad_lastname.'"
					},
					"email_address": "'.$order->email.'",
					"phone":{
						"phone_type": "MOBILE",
						"phone_number":{
							"national_number": "'.$order->pad_phone.'"
						}
					},
					"address":{
						"address_line_1": "'.$order->pad_address.'",
						"address_line_2": "",
						"admin_area_1": "'.$order->pad_city.'",
						"admin_area_2": "",
						"postal_code": "'.$order->pad_postcode.'",
						"country_code": "'.$order->pad_countrycode.'"
					}
				},
				"purchase_units": [{
					"reference_id": "'.$order->id.'",
					"description": "PlayWood products",
					"custom_id": "'.$order->id.'",
					"soft_descriptor": "PlayWood.it",
					"amount": {
						"currency_code": "EUR",
						"value": "'.str_replace(",", ".", $order->pad_price_total).'"
					}
				}]
			};';

		// Render HTML form Card + PayPal Button (include all PaylPal payment system)
		$out = '
			<div class="form-container" id="form-container">
				<div id="payments-sdk__contingency-lightbox"></div>
				<form id="card-form">
					<div class="cardinfo-card-number">
						<label class="cardinfo-label" for="card-number">Card Number</label>
						<div class="input-wrapper" id="card-number"></div>
						<div id="card-image"></div>
					</div>
					<div class="cardinfo-wrapper">
						<div class="cardinfo-exp-date">
							<label class="cardinfo-label" for="expiration-date">Valid Thru</label>
							<div class="input-wrapper" id="expiration-date"></div>
						</div>
						<div class="cardinfo-cvv">
							<label class="cardinfo-label" for="cvv">CVV</label>
							<div class="input-wrapper" id="cvv"></div>
						</div>
					</div>
					<input id="button-pay" type="submit"  class="show-button" value="Pay" />
				</form>
			</div>
			<!-- PayPal Button -->
			<div class="pp-container">
				<div style="margin:10px 0 0 0;text-align:center;" id="paypal-button-container"></div>
					<!-- The div that is replaced with the button -->
			</div>
			<div class="results" id="results">
				<div id="labelresult" ></div>
			</div>';

		$out .= "<script>
			".$data."
			var form = document.querySelector('#card-form');
			var o ;

			if (paypal.HostedFields.isEligible() === true) {
				paypal.HostedFields.render({
					createOrder: () => {
						// logic to return the order ID from PayPal
						return fetch('".$createOrder."', {
							method: 'post',
							headers: {
								'Accept': 'application/json',
								'Content-Type': 'application/json',
								'Authorization': 'Bearer ".$this->access_token()."'
							},
							body: JSON.stringify(data),
						})
						.then(function(res) {
							return res.json();
						})
						.then(function(data) {
							console.log(data);
							return data.id;
						});
					},
					styles: {
						'input': {
							'font-size': '14px',
							'font-family': 'Product Sans',
							'color': '#3a3a3a'
						},
						// Style the text of an invalid input
						'input.invalid': {
							'color': '#E53A40'
						},
					},
					fields: {
						number: {
							selector: '#card-number',
							placeholder: 'Credit Card Number',
						},
						cvv: {
							selector: '#cvv',
							placeholder: 'CVV',
						},
						expirationDate: {
							selector: '#expiration-date',
							placeholder: 'MM/YY',
						}
					}
				})
				.then((hostedFields) => {
					hostedFields.on('validityChange', function (event) {
						console.log('CCF Event \"validityChange\", state='+hostedFields.getState() + \",event=\" + event);
							// Check if all fields are valid, then show submit button
						var formValid = Object.keys(event.fields).every(function (key) {
							return event.fields[key].isValid;
						});

						if (formValid) {
								$('#button-pay').addClass('show-button');
						} else {
								$('#button-pay').removeClass('show-button');
						}
					});
					var cvvLabel = document.querySelector('label[for=\"cvv\"]'); // The label for your CVV field
					hostedFields.on('cardTypeChange', function (event) {
						console.log('CCF Event \"cardTypeChange\", state='+hostedFields.getState() + \",event=\" + event);
						console.log( event);

						// Change card bg depending on card type
						if (event.cards.length === 1) {
							$(form).removeClass().addClass(event.cards[0].type);
							$('#card-image').removeClass().addClass(event.cards[0].type);
							console.log('CCF Card Type '+ event.cards[0].niceType);

							// Change the CVV length for AmericanExpress cards
							if (event.cards[0].code.size === 4) {
								hostedFields.setAttribute({
									field: 'cvv',
									attribute: 'placeholder',
									value: '1234'
								});
							}
						} else {
							hostedFields.setAttribute({
								field: 'cvv',
								attribute: 'placeholder',
								value: '123'
							});
						}
					});
					document.querySelector('#card-form').addEventListener('submit', (event) => {
						event.preventDefault();
						hostedFields.submit({
							// Need to specify when triggering 3D Secure authentication
							contingencies: ['3D_SECURE']  }).then(payload => {
							console.log('payload --',payload);

							const data = { id: payload.orderId };

							return fetch('".$capture."', {
								method: 'post',
								mode: 'cors',
								headers: {
									'Accept': 'application/json',
									'Content-Type': 'application/json',
								},
								body: JSON.stringify(data)
							})
							.then(function(res) {
								return res.json();
							})
							.then(function(data) {
									console.log(data);
									var paymentStatus = data.purchase_units[0].payments.captures[0].status;
									console.log(paymentStatus);
									if (paymentStatus == 'COMPLETED') {
										document.querySelector('#messagePayment').innerHTML = 'Pagamento andato a buonfine';
										document.querySelector('#successful').innerHTML = '<p>Very soon you will be redirected to the confirmation page. If this does not happen within a few seconds, please click on the link.</p><a href=" . $successUrl . ">Go to confermation page</a>';
										document.querySelector('#modal-id-payment-check').classList.add('active');
										setTimeout(function(){ window.location.href = '" . $successUrl . "' }, 1500);
									}
									return data.id;
							})
							.catch(function(err) {
								document.querySelector('#messagePayment').innerHTML = 'Errore nel pagamento';
								document.querySelector('#modal-id-payment-check').classList.add('active');
								console.log(err);
						  })
						})
					})
				})
			};

			//////////////////////PayPal button
			paypal.Buttons({
				createOrder: () => {
					// logic to return an order ID from your server
					return fetch('".$createOrder."', {
						method: 'post',
						headers: {
							'Accept': 'application/json',
							'Content-Type': 'application/json',
				      'Authorization': 'Bearer ".$this->access_token()."'
						},
						body: JSON.stringify(data)
					})
					.then(function(res) {
						console.log(res);
						return res.json();
					})
					.then(function(data) {
						console.log(data);
						return data.id;
					});
				},

				onApprove: function(data, actions) {
					console.log('data:',data);

					return fetch('".$capture."?id=' + data.orderID)
					.then(function(res) {
						return res.json();
					}).then(function(data) {
						console.log(data);
						if (paymentStatus == 'COMPLETED') {
							document.querySelector('#messagePayment').innerHTML = 'Pagamento andato a buonfine';
							document.querySelector('#successful').innerHTML = '<p>Very soon you will be redirected to the confirmation page. If this does not happen within a few seconds, please click on the link.</p><a href=" . $successUrl . ">Go to confermation page</a>';
							document.querySelector('#modal-id-payment-check').classList.add('active');
							setTimeout(function(){ window.location.href = '" . $successUrl . "' }, 1500);
						}
						return data.id;
					});
				}
			}).render('#paypal-button-container');
			/////////////////////////////////
		</script>";

		return $out;
	}

	private function getOrder($order_id) {
		$order=wire('pages')->get($order_id);
		if( !$order || !$order instanceof PadOrder) {
			return false;
		}
		return $order;
	}

	public static function getModuleConfigInputfields(array $dataBK) {

		$inputfields=new InputfieldWrapper();
		$modules=wire('modules');

		$requiredFields=$modules->get("InputfieldFieldset");
		$requiredFields->label=__('Required Fields');

		$f=$modules->get('InputfieldRadios');
		$f->name='enviroment';
		$f->label=__('Enviroment');
		$f->required=true;
		$f->columnWidth=100;
		$f->addOption('sandbox', __('Sandbox'));
		$f->addOption('production', __('Live'));
		$f->value=isset($dataBK['enviroment']) ? $dataBK['enviroment']: 'sandbox';
		$inputfields->add($f);

		$f=$modules->get('InputfieldText');
		$f->name='clientIdSandbox';
		$f->label=__("Sandbox Client ID");
		$f->notes=__("Get your Sandbox Client ID by creating a REST API app [here](https://developer.paypal.com/developer/applications/create).");
		$f->required=true;
		$f->columnWidth=50;
		if(isset($dataBK['clientIdSandbox'])) $f->value=$dataBK['clientIdSandbox'];
		$inputfields->add($f);

		$f=$modules->get('InputfieldText');
		$f->name='clientIdLive';
		$f->label=__("Live Client ID");
		$f->notes=__("Get your Live Client ID by creating a REST API app [here](https://developer.paypal.com/developer/applications/create).");
		$f->required=false;
		$f->columnWidth=50;
		if(isset($dataBK['clientIdLive'])) $f->value=$dataBK['clientIdLive'];
		$inputfields->add($f);

		$f=$modules->get('InputfieldText');
		$f->name='secretCode';
		$f->label=__("SECRET CODE");
		$f->notes=__("Get your SECRET CODE somewhere");
		$f->required=true;
		$f->columnWidth=100;
		if(isset($dataBK['secretCode'])) $f->value=$dataBK['secretCode'];
		$inputfields->add($f);

		$f=$modules->get('InputfieldText');
		$f->name='capture';
		$f->label=__("Capture Page");
		$f->notes=__("Path of the URL, like capture-order");
		$f->required=true;
		$f->columnWidth=50;
		if(isset($dataBK['capture'])) $f->value=$dataBK['capture'];
		$inputfields->add($f);

		$f=$modules->get('InputfieldText');
		$f->name='successUrl';
		$f->label=__("Your success Page");
		$f->notes=__("URL of the success page, like: checkout/success");
		$f->required=true;
		$f->columnWidth=50;
		if(isset($dataBK['successUrl'])) $f->value=$dataBK['successUrl'];
		$inputfields->add($f);

		return $inputfields;
	}

}
