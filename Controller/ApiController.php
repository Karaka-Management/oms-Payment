<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Payment
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Payment\Controller;

use Modules\Billing\Models\Attribute\BillAttributeMapper;
use Modules\Billing\Models\Attribute\BillAttributeTypeMapper;
use Modules\Billing\Models\Bill;
use Modules\Billing\Models\BillMapper;
use Modules\Billing\Models\BillPaymentStatus;
use Modules\Billing\Models\BillStatus;
use Modules\ItemManagement\Models\ItemMapper;
use phpOMS\Autoloader;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\MimeType;
use phpOMS\Uri\HttpUri;

/**
 * Payment controller class.
 *
 * @package Modules\Payment
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Handle payment processing request.
     *
     * E.g. customer pays via credit card, paypal, etc.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Data
     *
     * @return Bill
     *
     * @since 1.0.0
     */
    public function handlePaymentRequest(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : Bill
    {
        /** @var \Modules\Attribute\Models\Attribute $attr */
        $attr = BillAttributeMapper::get()
            ->with('type')
            ->with('value')
            ->where('type/name', 'external_payment_id')
            ->where('value/valueStr', $request->getDataString('session_id') ?? '')
            ->execute();

        /** @var \Modules\Billing\Models\Bill $bill */
        $bill = BillMapper::get()
            ->with('client')
            ->where('id', $attr->ref)
            ->execute();

        // @todo: handle different payment providers, currently only stripe handled
        // idea: add a second attribute which defines the external_payment_provider, or use 2 values in the attribute e.g. valueInt contains the type of the payment provider?
        $status = $this->getStripePaymentStatus($request->getDataString('session_id') ?? '');

        // @todo: consider to have the bill as an offer and now turn it into an invoice. currently it's an invoice and now we need to update it instead of transfer it to an invoice
        if ($status === BillPaymentStatus::PAID
            && $bill->getPaymentStatus() !== BillPaymentStatus::PAID
        ) {
            $old = clone $bill;

            $bill->setPaymentStatus(BillPaymentStatus::PAID);
            $bill->setStatus(BillStatus::ARCHIVED);

            $account = empty($request->header->account)
                ? (int) $bill->client?->account->getId()
                : $request->header->account;

            $this->updateModel($account, $old, $bill, BillMapper::class, 'bill_payment', $request->getOrigin());

            // @todo: move this out of here. This is only a special case.
            // Even the temp implememntation is bad, as this should happen async in the Cli
            $internalRequest  = new HttpRequest(new HttpUri(''));
            $internalResponse = new HttpResponse();

            $internalRequest->header->account = $account;
            $internalRequest->setData('bill', $bill->getId());

            $this->app->moduleManager->get('Billing', 'Api')->apiBillPdfArchiveCreate($internalRequest, $internalResponse);
        }

        return $bill;
    }

    /**
     * Get payment status from stripe.
     *
     * @param string $sessionId Session id
     *
     * @return int BillPaymentStatus
     *
     * @since 1.0.0
     */
    public function getStripePaymentStatus(string $sessionId) : int
    {
        $api_key         = $_SERVER['OMS_STRIPE_SECRET'] ?? '';
        $endpoint_secret = $_SERVER['OMS_STRIPE_PUBLIC'] ?? '';

        $include = \realpath(__DIR__ . '/../../../Resources/Stripe');

        if (empty($api_key) || empty($endpoint_secret) || $include === false) {
            return BillPaymentStatus::UNKNOWN;
        }

        Autoloader::addPath($include);

        \Stripe\Stripe::setApiKey($api_key);

        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        $status  = $session->payment_status;

        switch (\strtolower($status)) {
            case 'paid':
                return BillPaymentStatus::PAID;
            case 'unpaid':
                return BillPaymentStatus::UNPAID;
            default:
                return BillPaymentStatus::UNKNOWN;
        }
    }

    /**
     * Create stripe checkout response
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param Bill             $bill     Bill
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setupStripe(
        RequestAbstract $request,
        ResponseAbstract $response,
        Bill $bill,
        mixed $data = null
    ) : void
    {
        $session = $this->createStripeSession($bill, $data['success'], $data['cancel']);

        // Assign payment id to bill
        /** \Modules\Billing\Models\Attribute\BillAttributeType $type */
        $type = BillAttributeTypeMapper::get()
            ->where('name', 'external_payment_id')
            ->execute();

        $internalRequest  = new HttpRequest(new HttpUri(''));
        $internalResponse = new HttpResponse();

        $internalRequest->header->account = $request->header->account;
        $internalRequest->setData('type', $type->getId());
        $internalRequest->setData('custom', (string) $session->id);
        $internalRequest->setData('bill', $bill->getId());
        $this->app->moduleManager->get('Billing', 'ApiAttribute')->apiBillAttributeCreate($internalRequest, $internalResponse, $data);

        // Redirect to stripe checkout page
        $response->header->status = RequestStatusCode::R_303;
        $response->header->set('Content-Type', MimeType::M_JSON, true);
        $response->header->set('Location', $session->url, true);
    }

    /**
     * Create stripe session
     *
     * @param Bill   $bill    Bill
     * @param string $success Success url
     * @param string $cancel  Cancel url
     *
     * @return \Stripe\Checkout\Session|null
     *
     * @since 1.0.0
     */
    private function createStripeSession(
        Bill $bill,
        string $success,
        string $cancel
    ) : ?\Stripe\Checkout\Session
    {
        // $this->app->appSettings->getEncrypted()

        // $stripeSecretKeyTemp = $this->app->appSettings->get();
        // $stripeSecretKey = $this->app->appSettings->decrypt($stripeSecretKeyTemp);

        // \Stripe\Stripe::setApiKey($stripeSecretKey);

        $api_key         = $_SERVER['OMS_STRIPE_SECRET'] ?? '';
        $endpoint_secret = $_SERVER['OMS_STRIPE_PUBLIC'] ?? '';

        $include = \realpath(__DIR__ . '/../../../Resources/Stripe');

        if (empty($api_key) || empty($endpoint_secret) || $include === false) {
            return null;
        }

        $isSubscription = false;
        $elements       = $bill->getElements();

        foreach ($elements as $element) {
            $item = ItemMapper::get()
                ->where('id', $element->item)
                ->execute();

            if ($item->getAttribute('subscription')->value->getValue() === 1) {
                $isSubscription = true;
                break;
            }
        }

        Autoloader::addPath($include);

        $stripeData = [
            'line_items' => [],
            'mode' => $isSubscription ? 'subscription' : 'payment',
            'currency' => $bill->getCurrency(),
            'success_url' => $success,
            'cancel_url' => $cancel,
            'client_reference_id' => $bill->number,
           // 'customer' => 'stripe_customer_id...',
            'customer_email' => $bill->client->account->getEmail(),
        ];

        foreach ($elements as $element) {
            $stripeData['line_items'][] = [
                'quantity' => 1,
                'price_data' => [
                    'tax_behavior' => 'inclusive',
                    'currency' => $bill->getCurrency(),
                    'unit_amount' => (int) ($element->totalSalesPriceGross->getInt() / 100),
                    //'amount_subtotal' => (int) ($bill->netSales->getInt() / 100),
                    //'amount_total' => (int) ($bill->grossSales->getInt() / 100),
                    'product_data' => [
                        'name' => $element->itemName,
                        'metadata' => [
                            'pro_id' => $element->itemNumber,
                        ],
                    ],
                ]
            ];
        }

        //$stripe = new \Stripe\StripeClient($api_key);
        \Stripe\Stripe::setApiKey($api_key);

        // @todo: instead of using account email, use client billing email if defined and only use account email as fallback
        $session = \Stripe\Checkout\Session::create($stripeData);

        return $session;
    }

    public function apiWebhook(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        switch($request->getData('type')) {
            case 'stripe':
                $this->webhookStripe($request, $response, $data);
        }
    }

    public function webhookStripe(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $api_key         = $_SERVER['OMS_STRIPE_SECRET'] ?? '';
        $endpoint_secret = $_SERVER['OMS_STRIPE_PUBLIC'] ?? '';

        $include = \realpath(__DIR__ . '/../../../Resources/Stripe');

        if (empty($api_key) || empty($endpoint_secret) || $include === false) {
            return;
        }

        Autoloader::addPath($include);

        \Stripe\Stripe::setApiKey($api_key);

        //$stripe = new \Stripe\StripeClient($api_key);

        //$endpoint_secret = '';

        $payload    = file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event      = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            $response->header->generate(400);

            return;
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            $response->header->generate(400);

            return;
        }

        switch ($event->type) {
            case 'account.updated':
                $account = $event->data->object;
                break;
            case 'account.external_account.created':
                $externalAccount = $event->data->object;
                break;
            case 'account.external_account.deleted':
                $externalAccount = $event->data->object;
                break;
            case 'account.external_account.updated':
                $externalAccount = $event->data->object;
                break;
            case 'balance.available':
                $balance = $event->data->object;
                break;
            case 'billing_portal.configuration.created':
                $configuration = $event->data->object;
                break;
            case 'billing_portal.configuration.updated':
                $configuration = $event->data->object;
                break;
            case 'billing_portal.session.created':
                $session = $event->data->object;
                break;
            case 'capability.updated':
                $capability = $event->data->object;
                break;
            case 'cash_balance.funds_available':
                $cashBalance = $event->data->object;
                break;
            case 'charge.captured':
                $charge = $event->data->object;
                break;
            case 'charge.expired':
                $charge = $event->data->object;
                break;
            case 'charge.failed':
                $charge = $event->data->object;
                break;
            case 'charge.pending':
                $charge = $event->data->object;
                break;
            case 'charge.refunded':
                $charge = $event->data->object;
                break;
            case 'charge.succeeded':
                $charge = $event->data->object;
                break;
            case 'charge.updated':
                $charge = $event->data->object;
                break;
            case 'charge.dispute.closed':
                $dispute = $event->data->object;
                break;
            case 'charge.dispute.created':
                $dispute = $event->data->object;
                break;
            case 'charge.dispute.funds_reinstated':
                $dispute = $event->data->object;
                break;
            case 'charge.dispute.funds_withdrawn':
                $dispute = $event->data->object;
                break;
            case 'charge.dispute.updated':
                $dispute = $event->data->object;
                break;
            case 'charge.refund.updated':
                $refund = $event->data->object;
                break;
            case 'checkout.session.async_payment_failed':
                $session = $event->data->object;
                break;
            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;
                break;
            case 'checkout.session.completed':
                $session = $event->data->object;
                break;
            case 'checkout.session.expired':
                $session = $event->data->object;
                break;
            case 'coupon.created':
                $coupon = $event->data->object;
                break;
            case 'coupon.deleted':
                $coupon = $event->data->object;
                break;
            case 'coupon.updated':
                $coupon = $event->data->object;
                break;
            case 'credit_note.created':
                $creditNote = $event->data->object;
                break;
            case 'credit_note.updated':
                $creditNote = $event->data->object;
                break;
            case 'credit_note.voided':
                $creditNote = $event->data->object;
                break;
            case 'customer.created':
                $customer = $event->data->object;
                break;
            case 'customer.deleted':
                $customer = $event->data->object;
                break;
            case 'customer.updated':
                $customer = $event->data->object;
                break;
            case 'customer.discount.created':
                $discount = $event->data->object;
                break;
            case 'customer.discount.deleted':
                $discount = $event->data->object;
                break;
            case 'customer.discount.updated':
                $discount = $event->data->object;
                break;
            case 'customer.source.created':
                $source = $event->data->object;
                break;
            case 'customer.source.deleted':
                $source = $event->data->object;
                break;
            case 'customer.source.expiring':
                $source = $event->data->object;
                break;
            case 'customer.source.updated':
                $source = $event->data->object;
                break;
            case 'customer.subscription.created':
                $subscription = $event->data->object;
                break;
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                break;
            case 'customer.subscription.paused':
                $subscription = $event->data->object;
                break;
            case 'customer.subscription.pending_update_applied':
                $subscription = $event->data->object;
                break;
            case 'customer.subscription.pending_update_expired':
                $subscription = $event->data->object;
                break;
            case 'customer.subscription.resumed':
                $subscription = $event->data->object;
                break;
            case 'customer.subscription.trial_will_end':
                $subscription = $event->data->object;
                break;
            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                break;
            case 'customer.tax_id.created':
                $taxId = $event->data->object;
                break;
            case 'customer.tax_id.deleted':
                $taxId = $event->data->object;
                break;
            case 'customer.tax_id.updated':
                $taxId = $event->data->object;
                break;
            case 'customer_cash_balance_transaction.created':
                $customerCashBalanceTransaction = $event->data->object;
                break;
            case 'file.created':
                $file = $event->data->object;
                break;
            case 'financial_connections.account.created':
                $account = $event->data->object;
                break;
            case 'financial_connections.account.deactivated':
                $account = $event->data->object;
                break;
            case 'financial_connections.account.disconnected':
                $account = $event->data->object;
                break;
            case 'financial_connections.account.reactivated':
                $account = $event->data->object;
                break;
            case 'financial_connections.account.refreshed_balance':
                $account = $event->data->object;
                break;
            case 'identity.verification_session.canceled':
                $verificationSession = $event->data->object;
                break;
            case 'identity.verification_session.created':
                $verificationSession = $event->data->object;
                break;
            case 'identity.verification_session.processing':
                $verificationSession = $event->data->object;
                break;
            case 'identity.verification_session.requires_input':
                $verificationSession = $event->data->object;
                break;
            case 'identity.verification_session.verified':
                $verificationSession = $event->data->object;
                break;
            case 'invoice.created':
                $invoice = $event->data->object;
                break;
            case 'invoice.deleted':
                $invoice = $event->data->object;
                break;
            case 'invoice.finalization_failed':
                $invoice = $event->data->object;
                break;
            case 'invoice.finalized':
                $invoice = $event->data->object;
                break;
            case 'invoice.marked_uncollectible':
                $invoice = $event->data->object;
                break;
            case 'invoice.paid':
                $invoice = $event->data->object;
                break;
            case 'invoice.payment_action_required':
                $invoice = $event->data->object;
                break;
            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                break;
            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                break;
            case 'invoice.sent':
                $invoice = $event->data->object;
                break;
            case 'invoice.upcoming':
                $invoice = $event->data->object;
                break;
            case 'invoice.updated':
                $invoice = $event->data->object;
                break;
            case 'invoice.voided':
                $invoice = $event->data->object;
                break;
            case 'invoiceitem.created':
                $invoiceitem = $event->data->object;
                break;
            case 'invoiceitem.deleted':
                $invoiceitem = $event->data->object;
                break;
            case 'invoiceitem.updated':
                $invoiceitem = $event->data->object;
                break;
            case 'issuing_authorization.created':
                $issuingAuthorization = $event->data->object;
                break;
            case 'issuing_authorization.updated':
                $issuingAuthorization = $event->data->object;
                break;
            case 'issuing_card.created':
                $issuingCard = $event->data->object;
                break;
            case 'issuing_card.updated':
                $issuingCard = $event->data->object;
                break;
            case 'issuing_cardholder.created':
                $issuingCardholder = $event->data->object;
                break;
            case 'issuing_cardholder.updated':
                $issuingCardholder = $event->data->object;
                break;
            case 'issuing_dispute.closed':
                $issuingDispute = $event->data->object;
                break;
            case 'issuing_dispute.created':
                $issuingDispute = $event->data->object;
                break;
            case 'issuing_dispute.funds_reinstated':
                $issuingDispute = $event->data->object;
                break;
            case 'issuing_dispute.submitted':
                $issuingDispute = $event->data->object;
                break;
            case 'issuing_dispute.updated':
                $issuingDispute = $event->data->object;
                break;
            case 'issuing_transaction.created':
                $issuingTransaction = $event->data->object;
                break;
            case 'issuing_transaction.updated':
                $issuingTransaction = $event->data->object;
                break;
            case 'mandate.updated':
                $mandate = $event->data->object;
                break;
            case 'order.created':
                $order = $event->data->object;
                break;
            case 'payment_intent.amount_capturable_updated':
                $paymentIntent = $event->data->object;
                break;
            case 'payment_intent.canceled':
                $paymentIntent = $event->data->object;
                break;
            case 'payment_intent.created':
                $paymentIntent = $event->data->object;
                break;
            case 'payment_intent.partially_funded':
                $paymentIntent = $event->data->object;
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                break;
            case 'payment_intent.processing':
                $paymentIntent = $event->data->object;
                break;
            case 'payment_intent.requires_action':
                $paymentIntent = $event->data->object;
                break;
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                break;
            case 'payment_link.created':
                $paymentLink = $event->data->object;
                break;
            case 'payment_link.updated':
                $paymentLink = $event->data->object;
                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object;
                break;
            case 'payment_method.automatically_updated':
                $paymentMethod = $event->data->object;
                break;
            case 'payment_method.detached':
                $paymentMethod = $event->data->object;
                break;
            case 'payment_method.updated':
                $paymentMethod = $event->data->object;
                break;
            case 'payout.canceled':
                $payout = $event->data->object;
                break;
            case 'payout.created':
                $payout = $event->data->object;
                break;
            case 'payout.failed':
                $payout = $event->data->object;
                break;
            case 'payout.paid':
                $payout = $event->data->object;
                break;
            case 'payout.updated':
                $payout = $event->data->object;
                break;
            case 'person.created':
                $person = $event->data->object;
                break;
            case 'person.deleted':
                $person = $event->data->object;
                break;
            case 'person.updated':
                $person = $event->data->object;
                break;
            case 'plan.created':
                $plan = $event->data->object;
                break;
            case 'plan.deleted':
                $plan = $event->data->object;
                break;
            case 'plan.updated':
                $plan = $event->data->object;
                break;
            case 'price.created':
                $price = $event->data->object;
                break;
            case 'price.deleted':
                $price = $event->data->object;
                break;
            case 'price.updated':
                $price = $event->data->object;
                break;
            case 'product.created':
                $product = $event->data->object;
                break;
            case 'product.deleted':
                $product = $event->data->object;
                break;
            case 'product.updated':
                $product = $event->data->object;
                break;
            case 'promotion_code.created':
                $promotionCode = $event->data->object;
                break;
            case 'promotion_code.updated':
                $promotionCode = $event->data->object;
                break;
            case 'quote.accepted':
                $quote = $event->data->object;
                break;
            case 'quote.canceled':
                $quote = $event->data->object;
                break;
            case 'quote.created':
                $quote = $event->data->object;
                break;
            case 'quote.finalized':
                $quote = $event->data->object;
                break;
            case 'radar.early_fraud_warning.created':
                $earlyFraudWarning = $event->data->object;
                break;
            case 'radar.early_fraud_warning.updated':
                $earlyFraudWarning = $event->data->object;
                break;
            case 'recipient.created':
                $recipient = $event->data->object;
                break;
            case 'recipient.deleted':
                $recipient = $event->data->object;
                break;
            case 'recipient.updated':
                $recipient = $event->data->object;
                break;
            case 'refund.created':
                $refund = $event->data->object;
                break;
            case 'refund.updated':
                $refund = $event->data->object;
                break;
            case 'reporting.report_run.failed':
                $reportRun = $event->data->object;
                break;
            case 'reporting.report_run.succeeded':
                $reportRun = $event->data->object;
                break;
            case 'review.closed':
                $review = $event->data->object;
                break;
            case 'review.opened':
                $review = $event->data->object;
                break;
            case 'setup_intent.canceled':
                $setupIntent = $event->data->object;
                break;
            case 'setup_intent.created':
                $setupIntent = $event->data->object;
                break;
            case 'setup_intent.requires_action':
                $setupIntent = $event->data->object;
                break;
            case 'setup_intent.setup_failed':
                $setupIntent = $event->data->object;
                break;
            case 'setup_intent.succeeded':
                $setupIntent = $event->data->object;
                break;
            case 'sigma.scheduled_query_run.created':
                $scheduledQueryRun = $event->data->object;
                break;
            case 'sku.created':
                $sku = $event->data->object;
                break;
            case 'sku.deleted':
                $sku = $event->data->object;
                break;
            case 'sku.updated':
                $sku = $event->data->object;
                break;
            case 'source.canceled':
                $source = $event->data->object;
                break;
            case 'source.chargeable':
                $source = $event->data->object;
                break;
            case 'source.failed':
                $source = $event->data->object;
                break;
            case 'source.mandate_notification':
                $source = $event->data->object;
                break;
            case 'source.refund_attributes_required':
                $source = $event->data->object;
                break;
            case 'source.transaction.created':
                $transaction = $event->data->object;
                break;
            case 'source.transaction.updated':
                $transaction = $event->data->object;
                break;
            case 'subscription_schedule.aborted':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'subscription_schedule.canceled':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'subscription_schedule.completed':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'subscription_schedule.created':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'subscription_schedule.expiring':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'subscription_schedule.released':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'subscription_schedule.updated':
                $subscriptionSchedule = $event->data->object;
                break;
            case 'tax_rate.created':
                $taxRate = $event->data->object;
                break;
            case 'tax_rate.updated':
                $taxRate = $event->data->object;
                break;
            case 'terminal.reader.action_failed':
                $reader = $event->data->object;
                break;
            case 'terminal.reader.action_succeeded':
                $reader = $event->data->object;
                break;
            case 'test_helpers.test_clock.advancing':
                $testClock = $event->data->object;
                break;
            case 'test_helpers.test_clock.created':
                $testClock = $event->data->object;
                break;
            case 'test_helpers.test_clock.deleted':
                $testClock = $event->data->object;
                break;
            case 'test_helpers.test_clock.internal_failure':
                $testClock = $event->data->object;
                break;
            case 'test_helpers.test_clock.ready':
                $testClock = $event->data->object;
                break;
            case 'topup.canceled':
                $topup = $event->data->object;
                break;
            case 'topup.created':
                $topup = $event->data->object;
                break;
            case 'topup.failed':
                $topup = $event->data->object;
                break;
            case 'topup.reversed':
                $topup = $event->data->object;
                break;
            case 'topup.succeeded':
                $topup = $event->data->object;
                break;
            case 'transfer.created':
                $transfer = $event->data->object;
                break;
            case 'transfer.reversed':
                $transfer = $event->data->object;
                break;
            case 'transfer.updated':
                $transfer = $event->data->object;
                break;
            default:
        }
    }
}
