<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'google_id',
        // 'onesignal_player_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Conversations where user is user_one
    public function conversationsAsUserOne()
    {
        return $this->hasMany(Conversation::class, 'user_one_id');
    }

    // Conversations where user is user_two
    public function conversationsAsUserTwo()
    {
        return $this->hasMany(Conversation::class, 'user_two_id');
    }

    // Merge both for all conversations
    public function conversations()
    {
        return $this->conversationsAsUserOne->merge($this->conversationsAsUserTwo);
    }
    public function receivedMessages()
{
    return $this->hasMany(Message::class, 'receiver_id');
}

}
