<?php

namespace IonBazan\PaymentQR\Poland;

use Endroid\QrCode\QrCode;
use RuntimeException;

class QrPayment
{
    const DELIMITER = '|';
    const DISALLOWED_CHARS = '/([^A-Za-z0-9 ,\.\/\\\\\-@#&\*\/¹æê³ñóœŸ¿¥ÆÊ£ÑŹÓŒąćęłńóśźżĄĆĘŁŃŚŻ¯_])/u';

    /** @var string|null */
    protected $nip;

    /** @var string */
    protected $country;

    /** @var string */
    protected $accountNumber;

    /** @var int */
    protected $amount;

    /** @var string */
    protected $recipient;

    /** @var string */
    protected $title;

    /** @var string|null */
    protected $directDebitId;

    /** @var string|null */
    protected $invoobillId;

    /** @var string|null */
    protected $reserved;

    /**
     * @param string      $accountNumber Account number (26 digits)
     * @param string      $recipient     Recipient name (max 20 characters)
     * @param string      $title         Transfer title (max 32 characters)
     * @param int         $amount        Amount as integer (in gr) - can be 0
     * @param null|string $nip           Recipient NIP (10 characters)
     * @param string      $country       Country code - the only supported is 'PL' (2 characters)
     * @param null|string $directDebitId Customer ID for Direct Debit (max 20 characters)
     * @param null|string $invoobillId   Invoobill ID (max 12 characters)
     * @param null|string $reserved      Reserved (max 24 characters)
     */
    public function __construct(
        string $accountNumber,
        string $recipient,
        string $title,
        int $amount = 0,
        ?string $nip = null,
        string $country = 'PL',
        ?string $directDebitId = null,
        ?string $invoobillId = null,
        ?string $reserved = null
    ) {
        $this->accountNumber = $accountNumber;
        $this->recipient = $recipient;
        $this->title = $title;
        $this->amount = $amount;
        $this->nip = $nip;
        $this->country = $country;
        $this->directDebitId = $directDebitId;
        $this->invoobillId = $invoobillId;
        $this->reserved = $reserved;
    }

    public static function fromQrString(string $qrString): QrPayment
    {
        $parts = explode(self::DELIMITER, $qrString);

        return new static(
            $parts[2],
            $parts[4],
            $parts[5],
            (int) ltrim($parts[3], '0'),
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

        $parts = array_map('trim', $parts);

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

    public function getNip(): ?string
    {
        return $this->nip;
    }

    public function setNip(?string $nip): QrPayment
    {
        $this->nip = $nip;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): QrPayment
    {
        $this->country = $country;

        return $this;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): QrPayment
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): QrPayment
    {
        $this->amount = $amount;

        return $this;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function setRecipient(string $recipient): QrPayment
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): QrPayment
    {
        $this->title = $title;

        return $this;
    }

    public function getDirectDebitId(): ?string
    {
        return $this->directDebitId;
    }

    public function setDirectDebitId(?string $directDebitId): QrPayment
    {
        $this->directDebitId = $directDebitId;

        return $this;
    }

    public function getInvoobillId(): ?string
    {
        return $this->invoobillId;
    }

    public function setInvoobillId(?string $invoobillId): QrPayment
    {
        $this->invoobillId = $invoobillId;

        return $this;
    }

    public function getReserved(): ?string
    {
        return $this->reserved;
    }

    public function setReserved(?string $reserved): QrPayment
    {
        $this->reserved = $reserved;

        return $this;
    }

    protected function filterVar(?string $variable, int $length = 0): ?string
    {
        $variable = preg_replace(self::DISALLOWED_CHARS, '', $variable);

        return substr($variable, 0, $length);
    }
}
