<h1>PayPal Commerce Platform for ProcessWire CMS/CMF & Padloper V1</h1>
<p>This system integrates the classic PayPal buy button, the alternative or localy payment method and the new payment system: credit/debit cards that doesn't go through the PayPal account.<br>
It is a Stripe-style payment, it connects directly with the bank and integrates 3D security validation.</p>

<h2>Operation</h2>
<p>The module performs two types of calls, the first client-side for creating the order ID, the second call is server-side for 3D security validation and then the payment.<br>
Payment via the Paypal button always remains client-side call.</p>

<h2>Installation</h2>
<h3>The module is a BETA. Currently the setting provides only the connection with Sandbox, to go live you need to change the API URLs for the access_token and client_token request</h3>
<p>
1. Copy the module in the folder site/modules and install it<br>
2. Create a template in the root site/templates, and create the respective page from the backend. So that you have a URL that can receive the API call.<br>
In this page you can copy the contents of the page <code>capture.php</code> present in the module.
3. The module must be configured by adding the required parameters.
</p>

<h2>Note</h2>
<p>For the correct functioning of the payment system you need a Sandbox Business Pro account. This can be created from the page https://developer.paypal.com/developer/accounts</p>

<h2>Link utili</h2>
https://developer.paypal.com/<br>
https://developer.paypal.com/docs/archive/checkout/integrate/<br>
https://developer.paypal.com/docs/api/payments/v1<br>
https://developer.paypal.com/docs/business/checkout/advanced-card-payments/ (Follow Advanced Card payment integration)<br>
Enable your account - https://developer.paypal.com/docs/business/checkout/advanced-card-payments/#1-enable-your-account<br>
Generate token - https://developer.paypal.com/docs/business/checkout/advanced-card-payments/#2-generate-client-token<br>
Capture order - https://developer.paypal.com/docs/business/checkout/advanced-card-payments/#4-capture-order<br>
Sandbox: https://developer.paypal.com/docs/api-basics/sandbox/accounts/ <br>
