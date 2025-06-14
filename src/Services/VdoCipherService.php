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
        $this->baseUrl = Config::get('vdocipher.base_url', 'https://dev.vdocipher.com');
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
        ])->post("{$this->baseUrl}/otp", $payload);

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
        ])->get("{$this->baseUrl}/videos/{$videoId}/metadata");

        return $response->json();
    }

    /**
     * Get a list of videos.
     *
     * @param array $params Parameters for filtering and pagination
     * @return array
     */
    public function getVideos(array $params = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
        ])->get("{$this->baseUrl}/videos", $params);

        return $response->json();
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

        return $response->json();
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
        ])->delete("{$this->baseUrl}/videos/{$videoId}");

        return $response->successful();
    }

    /**
     * Get credentials for uploading a video.
     *
     * @param string $title The title of the video
     * @param array $options Additional options for the upload
     * @return array
     */
    public function getVideoCredentials(string $title, array $options = []): array
    {
        $payload = array_merge([
            'title' => $title,
        ], $options);

        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/videos/files", $payload);

        return $response->json();
    }

    /**
     * Upload a video file to the API using the provided upload link and form data.
     *
     * @param string $uploadLink The upload URL
     * @param array $formData Form data for the upload
     * @param UploadedFile $file The file to upload
     * @return array
     */
    public function uploadVideoToApi(string $uploadLink, array $formData, UploadedFile $file): array
    {
        $response = Http::attach(
            'file',
            fopen($file->getRealPath(), 'r'),
            $file->getClientOriginalName()
        )->post($uploadLink, $formData);

        return $response->json() ?: ['success' => $response->successful()];
    }

    /**
     * Create a new player.
     *
     * @param array $data Player configuration data
     * @return array
     */
    public function createPlayer(array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/players", $data);

        return $response->json();
    }

    /**
     * List all players.
     *
     * @return array
     */
    public function listPlayers(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
        ])->get("{$this->baseUrl}/players");

        return $response->json();
    }

    /**
     * Update a player.
     *
     * @param string $playerId The ID of the player to update
     * @param array $data Updated player configuration data
     * @return array
     */
    public function updatePlayer(string $playerId, array $data): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->put("{$this->baseUrl}/players/{$playerId}", $data);

        return $response->json();
    }

    /**
     * Get analytics for a video.
     *
     * @param string $videoId The ID of the video
     * @param array $params Parameters for filtering analytics data
     * @return array
     */
    public function getVideoAnalytics(string $videoId, array $params = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Apisecret ' . $this->apiKey,
        ])->get("{$this->baseUrl}/videos/{$videoId}/analytics", $params);

        return $response->json() ?: [];
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
