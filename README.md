# Bento Laravel SDK
<img align="right" src="https://app.bentonow.com/brand/logoanim.gif">

> [!TIP]
> Need help? Join our [Discord](https://discord.gg/ssXXFRmt5F) or email jesse@bentonow.com for personalized support.

The Bento Laravel SDK makes it quick and easy to send emails and track events in your Laravel applications. We provide powerful and customizable APIs that can be used out-of-the-box to manage subscribers, track events, and send transactional emails. We also expose low-level APIs so that you can build fully custom experiences.

Get started with our [📚 integration guides](https://docs.bentonow.com), or [📘 browse the SDK reference](https://docs.bentonow.com/subscribers).

🐶 Battle-tested by [High Performance SQLite](https://highperformancesqlite.com/) (a Bento customer)!

❤️ Thank you [@aarondfrancis](https://github.com/aarondfrancis) for your contribution.

❤️ Thank you [@ziptied](https://github.com/ziptied) for your contribution.

[![Tests](https://github.com/bentonow/bento-laravel-sdk/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/bentonow/bento-laravel-sdk/actions/workflows/tests.yml)

Table of contents
=================

<!--ts-->
* [Features](#features)
* [Requirements](#requirements)
* [Getting started](#getting-started)
    * [Installation](#installation)
    * [Configuration](#configuration)
* [Modules](#modules)
* [Things to Know](#things-to-know)
* [Contributing](#contributing)
* [License](#license)
<!--te-->

## Features

* **Laravel Mail Integration**: Seamlessly integrate with Laravel's mail system to send transactional emails via Bento.
* **Event Tracking**: Easily track custom events and user behavior in your Laravel application.
* **Subscriber Management**: Import and manage subscribers directly from your Laravel app.
* **API Access**: Full access to Bento's REST API for advanced operations.
* **Laravel-friendly**: Designed to work smoothly with Laravel's conventions and best practices.


## Requirements

- PHP 8.0+
- Laravel 10.0+
- Bento API Keys

## Getting started

### Installation

Install the package via Composer:

```bash
composer require bentonow/bento-laravel-sdk
```

### Configuration

1. Publish the configuration file:

```bash
php artisan vendor:publish --tag bentonow
```

2. Add a new mailer definition in `config/mail.php`:

```php
'bento' => [
  'transport' => 'bento',
],
```

3. Add your Bento API keys to your `.env` file:

```dotenv
BENTO_PUBLISHABLE_KEY="bento-publishable-key"
BENTO_SECRET_KEY="bento-secret-key"
BENTO_SITE_UUID="bento-site-uuid"
MAIL_MAILER="bento"
```

## Modules

### Event Tracking

Track custom events in your application:

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\CreateEvents;
use Bentonow\BentoLaravel\DataTransferObjects\EventData;

$bento = new BentoConnector();
$data = collect([
  new EventData(
    type: "$completed_onboarding",
    email: "user@example.com",
    fields: [
      "first_name" => "John",
      "last_name" => "Doe"
    ]
  )
]);
$request = new CreateEvents($data);
$response = $bento->send($request);
```

### Subscriber Management

Import subscribers into your Bento account:

```php
use Bentonow\BentoLaravel\DataTransferObjects\ImportSubscribersData;
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;

$bento = new BentoConnector();
$data = collect([
  new ImportSubscribersData(
    email: "user@example.com",
    first_name: "John",
    last_name: "Doe",
    tags: ["lead", "mql"],
    removeTags: ["customers"],
    fields: ["role" => "ceo"]
  ),
]);
$request = new ImportSubscribers($data);
$response = $bento->send($request);
```


### Find Subscriber

Search your site for a subscriber

```php
  use Bentonow\BentoLaravel\BentoConnector;
  use Bentonow\BentoLaravel\DataTransferObjects\CreateSubscriberData;
  use Bentonow\BentoLaravel\Requests\FindSubscriber;

  $bento = new BentoConnector();

  $data = "test@example.com";
  $request = new FindSubscriber($data);
  $response = $bento->send($request);
  return $response->json();
```

### Create Subscriber

Creates a subscriber in your account and queues them for indexing.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\CreateSubscriberData;
use Bentonow\BentoLaravel\Requests\CreateSubscriber;

$bento = new BentoConnector();

$data = collect([
  new CreateSubscriberData(email: "test@example.com")
]);
$request = new CreateSubscriber($data);
$response = $bento->send($request);
return $response->json();
```


### Run Command

Endpoint to execute a command and change a subscriber's data.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\CommandData;
use Bentonow\BentoLaravel\Requests\SubscriberCommand;
use Bentonow\BentoLaravel\Enums\Command;

$bento = new BentoConnector();
$data = collect([
  new CommandData(Command::REMOVE_TAG, "test@gmail.com", "test")
]);
$request = new SubscriberCommand($data);
$response = $bento->send($request);
return $response->json();
```


### Get Tags

Returns a list of tags in your account.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetTags;

$bento = new BentoConnector();

$request = new GetTags();

$response = $bento->send($request);
return $response->json();
```

### Create Tag

Creates a custom tag in your account.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\CreateTag;
use Bentonow\BentoLaravel\DataTransferObjects\CreateTagData;

$bento = new BentoConnector();

$data = new CreateTagData(name: "example tag");

$request = new CreateTag($data);

$response = $bento->send($request);
return $response->json();
```

### Get Fields

The field model is a simple named key value pair, think of it as a form field.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetFields;

$bento = new BentoConnector();

$request = new GetFields();

$response = $bento->send($request);
return $response->json();
```

### Create Field

Creates a custom field in your account.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\CreateField;
use Bentonow\BentoLaravel\DataTransferObjects\CreateFieldData;

$bento = new BentoConnector();

$data = new CreateFieldData(key: "last_name");

$request = new CreateField($data);

$response = $bento->send($request);
return $response->json();
```

### Get Broadcasts

Returns a list of broadcasts in your account.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetBroadcasts;

$bento = new BentoConnector();
$request = new GetBroadcasts();
$response = $bento->send($request);
return $response->json();
```

### Create Broadcasts

Create new broadcasts to be sent.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\CreateBroadcastData;
use Bentonow\BentoLaravel\DataTransferObjects\ContactData;
use Bentonow\BentoLaravel\Requests\CreateBroadcast;
use Bentonow\BentoLaravel\Enums\BroadcastType;

$bento = new BentoConnector();
$data = Collect([
  new CreateBroadcastData(
    name: "Campaign #1 Example",
    subject: "Hello world Plain Text",
    content: "<p>Hi {{ visitor.first_name }}</p>",
    type: BroadcastType::PLAIN,
    from: new ContactData(
      name: "John Doe",
      emailAddress: "example@example.com"
    ),
    inclusive_tags: "lead,mql",
    exclusive_tags: "customers",
    segment_id: "segment_123456789",
    batch_size_per_hour: 1500
  ),
]);

$request = new CreateBroadcast($data);

$response = $bento->send($request);
return $response->json();
```

### Get Site Stats

Returns a list of site stats.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetSiteStats;

$bento = new BentoConnector();

$request = new GetSiteStats();

$response = $bento->send($request);
return $response->json();
```

### Get Segment Stats

Returns a list of a segments stats.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetSegmentStats;
use Bentonow\BentoLaravel\DataTransferObjects\SegmentStatsData;

$bento = new BentoConnector();

$data = new SegmentStatsData(segment_id: "123");

$request = new GetSegmentStats($data);

$response = $bento->send($request);
return $response->json();
```

### Get Report Stats

Returns an object containing data for a specific report.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetReportStats;
use Bentonow\BentoLaravel\DataTransferObjects\ReportStatsData;

$bento = new BentoConnector();

$data = new ReportStatsData(report_id: "456");

$request = new GetReportStats($data);

$response = $bento->send($request);
return $response->json();

```

### Search Blacklists

Validates the IP or domain name with industry email reputation services to check for delivery issues.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\BlacklistStatusData;
use Bentonow\BentoLaravel\Requests\GetBlacklistStatus;

$bento = new BentoConnector();
$data = new BlacklistStatusData(domain: null, ipAddress: "1.1.1.1");
$request = new GetBlacklistStatus($data);
$response = $bento->send($request);
return $response->json();
```

### Validate Email

Validates the email address using the provided information to infer its validity.

```php
$bento = new BentoConnector();
$data = new ValidateEmailData(
  emailAddress: "test@example.com",
  fullName: "John Snow",
  userAgent: null,
  ipAddress: null
);

$request = new ValidateEmail($data);
$response = $bento->send($request);
return $response->json();

```

### Moderate Content

An opinionated Content moderation.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\ContentModerationData;
use Bentonow\BentoLaravel\Requests\GetContentModeration;

$bento = new BentoConnector();
$data = new ContentModerationData(
  "Its just so fluffy!"
);
$request = new GetContentModeration($data);
$response = $bento->send($request);
return $response->json();
```

### Guess Gender

Guess a subscriber's gender using their first and last name. Best for US users; based on US Census Data.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\GenderData;
use Bentonow\BentoLaravel\Requests\GetGender;

$bento = new BentoConnector();
$data = new GenderData("John Doe");
$request = new GetGender($data);
$response = $bento->send($request);
return $response->json();
```

### Geolocate Ip Address

This endpoint attempts to geolocate the provided IP address.

```php
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\GeoLocateIpData;
use Bentonow\BentoLaravel\Requests\GeoLocateIp;

$bento = new BentoConnector();
$data = new GeoLocateIpData("1.1.1.1");
$request = new GeoLocateIp($data);
$response = $bento->send($request);
return $response->json();
```


## Things to Know

1. The SDK integrates seamlessly with Laravel's mail system for sending transactional emails.
2. For event tracking and data importing, use the BentoConnector class.
3. All API requests are made using strongly-typed request classes for better type safety.
4. The SDK supports Laravel's environment-based configuration for easy setup across different environments.
5. For more advanced usage, refer to the [Bento API Documentation](https://docs.bentonow.com).

## Contributing

We welcome contributions! Please see our [contributing guidelines](CODE_OF_CONDUCT.md) for details on how to submit pull requests, report issues, and suggest improvements.

## License

The Bento SDK for Laravel is available as open source under the terms of the [MIT License](LICENSE.md).
