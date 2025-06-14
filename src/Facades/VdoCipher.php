<?php

namespace ElFarmawy\VdoCipher\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getVideoDetails(string $videoId, array $options = [])
 * @method static array getOtp(string $videoId, array $options = [])
 * @method static array getMetadata(string $videoId)
 * @method static array getVideos(array $params = [])
 * @method static array getVideo(string $videoId)
 * @method static bool deleteVideo(string $videoId)
 * @method static array getVideoCredentials(string $title, array $options = [])
 * @method static array uploadVideoToApi(string $uploadLink, array $formData, \Illuminate\Http\UploadedFile $file)
 * @method static array createPlayer(array $data)
 * @method static array listPlayers()
 * @method static array updatePlayer(string $playerId, array $data)
 * @method static array getVideoAnalytics(string $videoId, array $params = [])
 * @method static void setApiKey(string $apiKey)
 * @method static void setBaseUrl(string $baseUrl)
 * @method static void setWatermarks(array $watermarks)
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
