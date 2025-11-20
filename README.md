# QR Payment (PL)

[![Latest Stable Version](http://img.shields.io/packagist/v/ion-bazan/pl-qr-payment.svg)](https://packagist.org/packages/ion-bazan/pl-qr-payment)
[![Build Status](http://img.shields.io/travis/com/IonBazan/pl-qr-payment.svg)](http://travis-ci.com/IonBazan/pl-qr-payment)
[![Codecov](https://img.shields.io/codecov/c/github/IonBazan/pl-qr-payment/master.svg)](https://codecov.io/gh/IonBazan/pl-qr-payment)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FIonBazan%2Fpl-qr-payment%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/IonBazan/pl-qr-payment/master)
[![Total Downloads](http://img.shields.io/packagist/dt/ion-bazan/pl-qr-payment.svg)](https://packagist.org/packages/ion-bazan/pl-qr-payment)
[![Monthly Downloads](http://img.shields.io/packagist/dm/ion-bazan/pl-qr-payment.svg)](https://packagist.org/packages/ion-bazan/pl-qr-payment)
[![License](http://img.shields.io/packagist/l/ion-bazan/pl-qr-payment.svg)](https://packagist.org/packages/ion-bazan/pl-qr-payment)

This library helps you generate payment QR codes for Polish bank mobile applications. This is useful in invoice generators, etc to let your customers pay event faster 💸.

Makes use of [endroid/qr-code](https://github.com/endroid/qr-code) for QR-code generation but you can use any library, because the QR string is available via `getQrString()` method.

**Please note, that this library is only suitable for Polish bank systems**

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

```bash
composer require ion-bazan/pl-qr-payment
```

## Minimal usage example

```php
use IonBazan\PaymentQR\Poland\QrPayment;

$payment = new QrPayment(
    '4249000050026313017364142', // Account number
    'Testowy odbiorca',          // Recipient name
    'Tytuł płatności',           // Payment title
    12345                        // Amount in gr
);

/** @var \Endroid\QrCode\QrCode $qrCode */
$qrCode = $payment->getQrCode(); // Do anything you want with the QrCode object

header('Content-Type: '.$qrCode->getContentType());
echo $qrCode->writeString();
```

## Advanced usage

```php
use IonBazan\PaymentQR\Poland\QrPayment;

$payment = new QrPayment(
    '4249000050026313017364142', // Account number
    'Testowy odbiorca',          // Recipient name
    'Tytuł płatności',           // Payment title
    12345,                       // Amount in gr
    '5214349636',                // Recipient NIP (optional)
    'PL',                        // Country code (only PL is supported) (optional)
    '11223344',                  // Customer ID for Direct Debit (optional)
    '990066'                     // Invoobill ID (optional)
);

$qrString = $payment->getQrString(); // You can encode it using the QR library of your choice ...
echo $qrString;                      // ... or just print it for debug
```

## Validation

Currently, this library does not offer any fancy validation. It tries to stop you from breaking things by stripping invalid characters but don't expect too much.
You should already have your data valid before generating the QR code.

## Footnotes

This library is a quick approach to implement so called [Rekomendacja Związku Banków Polskich dotycząca kodu dwuwymiarowego („2D”), umożliwiającego realizację polecenia przelewu oraz aktywację usług bankowych na rynku polskim - wersja 1.0](https://zbp.pl/getmedia/1d7fef90-d193-4a2d-a1c3-ffdf1b0e0649/2013-12-03_-_Rekomendacja_-_Standard_2D)

According to that document, QR codes should have following parameters:

| Parameter        | Value                             |
|------------------|-----------------------------------|
| Type             | **QR**                            |
| Size             | **250 px** (min. 1.8 cm x 1.8 cm) |
| Error Correction | **Low (L)**                       |
| Encoding         | **UTF-8**                         |

## License

This library is under the MIT license.
