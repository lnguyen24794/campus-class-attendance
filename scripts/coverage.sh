#!/usr/bin/env bash
# Generate PHPUnit coverage for app/ and write human + machine reports.
# Requires pcov (preferred) or xdebug.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

OUT_DIR="${COVERAGE_DIR:-docs/coverage}"
mkdir -p "$OUT_DIR"

PCOV_SO="${PCOV_SO:-$ROOT/.php/pcov.so}"
PHP_BIN=(php)
if php -m 2>/dev/null | grep -qi '^pcov$'; then
  :
elif [[ -f "$PCOV_SO" ]]; then
  PHP_BIN=(php -n -d "extension=$PCOV_SO" -d "pcov.enabled=1")
elif php -m 2>/dev/null | grep -qi '^xdebug$'; then
  export XDEBUG_MODE=coverage
else
  echo "ERROR: Need pcov or xdebug. Build pcov into .php/pcov.so or enable the extension." >&2
  exit 1
fi

CLOVER="$OUT_DIR/clover.xml"
HTML_DIR="$OUT_DIR/html"
TEXT="$OUT_DIR/coverage.txt"

"${PHP_BIN[@]}" vendor/bin/phpunit \
  --coverage-clover="$CLOVER" \
  --coverage-html="$HTML_DIR" \
  --coverage-text="$TEXT" \
  --colors=never

# Enforce 100% lines + methods on included source (app/)
"${PHP_BIN[@]}" -r '
$clover = $argv[1];
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

$summary = sprintf(
    "CCA coverage: lines %d/%d (%.2f%%) · methods %d/%d (%.2f%%)\n",
    $covered,
    $statements,
    $pct,
    $coveredMethods,
    $methods,
    $methodPct,
);
echo $summary;
file_put_contents(
    dirname($clover) . "/SUMMARY.md",
    "# Coverage summary\n\n"
    . "Generated: " . gmdate("c") . "\n\n"
    . "- Lines: **{$covered}/{$statements}** ({$pct}%)\n"
    . "- Methods: **{$coveredMethods}/{$methods}** ({$methodPct}%)\n"
    . "- **View HTML:** [http://localhost:8000/coverage](http://localhost:8000/coverage) (sau `composer coverage` + chạy app)\n"
    . "- Clover: `docs/coverage/clover.xml`\n"
    . "- Text: `docs/coverage/coverage.txt`\n"
    . "- HTML file: `docs/coverage/html/index.html`\n"
    . "- Guide: [Laravel News — PCOV coverage](https://laravel-news.com/generate-code-coverage-in-laravel-with-pcov)\n",
);

if ($pct < 100.0 || $methodPct < 100.0) {
    fwrite(STDERR, "FAIL: coverage must be 100% lines and methods.\n");
    exit(2);
}
echo "OK: 100% coverage.\n";
' "$CLOVER"
