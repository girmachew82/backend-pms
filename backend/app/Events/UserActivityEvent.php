<?php

namespace App\Events;

use Illuminate\Broadcasting\{Channel, InteractsWithSockets, PresenceChannel, PrivateChannel};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActivityEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user,$action;

    /**
     * Create a new event instance.
     */
    public function __construct($user, string $action)
    {
        $this->user = $user;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return 
            new Channel('dashboard.notifications');
    }
    public function broadcastAs()
    {
        return 'user.activity';
    }

    public function broadcastWith():array{
        return [
            "id"=>$this->user->id,
            "name"=>$this->user->name,
            "action"=>$this->action,
            "time"=> now()->format("H:i:s")
        ];
    }
}
