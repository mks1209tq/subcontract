<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Cert;
use Illuminate\Support\Facades\File;

class ProcessCertFiles extends Command
{
    protected $signature = 'app:cert';
    protected $description = 'Process certificate files and add file names to Cert model';

    
    
    public function handle()
    {
        $certsPath = storage_path('app/certs');
        $this->info("Checking directory: $certsPath");

        if (!File::isDirectory($certsPath)) {
            $this->error("The 'certs' directory does not exist!");
            return;
        }

        $files = File::files($certsPath);

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            $projectId = $this->extractProjectId($fileName);
            $orderId = $this->extractOrderId($fileName);
            $pcId = $this->extractPcId($fileName);

            Cert::firstOrCreate(['file_name' => $fileName,
            'project_id' => $projectId,
            'order_id' => $orderId,
            'pc_id' => $pcId
        ]);

            $this->info("Processed: $fileName");
        }

        $this->info('All files processed successfully.');

        // Call updateLatestPCs() after processing all files
        $this->updateLatestPCs();
    }

    private function extractProjectId($fileName)
    {
        // Regex pattern to match PIE20201006 at the start of the filename
        $pattern = '/^(PIE\d{8})/';
        
        if (preg_match($pattern, $fileName, $matches)) {
            return $matches[1];
        }

        return null; // Return null if no match found
    }

    private function extractOrderId($fileName)
{
    // Regex pattern to match "ORDER" followed by a space and then digits
    $pattern = '/ORDER\s+(\d+(-\d+)?)/i';
    
    if (preg_match($pattern, $fileName, $matches)) {
        return $matches[1]; // Return the matched order ID
    }

    return null; // Return null if no match found
}

private function extractPcId($fileName)
{
    // Regex pattern to match PC- followed by digits
    $pattern = '/(PC-\d+)/';
    
    if (preg_match($pattern, $fileName, $matches)) {
        return $matches[1]; // Return the matched PC ID
    }

    return null; // Return null if no match found
}

private function updateLatestPCs()
{
    $projects = Cert::select('project_id')->distinct()->pluck('project_id');

    foreach ($projects as $projectId) {
        $orders = Cert::where('project_id', $projectId)
            ->select('order_id')
            ->distinct()
            ->pluck('order_id');

        foreach ($orders as $orderId) {
            // Find all records for this project and order
            $certs = Cert::where('project_id', $projectId)
                ->where('order_id', $orderId)
                ->get();

            if ($certs->isNotEmpty()) {
                // Find the latest PC
                $latestPC = $certs->sortByDesc(function ($cert) {
                    return $cert->pc_id ? intval(substr($cert->pc_id, 3)) : -1;
                })->first();

                // dd($latestPC);

                // Update all records for this project and order
                $latestPC->update(['latest_pc_id' => true]);

                $this->info("Marked latest PC for project $projectId, order $orderId: {$latestPC->pc_id}");
            }
        }
    }
}
    
}
