<?php

namespace App\Console\Commands;

use App\Models\ServicePhoto;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteOldServicePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete service photos older than 1 year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneYearAgo = Carbon::now()->subYear();
        
        $oldPhotos = ServicePhoto::where('created_at', '<', $oneYearAgo)->get();
        
        $deletedCount = 0;
        
        foreach ($oldPhotos as $photo) {
            try {
                // Dosyayı storage'dan sil
                if (Storage::disk('public')->exists($photo->resimyol)) {
                    Storage::disk('public')->delete($photo->resimyol);
                }
                
                // Veritabanından sil
                $photo->delete();
                $deletedCount++;
                
            } catch (\Exception $e) {
                $this->error("Fotoğraf silinemedi (ID: {$photo->id}): " . $e->getMessage());
            }
        }
        
        $this->info("$deletedCount adet eski fotoğraf silindi.");
        
        // Boş klasörleri de temizle
        $this->cleanEmptyDirectories();
    }

    private function cleanEmptyDirectories()
    {
        $basePath = 'service_photos';
        $directories = Storage::disk('public')->directories($basePath);
        
        foreach ($directories as $dir) {
            $subDirs = Storage::disk('public')->directories($dir);
            foreach ($subDirs as $subDir) {
                $files = Storage::disk('public')->allFiles($subDir);
                if (empty($files)) {
                    Storage::disk('public')->deleteDirectory($subDir);
                }
            }
            
            // Ana dizin kontrolü
            $remainingFiles = Storage::disk('public')->allFiles($dir);
            if (empty($remainingFiles)) {
                Storage::disk('public')->deleteDirectory($dir);
            }
        }
    }
}
