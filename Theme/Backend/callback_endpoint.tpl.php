<?php

/*
valid origins:

a.stripecdn.com
api.stripe.com
atlas.stripe.com
auth.stripe.com
b.stripecdn.com
billing.stripe.com
buy.stripe.com
c.stripecdn.com
checkout.stripe.com
climate.stripe.com
connect.stripe.com
dashboard.stripe.com
express.stripe.com
files.stripe.com
hooks.stripe.com
invoice.stripe.com
invoicedata.stripe.com
js.stripe.com
m.stripe.com
m.stripe.network
manage.stripe.com
pay.stripe.com
payments.stripe.com
q.stripe.com
qr.stripe.com
r.stripe.com
verify.stripe.com
stripe.com
terminal.stripe.com
uploads.stripe.com
*/

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = 'whsec_...';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  http_response_code(400);
  exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
  // Invalid signature
  http_response_code(400);
  exit();
}

function fulfill_order($line_items) {
    // TODO: fill me in
    error_log("Fulfilling order...");
    error_log($line_items);
  }

// Handle the checkout.session.completed event
if ($event->type == 'checkout.session.completed') {
    // Retrieve the session. If you require line items in the response, you may include them by expanding line_items.
    $session = \Stripe\Checkout\Session::retrieve([
      'id' => $event->data->object->id,
      'expand' => ['line_items'],
    ]);

    // @todo: find bill with this session id
    // @todo: send bill as pdf

    $line_items = $session->line_items;
    // Fulfill the purchase...
    fulfill_order($line_items);
  }

http_response_code(200);