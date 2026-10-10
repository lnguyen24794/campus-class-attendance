#!/usr/bin/env bash
# Laravel coverage via Artisan (--coverage --min=100) + HTML in public/coverage.
# https://laravel.com/framework/docs/testing#reporting-test-coverage
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

OUT_DIR="${COVERAGE_DIR:-docs/coverage}"
HTML_DIR="${COVERAGE_HTML_DIR:-$ROOT/public/coverage}"
mkdir -p "$OUT_DIR"

PCOV_SO="${PCOV_SO:-$ROOT/.php/pcov.so}"
PHP_BIN=(php)

if php -m 2>/dev/null | grep -qi '^pcov$'; then
  :
elif [[ -f "$PCOV_SO" ]]; then
  PHP_BIN=(php -d "extension=$PCOV_SO" -d "pcov.enabled=1")
elif php -m 2>/dev/null | grep -qi '^xdebug$'; then
  export XDEBUG_MODE=coverage
else
  echo "ERROR: Need PCOV or Xdebug." >&2
  echo "See https://laravel.com/framework/docs/testing#reporting-test-coverage" >&2
  exit 1
fi

CLOVER="$OUT_DIR/clover.xml"
TEXT="$OUT_DIR/coverage.txt"

# Prefer vendor/bin/phpunit for reliable coverage drivers; same flags as artisan test --coverage.
"${PHP_BIN[@]}" vendor/bin/phpunit \
  --coverage-clover="$CLOVER" \
  --coverage-html="$HTML_DIR" \
  --coverage-text="$TEXT" \
  --colors=never

# Enforce Laravel-style minimum threshold (100%)
"${PHP_BIN[@]}" -r '
$clover = $argv[1];
$htmlDir = $argv[2];
$xml = simplexml_load_file($clover);
if ($xml === false) {
    fwrite(STDERR, "Cannot read clover: {$clover}\n");
    exit(1);
}
$metrics = $xml->project->metrics;
$statements = (int) $metrics["statements"];
$covered = (int) $metrics["coveredstatements"];
$methods = (int) $metrics["methods"];
$coveredMethods = (int) $metrics["coveredmethods"];
$pct = $statements > 0 ? round(100 * $covered / $statements, 2) : 100;
$methodPct = $methods > 0 ? round(100 * $coveredMethods / $methods, 2) : 100;

printf(
    "CCA coverage: lines %d/%d (%.2f%%) · methods %d/%d (%.2f%%)\n",
    $covered, $statements, $pct, $coveredMethods, $methods, $methodPct,
);

file_put_contents(
    dirname($clover) . "/SUMMARY.md",
    "# Coverage summary\n\n"
    . "Generated: " . gmdate("c") . "\n\n"
    . "- Lines: **{$covered}/{$statements}** ({$pct}%)\n"
    . "- Methods: **{$coveredMethods}/{$methods}** ({$methodPct}%)\n"
    . "- **View HTML:** [http://localhost:8000/coverage](http://localhost:8000/coverage)\n"
    . "- Clover: `docs/coverage/clover.xml`\n"
    . "- Text: `docs/coverage/coverage.txt`\n"
    . "- HTML: `public/coverage/` (gitignored)\n"
    . "- Laravel: [Reporting Test Coverage](https://laravel.com/framework/docs/testing#reporting-test-coverage)\n"
    . "- Equivalent CLI: `php artisan test --coverage --min=100`\n",
);

if ($pct < 100.0 || $methodPct < 100.0) {
    fwrite(STDERR, "FAIL: coverage must be 100% (same as artisan test --coverage --min=100).\n");
    exit(2);
}
if (! is_file($htmlDir . "/index.html")) {
    fwrite(STDERR, "FAIL: missing public/coverage/index.html\n");
    exit(2);
}
echo "OK: 100% coverage.\nOpen: http://localhost:8000/coverage\n";
echo "CLI equiv: php artisan test --coverage --min=100\n";
' "$CLOVER" "$HTML_DIR"
