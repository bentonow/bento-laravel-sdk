<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Console;

use Bentonow\BentoLaravel\DataTransferObjects\BlacklistStatusData;
use Bentonow\BentoLaravel\DataTransferObjects\CommandData;
use Bentonow\BentoLaravel\DataTransferObjects\ContactData;
use Bentonow\BentoLaravel\DataTransferObjects\ContentModerationData;
use Bentonow\BentoLaravel\DataTransferObjects\CreateBroadcastData;
use Bentonow\BentoLaravel\DataTransferObjects\CreateFieldData;
use Bentonow\BentoLaravel\DataTransferObjects\CreateSubscriberData;
use Bentonow\BentoLaravel\DataTransferObjects\CreateTagData;
use Bentonow\BentoLaravel\DataTransferObjects\EventData;
use Bentonow\BentoLaravel\DataTransferObjects\GenderData;
use Bentonow\BentoLaravel\DataTransferObjects\GeoLocateIpData;
use Bentonow\BentoLaravel\DataTransferObjects\ImportSubscribersData;
use Bentonow\BentoLaravel\DataTransferObjects\ReportStatsData;
use Bentonow\BentoLaravel\DataTransferObjects\SegmentStatsData;
use Bentonow\BentoLaravel\DataTransferObjects\ValidateEmailData;
use Bentonow\BentoLaravel\Enums\BroadcastType;
use Bentonow\BentoLaravel\Enums\Command;
use Bentonow\BentoLaravel\Facades\Bento;
use Illuminate\Console\Command as BaseCommand;

class ValidateCommand extends BaseCommand
{
    protected $signature = 'bento:validate
        {--email= : Email address to use for subscriber tests (default: test@example.com)}
        {--skip-write : Skip tests that create/modify data (imports, commands, broadcasts, etc.)}';

    protected $description = 'Validate all Bento SDK methods against the live API and display results';

    private array $results = [];

    private string $testEmail;

    public function handle(): int
    {
        $this->testEmail = $this->option('email') ?: 'test@example.com';

        $skipWrite = $this->option('skip-write');

        $this->info("Validating Bento SDK against live API...\n");
        $this->line("Test email: {$this->testEmail}");
        $this->line('Skip write operations: '.($skipWrite ? 'Yes' : 'No'));
        $this->newLine();

        // --- Subscribers ---
        $this->runTest('Find Subscriber', 'GET /fetch/subscribers', function () {
            $response = Bento::findSubscriber($this->testEmail);

            return $this->validateShape($response->json(), ['data' => ['id', 'type', 'attributes']]);
        });

        if (! $skipWrite) {
            $this->runTest('Create Subscriber', 'POST /fetch/subscribers', function () {
                $response = Bento::createSubscriber(new CreateSubscriberData(
                    email: $this->testEmail,
                ));

                return $this->validateShape($response->json(), ['data' => ['id', 'type', 'attributes']]);
            });

            $this->runTest('Import Subscribers', 'POST /batch/subscribers', function () {
                $response = Bento::importSubscribers(collect([
                    new ImportSubscribersData(
                        email: $this->testEmail,
                        firstName: 'SDK',
                        lastName: 'Test',
                        tags: null,
                        removeTags: null,
                        fields: null,
                    ),
                ]));

                return $this->validateBatchResult($response->json());
            });

            $this->runTest('Upsert Subscriber', 'POST+GET (compound)', function () {
                $response = Bento::upsertSubscriber(
                    email: $this->testEmail,
                    firstName: 'SDK',
                    lastName: 'Test',
                );

                return $this->validateShape($response->json(), ['data' => ['id', 'type', 'attributes']]);
            });

            $this->runTest('Subscriber Command (add_tag)', 'POST /fetch/commands', function () {
                $response = Bento::subscriberCommand(collect([
                    new CommandData(
                        command: Command::ADD_TAG,
                        email: $this->testEmail,
                        query: 'sdk_test_tag',
                    ),
                ]));

                return $this->validateBatchResult($response->json());
            });
        }

        // --- Convenience Event Methods ---
        if (! $skipWrite) {
            $this->runTest('Tag Subscriber (event)', 'POST /batch/events', function () {
                $response = Bento::tagSubscriber($this->testEmail, 'sdk_test_tag');

                return $this->validateBatchResult($response->json());
            });

            $this->runTest('Remove Tag (event)', 'POST /batch/events', function () {
                $response = Bento::removeTag($this->testEmail, 'sdk_test_tag');

                return $this->validateBatchResult($response->json());
            });

            $this->runTest('Add Subscriber (event)', 'POST /batch/events', function () {
                $response = Bento::addSubscriber($this->testEmail, ['first_name' => 'SDK']);

                return $this->validateBatchResult($response->json());
            });

            $this->runTest('Remove Subscriber (event)', 'POST /batch/events', function () {
                $response = Bento::removeSubscriber($this->testEmail);

                return $this->validateBatchResult($response->json());
            });

            $this->runTest('Update Fields (event)', 'POST /batch/events', function () {
                $response = Bento::updateFields($this->testEmail, ['sdk_test_field' => 'test']);

                return $this->validateBatchResult($response->json());
            });

            $this->runTest('Track Purchase (event)', 'POST /batch/events', function () {
                $response = Bento::trackPurchase($this->testEmail, [
                    'unique' => ['key' => 'sdk-test-'.time()],
                    'value' => ['amount' => 100, 'currency' => 'USD'],
                    'cart' => [['product_id' => 'sdk-test', 'quantity' => 1, 'price' => 100]],
                ]);

                return $this->validateBatchResult($response->json());
            });

            $this->runTest('Track Custom Event', 'POST /batch/events', function () {
                $response = Bento::track($this->testEmail, '$sdk_validation_test', ['source' => 'cli'], ['step' => 'validate']);

                return $this->validateBatchResult($response->json());
            });

            $this->runTest('Track Event (batch)', 'POST /batch/events', function () {
                $response = Bento::trackEvent(collect([
                    new EventData(
                        type: '$sdk_batch_test',
                        email: $this->testEmail,
                        fields: ['source' => 'cli'],
                        details: ['batch' => true],
                    ),
                ]));

                return $this->validateBatchResult($response->json());
            });
        }

        // --- Tags ---
        $this->runTest('Get Tags', 'GET /fetch/tags', function () {
            $response = Bento::getTags();
            $json = $response->json();

            if (! array_key_exists('data', $json)) {
                return [false, 'Missing "data" key'];
            }

            return [true, 'data[] with '.count($json['data']).' tag(s)'];
        });

        if (! $skipWrite) {
            $this->runTest('Create Tag', 'POST /fetch/tags', function () {
                $response = Bento::createTag(new CreateTagData(
                    name: 'sdk_test_'.time(),
                ));

                return $this->validateShape($response->json(), ['data' => ['id', 'type', 'attributes']]);
            });
        }

        // --- Fields ---
        $this->runTest('Get Fields', 'GET /fetch/fields', function () {
            $response = Bento::getFields();
            $json = $response->json();

            if (! array_key_exists('data', $json)) {
                return [false, 'Missing "data" key'];
            }

            return [true, 'data[] with '.count($json['data']).' field(s)'];
        });

        if (! $skipWrite) {
            $this->runTest('Create Field', 'POST /fetch/fields', function () {
                $response = Bento::createField(new CreateFieldData(
                    key: 'sdk_test_'.time(),
                ));

                return $this->validateShape($response->json(), ['data' => ['id', 'type', 'attributes']]);
            });
        }

        // --- Broadcasts ---
        $this->runTest('Get Broadcasts', 'GET /fetch/broadcasts', function () {
            $response = Bento::getBroadcasts();
            $json = $response->json();

            if (! array_key_exists('data', $json)) {
                return [false, 'Missing "data" key'];
            }

            return [true, 'data[] with '.count($json['data']).' broadcast(s)'];
        });

        if (! $skipWrite) {
            $this->runTest('Create Broadcast', 'POST /batch/broadcasts', function () {
                $response = Bento::createBroadcast(collect([
                    new CreateBroadcastData(
                        name: 'SDK Test '.time(),
                        subject: 'SDK Validation Test',
                        content: '<p>SDK test broadcast</p>',
                        type: BroadcastType::PLAIN,
                        from: new ContactData(
                            emailAddress: $this->testEmail,
                            name: 'SDK Test',
                        ),
                        inclusive_tags: '',
                        exclusive_tags: '',
                        batch_size_per_hour: 10,
                        send_at: now()->addYear()->toIso8601String(),
                        segment_id: '123',
                    ),
                ]));

                return $this->validateBatchResult($response->json());
            });
        }

        // --- Email Templates ---
        $this->runTest('Get Email Template', 'GET /fetch/emails/templates/{id}', function () {
            $response = Bento::getEmailTemplate(1);
            $json = $response->json();

            if (! array_key_exists('data', $json)) {
                return [false, 'Missing "data" key'];
            }

            return [true, 'data with expected shape'];
        });

        // --- Sequences ---
        $this->runTest('Get Sequences', 'GET /fetch/sequences', function () {
            $response = Bento::getSequences();
            $json = $response->json();

            if (! array_key_exists('data', $json)) {
                return [false, 'Missing "data" key'];
            }

            return [true, 'data[] with '.count($json['data']).' sequence(s)'];
        });

        // --- Workflows ---
        $this->runTest('Get Workflows', 'GET /fetch/workflows', function () {
            $response = Bento::getWorkflows();
            $json = $response->json();

            if (! array_key_exists('data', $json)) {
                return [false, 'Missing "data" key'];
            }

            return [true, 'data[] with '.count($json['data']).' workflow(s)'];
        });

        // --- Forms ---
        $this->runTest('Get Form Responses', 'GET /fetch/responses', function () {
            $response = Bento::getFormResponses('test-form');
            $json = $response->json();

            if (! is_array($json)) {
                return [false, 'Response is not an array'];
            }

            if (array_key_exists('data', $json)) {
                return [true, 'data[] with '.count($json['data']).' response(s)'];
            }

            return [true, 'Response with '.count($json).' response(s)'];
        });

        // --- Stats ---
        $this->runTest('Get Site Stats', 'GET /stats/site', function () {
            $response = Bento::getSiteStats();

            return $this->validateShape($response->json(), ['user_count', 'subscriber_count', 'unsubscriber_count']);
        });

        $this->runTest('Get Segment Stats', 'GET /stats/segment', function () {
            $response = Bento::getSegmentStats(new SegmentStatsData(
                segment_id: '123',
            ));

            return $this->validateShape($response->json(), ['user_count', 'subscriber_count', 'unsubscriber_count']);
        });

        $this->runTest('Get Report Stats', 'GET /stats/report', function () {
            $response = Bento::getReportStats(new ReportStatsData(
                report_id: '456',
            ));
            $json = $response->json();

            if (array_key_exists('report_data', $json)) {
                return [true, 'report_data with expected shape'];
            }

            if (is_array($json)) {
                return [true, 'Response received'];
            }

            return [false, 'Unexpected response format'];
        });

        // --- Experimental ---
        $this->runTest('Validate Email', 'POST /experimental/validation', function () {
            $response = Bento::validateEmail(new ValidateEmailData(
                emailAddress: $this->testEmail,
                fullName: null,
                userAgent: null,
                ipAddress: '1.1.1.1',
            ));

            return $this->validateShape($response->json(), ['valid']);
        });

        $this->runTest('Guess Gender', 'POST /experimental/gender', function () {
            $response = Bento::getGender(new GenderData(
                fullName: 'John Doe',
            ));

            return $this->validateShape($response->json(), ['gender', 'confidence']);
        });

        $this->runTest('Content Moderation', 'POST /experimental/content_moderation', function () {
            $response = Bento::getContentModeration(new ContentModerationData(
                content: 'Its just so fluffy!',
            ));

            return $this->validateShape($response->json(), ['valid']);
        });

        $this->runTest('Geolocate IP', 'GET /experimental/geolocation', function () {
            $response = Bento::geoLocateIp(new GeoLocateIpData(
                ipAddress: '1.1.1.1',
            ));

            return $this->validateShape($response->json(), ['ip', 'country_name', 'city_name']);
        });

        $this->runTest('Blacklist Status', 'GET /experimental/blacklist.json', function () {
            $response = Bento::getBlacklistStatus(new BlacklistStatusData(
                domain: null,
                ipAddress: '1.1.1.1',
            ));

            return $this->validateShape($response->json(), ['query', 'description', 'results']);
        });

        // --- Output ---
        $this->newLine();
        $this->table(
            ['#', 'Test', 'Endpoint', 'Status', 'Details'],
            collect($this->results)->map(function ($row, $index) {
                return [
                    $index + 1,
                    $row['test'],
                    $row['endpoint'],
                    $row['passed'] ? '<fg=green>PASS</>' : '<fg=red>FAIL</>',
                    $row['details'],
                ];
            })->toArray(),
        );

        $passed = collect($this->results)->where('passed', true)->count();
        $failed = collect($this->results)->where('passed', false)->count();
        $total = count($this->results);

        $this->newLine();
        if ($failed === 0) {
            $this->info("All {$total} tests passed.");
        } else {
            $this->warn("{$passed}/{$total} passed, {$failed} failed.");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function runTest(string $name, string $endpoint, callable $test): void
    {
        try {
            [$passed, $details] = $test();
            $this->results[] = [
                'test' => $name,
                'endpoint' => $endpoint,
                'passed' => $passed,
                'details' => $details,
            ];
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            if (strlen($message) > 80) {
                $message = substr($message, 0, 77).'...';
            }
            $this->results[] = [
                'test' => $name,
                'endpoint' => $endpoint,
                'passed' => false,
                'details' => class_basename($e).': '.$message,
            ];
        }
    }

    private function validateBatchResult(mixed $json): array
    {
        if (! is_array($json)) {
            return [false, 'Response is not JSON array/object'];
        }

        if (! array_key_exists('results', $json)) {
            return [false, 'Missing key: "results"'];
        }

        $results = $json['results'] ?? 0;
        $failed = $json['failed'] ?? 0;

        if ($failed > 0) {
            $reason = $json['failures'][0]['error'] ?? 'unknown';

            return [false, "failed={$failed} ({$reason})"];
        }

        if ($results === 0) {
            return [false, 'results=0, nothing was processed'];
        }

        return [true, "results={$results}, failed={$failed}"];
    }

    private function validateShape(mixed $json, array $expectedKeys): array
    {
        if (! is_array($json)) {
            return [false, 'Response is not JSON array/object'];
        }

        foreach ($expectedKeys as $key => $value) {
            if (is_int($key)) {
                // Simple key existence check
                if (! array_key_exists($value, $json)) {
                    return [false, "Missing key: \"{$value}\""];
                }
            } else {
                // Nested key check
                if (! array_key_exists($key, $json)) {
                    return [false, "Missing key: \"{$key}\""];
                }
                if (is_array($value) && is_array($json[$key])) {
                    foreach ($value as $nestedKey) {
                        if (! array_key_exists($nestedKey, $json[$key])) {
                            return [false, "Missing key: \"{$key}.{$nestedKey}\""];
                        }
                    }
                }
            }
        }

        return [true, 'Response shape valid'];
    }
}
