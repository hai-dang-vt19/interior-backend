<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerContactLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'channel',
        'title',
        'message',
        'contacted_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contactedBy()
    {
        return $this->belongsTo(User::class, 'contacted_by');
    }
}
