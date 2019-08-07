<?php

namespace IonBazan\QrPayment\Poland\Tests;

use Endroid\QrCode\ErrorCorrectionLevel;
use IonBazan\PaymentQR\Poland\QrPayment;
use PHPUnit\Framework\TestCase;

class QrPaymentTest extends TestCase
{
    public function testItGeneratesValidQrStringForExampleInput() {
        $payment = new QrPayment(
            '4249000050026313017364142',
            'Testowy odbiorca',
            'Tytuł płatności',
            12345,
            '5214349636',
            'PL',
            '11223344',
            '990066'
        );

        $this->assertSame(
            '5214349636|PL|4249000050026313017364142|012345|Testowy odbiorca|Tytuł płatności|11223344|990066|',
            $payment->getQrString()
        );
    }

    /**
     * @dataProvider documentationExamplesProvider
     *
     * @param string    $expectedResult
     * @param QrPayment $paymentQr
     */
    public function testItGeneratesValidQrString(string $expectedResult, QrPayment $paymentQr)
    {
        $this->assertSame($expectedResult, $paymentQr->getQrString());
        $this->assertEquals($paymentQr, QrPayment::fromQrString($expectedResult));
    }

    /**
     * @dataProvider documentationExamplesProvider
     *
     * @param string    $inputString
     * @param QrPayment $expectedPayment
     */
    public function testItCreatesValidObjectFromString(string $inputString, QrPayment $expectedPayment)
    {
        $payment = QrPayment::fromQrString($inputString);
        $this->assertEquals($expectedPayment, $payment);
        $this->assertSame($inputString, $payment->getQrString());
    }

    public function testItGeneratesQrImage()
    {
        $payment = new QrPayment(
            '4249000050026313017364142',
            'Testowy odbiorca',
            'Tytuł płatności',
            12345,
            '5214349636',
            'PL',
            '11223344',
            '990066'
        );

        $qrCode = $payment->getQrCode();

        $this->assertSame(
            '5214349636|PL|4249000050026313017364142|012345|Testowy odbiorca|Tytuł płatności|11223344|990066|',
            $qrCode->getText()
        );

        $this->assertSame(250, $qrCode->getSize());
        $this->assertEquals(ErrorCorrectionLevel::LOW, $qrCode->getErrorCorrectionLevel());
        $this->assertSame('UTF-8', $qrCode->getEncoding());
    }

    public function testItStripsInvalidCharactersAndTrims()
    {
        $payment = new QrPayment(
            '|%()4249000050026313017364142',
            '|%()Testowy odbiorca',
            '|%()Tytuł płatności',
            123456789,
            '|%()5214349636',
            '|%()PL',
            '|%()11223344',
            '|%()990066',
            '123456789012345678901234'
        );

        $this->assertSame(
            '5214349636|PL|4249000050026313017364142|123456789|Testowy odbiorca|Tytuł płatności|11223344|990066|123456789012345678901',
            $payment->getQrString()
        );
    }

    public function documentationExamplesProvider(): array
    {
        return [
            '2D dla Odbiorcy typ 1 z kwotą płatności bez możliwości edycji' => [
                '1234567890|PL|92124012340001567890123456|001200|Odbiorca 1|FV 1234/34/2012|||',
                new QrPayment(
                    '92124012340001567890123456',
                    'Odbiorca 1',
                    'FV 1234/34/2012',
                    1200,
                    '1234567890'
                ),
            ],
            '2D dla Odbiorcy typ 1 z manualnie uzupełnianą kwotą przez dokonującego płatność' => [
                '1234567890|PL|92124012340001567890123456|000000|Odbiorca 1|FV 1234/34/2012|||',
                new QrPayment(
                    '92124012340001567890123456',
                    'Odbiorca 1',
                    'FV 1234/34/2012',
                    0,
                    '1234567890'
                ),
            ],
            '2D dla Odbiorcy typ 2 z kwotą płatności bez możliwości edycji' => [
                '|PL|92124012340001567890123456|001200|Odbiorca 1|Przelew ekspress|||',
                new QrPayment(
                    '92124012340001567890123456',
                    'Odbiorca 1',
                    'Przelew ekspress',
                    1200
                ),
            ],
        ];
    }
}
