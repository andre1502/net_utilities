<?php

use Andre1502\NetUtilities\Security\PHP2ShellProtection;
use Illuminate\Support\Facades\Artisan;

test('correctly identifies vulnerable and patched PHP versions for PHP2Shell', function () {
    // EOL versions (< 8.2)
    expect(PHP2ShellProtection::isVulnerableVersion('8.1.28'))->toBeTrue()
        ->and(PHP2ShellProtection::isVulnerableVersion('8.0.30'))->toBeTrue()
        ->and(PHP2ShellProtection::isVulnerableVersion('7.4.33'))->toBeTrue();

    // PHP 8.2 (< 8.2.35 is vulnerable, >= 8.2.35 is safe)
    expect(PHP2ShellProtection::isVulnerableVersion('8.2.34'))->toBeTrue()
        ->and(PHP2ShellProtection::isVulnerableVersion('8.2.35'))->toBeFalse()
        ->and(PHP2ShellProtection::isVulnerableVersion('8.2.36'))->toBeFalse();

    // PHP 8.3 (< 8.3.36 is vulnerable, >= 8.3.36 is safe)
    expect(PHP2ShellProtection::isVulnerableVersion('8.3.35'))->toBeTrue()
        ->and(PHP2ShellProtection::isVulnerableVersion('8.3.36'))->toBeFalse()
        ->and(PHP2ShellProtection::isVulnerableVersion('8.3.37'))->toBeFalse();

    // PHP 8.4 (< 8.4.27 is vulnerable, >= 8.4.27 is safe)
    expect(PHP2ShellProtection::isVulnerableVersion('8.4.26'))->toBeTrue()
        ->and(PHP2ShellProtection::isVulnerableVersion('8.4.27'))->toBeFalse();

    // PHP 8.5 (< 8.5.12 is vulnerable, >= 8.5.12 is safe)
    expect(PHP2ShellProtection::isVulnerableVersion('8.5.11'))->toBeTrue()
        ->and(PHP2ShellProtection::isVulnerableVersion('8.5.12'))->toBeFalse();
});

test('audit returns complete diagnostic structure with recommendations', function () {
    $auditVulnerable = PHP2ShellProtection::audit('8.5.11');

    expect($auditVulnerable)->toBeArray()
        ->and($auditVulnerable['php_version'])->toBe('8.5.11')
        ->and($auditVulnerable['is_vulnerable_version'])->toBeTrue()
        ->and($auditVulnerable['recommendations'])->not()->toBeEmpty();

    $auditPatched = PHP2ShellProtection::audit('8.5.12');
    expect($auditPatched['is_vulnerable_version'])->toBeFalse();
});

test('artisan security check command runs successfully', function () {
    $exitCode = Artisan::call('net-utilities:security-check');
    $output = Artisan::output();

    expect($output)->toContain('Net Utilities Security Audit')
        ->and($output)->toContain('PHP Runtime Version')
        ->and($output)->toContain('session.upload_progress')
        ->and($output)->toContain('PHP2Shell')
        ->and($output)->toContain('Zero shell/exec calls in package');
});
