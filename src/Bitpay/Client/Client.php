<?php
/**
 * @license Copyright 2011-2015 BitPay Inc., MIT License
 * see https://github.com/bitpay/php-bitpay-client/blob/master/LICENSE
 */

namespace Bitpay\Client;

use Bitpay\Client\Adapter\AdapterInterface;
use Bitpay\TokenInterface;
use Bitpay\InvoiceInterface;
use Bitpay\PayoutInterface;
use Bitpay\Util\Util;
use Bitpay\PublicKey;
use Bitpay\PrivateKey;

date_default_timezone_set('UTC');
/**
 * Client used to send requests and receive responses for BitPay's Web API.
 *
 * @package Bitpay
 */
class Client implements ClientInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var PublicKey
     */
    protected $publicKey;

    /**
     * @var PrivateKey
     */
    protected $privateKey;

    /**
     * @var string
     */
    protected $uri;

    public function setUri($uri)
    {
        $this->uri = trim($uri);
    }

    /**
     * Set the Public Key to use to help identify who you are to BitPay. Please
     * note that you must first pair your keys and get a token in return to use.
     *
     * @param PublicKey $key
     */
    public function setPublicKey(PublicKey $key)
    {
        $this->publicKey = $key;
    }

    /**
     * Set the Private Key to use, this is used when signing request strings
     *
     * @param PrivateKey $key
     */
    public function setPrivateKey(PrivateKey $key)
    {
        $this->privateKey = $key;
    }

    /**
     * Set the network adapter object to use.
     *
     * @param AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Assigns the token to use for a request.
     *
     * @param TokenInterface $token
     * @return ClientInterface
     */
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function fillInvoiceData(InvoiceInterface $invoice, $data)
    {
        # BitPay returns the invoice time in milliseconds. PHP's DateTime object expects the time to be in seconds
        $invoiceTime = is_numeric($data['invoiceTime']) ? intval($data['invoiceTime']/1000) : $data['invoiceTime'];
        $expirationTime = is_numeric($data['expirationTime']) ? intval($data['expirationTime']/1000) : $data['expirationTime'];
        $currentTime = is_numeric($data['currentTime']) ? intval($data['currentTime']/1000) : $data['currentTime'];
        
        $invoiceToken = new \Bitpay\Token();
        $paymentUrls = new \Bitpay\PaymentUrlSet();

        $invoice
            ->setToken($invoiceToken->setToken($data['token']))
            ->setUrl($data['url'])
            ->setPosData(array_key_exists('posData', $data) ? $data['posData'] : '')
            ->setStatus($data['status'])
            ->setBtcPrice(array_key_exists('btcPrice', $data) ? $data['btcPrice'] : '')
            ->setPrice($data['price'])
            ->setTaxIncluded($data['taxIncluded'])
            ->setCurrency(new \Bitpay\Currency($data['currency']))
            ->setOrderId(array_key_exists('orderId', $data) ? $data['orderId'] : '')
            ->setInvoiceTime($invoiceTime)
            ->setExpirationTime($expirationTime)
            ->setCurrentTime($currentTime)
            ->setId($data['id'])
            ->setBtcPaid(array_key_exists('btcPaid', $data) ? $data['btcPaid'] : '')
            ->setAmountPaid(array_key_exists('amountPaid', $data) ? $data['amountPaid'] : '')
            ->setRate(array_key_exists('rate', $data) ? $data['rate'] : '')
            ->setExceptionStatus($data['exceptionStatus'])
            ->setRefundAddresses(array_key_exists('refundAddresses', $data) ? $data['refundAddresses'] : '')
            ->setTransactionCurrency(array_key_exists('transactionCurrency', $data) ? $data['transactionCurrency'] : null)
            ->setPaymentTotals(array_key_exists('paymentTotals', $data) ? $data['paymentTotals'] : '')
            ->setPaymentSubtotals(array_key_exists('paymentSubtotals', $data) ? $data['paymentSubtotals'] : '')
            ->setExchangeRates(array_key_exists('exchangeRates', $data) ? $data['exchangeRates'] : '');

        if (isset($data['paymentUrls'])) {
            $invoice
                ->setPaymentUrls($paymentUrls->setUrls($data['paymentUrls']));
        }

        return $invoice;
    }

    /**
     * @inheritdoc
     */
    public function createInvoice(InvoiceInterface $invoice)
    {
        $request = $this->createNewRequest();
        $request->setMethod(Request::METHOD_POST);
        $request->setPath('invoices');

        $currency     = $invoice->getCurrency();
        $item         = $invoice->getItem();
        $buyer        = $invoice->getBuyer();
        $buyerAddress = $buyer->getAddress();

        $this->checkPriceAndCurrency($item->getPrice(), $currency->getCode());

        $body = array(
            'price'                 => $item->getPrice(),
            'taxIncluded'           => $item->getTaxIncluded(),
            'currency'              => $currency->getCode(),
            'posData'               => $invoice->getPosData(),
            'notificationURL'       => $invoice->getNotificationUrl(),
            'transactionSpeed'      => $invoice->getTransactionSpeed(),
            'fullNotifications'     => $invoice->isFullNotifications(),
            'extendedNotifications' => $invoice->isExtendedNotifications(),
            'notificationEmail'     => $invoice->getNotificationEmail(),
            'redirectURL'           => $invoice->getRedirectUrl(),
            'orderID'               => $invoice->getOrderId(),
            'itemDesc'              => $item->getDescription(),
            'itemCode'              => $item->getCode(),
            'physical'              => $item->isPhysical(),
            'buyerName'             => trim(sprintf('%s %s', $buyer->getFirstName(), $buyer->getLastName())),
            'buyerAddress1'         => isset($buyerAddress[0]) ? $buyerAddress[0] : '',
            'buyerAddress2'         => isset($buyerAddress[1]) ? $buyerAddress[1] : '',
            'buyerCity'             => $buyer->getCity(),
            'buyerState'            => $buyer->getState(),
            'buyerZip'              => $buyer->getZip(),
            'buyerCountry'          => $buyer->getCountry(),
            'buyerEmail'            => $buyer->getEmail(),
            'buyerPhone'            => $buyer->getPhone(),
            'buyerNotify'           => $buyer->getNotify(),
            'guid'                  => Util::guid(),
            'token'                 => $this->token->getToken(),
        );

        $request->setBody(json_encode($body));

        $this->addIdentityHeader($request);
        $this->addSignatureHeader($request);

        $this->request  = $request;
        $this->response = $this->sendRequest($request);

        $body = json_decode($this->response->getBody(), true);

        $error_message = false;
        $error_message = (!empty($body['error'])) ? $body['error'] : $error_message;
        $error_message = (!empty($body['errors'])) ? $body['errors'] : $error_message;
        $error_message = (is_array($error_message)) ? implode("\n", $error_message) : $error_message;

        if (false !== $error_message) {
            throw new \Exception($error_message);
        }

        $data = $body['data'];

        $invoice = $this->fillInvoiceData($invoice, $data);

        return $invoice;
    }

    /**
     * @inheritdoc
     */
    public function getCurrencies()
    {
        $this->request = $this->createNewRequest();
        $this->request->setMethod(Request::METHOD_GET);
        $this->request->setPath('currencies');

        $this->response = $this->sendRequest($this->request);

        $body = json_decode($this->response->getBody(), true);

        if (empty($body['data'])) {
            throw new \Exception('Error with request: no data returned');
        }

        $currencies = $body['data'];

        array_walk($currencies, function (&$value, $key) {
            $currency = new \Bitpay\Currency();
            $currency
                ->setCode($value['code'])
                ->setSymbol($value['symbol'])
                ->setPrecision($value['precision'])
                ->setExchangePctFee($value['exchangePctFee'])
                ->setPayoutEnabled($value['payoutEnabled'])
                ->setName($value['name'])
                ->setPluralName($value['plural'])
                ->setAlts($value['alts'])
                ->setPayoutFields($value['payoutFields']);

            $value = $currency;
        });

        return $currencies;
    }

    /**
     * @inheritdoc
     */
    public function createPayout(PayoutInterface $payout)
    {
        $request = $this->createNewRequest();
        $request->setMethod($request::METHOD_POST);
        $request->setPath('payouts');

        $amount         = $payout->getAmount();
        $currency       = $payout->getCurrency();
        $effectiveDate  = $payout->getEffectiveDate();
        $token          = $payout->getToken();

        $body = array(
            'token'         => $token->getToken(),
            'amount'        => $amount,
            'currency'      => $currency->getCode(),
            'instructions'  => array(),
            'effectiveDate' => $effectiveDate,
            'pricingMethod' => $payout->getPricingMethod(),
            'guid'          => Util::guid(),
        );

        // Optional
        foreach (array('reference','notificationURL','notificationEmail') as $value) {
            $function = 'get' . ucfirst($value);

            if ($payout->$function() != null) {
                $body[$value] = $payout->$function();
            }
        }

        // Add instructions
        foreach ($payout->getInstructions() as $instruction) {
            $body['instructions'][] = array(
                'label'   => $instruction->getLabel(),
                'address' => $instruction->getAddress(),
                'amount'  => $instruction->getAmount()
            );
        }

        $request->setBody(json_encode($body));

        $this->addIdentityHeader($request);
        $this->addSignatureHeader($request);

        $this->request  = $request;
        $this->response = $this->sendRequest($request);

        $body = json_decode($this->response->getBody(), true);

        $error_message = false;
        $error_message = (!empty($body['error'])) ? $body['error'] : $error_message;
        $error_message = (!empty($body['errors'])) ? $body['errors'] : $error_message;
        $error_message = (is_array($error_message)) ? implode("\n", $error_message) : $error_message;

        if (false !== $error_message) {
            throw new \Exception($error_message);
        }

        $data = $body['data'];

        $payout
            ->setId($data['id'])
            ->setAccountId($data['account'])
            ->setResponseToken($data['token'])
            ->setStatus($data['status']);

        foreach ($data['instructions'] as $c => $instruction) {
            $payout->updateInstruction($c, 'setId', $instruction['id']);
        }

        return $payout;
    }

    /**
     * @inheritdoc
     */
    public function getPayouts($status = null)
    {
        $request = $this->createNewRequest();
        $request->setMethod(Request::METHOD_GET);

        $path = 'payouts?token='
                    . $this->token->getToken()
                    . (($status == null) ? '' : '&status=' . $status);

        $request->setPath($path);

        $this->addIdentityHeader($request);
        $this->addSignatureHeader($request);

        $this->request  = $request;
        $this->response = $this->sendRequest($this->request);

        $body = json_decode($this->response->getBody(), true);

        $error_message = false;
        $error_message = (!empty($body['error'])) ? $body['error'] : $error_message;
        $error_message = (!empty($body['errors'])) ? $body['errors'] : $error_message;
        $error_message = (is_array($error_message)) ? implode("\n", $error_message) : $error_message;

        if (false !== $error_message) {
            throw new \Exception($error_message);
        }

        $payouts = array();

        array_walk($body['data'], function ($value, $key) use (&$payouts) {
            $payout = new \Bitpay\Payout();
            $payout
                ->setId($value['id'])
                ->setAccountId($value['account'])
                ->setCurrency(new \Bitpay\Currency($value['currency']))
                ->setEffectiveDate($value['effectiveDate'])
                ->setRequestdate($value['requestDate'])
                ->setPricingMethod($value['pricingMethod'])
                ->setStatus($value['status'])
                ->setAmount($value['amount'])
                ->setResponseToken($value['token'])
                ->setRate(@$value['rate'])
                ->setBtcAmount(@$value['btc'])
                ->setReference(@$value['reference'])
                ->setNotificationURL(@$value['notificationURL'])
                ->setNotificationEmail(@$value['notificationEmail']);

            array_walk($value['instructions'], function ($value, $key) use (&$payout) {
                $instruction = new \Bitpay\PayoutInstruction();
                $instruction
                    ->setId($value['id'])
                    ->setLabel($value['label'])
                    ->setAddress($value['address'])
                    ->setAmount($value['amount'])
                    ->setStatus($value['status']);

                array_walk($value['transactions'], function ($value, $key) use (&$instruction) {
                    $transaction = new \Bitpay\PayoutTransaction();
                    $transaction
                        ->setTransactionId($value['txid'])
                        ->setAmount($value['amount'])
                        ->setDate($value['date']);

                    $instruction->addTransaction($transaction);
                });

                $payout->addInstruction($instruction);
            });

            $payouts[] = $payout;
        });

        return $payouts;
    }

    /**
     * @inheritdoc
     */
    public function deletePayout(PayoutInterface $payout)
    {
        $request = $this->createNewRequest();
        $request->setMethod(Request::METHOD_DELETE);
        $request->setPath(sprintf('payouts/%s?token=%s', $payout->getId(), $payout->getResponseToken()));

        $this->addIdentityHeader($request);
        $this->addSignatureHeader($request);

        $this->request  = $request;
        $this->response = $this->sendRequest($this->request);

        $body = json_decode($this->response->getBody(), true);

        if (empty($body['data'])) {
            throw new \Exception('Error with request: no data returned');
        }

        $data = $body['data'];

        $payout->setStatus($data['status']);

        return $payout;
    }

    /**
     * @inheritdoc
     */
    public function getPayout($payoutId)
    {
        $request = $this->createNewRequest();
        $request->setMethod(Request::METHOD_GET);
        $request->setPath(sprintf('payouts/%s?token=%s', $payoutId, $this->token->getToken()));

        $this->addIdentityHeader($request);
        $this->addSignatureHeader($request);

        $this->request  = $request;
        $this->response = $this->sendRequest($this->request);

        $body = json_decode($this->response->getBody(), true);

        if (empty($body['data'])) {
            throw new \Exception('Error with request: no data returned');
        }

        $data   = $body['data'];
        $payout = new \Bitpay\Payout();

        $payout
            ->setId($data['id'])
            ->setAccountId($data['account'])
            ->setStatus($data['status'])
            ->setCurrency(new \Bitpay\Currency($data['currency']))
            ->setRate(@$data['rate'])
            ->setAmount($data['amount'])
            ->setBtcAmount(@$data['btc'])
            ->setPricingMethod(@$data['pricingMethod'])
            ->setReference(@$data['reference'])
            ->setNotificationEmail(@$data['notificationEmail'])
            ->setNotificationUrl(@$data['notificationURL'])
            ->setRequestDate($data['requestDate'])
            ->setEffectiveDate($data['effectiveDate'])
            ->setResponseToken($data['token']);

        array_walk($data['instructions'], function ($value, $key) use (&$payout) {
            $instruction = new \Bitpay\PayoutInstruction();
            $instruction
                ->setId($value['id'])
                ->setLabel($value['label'])
                ->setAddress($value['address'])
                ->setStatus($value['status'])
                ->setAmount($value['amount'])
                ->setBtc($value['btc']);

            array_walk($value['transactions'], function ($value, $key) use (&$instruction) {
                $transaction = new \Bitpay\PayoutTransaction();
                $transaction
                    ->setTransactionId($value['txid'])
                    ->setAmount($value['amount'])
                    ->setDate($value['date']);

                $instruction->addTransaction($transaction);
            });

            $payout->addInstruction($instruction);
        });

        return $payout;
    }

    /**
     * @inheritdoc
     */
    public function getTokens()
    {
        $request = $this->createNewRequest();
        $request->setMethod(Request::METHOD_GET);
        $request->setPath('tokens');

        $this->addIdentityHeader($request);
        $this->addSignatureHeader($request);

        $this->request  = $request;
        $this->response = $this->sendRequest($this->request);

        $body = json_decode($this->response->getBody(), true);

        if (empty($body['data'])) {
            throw new \Exception('Error with request: no data returned');
        }

        $tokens = array();

        array_walk($body['data'], function ($value, $key) use (&$tokens) {
            $key   = current(array_keys($value));
            $value = current(array_values($value));
            $token = new \Bitpay\Token();
            $token
                ->setFacade($key)
                ->setToken($value);

            $tokens[$token->getFacade()] = $token;
        });

        return $tokens;
    }

    /**
     * @inheritdoc
     */
    public function createToken(array $payload = array())
    {
        if (isset($payload['pairingCode']) && 1 !== preg_match('/^[a-zA-Z0-9]{7}$/', $payload['pairingCode'])) {
            throw new \Exception('[ERROR] In Client::createToken(): The pairing code provided is not legal.');
        }

        $this->request = $this->createNewRequest();
        $this->request->setMethod(Request::METHOD_POST);
        $this->request->setPath('tokens');

        $payload['guid'] = Util::guid();

        $this->request->setBody(json_encode($payload));
        $this->response = $this->sendRequest($this->request);

        $body = json_decode($this->response->getBody(), true);

        if (isset($body['error'])) {
            throw new \Bitpay\Client\BitpayException($this->response->getStatusCode() . ': ' . $body['error']);
        }

        if($this->response->getStatusCode() >= 400) {
            throw new \Exception('invalid status code: '. $this->response->getStatusCode());
        }

        $tkn = $body['data'][0];
        $createdAt = new \DateTime();
        $pairingExpiration = new \DateTime();
        $token = new \Bitpay\Token();

        $token
            ->setPolicies($tkn['policies'])
            ->setToken($tkn['token'])
            ->setFacade($tkn['facade'])
            ->setCreatedAt($createdAt->setTimestamp(floor($tkn['dateCreated'] / 1000)));

        if (isset($tkn['resource'])) {
            $token->setResource($tkn['resource']);
        }

        if (isset($tkn['pairingCode'])) {
            $token->setPairingCode($tkn['pairingCode']);
            $token->setPairingExpiration($pairingExpiration->setTimestamp(floor($tkn['pairingExpiration'] / 1000)));
        }

        return $token;
    }

    /**
     * Returns the Response object that BitPay returned from
     * the request that was sent.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns the request object that was sent to BitPay.
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function getInvoice($invoiceId)
    {
        $this->request = $this->createNewRequest();
        $this->request->setMethod(Request::METHOD_GET);

        if ($this->token && $this->token->getFacade() === 'merchant') {
            $this->request->setPath(sprintf('invoices/%s?token=%s', $invoiceId, $this->token->getToken()));
            $this->addIdentityHeader($this->request);
            $this->addSignatureHeader($this->request);
        } else {
            $this->request->setPath(sprintf('invoices/%s', $invoiceId));
        }

        $this->response = $this->sendRequest($this->request);

        $body = json_decode($this->response->getBody(), true);

        if (isset($body['error'])) {
            throw new \Exception($body['error']);
        }

        $data = $body['data'];
        
        $invoice = new \Bitpay\Invoice();
        $invoice = $this->fillInvoiceData($invoice, $data);

        return $invoice;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function sendRequest(RequestInterface $request)
    {
        if (null === $this->adapter) {
            // Uses the default adapter
            $this->adapter = new \Bitpay\Client\Adapter\CurlAdapter();
        }

        return $this->adapter->sendRequest($request);
    }

    /**
     * @param RequestInterface $request
     * @throws \Exception
     */
    protected function addIdentityHeader(RequestInterface $request)
    {
        if (null === $this->publicKey) {
            throw new \Exception('[ERROR] In Client::addIdentityHeader(): No public key value found. Please set your kublic key first before you can add the x-identity header.');
        }

        $request->setHeader('x-identity', (string) $this->publicKey);
    }

    /**
     * @param RequestInterface $request
     * @throws \Exception
     */
    protected function addSignatureHeader(RequestInterface $request)
    {
        if (null === $this->privateKey) {
            throw new \Exception('Please set your Private Key');
        }

        $url = $request->getFullUri();

        $message = sprintf(
            '%s%s',
            $url,
            $request->getBody()
        );

        $signature = $this->privateKey->sign($message);

        $request->setHeader('x-signature', $signature);
    }

    /**
     * @return RequestInterface
     *
     * @throws BitpayException
     */
    protected function createNewRequest()
    {
        if ($this->uri === null) {
            throw new BitpayException('You should provider the url of your BTCPAY server');
        }
        $request = new Request();
        $request->setUri($this->uri);
        $this->prepareRequestHeaders($request);

        return $request;
    }

    /**
     * Prepares the request object by adding additional headers
     *
     * @see http://en.wikipedia.org/wiki/User_agent
     * @param RequestInterface $request
     */
    protected function prepareRequestHeaders(RequestInterface $request)
    {
        $request->setHeader(
            'User-Agent',
            sprintf('%s/%s (PHP %s)', self::NAME, self::VERSION, phpversion())
        );

        $request->setHeader('X-BitPay-Plugin-Info', sprintf('%s/%s', self::NAME, self::VERSION));
        $request->setHeader('Content-Type', 'application/json');
        $request->setHeader('X-Accept-Version', '2.0.0');
    }

    /**
     * @param string $price
     * @param string $currency
     */
    protected function checkPriceAndCurrency($price, $currency)
    {
    }
}
