<?php

declare(strict_types=1);

namespace IonBazan\PaymentQR\Poland;

use Endroid\QrCode\QrCode;
use RuntimeException;

class QrPayment
{
    public const DELIMITER = '|';
    public const DISALLOWED_CHARS = '/([^A-Za-z0-9 ,\.\/\\\\\-@#&\*\/¹æê³ñóœŸ¿¥ÆÊ£ÑŹÓŒąćęłńóśźżĄĆĘŁŃŚŻ¯_])/u';

    /**
     * @param string      $accountNumber Account number (26 digits)
     * @param string      $recipient     Recipient name (max 20 characters)
     * @param string      $title         Transfer title (max 32 characters)
     * @param int         $amount        Amount as integer (in gr) - can be 0
     * @param string|null $nip           Recipient NIP (10 characters)
     * @param string      $country       Country code - the only supported is 'PL' (2 characters)
     * @param string|null $directDebitId Customer ID for Direct Debit (max 20 characters)
     * @param string|null $invoobillId   Invoobill ID (max 12 characters)
     * @param string|null $reserved      Reserved (max 24 characters)
     */
    public function __construct(
        public readonly string $accountNumber,
        public readonly string $recipient,
        public readonly string $title,
        public readonly int $amount,
        public readonly ?string $nip = null,
        public readonly string $country = 'PL',
        public readonly ?string $directDebitId = null,
        public readonly ?string $invoobillId = null,
        public readonly ?string $reserved = null
    ) {
    }

    public static function fromQrString(string $qrString): self
    {
        $parts = explode(self::DELIMITER, $qrString);

        return new static(
            $parts[2],
            $parts[4],
            $parts[5],
            (int) $parts[3],
            $parts[0],
            $parts[1],
            $parts[6],
            $parts[7],
            $parts[8]
        );
    }

    public function getQrString(): string
    {
        $amount = sprintf('%06d', $this->amount);

        $parts = [
            $this->filterVar($this->nip, 10),
            $this->filterVar($this->country, 2),
            $this->filterVar($this->accountNumber, 26),
            $amount,
            $this->filterVar($this->recipient, 20),
            $this->filterVar($this->title, 32),
            $this->filterVar($this->directDebitId, 20),
            $this->filterVar($this->invoobillId, 12),
            $this->filterVar($this->reserved, 24 - strlen($amount) + 6),
        ];

        return implode(self::DELIMITER, $parts);
    }

    public function getQrCode(): QrCode
    {
        if (!class_exists(QrCode::class)) {
            throw new RuntimeException('Generating QR images requires endroid/qr-code');
        }

        $qrCode = new QrCode($this->getQrString());
        $qrCode->setSize(250);

        return $qrCode;
    }

    private function filterVar(?string $variable, int $length): ?string
    {
        return substr(trim(preg_replace(self::DISALLOWED_CHARS, '', (string) $variable)), 0, $length);
    }
}
