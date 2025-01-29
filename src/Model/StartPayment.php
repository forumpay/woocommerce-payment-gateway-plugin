<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Model;

use ForumPay\PaymentGateway\PHPClient\Response\RequestKycResponse;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\ApiHttpException;
use ForumPay\PaymentGateway\WoocommercePlugin\Exception\OrderNotFoundException;
use ForumPay\PaymentGateway\WoocommercePlugin\Logger\ForumPayLogger;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payer\Payer;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payment;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Payment\ForumPay;
use ForumPay\PaymentGateway\WoocommercePlugin\Request;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiExceptionInterface;
use ForumPay\PaymentGateway\PHPClient\Response\StartPaymentResponse;
use ForumPay\PaymentGateway\WoocommercePlugin\Model\Data\Payment\BeneficiaryVaspDetails;

class StartPayment
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

    /**
     * @throws OrderNotFoundException
     * @throws ApiExceptionInterface
     * @throws ApiHttpException
     * @return Payment|RequestKycResponse
     */
    public function execute(Request $request)
    {
        try {
            $orderId = $request->getRequired('orderId');
        } catch (\InvalidArgumentException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new OrderNotFoundException(3005);
        }

        try {
            $currency = $request->getRequired('currency');
            $payer = $request->getRequired('payer');
            $kyc = $request->get('kycPin');

            $payer = Payer::valueOf($payer);

            $this->logger->info('StartPayment entrypoint called.', ['currency' => $currency]);

            /** @var StartPaymentResponse $response */
            $response = $this->forumPay->startPayment($orderId, $currency, '', $kyc, $payer);

            $notices = [];
            foreach ($response->getNotices() as $notice) {
                $notices[] = new Payment\Notice($notice['code'], $notice['message']);
            }

            $beneficiaryVaspDetails = BeneficiaryVaspDetails::fromArray($response->getBeneficiaryVaspDetails());

            $payment = new Payment(
                $response->getPaymentId(),
                $response->getAddress(),
                '',
                $response->getMinConfirmations(),
                $response->getFastTransactionFee(),
                $response->getFastTransactionFeeCurrency(),
                $response->getQr(),
                $response->getQrAlt(),
                $response->getQrImg(),
                $response->getQrAltImg(),
                $notices,
                $response->getStatsToken(),
                $beneficiaryVaspDetails,
            );

            $this->logger->info('StartPayment entrypoint finished.');

            return $payment;
        } catch (ApiExceptionInterface $e) {
            $this->logger->logApiException($e);
            $errorCode = $e->getErrorCode();

            if ($errorCode === null) {
                throw new ApiHttpException($e, 3050);
            }

            if (
                $errorCode === 'payerAuthNeeded' ||
                $errorCode === 'payerKYCNotVerified' ||
                $errorCode === 'payerKYCNeeded' ||
                $errorCode === 'payerEmailVerificationCodeNeeded'
            ) {
                $this->forumPay->requestKyc($orderId);
                throw new ApiHttpException($e, 3051);
            } elseif (substr($errorCode, 0, 5) === 'payer') {
                throw new ApiHttpException($e, 3052);
            } elseif ($errorCode === 'missingPayerData' || $errorCode === 'incompletePayerData') {
                throw new ApiHttpException($e, 3056);
            } else {
                throw new ApiHttpException($e, 3050);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new \Exception($e->getMessage(), 3100, $e);
        }
    }
}
