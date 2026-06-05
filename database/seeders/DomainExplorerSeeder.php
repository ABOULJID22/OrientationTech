<?php

namespace Database\Seeders;

use App\Services\Domains\DomainCsvImportService;
use Illuminate\Database\Seeder;

class DomainExplorerSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/orientationtech_domaines_enrichis.csv');
        $count = app(DomainCsvImportService::class)->import($path);

        $this->command?->info("Domaines importes: {$count}");
    }
}
