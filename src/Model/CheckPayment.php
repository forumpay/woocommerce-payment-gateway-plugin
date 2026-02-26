<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model;

use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ApiHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\OrderNotFoundException;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\TransactionDetailsMissingException;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\ForumPayLogger;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\PaymentDetails;
use ForumPay\PaymentGateway\WoocommercePlugin\Request;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiExceptionInterface;
use ForumPay\PaymentGateway\PHPClient\Response\CheckPaymentResponse;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\ForumPay;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\PaymentDetails\Underpayment;

/**
 * @inheritdoc
 */
class CheckPayment
{
    /**
     * ForumPay payment model
     *
     * @var ForumPay
     */
    private ForumPay $forumPay;

    /**
     * @var ForumPayLogger
     */
    private ForumPayLogger $logger;

    /**
     * Constructor
     *
     * @param ForumPay $forumPay
     * @param ForumPayLogger $logger
     */
    public function __construct(
        ForumPay $forumPay,
        ForumPayLogger $logger
    ) {
        $this->forumPay = $forumPay;
        $this->logger = $logger;
    }

    public function execute(Request $request): PaymentDetails
    {
        try {
            try {
                $orderId = $request->getRequired('forumpay_order_id');
            } catch (\InvalidArgumentException $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());
                throw new OrderNotFoundException(4005);
            }
            $paymentId = $request->getRequired('payment_id');
            $this->logger->info('CheckPayment entrypoint called.', ['paymentId' => $paymentId]);

            /** @var CheckPaymentResponse $response */
            $response = $this->forumPay->checkPayment($orderId, $paymentId);

            if ($response->getUnderpayment()) {
                $underPayment = new Underpayment(
                    $response->getUnderpayment()->getAddress(),
                    $response->getUnderpayment()->getMissingAmount(),
                    $response->getUnderpayment()->getQr(),
                    $response->getUnderpayment()->getQrAlt(),
                    $response->getUnderpayment()->getQrImg(),
                    $response->getUnderpayment()->getQrAltImg()
                );
                $this->logger->debug('CheckPayment - Underpayment.', ['paymentId' => $paymentId]);
            }

            $paymentDetails = new PaymentDetails(
                $response->getReferenceNo(),
                $response->getInserted(),
                $response->getInvoiceAmount(),
                $response->getType(),
                $response->getInvoiceCurrency(),
                $response->getAmount(),
                $response->getOriginalAmount(),
                $response->getMinConfirmations(),
                $response->isAcceptZeroConfirmations(),
                $response->isRequireKytForConfirmation(),
                $response->getCurrency(),
                $response->isConfirmed(),
                $response->getConfirmedTime(),
                $response->getReason(),
                $response->getPayment(),
                $response->getSid(),
                $response->getConfirmations(),
                $response->getAccessToken(),
                $response->getAccessUrl(),
                $response->getWaitTime(),
                $response->getStatus(),
                $response->isCancelled(),
                $response->getCancelledTime(),
                $response->getPrintString(),
                $response->getState(),
                $underPayment ?? null,
                $response->getItemName(),
                $response->getInvoiceSurchargeAmount(),
                $response->getInvoiceAmountWithSurcharge(),
                $response->getInvoiceSurchargePercent(),
            );

            $this->logger->info('CheckPayment entrypoint finished.');

            return $paymentDetails;
        } catch (TransactionDetailsMissingException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new TransactionDetailsMissingException($e->getMessage(), 4006, $e);
        } catch (ApiExceptionInterface $e) {
            $this->logger->logApiException($e);
            throw new ApiHttpException($e, 4050);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new \Exception($e->getMessage(), 4100, $e);
        }
    }
}
