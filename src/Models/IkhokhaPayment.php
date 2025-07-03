<?php

namespace Elmmac\Ikhokha\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IkhokhaPayment extends Model
{
    // use HasFactory;
    protected $fillable = [

        'user_id',
        'transaction_id',
        'customer_email',
        'amount',
        'currency',
        'status',
        'description',
        'paylink_id',
        'paylink_url',
        'paid_at',
    ];
    protected $dates = ['paid_at'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
