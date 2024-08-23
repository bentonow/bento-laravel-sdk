<?php

namespace Bentonow\BentoLaravel;

use Symfony\Component\Mime\Email;

class MailDetails
{
    public function __construct(
        public string $toAddress,
        public string $fromAddress,
    ) {}

    public static function fromEmail(Email $mail): self
    {
        throw_if(count($mail->getTo()) > 1, new \Exception('Bulk emails are not possible'));

        return new static (
            toAddress: $mail->getTo()[0]->getAddress(),
            fromAddress: $mail->getFrom()[0]->getAddress(),
        );
    }
}
