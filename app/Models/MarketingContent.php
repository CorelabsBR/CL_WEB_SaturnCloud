<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingContent extends Model
{
    public const STATUSES = ['draft', 'pending_approval', 'scheduled', 'published', 'archived'];
    protected $guarded = ['id', 'approved_by', 'published_at'];
    protected $casts = ['tags' => 'array', 'scheduled_at' => 'datetime', 'published_at' => 'datetime'];
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function revisions() { return $this->hasMany(MarketingContentRevision::class, 'content_id'); }
}
