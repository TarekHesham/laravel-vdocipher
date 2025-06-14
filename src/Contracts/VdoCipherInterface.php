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
     * Generate OTP for offline video playback (persistent encrypted download).
     *
     * @param string $videoId The ID of the video
     * @param int $rentalDuration Rental duration in seconds (default: 1296000 = 15 days)
     * @param array $extra Optional extra OTP parameters (ttl, userId, watermarks)
     * @return array
     */
    public function getOfflineOtp(string $videoId, int $rentalDuration = 1296000, array $extra = []): array;

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
     * @param string $folderId The ID of the folder to upload the video to
     * @return array
     */
    public function getVideoCredentials(string $title, ?string $folderId = null): array;

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
     * Get analytics for a video.
     *
     * @param string $videoId The ID of the video
     * @param string $userId The ID of the user
     * @param int|null $ttl The TTL for the OTP
     * @return array
     */
    public function getVideoAnalytics(string $videoId, string $userId, ?int $ttl = null): array;

    /**
     * Import a video from an external URL (HTTP, HTTPS or FTP).
     *
     * @param string $url The direct video URL to be imported.
     * @param string|null $folderId The folder ID to store the video (default: "root").
     * @param string|null $title The title to assign to the imported video.
     * @return array
     */
    public function importVideoFromUrl(string $url, ?string $folderId = null, ?string $title = null): array;

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
