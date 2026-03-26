<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SaveMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messageData;

    /**
     * Create a new job instance.
     */
    public function __construct($messageData)
    {
        $this->messageData = $messageData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('SaveMessageJob started', $this->messageData);
            
            // Get or create conversation
            $conversation = Conversation::where(function($query) {
                $query->where('user1_id', $this->messageData['sender_id'])
                      ->where('user2_id', $this->messageData['receiver_id']);
            })->orWhere(function($query) {
                $query->where('user1_id', $this->messageData['receiver_id'])
                      ->where('user2_id', $this->messageData['sender_id']);
            })->first();

            if (!$conversation) {
                Log::info('Creating new conversation', [
                    'user1_id' => min($this->messageData['sender_id'], $this->messageData['receiver_id']),
                    'user2_id' => max($this->messageData['sender_id'], $this->messageData['receiver_id'])
                ]);
                
                $conversation = Conversation::create([
                    'user1_id' => min($this->messageData['sender_id'], $this->messageData['receiver_id']),
                    'user2_id' => max($this->messageData['sender_id'], $this->messageData['receiver_id']),
                    'unread_count_user1' => 0,
                    'unread_count_user2' => 0,
                ]);
                
                Log::info('Conversation created', ['id' => $conversation->id]);
            } else {
                Log::info('Using existing conversation', ['id' => $conversation->id]);
            }

            // Create message
            $message = Message::create([
                'uuid' => $this->messageData['uuid'],
                'conversation_id' => $conversation->id,
                'sender_id' => $this->messageData['sender_id'],
                'receiver_id' => $this->messageData['receiver_id'],
                'message' => $this->messageData['message'],
                'type' => $this->messageData['message_type'],
                'media_path' => $this->messageData['metadata']['media_path'] ?? null,
                'media_type' => $this->messageData['metadata']['media_type'] ?? null,
                'created_at' => now(),
            ]);

            // Update conversation
            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at,
            ]);

            // Increment unread count for receiver
            if ($message->receiver_id === $conversation->user1_id) {
                $conversation->increment('unread_count_user1');
            } else {
                $conversation->increment('unread_count_user2');
            }

            Log::info('Message saved successfully', [
                'message_id' => $message->id,
                'uuid' => $this->messageData['uuid'],
                'conversation_id' => $conversation->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save message in background', [
                'message_data' => $this->messageData,
                'error' => $e->getMessage()
            ]);
            
            // Re-queue the job for retry
            $this->release(60); // Retry after 1 minute
        }
    }
}
