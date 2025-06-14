<?php

namespace ElFarmawy\VdoCipher\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getVideoDetails(string $videoId, array $options = [])
 * @method static array getOtp(string $videoId, array $options = [])
 * @method static array getOfflineOtp(string $videoId, int $rentalDuration = 1296000, array $extra = [])
 * @method static array getMetadata(string $videoId)
 * @method static array getVideos(array $params = [])
 * @method static array getVideo(string $videoId)
 * @method static bool deleteVideo(string $videoId)
 * @method static array getVideoCredentials(string $title, ?string $folderId = null)
 * @method static array uploadVideoToApi(string $uploadLink, array $formData, UploadedFile $file)
 * @method static array importVideoFromUrl(string $url, ?string $folderId = null, ?string $title = null)
 * @method static void setApiKey(string $apiKey)
 * @method static void setBaseUrl(string $baseUrl)
 * @method static void setWatermarks(array $watermarks)
 * @method static array getVideoAnalytics(string $videoId, string $userId, ?int $ttl = null)
 * 
 * @see \ElFarmawy\VdoCipher\Services\VdoCipherService
 */
class VdoCipher extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'vdocipher';
    }
}
