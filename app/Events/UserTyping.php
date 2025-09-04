<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcastNow
{
    use SerializesModels;

    public $senderId;
    public $senderName;
    public $receiverId;
    public $isTyping;

    /**
     * Create a new event instance.
     */
    public function __construct($senderId, $senderName, $receiverId, $isTyping)
    {
        $this->senderId = $senderId;
        $this->senderName = $senderName;
        $this->receiverId = $receiverId;
        $this->isTyping = $isTyping;
    }

    /**
     * The channel the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('typing.' . $this->receiverId);
    }

    /**
     * Event name for JS listening
     */
    public function broadcastAs()
    {
        return 'UserTyping';
    }
}
