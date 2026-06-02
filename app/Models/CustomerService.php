<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerService extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'user_id',
        'order_id',
        'order_item_id',
        'product_id',
        'type',
        'status',
        'name',
        'domain',
        'provision_identifier',
        'expires_at',
        'cancellation_requested_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'cancellation_requested_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function pterodactylServer()
    {
        return $this->hasOne(PterodactylServer::class, 'service_id');
    }

    public function provisioningConfiguration()
    {
        return $this->hasOne(ServiceProvisioningConfiguration::class, 'service_id');
    }

    public function hestiaAccount()
    {
        return $this->hasOne(HestiaAccount::class, 'service_id');
    }
}
