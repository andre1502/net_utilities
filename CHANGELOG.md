# Changelog

All notable changes to `andre1502/net_utilities` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.4] - 2026-10-24

### Added
- **Modern PHP Support**: Full compatibility with PHP `^8.2`, `^8.3`, `^8.4`, and `^8.5`.
- **Modern Laravel Support**: Full compatibility with Laravel `10.x`, `11.x`, `12.x`, and `13.x`.
- **PHP2Shell Exploit Protection**:
  - Added `Andre1502\NetUtilities\Security\PHP2ShellProtection` to detect vulnerable PHP runtimes (CVE-2026-109618 / CVE-2026-109619) and active `session.upload_progress` vectors.
  - Added automated security boot warning via `PHP2ShellProtection::verifyOrWarn()`.
  - Added Artisan CLI command: `php artisan net-utilities:security-check`.
- **Zero Shell Execution**: Replaced all shell `exec()` calls in `routes/console.php` (`logs:clear`) with pure PHP `Illuminate\Support\Facades\File` operations to eliminate command injection / PHP-to-shell escalation vectors.
- **Testing & Quality Assurance**:
  - Comprehensive test suite built with Pest 3 and Orchestra Testbench (31 package unit/feature tests).
  - Standalone Laravel integration test suite (`standalone-test/`) linking the local package repository directly in a real Laravel application (7 integration tests).
  - Automated standalone test runner script (`scripts/test-standalone.sh`).
  - PHPStan Level 5 static analysis configuration (`phpstan.neon.dist`).
  - Pre-build and pre-publish verification scripts in Composer: `composer test`, `composer test:standalone`, `composer test:all`, and `composer verify`.
- **Command Enhancements**: Added `--force` option and `net-utilities:jwt-key` command alias to `GenerateJWTSecret`.

### Changed
- Upgraded `lcobucci/jwt` to `^5.6.1` and `lcobucci/clock` to `^3.6.0` (supporting PHP 8.4/8.5).
- Upgraded `pestphp/pest` to `^3.8` and `pestphp/pest-plugin-laravel` to `^3.2` to resolve PHP 8.5 reflection deprecations.
- Modernized `Utils::getClientIp()` to support Symfony/Laravel request bags and removed `static $ip` variable to prevent cross-request IP leakage in persistent worker environments (FrankenPHP, Laravel Octane).
- Renamed internal `HttpRequest::init()` to `initHttpRequest()` and removed unused `$logChannel` declaration in `JWT` trait to allow composing both traits in the same class without trait collision.
- Dropped support for End-of-Life PHP 8.0 and 8.1.

### Fixed
- Fixed bug in `GenerateJWTSecret` where new keys were saved to a filename named after the random key string rather than appended to the `.env` file.
- Fixed bug in `HttpRequest::setTimeoutAndRetry()` where `$this->timeout` was mistakenly assigned the values of `$retry` and `$retryIntervalMs`.
- Fixed null pointer access on `$ex->getResponse()?->getStatusCode()` in `HttpRequest` connection error handling.
- Fixed `TypeError` in `DashboardBasicAuth` when `basic_auth.allowed_ip` was empty, null, or a pre-parsed array.
- Fixed `Utils::jsonValidate()` passing encoding flags to depth parameter of `json_decode()`.
- Fixed `Utils::convert()` array offset float type error and added bounds check.
- Fixed `RedisCheckJWT` using unimported root `Cache` facade.
- Fixed return types and PHPDoc annotations across exceptions and traits.

## [0.3.3] - 2024-09-04

### Added
- Added Packagist badge to README.
- Initial public release supporting Laravel 9, 10, and 11 on PHP 8.0–8.3.
- Standardized JWT authentication trait and middleware.
- Centralized HTTP client utilities with retry and logging.
- Dashboard basic authentication and internal API token middleware.
- Automated language translations publication.
