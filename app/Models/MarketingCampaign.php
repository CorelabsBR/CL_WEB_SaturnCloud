<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    public const STATUSES = ['draft', 'pending_approval', 'scheduled', 'active', 'paused', 'completed', 'cancelled'];
    protected $guarded = ['id', 'approved_by', 'approved_at'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'approved_at' => 'datetime', 'budget_limit' => 'decimal:2'];
    public function product() { return $this->belongsTo(Product::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
