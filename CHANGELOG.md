# Changelog

All notable changes to `vdocipher` will be documented in this file.

## 1.0.0 - 2025-6-14

- Initial release
- Support for VdoCipher API integration
- Video playback, management, and upload functionality
- Customizable watermarks

## 1.1.0 - 2025-06-14

### Added

- `getVideoAnalytics(videoId, userId, ttl)` for viewer-based analytics with userId binding.
- `importVideoFromUrl(url, folderId, title)` to import videos from public HTTP/FTP URLs.
- `getOfflineOtp(videoId, rentalDuration, extra)` for generating OTPs with license rules for offline playback.

### Improved

- Full test coverage added for new features using Laravel Testbench and HTTP fakes.
- Updated `README.md` with documentation and usage examples for new methods.

### Meta

- Updated `composer.json` with improved metadata and keywords.
