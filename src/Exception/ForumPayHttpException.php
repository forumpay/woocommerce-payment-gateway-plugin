<?php

declare(strict_types=1);

namespace ForumPay\PaymentGateway\WoocommercePlugin\Exception;

class ForumPayHttpException extends \Exception
{
    const HTTP_BAD_REQUEST = 400;

    const HTTP_FORBIDDEN = 403;

    const HTTP_NOT_FOUND = 404;

    const HTTP_INTERNAL_ERROR = 500;

    /**
     * Optional exception details.
     *
     * @var array
     */
    protected $details;

    /**
     * HTTP status code associated with current exception.
     *
     * @var int
     */
    protected $httpCode;

    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Stacktrace
     *
     * @var string
     */
    protected $stackTrace;

    /**
     * List of errors
     *
     * @var null|\Exception[]
     */
    protected $errors;

    /**
     * Initialize exception with HTTP code.
     *
     * @param string $message
     * @param int $code Error code
     * @param int $httpCode
     * @param array $details Additional exception details
     * @param string $name Exception name
     * @param \Exception[]|null $errors Array of errors messages
     * @param string $stackTrace
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $message,
               $code = 0,
               $httpCode = self::HTTP_BAD_REQUEST,
        array $details = [],
               $name = '',
               $errors = null,
               $stackTrace = null
    ) {
        /** Only HTTP error codes are allowed. No success or redirect codes must be used. */
        if ($httpCode < 400 || $httpCode > 599) {
            throw new \InvalidArgumentException(sprintf('The specified HTTP code "%d" is invalid.', $httpCode));
        }
        parent::__construct($message, $code);
        $this->code = $code;
        $this->httpCode = $httpCode;
        $this->details = $details;
        $this->name = $name;
        $this->errors = $errors;
        $this->stackTrace = $stackTrace;
    }

    /**
     * Retrieve current HTTP code.
     *
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Retrieve exception details.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Retrieve exception name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Retrieve list of errors.
     *
     * @return null|\Exception[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Retrieve stack trace string.
     *
     * @return null|string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }
}
