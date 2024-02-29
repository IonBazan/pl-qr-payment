<?php

declare(strict_types=1);

namespace IonBazan\PaymentQR\Poland\Tests;

use Endroid\QrCode\ErrorCorrectionLevel;
use IonBazan\PaymentQR\Poland\QrPayment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class QrPaymentTest extends TestCase
{
    public function testItGeneratesValidQrStringForExampleInput(): void
    {
        $payment = new QrPayment(
            '24160000035175530643314956',
            'Testowy odbiorca',
            'Tytuł płatności',
            12345,
            '5214349636',
            'PL',
            '11223344',
            '990066',
            'reserved'
        );

        $this->assertSame('24160000035175530643314956', $payment->accountNumber);
        $this->assertSame('Testowy odbiorca', $payment->recipient);
        $this->assertSame('Tytuł płatności', $payment->title);
        $this->assertSame(12345, $payment->amount);
        $this->assertSame('5214349636', $payment->nip);
        $this->assertSame('PL', $payment->country);
        $this->assertSame('11223344', $payment->directDebitId);
        $this->assertSame('990066', $payment->invoobillId);
        $this->assertSame('reserved', $payment->reserved);

        $this->assertSame(
            '5214349636|PL|24160000035175530643314956|012345|Testowy odbiorca|Tytuł płatności|11223344|990066|reserved',
            $payment->getQrString()
        );
    }

    #[DataProvider('documentationExamplesProvider')]
    public function testItGeneratesValidQrString(string $expectedResult, QrPayment $paymentQr): void
    {
        $this->assertSame($expectedResult, $paymentQr->getQrString());
        $this->assertEquals($paymentQr, QrPayment::fromQrString($expectedResult));
    }

    #[DataProvider('documentationExamplesProvider')]
    public function testItCreatesValidObjectFromString(string $inputString, QrPayment $expectedPayment): void
    {
        $payment = QrPayment::fromQrString($inputString);
        $this->assertEquals($expectedPayment, $payment);
        $this->assertSame($inputString, $payment->getQrString());
    }

    #[RunInSeparateProcess]
    public function testItGeneratesQrImage(): void
    {
        $payment = new QrPayment(
            '24160000035175530643314956',
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
            '5214349636|PL|24160000035175530643314956|012345|Testowy odbiorca|Tytuł płatności|11223344|990066|',
            $qrCode->getData()
        );

        if (class_exists(ErrorCorrectionLevel\ErrorCorrectionLevelLow::class)) {
            $correctionLevel = new ErrorCorrectionLevel\ErrorCorrectionLevelLow();
        } else {
            $correctionLevel = ErrorCorrectionLevel::Low;
        }

        $this->assertSame(250, $qrCode->getSize());
        $this->assertEquals($correctionLevel, $qrCode->getErrorCorrectionLevel());
        $this->assertSame('UTF-8', (string) $qrCode->getEncoding());
    }

    public function testItThrowsExceptionWhenEndroidIsNotInstalled(): void
    {
        $autoloaders = spl_autoload_functions();
        $this->expectException(\RuntimeException::class);
        $payment = new QrPayment(
            '24160000035175530643314956',
            'Testowy odbiorca',
            'Tytuł płatności',
            12345,
            '5214349636',
            'PL',
            '11223344',
            '990066'
        );

        array_map('spl_autoload_unregister', $autoloaders);

        try {
            $payment->getQrCode();
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            array_map('spl_autoload_register', $autoloaders);
        }
    }

    public function testItStripsInvalidCharactersAndTrims(): void
    {
        $payment = new QrPayment(
            '|%()24160000035175530643314956_123456789',
            '|%()Testowy odbiorca 123456789',
            '|%()Tytuł płatności 01234567890123456789',
            123456789,
            '|%()5214349636_123456789',
            '|%()PL123',
            '|%()123456789012345678901234567890',
            '|%() 990066990066990066',
            '123456789012345678901234'
        );

        $this->assertSame(
            '5214349636|PL|24160000035175530643314956|123456789|Testowy odbiorca 123|Tytuł płatności 0123456789012|12345678901234567890|990066990066|123456789012345678901',
            $payment->getQrString()
        );
    }

    public static function documentationExamplesProvider(): array
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
            'Full example' => [
                '5214349636|PL|4249000050026313017364142|012345|Testowy odbiorca|Tytuł płatności|11223344|990066|reserved',
                new QrPayment(
                    '4249000050026313017364142',
                    'Testowy odbiorca',
                    'Tytuł płatności',
                    12345,
                    '5214349636',
                    'PL',
                    '11223344',
                    '990066',
                    'reserved'
                ),
            ],
        ];
    }
}
