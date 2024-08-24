<?php

namespace Bentonow\BentoLaravel;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class SentMessagePayloadTransformer
{
    public function transform(SentMessage $sentMessage): array
    {
        $symfonyEmail = MessageConverter::toEmail($sentMessage->getOriginalMessage());

        $payload = [
            'from' => $symfonyEmail->getFrom()[0]->getAddress(),
            'subject' => $symfonyEmail->getSubject(),
            'html_body' => $symfonyEmail->getHtmlBody(),
            'transactional' => true,
        ];

        if ($symfonyEmail->getTo()) {
            $payload['to'] = $this->formatEmailAddresses($symfonyEmail->getTo());
        }

        if ($symfonyEmail->getCc()) {
            $payload['cc'] = $this->formatEmailAddresses($symfonyEmail->getCc());
        }

        if ($symfonyEmail->getBcc()) {
            $payload['bcc'] = $this->formatEmailAddresses($symfonyEmail->getBcc());
        }

        return [
            'emails' => [$payload],
        ];
    }

    /**
     * @param  Address[]  $addresses
     */
    private function formatEmailAddresses(array $addresses): string
    {
        return implode(
            ',',
            array_map(fn (Address $address) => $address->getAddress(), $addresses),
        );
    }
}
