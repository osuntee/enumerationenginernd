<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;

class GenerateBatchCodes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes for large batches

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Project $project,
        protected Batch $batch,
        protected int $count
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->batch->update(['status' => 'processing']);

        try {
            DB::transaction(function () {
                $appUrl = config('app.url');

                for ($i = 0; $i < $this->count; $i++) {
                    $ref = $this->generateReferenceNumber();
                    $url = "{$appUrl}/verify/{$ref}";

                    $qrCode = new QrCode(
                        data: $url,
                        size: 200,
                        margin: 10,
                    );

                    $writer = new PngWriter();
                    $qrCodeImage = $writer->write($qrCode);
                    $qrCodeBase64 = base64_encode($qrCodeImage->getString());

                    $this->batch->codes()->create([
                        'project_id' => $this->project->id,
                        'reference' => $ref,
                        'qrcode' => $qrCodeBase64,
                        'is_used' => false,
                    ]);
                }

                $this->batch->update(['status' => 'completed']);
            });
        } catch (\Exception $e) {
            $this->batch->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Generate a unique reference number for the enumeration.
     */
    private function generateReferenceNumber()
    {
        $reference = null;

        do {
            $timestamp = now()->format('YmdHisv');
            $uniqueId = strtoupper(Str::random(3));
            $reference = $timestamp . $uniqueId;
        } while ($this->batch->codes()->where('reference', $reference)->exists());

        return $reference;
    }
}
