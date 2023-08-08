<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Exception;

use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiExceptionInterface;

/**
 * ForumPay plugin HTTP exception
 */
class ApiHttpException extends ForumPayHttpException
{
    /**
     * ApiHttpException constructor
     *
     * @param ApiExceptionInterface|null $cause
     * @param int $code
     * @param int $httpCode
     */
    public function __construct(
        ApiExceptionInterface $cause = null,
        $code = 0,
        $httpCode = ForumPayHttpException::HTTP_BAD_REQUEST
    ) {
        $pos = strrpos(get_class($cause), '\\');
        $message = sprintf(
            "[%s] %s",
            $pos === false ? get_class($cause) : substr(get_class($cause), $pos + 1),
            $cause->getMessage()
        );

        parent::__construct($message, $code, $httpCode);
    }
}
