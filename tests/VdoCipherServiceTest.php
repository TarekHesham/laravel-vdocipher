<?php

namespace ElFarmawy\VdoCipher\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use ElFarmawy\VdoCipher\Facades\VdoCipher;
use ElFarmawy\VdoCipher\Providers\VdoCipherServiceProvider;
use ReflectionClass;

class VdoCipherServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            VdoCipherServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'VdoCipher' => VdoCipher::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Configure the package
        Config::set('vdocipher.api_key', 'test-api-key');
        Config::set('vdocipher.base_url', 'https://dev.vdocipher.com/api');
        Config::set('vdocipher.watermarks', [
            [
                'type'     => 'rtext',
                'text'     => 'Test Watermark',
                'alpha'    => '0.5',
                'color'    => '0xFFFFFF',
                'size'     => '20',
                'interval' => '5000',
            ],
        ]);
    }

    public function testGetOtpReturnsCorrectData()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos/video123/otp' => Http::response([
                'otp' => 'test-otp',
                'playbackInfo' => 'test-playback-info',
            ], 200),
        ]);

        $result = VdoCipher::getOtp('video123');

        Http::assertSent(function ($request) {
            $this->assertEquals('https://dev.vdocipher.com/api/videos/video123/otp', $request->url());
            $this->assertEquals('Apisecret test-api-key', $request->header('Authorization')[0]);

            $body = json_decode($request->body(), true);

            $this->assertArrayHasKey('ttl', $body);
            $this->assertArrayHasKey('annotate', $body);
            $this->assertNotEmpty($body['annotate']);

            return true;
        });

        $this->assertEquals('test-otp', $result['otp']);
        $this->assertEquals('test-playback-info', $result['playbackInfo']);
    }

    public function testGetOtpWithoutWatermarks()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos/video123/otp' => Http::response([
                'otp' => 'test-otp',
                'playbackInfo' => 'test-playback-info',
            ], 200),
        ]);

        $result = VdoCipher::getOtp('video123', ['watermarks' => false]);

        Http::assertSent(function ($request) {
            $this->assertEquals('https://dev.vdocipher.com/api/videos/video123/otp', $request->url());

            $body = json_decode($request->body(), true);

            $this->assertArrayHasKey('ttl', $body);
            $this->assertArrayNotHasKey('watermark', $body);

            return true;
        });

        $this->assertEquals('test-otp', $result['otp']);
    }

    public function testGetOtpWithCustomWatermarks()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos/video123/otp' => Http::response([
                'otp' => 'test-otp',
                'playbackInfo' => 'test-playback-info',
            ], 200),
        ]);

        $customWatermarks = [
            [
                'type'     => 'rtext',
                'text'     => 'Custom Watermark',
                'alpha'    => '0.7',
                'color'    => '0x00FF00',
                'size'     => '25',
                'interval' => '3000',
            ]
        ];

        $result = VdoCipher::getOtp('video123', ['watermarks' => $customWatermarks]);

        Http::assertSent(function ($request) use ($customWatermarks) {
            $this->assertEquals('https://dev.vdocipher.com/api/videos/video123/otp', $request->url());

            $body = json_decode($request->body(), true);

            $this->assertArrayHasKey('ttl', $body);
            $this->assertArrayHasKey('annotate', $body);

            $this->assertEquals(json_encode($customWatermarks), $body['annotate']);

            return true;
        });

        $this->assertEquals('test-otp', $result['otp']);
    }

    public function testGetVideoDetails()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos/video123' => Http::response([
                'id' => 'video123',
                'title' => 'Test Video',
                'status' => 'ready',
            ], 200),
            'https://dev.vdocipher.com/api/videos/video123/otp' => Http::response([
                'otp' => 'test-otp',
                'playbackInfo' => 'test-playback-info',
            ], 200),
        ]);

        $result = VdoCipher::getVideoDetails('video123');

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('video123', $result['id']);
        $this->assertEquals('Test Video', $result['title']);
        $this->assertEquals('ready', $result['status']);
        $this->assertEquals('test-otp', $result['otp']['otp']);
    }


    public function testGetVideos()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos' => Http::response([
                'videos' => [
                    ['id' => 'video123', 'title' => 'Test Video 1'],
                    ['id' => 'video456', 'title' => 'Test Video 2'],
                ],
                'count' => 2,
            ], 200),
        ]);

        $result = VdoCipher::getVideos();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://dev.vdocipher.com/api/videos';
        });

        $this->assertEquals(2, $result['count']);
        $this->assertCount(2, $result['videos']);
    }


    public function testDeleteVideo()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos?videos=video123' => Http::response(null, 204),
        ]);

        $result = VdoCipher::deleteVideo('video123');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://dev.vdocipher.com/api/videos?videos=video123'
                && $request->method() === 'DELETE';
        });

        $this->assertTrue($result);
    }

    public function testGetVideoCredentials()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos?title=New%20Video%20Title' => Http::response([
                'clientPayload' => [
                    "policy"           => "test-policy",
                    "key"              => "test-key",
                    "x-amz-signature"  => "test-signature",
                    "x-amz-algorithm"  => "test-algorithm",
                    "x-amz-date"       => "test-date",
                    "x-amz-credential" => "test-credential",
                    'uploadLink'       => 'upload-link',
                ],
                'videoId' => 'new-video-id',
            ], 200),
        ]);

        $result = VdoCipher::getVideoCredentials('New Video Title');

        $this->assertEquals('new-video-id', $result['videoId']);
        $this->assertEquals('upload-link', $result['clientPayload']['uploadLink']);
    }

    public function testUploadVideoToApi()
    {
        Http::fake([
            'https://upload.vdocipher.com/upload' => Http::response('', 201),
        ]);

        $file = UploadedFile::fake()->create('video.mp4', 1024);
        $uploadLink = 'https://upload.vdocipher.com/upload';
        $formData = [
            'policy' => 'upload-policy',
            'key' => 'video-key',
            'x-amz-signature' => 'signature',
            'x-amz-date' => 'date',
            'x-amz-algorithm' => 'algorithm',
            'x-amz-credential' => 'credential',
            'success_action_status' => 201,
            'success_action_redirect' => '',
        ];

        $result = VdoCipher::uploadVideoToApi($uploadLink, $formData, $file);

        $this->assertTrue($result['success']);
        $this->assertEquals(201, $result['status']);
    }

    public function testSettersWork()
    {
        VdoCipher::setApiKey('new-api-key');
        $service = app('vdocipher');

        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('apiKey');
        $property->setAccessible(true);
        $this->assertEquals('new-api-key', $property->getValue($service));

        VdoCipher::setBaseUrl('https://dev.vdocipher.com/api');
        $property = $reflection->getProperty('baseUrl');
        $property->setAccessible(true);
        $this->assertEquals('https://dev.vdocipher.com/api', $property->getValue($service));

        $newWatermarks = [
            [
                'type'     => 'rtext',
                'text'     => 'New Watermark',
                'alpha'    => '0.3',
                'color'    => '0x00FF00',
                'size'     => '30',
                'interval' => '2000',
            ],
        ];
        VdoCipher::setWatermarks($newWatermarks);
        $property = $reflection->getProperty('watermarks');
        $property->setAccessible(true);
        $this->assertEquals($newWatermarks, $property->getValue($service));
    }

    public function testGetVideoAnalyticsGeneratesOtp()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos/video456/otp' => Http::response([
                'otp' => 'analytics-otp-token',
            ], 200),
        ]);

        $result = VdoCipher::getVideoAnalytics('video456', 'user_test');

        Http::assertSent(function ($request) {
            $this->assertEquals('https://dev.vdocipher.com/api/videos/video456/otp', $request->url());

            $body = json_decode($request->body(), true);
            $this->assertEquals('user_test', $body['userId']);
            $this->assertArrayHasKey('ttl', $body);

            return true;
        });

        $this->assertEquals('analytics-otp-token', $result['otp']);
    }

    public function testImportVideoFromUrlReturnsVideoInfo()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos/importUrl' => Http::response([
                'id'     => '4b02d06e4b374f6085edc4061a0dc3fb',
                'status' => 'Queued',
                'title'  => 'Test Imported Title',
            ], 200),
        ]);

        $result = VdoCipher::importVideoFromUrl('https://example.com/video.mp4', 'root', 'Test Imported Title');

        Http::assertSent(function ($request) {
            $this->assertEquals('https://dev.vdocipher.com/api/videos/importUrl', $request->url());

            $body = json_decode($request->body(), true);
            $this->assertEquals('https://example.com/video.mp4', $body['url']);
            $this->assertEquals('root', $body['folderId']);
            $this->assertEquals('Test Imported Title', $body['title']);

            return true;
        });

        $this->assertEquals('Queued', $result['status']);
        $this->assertEquals('4b02d06e4b374f6085edc4061a0dc3fb', $result['id']);
    }

    public function testGetOfflineOtpWithDefaultRentalDuration()
    {
        Http::fake([
            'https://dev.vdocipher.com/api/videos/video789/otp' => Http::response([
                'otp' => 'offline-otp-token',
            ], 200),
        ]);

        $result = VdoCipher::getOfflineOtp('video789');

        Http::assertSent(function ($request) {
            $this->assertEquals('https://dev.vdocipher.com/api/videos/video789/otp', $request->url());

            $body = json_decode($request->body(), true);
            $rules = json_decode($body['licenseRules'], true);

            $this->assertTrue($rules['canPersist']);
            $this->assertEquals(1296000, $rules['rentalDuration']); // 15 days

            return true;
        });

        $this->assertEquals('offline-otp-token', $result['otp']);
    }
}
