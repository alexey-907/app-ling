<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class CleanOldFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаление старых файлов';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = 'public'; // Папка storage/app/public

        $files = Storage::disk('public')->files();

        foreach ($files as $file){
            $lastModified = Storage::disk('public')->lastModified($file);
            $fileTime = Carbon::createFromTimestamp($lastModified);

            if ($fileTime->diffInHours(now()) >= 24) {
                Storage::disk('public')->delete($file);
            }
        }
    }
}
