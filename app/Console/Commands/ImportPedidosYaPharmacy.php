<?php

namespace App\Console\Commands;

use App\Services\PedidosYaPharmacyImportService;
use Illuminate\Console\Command;

class ImportPedidosYaPharmacy extends Command
{
    protected $signature = 'products:import-pedidosya-pharmacy
                            {--cookie= : Raw Cookie header from browser DevTools (required)}
                            {--cookie-file= : Path to a text file containing raw cookie header value}
                            {--curl= : Full "Copy as cURL" command from browser (cookie is extracted automatically)}
                            {--json-file= : Path to JSON payload exported from browser response (menu/sections)}
                            {--har-file= : Path to HAR exported from DevTools (Save all as HAR with content)}
                            {--user=   : User ID to assign as owner of imported products}
                            {--deactivate-missing : Mark products missing from current catalog as inactive}';

    protected $description = 'Import pharmacy products from PedidosYa (Pharmacy Pasaje, Bariloche) into the local catalog.';

    public function handle(PedidosYaPharmacyImportService $service): int
    {
        $jsonFile = trim((string) $this->option('json-file'));
        $harFile = trim((string) $this->option('har-file'));
        $userId  = $this->option('user') ? (int) $this->option('user') : null;
        $deactivateMissing = (bool) $this->option('deactivate-missing');

        if ($harFile !== '') {
            if (!is_file($harFile)) {
                $this->error('--har-file does not exist: ' . $harFile);
                return self::FAILURE;
            }

            $raw = file_get_contents($harFile);
            $payload = json_decode((string) $raw, true);

            if (!is_array($payload)) {
                $this->error('Invalid JSON in --har-file.');
                return self::FAILURE;
            }

            $this->info('Starting PedidosYa Pharmacy import from HAR file...');

            try {
                $result = $service->importFromHarPayload($payload, $userId, $deactivateMissing);
            } catch (\RuntimeException $e) {
                $this->error($e->getMessage());
                return self::FAILURE;
            }

            $this->table(
                ['Created', 'Updated', 'Skipped', 'Deactivated'],
                [[$result['created'], $result['updated'], $result['skipped'], $result['deactivated']]]
            );

            $this->info('Done!');

            return self::SUCCESS;
        }

        if ($jsonFile !== '') {
            if (!is_file($jsonFile)) {
                $this->error('--json-file does not exist: ' . $jsonFile);
                return self::FAILURE;
            }

            $raw = file_get_contents($jsonFile);
            $payload = json_decode((string) $raw, true);

            if (!is_array($payload)) {
                $this->error('Invalid JSON in --json-file.');
                return self::FAILURE;
            }

            $this->info('Starting PedidosYa Pharmacy import from JSON file...');

            try {
                $result = $service->importFromJsonPayload($payload, $userId, $deactivateMissing);
            } catch (\RuntimeException $e) {
                $this->error($e->getMessage());
                return self::FAILURE;
            }

            $this->table(
                ['Created', 'Updated', 'Skipped', 'Deactivated'],
                [[$result['created'], $result['updated'], $result['skipped'], $result['deactivated']]]
            );

            $this->info('Done!');

            return self::SUCCESS;
        }

        $cookie = trim((string) $this->option('cookie'));
        $cookieFile = trim((string) $this->option('cookie-file'));
        $curl = trim((string) $this->option('curl'));

        if ($cookie === '' && $cookieFile !== '') {
            if (!is_file($cookieFile)) {
                $this->error('--cookie-file does not exist: ' . $cookieFile);
                return self::FAILURE;
            }

            $cookie = trim((string) file_get_contents($cookieFile));
        }

        if ($cookie === '' && $curl !== '') {
            $cookie = $this->extractCookieFromCurl($curl);
        }

        if ($cookie === '') {
            $this->error('Cookie is required via --cookie, --cookie-file, or --curl.');
            $this->line('');
            $this->line('How to get your cookie:');
            $this->line('  1. Open https://www.pedidosya.com.ar in Chrome/Firefox and log in.');
            $this->line('  2. Open DevTools → Network tab.');
            $this->line('  3. Reload the pharmacy page.');
            $this->line('  4. Click any request to pedidosya.com.ar.');
            $this->line('  5. Under "Request Headers", find the Cookie line.');
            $this->line('  6. Copy the entire value and paste it after --cookie=');
            $this->line('');
            $this->line('Alternative (recommended):');
            $this->line('  - Right click request → Copy → Copy as cURL');
            $this->line('  - Run with: php artisan products:import-pedidosya-pharmacy --curl="<paste curl>"');
            $this->line('');
            $this->line('Alternative when anti-bot blocks backend requests:');
            $this->line('  - In Network tab, open the menu JSON response and save it as a .json file');
            $this->line('  - Run with: php artisan products:import-pedidosya-pharmacy --json-file="menu.json"');
            $this->line('  - Or export HAR: Save all as HAR with content');
            $this->line('  - Run with: php artisan products:import-pedidosya-pharmacy --har-file="pedidosya.har"');
            $this->line('');
            $this->line('Example:');
            $this->line('  php artisan products:import-pedidosya-pharmacy --cookie="PHPSESSID=abc123; _ga=GA1..."');

            return self::FAILURE;
        }

        $this->info('Starting PedidosYa Pharmacy import...');

        try {
            $result = $service->import($cookie, $userId, $deactivateMissing);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->table(
            ['Created', 'Updated', 'Skipped', 'Deactivated'],
            [[$result['created'], $result['updated'], $result['skipped'], $result['deactivated']]]
        );

        $this->info('Done!');

        return self::SUCCESS;
    }

    private function extractCookieFromCurl(string $curl): string
    {
        if (preg_match('/-H\\s+["\']cookie:\\s*([^"\']+)["\']/i', $curl, $matches) === 1) {
            return trim($matches[1]);
        }

        if (preg_match('/--header\\s+["\']cookie:\\s*([^"\']+)["\']/i', $curl, $matches) === 1) {
            return trim($matches[1]);
        }

        return '';
    }
}
