<?php
/**
 * @license Copyright 2011-2015 BitPay Inc., MIT License
 * see https://github.com/bitpay/php-bitpay-client/blob/master/LICENSE
 */

namespace Bitpay;

class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    private $invoice;

    public function setUp()
    {
        $this->invoice = new Invoice();
    }

    public function testGetPrice()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getPrice(), 0.0);
    }

    /**
     * @depends testGetPrice
     */
    public function testSetPrice()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setPrice(9.99);
        $this->assertSame(9.99, $this->invoice->getPrice());
    }

    public function testGetCurrency()
    {
        $this->assertNotNull($this->invoice);
        $this->assertInstanceOf('Bitpay\Currency', $this->invoice->getCurrency());
    }

    /**
     * @depends testGetCurrency
     */
    public function testSetCurrency()
    {
        $this->assertNotNull($this->invoice);
        $currency = $this->getMockCurrency();
        $this->invoice->setCurrency($currency);
        $this->assertSame($currency, $this->invoice->getCurrency());
    }

    public function testGetItem()
    {
        $this->assertNotNull($this->invoice);
        $this->assertInstanceOf('Bitpay\ItemInterface', $this->invoice->getItem());
    }

    /**
     * @depends testGetItem
     */
    public function testSetItem()
    {
        $this->assertNotNull($this->invoice);
        $item = $this->getMockItem();
        $this->invoice->setItem($item);
        $this->assertSame($item, $this->invoice->getItem());
    }

    public function testGetBuyer()
    {
        $this->assertNotNull($this->invoice);
        $this->assertInstanceOf('Bitpay\BuyerInterface', $this->invoice->getBuyer());
    }

    /**
     * @depends testGetBuyer
     */
    public function testSetBuyer()
    {
        $this->assertNotNull($this->invoice);
        $buyer = $this->getMockBuyer();
        $this->invoice->setBuyer($buyer);
        $this->assertSame($buyer, $this->invoice->getBuyer());
    }

    public function testGetTransactionSpeed()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame(Invoice::TRANSACTION_SPEED_MEDIUM, $this->invoice->getTransactionSpeed());
    }

    /**
     * @depends testGetTransactionSpeed
     */
    public function testSetTransactionSpeed()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setTransactionSpeed(Invoice::TRANSACTION_SPEED_LOW);
        $this->assertSame(Invoice::TRANSACTION_SPEED_LOW, $this->invoice->getTransactionSpeed());
    }

    public function testGetNotificationEmail()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getNotificationEmail(), '');
    }

    /**
     * @depends testGetNotificationEmail
     */
    public function testSetNotificationEmail()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setNotificationEmail('integrations@bitpay.com');
        $this->assertSame('integrations@bitpay.com', $this->invoice->getNotificationEmail());
    }

    public function testGetNotificationUrl()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getNotificationUrl(), '');
    }

    /**
     * @depends testGetNotificationUrl
     */
    public function testSetNotificationUrl()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setNotificationUrl('https://bitpay.com');
        $this->assertSame('https://bitpay.com', $this->invoice->getNotificationUrl());
    }

    public function testGetRedirectUrl()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getRedirectUrl(), '');
    }

    /**
     * @depends testGetRedirectUrl
     */
    public function testSetRedirectUrl()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setRedirectUrl('https://bitpay.com');
        $this->assertSame('https://bitpay.com', $this->invoice->getRedirectUrl());
    }

    public function testGetPosData()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getPosData(), '');
    }

    /**
     * @depends testGetPosData
     */
    public function testSetPosData()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setPosData('https://bitpay.com');
        $this->assertSame('https://bitpay.com', $this->invoice->getPosData());
    }

    public function testGetStatus()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getStatus(), '');
    }

    /**
     * @depends testGetStatus
     */
    public function testSetStatus()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setStatus('new');
        $this->assertSame('new', $this->invoice->getStatus());
    }

    public function testIsFullNotifications()
    {
        $this->assertNotNull($this->invoice);
        $this->assertTrue($this->invoice->isFullNotifications());
    }

    public function testGetId()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getId(), '');
    }

    /**
     * @depends testGetId
     */
    public function testSetId()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setId('af7as6fd97ad6fa');
        $this->assertSame('af7as6fd97ad6fa', $this->invoice->getId());
    }

    public function testGetUrl()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getUrl(), '');
    }

    /**
     * @depends testGetUrl
     */
    public function testSetUrl()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setUrl('https://bitpay.com/invoice?id=af7as6fd97ad6fa');
        $this->assertSame('https://bitpay.com/invoice?id=af7as6fd97ad6fa', $this->invoice->getUrl());
    }

    public function testGetBtcPrice()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getBtcPrice(), 0.0);
    }

    /**
     * @depends testGetBtcPrice
     */
    public function testSetBtcPrice()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setBtcPrice(0.001);
        $this->assertSame(0.001, $this->invoice->getBtcPrice());
    }

    public function testGetInvoiceTime()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getInvoiceTime());
    }

    /**
     * @depends testGetInvoiceTime
     */
    public function testSetInvoiceTime()
    {
        $this->assertNotNull($this->invoice);
        $date = new \DateTime('now', new \DateTimeZone("UTC"));
        $this->invoice->setInvoiceTime($date);
        $this->assertSame($date, $this->invoice->getInvoiceTime());
    }

    public function testGetExpirationTime()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getExpirationTime());
    }

    /**
     * @depends testGetExpirationTime
     */
    public function testSetExpirationTime()
    {
        $this->assertNotNull($this->invoice);

        $date = new \DateTime('now',new \DateTimeZone("UTC"));

        $this->assertNotNull($date);

        $this->invoice->setExpirationTime($date);
        $this->assertSame($date, $this->invoice->getExpirationTime());
    }

    public function testGetCurrentTime()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getCurrentTime());
    }

    /**
     * @depends testGetCurrentTime
     */
    public function testSetCurrentTime()
    {
        $this->assertNotNull($this->invoice);

        $date = new \DateTime('now',new \DateTimeZone("UTC"));

        $this->assertNotNull($date);

        $this->invoice->setCurrentTime($date);
        $this->assertSame($date, $this->invoice->getCurrentTime());
    }

    public function testGetOrderId()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getOrderId(), '');
    }

    /**
     * @depends testGetOrderId
     */
    public function testSetOrderId()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setOrderId('100001');
        $this->assertSame('100001', $this->invoice->getOrderId());
    }

    public function testGetItemDesc()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getItemDesc());
    }

    public function testGetItemCode()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getItemCode());
    }

    public function testIsPhysical()
    {
        $this->assertNotNull($this->invoice);
        $this->assertFalse($this->invoice->isPhysical());
    }

    public function testGetBuyerName()
    {
        $this->assertNotNull($this->invoice);
        $this->assertEmpty($this->invoice->getBuyerName());
    }

    public function testGetBuyerAddress1()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getBuyerAddress1());
    }

    public function testGetBuyerAddress2()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getBuyerAddress2());
    }

    public function testGetBuyerCity()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getBuyerCity());
    }

    public function testGetBuyerState()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getBuyerState());
    }

    public function testGetBuyerZip()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getBuyerZip());
    }

    public function testGetBuyerCountry()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getBuyerCountry());
    }

    public function testGetBuyerEmail()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getBuyerEmail());
    }

    public function testGetBuyerPhone()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getBuyerPhone());
    }

    public function testGetExceptionStatus()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getExceptionStatus(), '');
    }

    /**
     * @depends testGetExceptionStatus
     */
    public function testSetExcpetionStatus()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setExceptionStatus(false);
        $this->assertFalse($this->invoice->getExceptionStatus());
    }

    public function testGetBtcPaid()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getBtcPaid(), 0.0);
    }

    /**
     * @depends testGetBtcPaid
     */
    public function testSetBtcPaid()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setBtcPaid(0.00);
        $this->assertSame(0.00, $this->invoice->getBtcPaid());
    }

    public function testGetRate()
    {
        $this->assertNotNull($this->invoice);
        $this->assertSame($this->invoice->getRate(), 0.0);
    }

    /**
     * @depends testGetRate
     */
    public function testSetRate()
    {
        $this->assertNotNull($this->invoice);
        $this->invoice->setRate(600.00);
        $this->assertSame(600.00, $this->invoice->getRate());
    }

    public function testGetToken()
    {
        $this->assertNotNull($this->invoice);
        $this->assertNull($this->invoice->getToken());
    }

    /**
     * @depends testGetToken
     */
    public function testSetToken()
    {
        $invoiceToken = new \Bitpay\Token();
        $this->assertNotNull($this->invoice);
        $this->invoice->setToken($invoiceToken);
        $this->assertSame($invoiceToken, $this->invoice->getToken());
    }

    public function testSetFullNotifications()
    {
        $this->assertTrue($this->invoice->isFullNotifications());
        $this->invoice->setFullNotifications(false);
        $this->assertFalse($this->invoice->isFullNotifications());
    }

    private function getMockItem()
    {
        return new \Bitpay\Item();
    }

    private function getMockBuyer()
    {
        return new \Bitpay\Buyer();
    }

    private function getMockCurrency()
    {
        return new \Bitpay\Currency();
    }
}
