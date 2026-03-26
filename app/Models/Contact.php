<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'phone',
        'message',
        'file_url',
        'image_url',
        'done_send',
    ];

    protected $casts = [
        'done_send' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}