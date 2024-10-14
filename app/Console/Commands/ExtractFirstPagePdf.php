<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\File;


class ExtractFirstPagePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:extract-first-page-pdf {directory=pdf}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inputDirectory = storage_path('app/pdf');
        $outputDirectory = $inputDirectory . '/finished';

        if (!File::isDirectory($inputDirectory)) {
            $this->error("The specified directory does not exist: $inputDirectory");
            return 1;
        }

        File::ensureDirectoryExists($outputDirectory, 0755, true);

        $pdfFiles = collect(File::files($inputDirectory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'pdf');

        if ($pdfFiles->isEmpty()) {
            $this->warn("No PDF files found in the specified directory.");
            return 0;
        }

        $pdfFiles->each(function ($pdfFile) use ($outputDirectory) {
            $inputFile = $pdfFile->getPathname();
            $outputFile = $outputDirectory . '/' . $pdfFile->getFilename();

            try {
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pageCount = $pdf->setSourceFile($inputFile);

                if ($pageCount < 1) {
                    $this->warn("The PDF file is empty: " . $pdfFile->getFilename());
                    return;
                }

                $pdf->useTemplate($pdf->importPage(1));
                $pdf->Output($outputFile, 'F');

                $this->info("Processed: " . $pdfFile->getFilename() . " -> " . basename($outputFile));
            } catch (\Exception $e) {
                $this->error("Error processing " . $pdfFile->getFilename() . ": " . $e->getMessage());
            }
        });

        $this->info("All PDF files processed. First pages extracted and saved to: $outputDirectory");
        return 0;
    }
}
