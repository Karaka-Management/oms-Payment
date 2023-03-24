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

use phpOMS\Autoloader;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;

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
    public function apiWebhook(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        switch($request->getData('type')) {
            case 'stripe':
                $this->webhookStripe($request, $response, $data);
        }
    }

    public function webhookStripe(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $api_key = $_SERVER['OMS_STRIPE_SECRET'] ?? '';
        $endpoint_secret = $_SERVER['OMS_STRIPE_PUBLIC'] ?? '';

        $include = \realpath(__DIR__ . '/../../../Resources/');

        if (empty($api_key) || empty($endpoint_secret) || $include === false) {
            return;
        }

        Autoloader::addPath($include);

        \Stripe\Stripe::setApiKey($api_key);

        //$stripe = new \Stripe\StripeClient($api_key);

        //$endpoint_secret = '';

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

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
