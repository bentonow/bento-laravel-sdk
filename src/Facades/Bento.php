<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Facades;

use Bentonow\BentoLaravel\Requests\CreateBroadcast;
use Bentonow\BentoLaravel\Requests\CreateEvents;
use Bentonow\BentoLaravel\Requests\CreateField;
use Bentonow\BentoLaravel\Requests\CreateSubscriber;
use Bentonow\BentoLaravel\Requests\CreateTag;
use Bentonow\BentoLaravel\Requests\FindSubscriber;
use Bentonow\BentoLaravel\Requests\GeoLocateIp;
use Bentonow\BentoLaravel\Requests\GetBlacklistStatus;
use Bentonow\BentoLaravel\Requests\GetBroadcasts;
use Bentonow\BentoLaravel\Requests\GetContentModeration;
use Bentonow\BentoLaravel\Requests\GetFields;
use Bentonow\BentoLaravel\Requests\GetGender;
use Bentonow\BentoLaravel\Requests\GetReportStats;
use Bentonow\BentoLaravel\Requests\GetSegmentStats;
use Bentonow\BentoLaravel\Requests\GetSiteStats;
use Bentonow\BentoLaravel\Requests\GetTags;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Bentonow\BentoLaravel\Requests\SubscriberCommand;
use Bentonow\BentoLaravel\Requests\ValidateEmail;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\Client\Response trackEvent(\Illuminate\Support\Collection $data)
 * @method static \Illuminate\Http\Client\Response importSubscribers(\Illuminate\Support\Collection $data)
 * @method static \Illuminate\Http\Client\Response findSubscriber(string $email)
 * @method static \Illuminate\Http\Client\Response createSubscriber(\Illuminate\Support\Collection $data)
 * @method static \Illuminate\Http\Client\Response subscriberCommand(\Illuminate\Support\Collection $data)
 * @method static \Illuminate\Http\Client\Response getTags()
 * @method static \Illuminate\Http\Client\Response createTag(\Bentonow\BentoLaravel\DataTransferObjects\CreateTagData $data)
 * @method static \Illuminate\Http\Client\Response getFields()
 * @method static \Illuminate\Http\Client\Response createField(\Bentonow\BentoLaravel\DataTransferObjects\CreateFieldData $data)
 * @method static \Illuminate\Http\Client\Response getBroadcasts()
 * @method static \Illuminate\Http\Client\Response createBroadcast(\Illuminate\Support\Collection $data)
 * @method static \Illuminate\Http\Client\Response getSiteStats()
 * @method static \Illuminate\Http\Client\Response getSegmentStats(\Bentonow\BentoLaravel\DataTransferObjects\SegmentStatsData $data)
 * @method static \Illuminate\Http\Client\Response getReportStats(\Bentonow\BentoLaravel\DataTransferObjects\ReportStatsData $data)
 * @method static \Illuminate\Http\Client\Response getBlacklistStatus(\Bentonow\BentoLaravel\DataTransferObjects\BlacklistStatusData $data)
 * @method static \Illuminate\Http\Client\Response validateEmail(\Bentonow\BentoLaravel\DataTransferObjects\ValidateEmailData $data)
 * @method static \Illuminate\Http\Client\Response getContentModeration(\Bentonow\BentoLaravel\DataTransferObjects\ContentModerationData $data)
 * @method static \Illuminate\Http\Client\Response getGender(\Bentonow\BentoLaravel\DataTransferObjects\GenderData $data)
 * @method static \Illuminate\Http\Client\Response geoLocateIp(\Bentonow\BentoLaravel\DataTransferObjects\GeoLocateIpData $data)
 */
class Bento extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'bento';
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        $requestClass = self::getRequestClass($method);
        $request = new $requestClass(...$args);

        return $instance->send($request);
    }

    /**
     * Get the request class for the given method.
     */
    private static function getRequestClass(string $method): ?string
    {
        $mapping = [
            'trackEvent' => CreateEvents::class,
            'importSubscribers' => ImportSubscribers::class,
            'upsertSubscribers' => ImportSubscribers::class,
            'findSubscriber' => FindSubscriber::class,
            'createSubscriber' => CreateSubscriber::class,
            'subscriberCommand' => SubscriberCommand::class,
            'getTags' => GetTags::class,
            'createTag' => CreateTag::class,
            'getFields' => GetFields::class,
            'createField' => CreateField::class,
            'getBroadcasts' => GetBroadcasts::class,
            'createBroadcast' => CreateBroadcast::class,
            'getSiteStats' => GetSiteStats::class,
            'getSegmentStats' => GetSegmentStats::class,
            'getReportStats' => GetReportStats::class,
            'getBlacklistStatus' => GetBlacklistStatus::class,
            'validateEmail' => ValidateEmail::class,
            'getContentModeration' => GetContentModeration::class,
            'getGender' => GetGender::class,
            'geoLocateIp' => GeoLocateIp::class,
        ];

        return $mapping[$method] ?? null;
    }
}
