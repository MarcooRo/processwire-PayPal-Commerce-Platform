<?php

// THIS PAGE NEED TO BE ADD IN YOUR SITE/TEMPLATE
// AND NEED BE AN URL ACCESSIBLE BY A CALL

$paypal = $modules->get('PaymentPayPalCommercePlatform');
$access_token = $paypal->access_token();

$request_body = file_get_contents('php://input');
$data = json_decode($request_body);

$orderid = $data->id;


$curl = curl_init();
$authOK = false;

curl_setopt_array($curl, array(
	CURLOPT_URL => "https://api.sandbox.paypal.com/v2/checkout/orders/".$orderid,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_SSL_VERIFYHOST => false,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HEADER => false,
	CURLOPT_HTTPHEADER => array(

		"Content-Type: application/json",
		"Authorization: Bearer " . $access_token,

	),
));
$response = curl_exec($curl);
$response = json_decode($response);

/*****
	BEFORE CAPTURE in case of Payment with credit card, test data about 3dsecure
	liability_shift
	YES. Liability has shifted to the card issuer. Available only after order is authorized or captured.
	NO. Liability is with the merchant.
	POSSIBLE. Liability may shift to the card issuer. Available only before order is authorized or captured.
	UNKNOWN. The authentication system is not available.

	three_d_secure

	authentication_status enum
	The outcome of the issuer's authentication. The possible values are:
	Y. Successful authentication.
	N. Failed authentication / account not verified / transaction denied.
	U. Unable to complete authentication.
	A. Successful attempts transaction.
	C. Challenge required for authentication.
	R. Authentication rejected (merchant must not submit for authorization).
	D. Challenge required; decoupled authentication confirmed.
	I. Informational only; 3DS requestor challenge preference acknowledged.

	enrollment_status enum
	Status of authentication eligibility. The possible values are:
	Y. Yes. The bank is participating in 3-D Secure protocol and will return the ACSUrl.
	N. No. The bank is not participating in 3-D Secure protocol.
	U. Unavailable. The DS or ACS is not available for authentication at the time of the request.
	B. Bypass. The merchant authentication rule is triggered to bypass authentication.
	*/
	//print_r($response);

	//print_r($response->payment_source->card->authentication_result->three_d_secure);

/***** check https://developer.paypal.com/docs/business/checkout/3d-secure/3d-secure-api/  ****/

$liability_shift = $response->payment_source->card->authentication_result->liability_shift;
$enrollment_status = $response->payment_source->card->authentication_result->three_d_secure->enrollment_status;
$authentication_Status = $response->payment_source->card->authentication_result->three_d_secure->authentication_status;

switch ($liability_shift){
	case 'POSSIBLE':
		$authOK=true;
		break;
	case 'NO':
		if($enrollment_status == 'Y' && $authentication_Status == 'N'){
			$authOK=false;
		} elseif ($enrollment_status == 'Y' && $authentication_Status == 'R'){
			$authOK=false;
		} elseif ($enrollment_status == 'Y' && $authentication_Status == 'U'){
			$authOK=false;
		} elseif ($enrollment_status == 'Y' && $authentication_Status == ''){
			$authOK=false;
		} elseif ($enrollment_status == 'N'){
			$authOK=true;
		} elseif ($enrollment_status == 'U'){
			$authOK=true;
		} elseif ($enrollment_status == 'B'){
			$authOK=true;
		} else {
			$authOK=false;
		}
		break;
	case 'YES':
		$authOK=true;
		break;
	case 'UNKNOWN':
		if($enrollment_status == 'Y' && $authentication_Status == 'U'){
			$authOK=true;
		} elseif ($enrollment_status == 'Y' && $authentication_Status == 'C'){
			$authOK=false;
		} elseif ($enrollment_status == 'U'){
			$authOK=true;
		} elseif ($enrollment_status == ''){
			$authOK=false;
		} else {
			$authOK=false;
		}
		break;
	default:
		$authOK=false;
};


if ($authOK == true) {
	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://api.sandbox.paypal.com/v2/checkout/orders/".$orderid."/capture" ,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_HEADER => false,
		CURLOPT_HTTPHEADER => array(
			"Content-Type: application/json",
			"Authorization: Bearer " . $access_token,
		),
	));
	$response = curl_exec($curl);
	print_r($response);
} else {
	print_r($response);
}