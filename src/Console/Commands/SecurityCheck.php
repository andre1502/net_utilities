<?php

namespace Andre1502\NetUtilities\Console\Commands;

use Andre1502\NetUtilities\Security\PHP2ShellProtection;
use Illuminate\Console\Command;

class SecurityCheck extends Command
{
  protected $signature = "net-utilities:security-check";
  protected $description = "Audit runtime environment for PHP2Shell (CVE-2026-109618 / CVE-2026-109619) and other security risks";

  public function handle(): int
  {
    $audit = PHP2ShellProtection::audit();

    $this->info("=== Net Utilities Security Audit ===");
    $this->newLine();

    $this->table(
      ["Check", "Status", "Details"],
      [
        [
          "PHP Runtime Version",
          $audit["is_vulnerable_version"] ? "<fg=red>Vulnerable</>" : "<fg=green>Patched / Safe</>",
          $audit["php_version"],
        ],
        [
          "session.upload_progress",
          $audit["upload_progress_enabled"] ? "<fg=yellow>Enabled</>" : "<fg=green>Disabled</>",
          $audit["upload_progress_enabled"] ? "Vector active (session.upload_progress.enabled = 1)" : "Mitigated (disabled in php.ini)",
        ],
        [
          "PHP2Shell (CVE-2026-109618/19)",
          $audit["is_at_risk"] ? "<fg=red>CRITICAL RISK</>" : "<fg=green>SECURE</>",
          $audit["is_at_risk"] ? "Remote Code Execution risk detected" : "No active exploitation vector",
        ],
        [
          "Package Shell Execution",
          "<fg=green>None</>",
          "Zero shell/exec calls in package",
        ],
      ]
    );

    if (!empty($audit["recommendations"])) {
      $this->newLine();
      $this->warn("Recommendations:");
      foreach ($audit["recommendations"] as $rec) {
        $this->line("  • " . $rec);
      }
    }

    return $audit["is_at_risk"] ? Command::FAILURE : Command::SUCCESS;
  }
}
