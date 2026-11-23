<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\Che168\PhotoDownloadService;
use Illuminate\Console\Command;

class Che168DownloadPhotosCommand extends Command
{
    protected $signature = 'che168:download-photos
        {infoid? : Vehicle ID}
        {--pending=10 : Listings with photos missing file_path}
        {--preview : Also save preview webp}';

    protected $description = 'Download original JPEG (and optional preview) to storage/app';

    public function handle(PhotoDownloadService $downloader): int
    {
        $infoid = $this->argument('infoid');

        if ($infoid !== null) {
            $n = $downloader->downloadForInfoid((int) $infoid, true, (bool) $this->option('preview'));
            $this->info("Downloaded files: {$n}");

            return $n > 0 ? self::SUCCESS : self::FAILURE;
        }

        $listings = Listing::query()
            ->whereHas('photos', fn ($q) => $q->whereNull('file_path'))
            ->limit((int) $this->option('pending'))
            ->pluck('infoid');

        $total = 0;
        foreach ($listings as $id) {
            $total += $downloader->downloadForInfoid((int) $id, true, (bool) $this->option('preview'));
            $this->line("  {$id}: ok");
        }

        $this->info("Total files: {$total}");

        return self::SUCCESS;
    }
}