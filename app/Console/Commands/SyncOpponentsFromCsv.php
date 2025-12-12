<?php

namespace App\Console\Commands;

use App\Models\Opponent;
use Illuminate\Console\Command;

class SyncOpponentsFromCsv extends Command
{
    protected $signature = 'opponents:sync-csv';

    protected $description = 'Sync opponents from a CSV file, updating existing opponents in the database';

    public function handle()
    {
        $path = storage_path('app/private/opponents.csv');

        if (! file_exists($path)) {
            $this->error("CSV file not found at: {$path}");
            return self::FAILURE;
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->error("Unable to open CSV file: {$path}");
            return self::FAILURE;
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (! $headers) {
            $this->error('CSV file is empty or invalid');
            fclose($handle);
            return self::FAILURE;
        }

        $updated = 0;
        $skipped = 0;
        $row = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            if (empty($data[0])) {
                continue;
            }

            // Map CSV data to associative array
            $record = array_combine($headers, $data);
            $id = (int) $record['id'];

            $opponent = Opponent::find($id);
            if (! $opponent) {
                $this->warn("Opponent with ID {$id} not found (row {$row}), skipping...");
                $skipped++;
                continue;
            }

            // Update all fillable fields from CSV
            $opponent->update([
                'name' => $record['name'],
                'real_name' => $record['real_name'],
                'location' => $record['location'],
                'address' => $record['address'],
                'website' => $record['website'],
                'logo' => $record['logo'],
                'latitude' => $record['latitude'],
                'longitude' => $record['longitude'],
                'kit_reference' => $record['kit_reference'],
            ]);

            $updated++;
            $this->line("Updated opponent: {$opponent->name} (ID: {$id})");
        }

        fclose($handle);

        $this->info("Sync complete!");
        $this->info("Updated: {$updated} opponents");
        if ($skipped > 0) {
            $this->warn("Skipped: {$skipped} opponents (not found in database)");
        }

        return self::SUCCESS;
    }
}
