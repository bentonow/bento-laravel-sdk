<?php

declare(strict_types=1);

use Bentonow\BentoLaravel\DataTransferObjects\ContactData;
use Bentonow\BentoLaravel\DataTransferObjects\CreateBroadcastData;
use Bentonow\BentoLaravel\Enums\BroadcastType;
use Bentonow\BentoLaravel\Requests\CreateBroadcast;

it('removes array keys from broadcasts collection when serializing', function () {
    $data = collect([
        5 => new CreateBroadcastData(
            name: 'Broadcast 1',
            subject: 'Subject 1',
            content: 'Content 1',
            type: BroadcastType::PLAIN,
            from: new ContactData('sender1@example.com', 'Sender One'),
            inclusive_tags: 'tag1',
            exclusive_tags: '',
            segment_id: '',
            batch_size_per_hour: 1000,
            send_at: '2024-12-31T12:00:00Z',
        ),
        10 => new CreateBroadcastData(
            name: 'Broadcast 2',
            subject: 'Subject 2',
            content: 'Content 2',
            type: BroadcastType::PLAIN,
            from: new ContactData('sender2@example.com', 'Sender Two'),
            inclusive_tags: 'tag2',
            exclusive_tags: '',
            segment_id: '',
            batch_size_per_hour: 2000,
            send_at: '2024-12-31T13:00:00Z',
        ),
        15 => new CreateBroadcastData(
            name: 'Broadcast 3',
            subject: 'Subject 3',
            content: 'Content 3',
            type: BroadcastType::PLAIN,
            from: new ContactData('sender3@example.com', 'Sender Three'),
            inclusive_tags: 'tag3',
            exclusive_tags: '',
            segment_id: '',
            batch_size_per_hour: 3000,
            send_at: '2024-12-31T14:00:00Z',
        ),
    ]);

    $request = new CreateBroadcast($data);
    $body = $request->body()->all();

    $broadcasts = $body['broadcasts']->all();

    expect($broadcasts)->toBeArray()
        ->and($broadcasts)->toHaveCount(3)
        ->and(array_keys($broadcasts))->toBe([0, 1, 2])
        ->and($broadcasts[0]['name'])->toBe('Broadcast 1')
        ->and($broadcasts[1]['name'])->toBe('Broadcast 2')
        ->and($broadcasts[2]['name'])->toBe('Broadcast 3');
});
