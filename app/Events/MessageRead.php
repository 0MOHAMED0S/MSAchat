<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageRead implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $conversationId;
    public $readerId;

    public function __construct(Conversation $conversation, $readerId)
    {
        $this->conversationId = $conversation->id;
        $this->readerId = $readerId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->conversationId);
    }

    public function broadcastWith()
    {
        return [
            'readerId' => $this->readerId,
            'conversation_id' => $this->conversationId,
        ];
    }
}
