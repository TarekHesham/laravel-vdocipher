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
        Config::set('vdocipher.base_url', 'https://dev.vdocipher.com');
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
            'https://dev.vdocipher.com/otp' => Http::response([
                'otp' => 'test-otp',
                'playbackInfo' => 'test-playback-info',
            ], 200),
        ]);

        $result = VdoCipher::getOtp('video123');

        Http::assertSent(function ($request) {
            return $request->url() == 'https://dev.vdocipher.com/otp'
                && $request->header('Authorization')[0] == 'Apisecret test-api-key'
                && $request['watermark'] != null;
        });

        $this->assertEquals('test-otp', $result['otp']);
        $this->assertEquals('test-playback-info', $result['playbackInfo']);
    }

    public function testGetOtpWithoutWatermarks()
    {
        Http::fake([
            'https://dev.vdocipher.com/otp' => Http::response([
                'otp' => 'test-otp',
                'playbackInfo' => 'test-playback-info',
            ], 200),
        ]);

        $result = VdoCipher::getOtp('video123', ['watermarks' => false]);

        Http::assertSent(function ($request) {
            return $request->url() == 'https://dev.vdocipher.com/otp'
                && !isset($request['watermark']);
        });

        $this->assertEquals('test-otp', $result['otp']);
    }

    public function testGetOtpWithCustomWatermarks()
    {
        Http::fake([
            'https://dev.vdocipher.com/otp' => Http::response([
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
            return $request->url() == 'https://dev.vdocipher.com/otp'
                && $request['watermark'] == json_encode($customWatermarks);
        });

        $this->assertEquals('test-otp', $result['otp']);
    }

    public function testGetVideoDetails()
    {
        Http::fake([
            'https://dev.vdocipher.com/videos/video123' => Http::response([
                'id' => 'video123',
                'title' => 'Test Video',
                'status' => 'ready',
            ], 200),
            'https://dev.vdocipher.com/otp' => Http::response([
                'otp' => 'test-otp',
                'playbackInfo' => 'test-playback-info',
            ], 200),
        ]);

        $result = VdoCipher::getVideoDetails('video123');

        $this->assertEquals('video123', $result['id']);
        $this->assertEquals('Test Video', $result['title']);
        $this->assertEquals('ready', $result['status']);
        $this->assertEquals('test-otp', $result['otp']['otp']);
    }

    public function testGetVideos()
    {
        Http::fake([
            'https://dev.vdocipher.com/videos' => Http::response([
                'videos' => [
                    [
                        'id' => 'video123',
                        'title' => 'Test Video 1',
                    ],
                    [
                        'id' => 'video456',
                        'title' => 'Test Video 2',
                    ],
                ],
                'count' => 2,
            ], 200),
        ]);

        $result = VdoCipher::getVideos();

        Http::assertSent(function ($request) {
            return $request->url() == 'https://dev.vdocipher.com/videos';
        });

        $this->assertEquals(2, $result['count']);
        $this->assertCount(2, $result['videos']);
    }

    public function testDeleteVideo()
    {
        Http::fake([
            'https://dev.vdocipher.com/videos/video123' => Http::response(null, 204),
        ]);

        $result = VdoCipher::deleteVideo('video123');

        Http::assertSent(function ($request) {
            return $request->url() == 'https://dev.vdocipher.com/videos/video123'
                && $request->method() == 'DELETE';
        });

        $this->assertTrue($result);
    }

    public function testGetVideoCredentials()
    {
        Http::fake([
            'https://dev.vdocipher.com/videos/files' => Http::response([
                'clientPayload' => [
                    'token' => 'upload-token',
                    'uploadLink' => 'https://upload.vdocipher.com/upload',
                ],
                'videoId' => 'new-video-id',
            ], 200),
        ]);

        $result = VdoCipher::getVideoCredentials('New Video Title', ['description' => 'Test description']);

        Http::assertSent(function ($request) {
            return $request->url() == 'https://dev.vdocipher.com/videos/files'
                && $request['title'] == 'New Video Title'
                && $request['description'] == 'Test description';
        });

        $this->assertEquals('new-video-id', $result['videoId']);
        $this->assertEquals('upload-token', $result['clientPayload']['token']);
    }

    public function testUploadVideoToApi()
    {
        Http::fake([
            'https://upload.vdocipher.com/upload' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $file = UploadedFile::fake()->create('video.mp4', 1024);
        $uploadLink = 'https://upload.vdocipher.com/upload';
        $formData = [
            'token' => 'upload-token',
            'policy' => 'upload-policy',
        ];

        $result = VdoCipher::uploadVideoToApi($uploadLink, $formData, $file);

        Http::assertSent(function ($request) use ($uploadLink, $file, $formData) {
            if ($request->url() !== $uploadLink) {
                return false;
            }

            $body = $request->body();
            if (strpos($body, $file->getClientOriginalName()) === false) {
                return false;
            }

            foreach ($formData as $key => $value) {
                if (strpos($body, $key) === false || strpos($body, $value) === false) {
                    return false;
                }
            }

            return true;
        });

        $this->assertTrue($result['success']);
    }

    public function testCreatePlayer()
    {
        Http::fake([
            'https://dev.vdocipher.com/players' => Http::response([
                'id' => 'player123',
                'name' => 'Test Player',
            ], 200),
        ]);

        $playerData = [
            'name' => 'Test Player',
            'controls' => true,
            'autoplay' => false,
        ];

        $result = VdoCipher::createPlayer($playerData);

        Http::assertSent(function ($request) use ($playerData) {
            return $request->url() == 'https://dev.vdocipher.com/players'
                && $request['name'] == $playerData['name']
                && $request['controls'] == $playerData['controls']
                && $request['autoplay'] == $playerData['autoplay'];
        });

        $this->assertEquals('player123', $result['id']);
        $this->assertEquals('Test Player', $result['name']);
    }

    public function testListPlayers()
    {
        Http::fake([
            'https://dev.vdocipher.com/players' => Http::response([
                'players' => [
                    [
                        'id' => 'player123',
                        'name' => 'Test Player 1',
                    ],
                    [
                        'id' => 'player456',
                        'name' => 'Test Player 2',
                    ],
                ],
                'count' => 2,
            ], 200),
        ]);

        $result = VdoCipher::listPlayers();

        Http::assertSent(function ($request) {
            return $request->url() == 'https://dev.vdocipher.com/players';
        });

        $this->assertEquals(2, $result['count']);
        $this->assertCount(2, $result['players']);
    }

    public function testUpdatePlayer()
    {
        Http::fake([
            'https://dev.vdocipher.com/players/player123' => Http::response([
                'id' => 'player123',
                'name' => 'Updated Player',
                'controls' => false,
            ], 200),
        ]);

        $playerData = [
            'name' => 'Updated Player',
            'controls' => false,
        ];

        $result = VdoCipher::updatePlayer('player123', $playerData);

        Http::assertSent(function ($request) use ($playerData) {
            return $request->url() == 'https://dev.vdocipher.com/players/player123'
                && $request->method() == 'PUT'
                && $request['name'] == $playerData['name']
                && $request['controls'] == $playerData['controls'];
        });

        $this->assertEquals('player123', $result['id']);
        $this->assertEquals('Updated Player', $result['name']);
        $this->assertFalse($result['controls']);
    }

    public function testGetVideoAnalytics()
    {
        Http::fake([
            'https://dev.vdocipher.com/videos/video123/analytics*' => Http::response([
                'totalViews' => 100,
                'averagePlaytime' => 45,
                'viewsByDay' => [
                    ['date' => '2025-05-29', 'views' => 25],
                    ['date' => '2025-05-30', 'views' => 75],
                ],
            ], 200),
        ]);

        $params = [
            'startDate' => '2025-05-29',
            'endDate' => '2025-05-30',
        ];

        VdoCipher::setBaseUrl('https://dev.vdocipher.com');
        Config::set('vdocipher.base_url', 'https://dev.vdocipher.com');
        $result = VdoCipher::getVideoAnalytics('video123', $params);

        Http::assertSent(function ($request) use ($params) {
            if (!str_starts_with($request->url(), 'https://dev.vdocipher.com/videos/video123/analytics')) {
                return false;
            }

            parse_str(parse_url($request->url(), PHP_URL_QUERY), $query);

            return isset($query['startDate'], $query['endDate'])
                && $query['startDate'] === $params['startDate']
                && $query['endDate'] === $params['endDate'];
        });

        $this->assertEquals(100, $result['totalViews']);
        $this->assertEquals(45, $result['averagePlaytime']);
        $this->assertCount(2, $result['viewsByDay']);
    }

    public function testSettersWork()
    {
        VdoCipher::setApiKey('new-api-key');
        $service = app('vdocipher');

        $reflection = new ReflectionClass($service);
        $property = $reflection->getProperty('apiKey');
        $property->setAccessible(true);
        $this->assertEquals('new-api-key', $property->getValue($service));

        VdoCipher::setBaseUrl('https://dev.vdocipher.com');
        $property = $reflection->getProperty('baseUrl');
        $property->setAccessible(true);
        $this->assertEquals('https://dev.vdocipher.com', $property->getValue($service));

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
}
