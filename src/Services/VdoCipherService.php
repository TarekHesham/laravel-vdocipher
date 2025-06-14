<?php

namespace ElFarmawy\VdoCipher\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use ElFarmawy\VdoCipher\Contracts\VdoCipherInterface;
use Illuminate\Support\Facades\Config;

class VdoCipherService implements VdoCipherInterface
{
    /**
     * @var string
     */
    protected string $apiKey;

    /**
     * @var string
     */
    protected string $baseUrl;

    /**
     * @var array
     */
    protected array $watermarks = [];

    /**
     * @var int
     */
    protected int $otpTtl;

    /**
     * Create a new VdoCipherService instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->apiKey = Config::get('vdocipher.api_key', '');
        $this->baseUrl = Config::get('vdocipher.base_url', 'https://dev.vdocipher.com/api');
        $this->watermarks = Config::get('vdocipher.watermarks', []);
        $this->otpTtl = Config::get('vdocipher.otp_ttl', 300);
    }

    /**
     * Get detailed information about a video.
     *
     * @param string $videoId The ID of the video
     * @param array $options Additional options for the request
     * @return array
     */
    public function getVideoDetails(string $videoId, array $options = []): array
    {
        $otp = $this->getOtp($videoId, $options);
        $video = $this->getVideo($videoId);

        return array_merge($video, ['otp' => $otp]);
    }

    /**
     * Get OTP for video playback.
     *
     * @param string $videoId The ID of the video
     * @param array $options Additional options for the request
     * @return array
     */
    public function getOtp(string $videoId, array $options = []): array
    {
        $payload = [
            'ttl' => $options['ttl'] ?? $this->otpTtl,
        ];

        // Add userId if provided
        if (isset($options['userId'])) {
            $payload['annotate'] = json_encode([
                'userId' => $options['userId']
            ]);
        }

        // Merge watermarks if they are not explicitly disabled
        if (!isset($options['watermarks']) || $options['watermarks'] !== false) {
            $watermarks = $options['watermarks'] ?? $this->watermarks;
            if (!empty($watermarks)) {
                $payload['watermark'] = json_encode($watermarks);
            }
        }

        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/videos/{$videoId}/otp", $payload);

        return $response->json();
    }

    /**
     * Get metadata for a video.
     *
     * @param string $videoId The ID of the video
     * @return array
     */
    public function getMetadata(string $videoId): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
        ])->get("{$this->baseUrl}/meta/{$videoId}");

        return $response->json() ?? [];
    }

    /**
     * Get a list of videos from VdoCipher.
     *
     * This method supports pagination and filtering options via query parameters.
     *
     * Available parameters:
     * - page (int)         : Page number for pagination (e.g. 1, 2, 3).
     * - limit (int)        : Number of videos per page (default: 20, max: 100).
     * - tags (string)      : Comma-separated list of tags to filter by (case-sensitive).
     * - q (string)         : Search query for video ID or title.
     * - folderId (string)  : ID of the folder to list videos from (use "root" for top-level).
     *
     * Example usage:
     * $videos = VdoCipher::getVideos([
     *     'page' => 2,
     *     'limit' => 40,
     *     'tags' => 'Course1,Promo',
     *     'q' => 'Intro',
     *     'folderId' => 'root',
     * ]);
     *
     * @param array $params Optional query parameters for filtering and pagination
     * @return array Response data from VdoCipher API
     */
    public function getVideos(array $params = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
        ])->get("{$this->baseUrl}/videos", $params);

        return $response->json() ?? [];
    }

    /**
     * Get details of a specific video.
     *
     * @param string $videoId The ID of the video
     * @return array
     */
    public function getVideo(string $videoId): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
        ])->get("{$this->baseUrl}/videos/{$videoId}");

        return $response->json() ?? [];
    }

    /**
     * Delete a video.
     *
     * @param string $videoId The ID of the video
     * @return bool
     */
    public function deleteVideo(string $videoId): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
        ])->delete("{$this->baseUrl}/videos?videos={$videoId}");

        return $response->successful() ? true : false;
    }

    /**
     * Get credentials for uploading a video.
     *
     * @param string $title The title of the video
     * @param string $folderId The ID of the folder to upload the video to
     * @return array
     */
    public function getVideoCredentials(string $title, ?string $folderId = null): array
    {
        $query = ['title' => $title];

        if ($folderId) {
            $query['folderId'] = $folderId;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
        ])->withOptions([
            'query' => $query,
        ])->put("{$this->baseUrl}/videos");

        return $response->json() ?? [];
    }

    /**
     * Upload a video file to VdoCipher API.
     *
     * This should be called after obtaining upload credentials using getVideoCredentials().
     *
     * Note: The related videoId is returned in the getVideoCredentials() response
     * and should be stored before calling this method.
     *
     * @param string $uploadLink The URL to which the video will be uploaded
     * @param array $formData The full form data including policy, signature, etc.
     * @param UploadedFile $file The video file to be uploaded
     * @return array Response from VdoCipher (may be empty if upload was successful with 201)
     */
    public function uploadVideoToApi(string $uploadLink, array $formData, UploadedFile $file): array
    {
        unset($formData['uploadLink']);

        $response = Http::asMultipart()
            ->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $file->getClientOriginalName(),
                ['Content-Type' => $file->getMimeType()]
            )
            ->post($uploadLink, $formData);

        return [
            'success' => $response->status() === 201,
            'status' => $response->status(),
            'raw' => $response->body(),
        ];
    }

    /**
     * Set the API key.
     *
     * @param string $apiKey The API key
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Set the base URL for API requests.
     *
     * @param string $baseUrl The base URL
     * @return void
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set watermarks to be used for videos.
     *
     * @param array $watermarks Array of watermark definitions
     * @return void
     */
    public function setWatermarks(array $watermarks): void
    {
        $this->watermarks = $watermarks;
    }
}
