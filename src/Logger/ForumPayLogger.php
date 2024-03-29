<?php

namespace ForumPay\PaymentGateway\WoocommercePlugin\Logger;

use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiExceptionInterface;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\ApiErrorException;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\InvalidApiResponseException;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\InvalidResponseException;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\InvalidResponseJsonException;
use ForumPay\PaymentGateway\PHPClient\Http\Exception\InvalidResponseStatusCodeException;
use Psr\Log\LoggerInterface;

/**
 * Small wrapper around the webapi services
 */
class ForumPayLogger implements LoggerInterface
{
    /**
     * @var string
     */
    private string $prefix;

    /**
     * @var WcPsrLoggerAdapter
     */
    private WcPsrLoggerAdapter $logger;


    /**
     * @var ParserInterface[]
     */
    private array $parsers;

    /**
     * Constructor
     *
     * @param WcPsrLoggerAdapter $logger
     * @param string $prefix
     */
    public function __construct(
        WcPsrLoggerAdapter $logger,
        string $prefix = 'ForumPayWebApi'
    ) {
        $this->logger = $logger;
        $this->prefix = $prefix;
        $this->parsers = [];
    }

    /**
     * Data parsers are added using this method.
     *
     * @param ParserInterface $parser
     */
    public function addParser(ParserInterface $parser): void
    {
        $this->parsers[] = $parser;
    }

    /**
     * @inheritdoc
     */
    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @inheritdoc
     */
    public function alert($message, array $context = []): void
    {
        $this->logger->alert($this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @inheritdoc
     */
    public function critical($message, array $context = []): void
    {
        $this->logger->critical($this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @inheritdoc
     */
    public function error($message, array $context = []): void
    {
        $this->logger->error($this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @inheritdoc
     */
    public function warning($message, array $context = []): void
    {
        $this->logger->warning($this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @inheritdoc
     */
    public function notice($message, array $context = []): void
    {
        $this->logger->notice($this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @inheritdoc
     */
    public function info($message, array $context = []): void
    {
        $this->logger->info($this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @inheritdoc
     */
    public function debug($message, array $context = []): void
    {
        $this->logger->debug($this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $this->formatLogMessage($message), $this->parseContext($context));
    }

    /**
     * @param ApiExceptionInterface $e
     * @return void
     */
    public function logApiException(ApiExceptionInterface $e): void
    {
        $pos = strrpos(get_class($e), '\\');
        $exceptionClass = $pos === false ? get_class($e) : substr(get_class($e), $pos + 1);

        switch ($e) {
            case ($e instanceof ApiErrorException || $e instanceof InvalidResponseJsonException):
                $this->logger->error(
                    $this->formatApiExceptionMessage($e, $exceptionClass),
                    ['parameters' => $e->getCallParameters(), 'trace' => $e->getTrace()]
                );
                break;

            case ($e instanceof InvalidApiResponseException):
                $this->logger->error(
                    $this->formatApiExceptionMessage($e, $exceptionClass),
                    [
                        'curlInfo' => $e->getCurlInfo(),
                        'parameters' => $e->getCallParameters(),
                        'trace' => $e->getTrace()
                    ]
                );
                break;

            case ($e instanceof InvalidResponseException):
                $this->logger->error(
                    $this->formatInvalidResponseExceptionMessage($e, $exceptionClass),
                    [
                        'response' => $e->getResponse(),
                        'parameters' => $e->getCallParameters(),
                        'trace' => $e->getTrace()
                    ]
                );
                break;

            case ($e instanceof InvalidResponseStatusCodeException):
                $this->logger->error(
                    $this->formatInvalidResponseStatusCodeExceptionMessage($e, $exceptionClass),
                    ['parameters' => $e->getCallParameters(), 'trace' => $e->getTrace()]
                );
                break;
        }
    }

    /**
     * Method for formatting instances of ApiExceptionInterface that
     * do not have any additional properties apart from those in
     * ForumPay\PaymentGateway\PHPClient\Http\Exception\AbstractApiException
     *
     * @param ApiExceptionInterface $e
     * @param string $exceptionClass
     * @return string
     */
    private function formatApiExceptionMessage(ApiExceptionInterface $e, string $exceptionClass): string
    {
        return $this->formatLogMessage(sprintf(
            '%s: %s %s, Message: %s',
            $exceptionClass,
            $e->getHttpMethod(),
            $e->getUri(),
            $e->getMessage()
        ));
    }

    /**
     * Method for formatting InvalidResponseException
     *
     * @param InvalidResponseException $e
     * @param string $exceptionClass
     * @return string
     */
    private function formatInvalidResponseExceptionMessage(InvalidResponseException $e, string $exceptionClass): string
    {
        return $this->formatLogMessage(sprintf(
            '%s: %s %s, Message: %s, Action: %s',
            $exceptionClass,
            $e->getHttpMethod(),
            $e->getUri(),
            $e->getMessage(),
            $e->getAction(),
        ));
    }

    /**
     * Method for formatting InvalidResponseStatusCodeException
     *
     * @param InvalidResponseStatusCodeException $e
     * @param string $exceptionClass
     * @return string
     */
    private function formatInvalidResponseStatusCodeExceptionMessage(
        InvalidResponseStatusCodeException $e,
        string $exceptionClass
    ): string {
        return $this->formatLogMessage(sprintf(
            '%s: %s %s, Status Code: %s, Message: %s',
            $exceptionClass,
            $e->getHttpMethod(),
            $e->getUri(),
            $e->getResponseStatusCode(),
            $e->getMessage(),
        ));
    }

    /**
     * Return default formatted message
     *
     * @param string $message
     * @return string
     */
    private function formatLogMessage(string $message): string
    {
        return sprintf('%s - %s', $this->prefix, $message);
    }

    /**
     * Run data parsing logic on the context array
     *
     * @param array $context
     * @return array
     */
    private function parseContext(array $context): array
    {
        if (count($this->parsers) !== 0) {
            foreach ($this->parsers as $parser) {
                $context = $parser->parse(['access_token', 'stats_token'], $context);
            }
        }

        return $context;
    }
}
