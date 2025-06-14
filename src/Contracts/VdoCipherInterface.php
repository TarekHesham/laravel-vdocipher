<?php

namespace ElFarmawy\VdoCipher\Contracts;

use Illuminate\Http\UploadedFile;

interface VdoCipherInterface
{
    /**
     * Get detailed information about a video.
     *
     * @param string $videoId The ID of the video
     * @param array $options Additional options for the request
     * @return array
     */
    public function getVideoDetails(string $videoId, array $options = []): array;

    /**
     * Get OTP for video playback.
     *
     * @param string $videoId The ID of the video
     * @param array $options Additional options for the request
     * @return array
     */
    public function getOtp(string $videoId, array $options = []): array;

    /**
     * Get metadata for a video.
     *
     * @param string $videoId The ID of the video
     * @return array
     */
    public function getMetadata(string $videoId): array;

    /**
     * Get a list of videos.
     *
     * @param array $params Parameters for filtering and pagination
     * @return array
     */
    public function getVideos(array $params = []): array;

    /**
     * Get details of a specific video.
     *
     * @param string $videoId The ID of the video
     * @return array
     */
    public function getVideo(string $videoId): array;

    /**
     * Delete a video.
     *
     * @param string $videoId The ID of the video
     * @return bool
     */
    public function deleteVideo(string $videoId): bool;

    /**
     * Get credentials for uploading a video.
     *
     * @param string $title The title of the video
     * @param array $options Additional options for the upload
     * @return array
     */
    public function getVideoCredentials(string $title, array $options = []): array;

    /**
     * Upload a video file to the API using the provided upload link and form data.
     *
     * @param string $uploadLink The upload URL
     * @param array $formData Form data for the upload
     * @param UploadedFile $file The file to upload
     * @return array
     */
    public function uploadVideoToApi(string $uploadLink, array $formData, UploadedFile $file): array;

    /**
     * Create a new player.
     *
     * @param array $data Player configuration data
     * @return array
     */
    public function createPlayer(array $data): array;

    /**
     * List all players.
     *
     * @return array
     */
    public function listPlayers(): array;

    /**
     * Update a player.
     *
     * @param string $playerId The ID of the player to update
     * @param array $data Updated player configuration data
     * @return array
     */
    public function updatePlayer(string $playerId, array $data): array;

    /**
     * Get analytics for a video.
     *
     * @param string $videoId The ID of the video
     * @param array $params Parameters for filtering analytics data
     * @return array
     */
    public function getVideoAnalytics(string $videoId, array $params = []): array;

    /**
     * Set the API key.
     *
     * @param string $apiKey The API key
     * @return void
     */
    public function setApiKey(string $apiKey): void;

    /**
     * Set the base URL for API requests.
     *
     * @param string $baseUrl The base URL
     * @return void
     */
    public function setBaseUrl(string $baseUrl): void;

    /**
     * Set watermarks to be used for videos.
     *
     * @param array $watermarks Array of watermark definitions
     * @return void
     */
    public function setWatermarks(array $watermarks): void;
}
