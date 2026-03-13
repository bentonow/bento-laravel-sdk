<?php

namespace Bentonow\BentoLaravel;

use Bentonow\BentoLaravel\DataTransferObjects\EventData;
use Bentonow\BentoLaravel\DataTransferObjects\ImportSubscribersData;
use Bentonow\BentoLaravel\Enums\BentoEvent;
use Bentonow\BentoLaravel\Requests\CreateEvents;
use Bentonow\BentoLaravel\Requests\FindSubscriber;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Bentonow\BentoLaravel\Responses\BentoApiResponse;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class BentoConnector extends Connector
{
    use AlwaysThrowOnErrors;

    protected ?string $response = BentoApiResponse::class;

    public function resolveBaseUrl(): string
    {
        return 'https://app.bentonow.com/api/v1';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'bento-laravel-'.config('bentonow.site_uuid'),
        ];
    }

    protected function defaultQuery(): array
    {
        return [
            'site_uuid' => config('bentonow.site_uuid'),
        ];
    }

    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator(config('bentonow.publishable_key'), config('bentonow.secret_key'));
    }

    /**
     * Tag a subscriber (triggers automations).
     */
    public function tagSubscriber(string $email, string $tagName): Response
    {
        return $this->send(new CreateEvents(collect([
            new EventData(
                type: BentoEvent::TAG->value,
                email: $email,
                details: ['tag' => $tagName],
            ),
        ])));
    }

    /**
     * Add/subscribe a subscriber (triggers automations).
     */
    public function addSubscriber(string $email, ?array $fields = null): Response
    {
        return $this->send(new CreateEvents(collect([
            new EventData(
                type: BentoEvent::SUBSCRIBE->value,
                email: $email,
                fields: $fields,
            ),
        ])));
    }

    /**
     * Remove/unsubscribe a subscriber (triggers automations).
     */
    public function removeSubscriber(string $email): Response
    {
        return $this->send(new CreateEvents(collect([
            new EventData(
                type: BentoEvent::UNSUBSCRIBE->value,
                email: $email,
            ),
        ])));
    }

    /**
     * Update custom fields on a subscriber (triggers automations).
     */
    public function updateFields(string $email, array $fields): Response
    {
        return $this->send(new CreateEvents(collect([
            new EventData(
                type: BentoEvent::UPDATE_FIELDS->value,
                email: $email,
                fields: $fields,
            ),
        ])));
    }

    /**
     * Track a purchase for LTV calculation (triggers automations).
     */
    public function trackPurchase(string $email, array $purchaseDetails): Response
    {
        return $this->send(new CreateEvents(collect([
            new EventData(
                type: BentoEvent::PURCHASE->value,
                email: $email,
                details: $purchaseDetails,
            ),
        ])));
    }

    /**
     * Track a custom event (triggers automations).
     */
    public function track(string $email, string $type, ?array $fields = null, ?array $details = null): Response
    {
        return $this->send(new CreateEvents(collect([
            new EventData(
                type: $type,
                email: $email,
                fields: $fields,
                details: $details,
            ),
        ])));
    }

    /**
     * Create or update a subscriber and return the record.
     */
    public function upsertSubscriber(
        string $email,
        ?string $firstName = null,
        ?string $lastName = null,
        ?array $tags = null,
        ?array $removeTags = null,
        ?array $fields = null,
    ): Response {
        $this->send(new ImportSubscribers(collect([
            new ImportSubscribersData(
                email: $email,
                firstName: $firstName,
                lastName: $lastName,
                tags: $tags,
                removeTags: $removeTags,
                fields: $fields,
            ),
        ])));

        return $this->send(new FindSubscriber($email));
    }
}
