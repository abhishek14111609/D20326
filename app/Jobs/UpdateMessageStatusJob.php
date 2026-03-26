<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateMessageStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messageUuid;
    protected $status;
    protected $timestamp;

    /**
     * Create a new job instance.
     */
    public function __construct($messageUuid, $status, $timestamp)
    {
        $this->messageUuid = $messageUuid;
        $this->status = $status;
        $this->timestamp = $timestamp;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $message = Message::where('uuid', $this->messageUuid)->first();
            
            if ($message) {
                $updateData = [];
                
                if ($this->status === 'delivered') {
                    $updateData['delivered_at'] = now();
                } elseif ($this->status === 'read') {
                    $updateData['read_at'] = now();
                }
                
                $message->update($updateData);
                
                Log::info('Message status updated', [
                    'message_uuid' => $this->messageUuid,
                    'status' => $this->status,
                    'message_id' => $message->id
                ]);
            } else {
                Log::warning('Message not found for status update', [
                    'message_uuid' => $this->messageUuid
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update message status', [
                'message_uuid' => $this->messageUuid,
                'status' => $this->status,
                'error' => $e->getMessage()
            ]);
            
            // Re-queue for retry
            $this->release(30);
        }
    }
}
