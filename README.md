# VdoCipher for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ElFarmawy/vdocipher.svg?style=flat-square)](https://packagist.org/packages/ElFarmawy/vdocipher)
[![Total Downloads](https://img.shields.io/packagist/dt/ElFarmawy/vdocipher.svg?style=flat-square)](https://packagist.org/packages/ElFarmawy/vdocipher)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/TarekHesham/laravel-vdocipher/run-tests.yml?branch=main)](https://github.com/TarekHesham/laravel-vdocipher/actions?query=workflow%3Arun-tests+branch%3Amain)

A Laravel package that provides a clean, API-only integration with the VdoCipher video platform. Supports Laravel 10, 11, and 12.

## Installation

You can install the package via Composer:

```bash
composer require ElFarmawy/vdocipher
```

## Publishing the config file

```bash
php artisan vendor:publish --provider="ElFarmawy\VdoCipher\Providers\VdoCipherServiceProvider" --tag=config
```

This will create a `config/vdocipher.php` configuration file in your app where you can modify the settings.

## Configuration

Update your `.env` file with your VdoCipher API key:

```
VDOCIPHER_API_KEY=your-api-key
VDOCIPHER_BASE_URL=https://dev.vdocipher.com/api
VDOCIPHER_OTP_TTL=300
```

You can also configure default watermarks in the `config/vdocipher.php` file:

```php
'watermarks' => [
    [
        'type'     => 'rtext',
        'text'     => 'Copyright © ' . date('Y'),
        'alpha'    => '0.6',
        'color'    => '0xFFFFFF',
        'size'     => '15',
        'interval' => '5000',
    ],
],
```

## Usage

### Basic Example

```php
use ElFarmawy\VdoCipher\Facades\VdoCipher;

// Get OTP for video playback
$otpInfo = VdoCipher::getOtp('video_id', [
    'userId' => 42,
]);

// Get complete video details including OTP
$details = VdoCipher::getVideoDetails('video_id', [
    'userId' => 42,
]);

// List all videos
$videos = VdoCipher::getVideos([
    'page' => 1,
    'limit' => 10,
]);
```

### Overriding Default Configuration at Runtime

You can override the default configuration at runtime:

```php
// Override API key
VdoCipher::setApiKey('new-api-key');

// Override base URL
VdoCipher::setBaseUrl('https://custom-dev.vdocipher.com/api');

// Override default watermarks
VdoCipher::setWatermarks([
    [
        'type'     => 'rtext',
        'text'     => 'MyCustomText',
        'alpha'    => '0.2',
        'color'    => '0x00FF00',
        'size'     => '40',
        'interval' => '100000',
    ],
]);
```

### Managing Videos

```php
// Get a specific video
$video = VdoCipher::getVideo('video_id');

// Delete a video
$deleted = VdoCipher::deleteVideo('video_id');
```

### Video Upload

```php
// Get upload credentials
$credentials = VdoCipher::getVideoCredentials('My Video Title', '7d52*******'); // folderId Optional

// Upload a video file
$uploadLink = $credentials['clientPayload']['uploadLink'];
$formData = $credentials['clientPayload'];
$file = $request->file('video');

// Added custom redirect if needed
// $formData['success_action_status'] = 201;
// $formData['success_action_redirect'] = '';

$uploadResult = VdoCipher::uploadVideoToApi($uploadLink, $formData, $file);
```

---

## Full Documentation

For complete documentation and advanced usage, please check the [Wiki](https://github.com/TarekHesham/laravel-vdocipher/wiki).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email tarekelfarmawy@outlook.com instead of using the issue tracker.

## Credits

- [Tarek Hesham](https://github.com/TarekHesham)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
