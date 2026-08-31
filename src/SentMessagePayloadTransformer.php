<?php

namespace Bentonow\BentoLaravel;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\HeaderInterface;
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

        if ($symfonyEmail->getReplyTo()) {
            $payload['reply_to'] = $this->formatEmailAddresses($symfonyEmail->getReplyTo());
        }

        $headers = $this->formatHeaders($symfonyEmail->getHeaders()->all());

        if ($headers) {
            $payload['headers'] = $headers;
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

    /**
     * @param  iterable<string, HeaderInterface>  $headers
     * @return array<string, string>
     */
    private function formatHeaders(iterable $headers): array
    {
        $formatted = [];

        foreach ($headers as $header) {
            $name = $header->getName();

            if (in_array(strtolower($name), ['from', 'to', 'cc', 'bcc', 'subject', 'reply-to'], true)) {
                continue;
            }

            if (! preg_match('/^[\x21-\x39\x3B-\x7E]+$/D', $name)) {
                continue;
            }

            $value = preg_replace('/[\\x00-\\x1F\\x7F]/', '', $header->getBodyAsString());

            if ($value === null || $value === '') {
                continue;
            }

            $formatted[$name] = isset($formatted[$name])
                ? $formatted[$name].', '.$value
                : $value;
        }

        return $formatted;
    }
}
