<?php

namespace Elmmac\Ikhokha\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IkhokhaPayment extends Model
{
    protected $table = 'ikhokha_payments';

    protected $fillable = [
        'user_id',
        'transaction_id',
        'paylink_id',
        'description',
        'customer_email',
        'amount',
        'currency',
        'mode',
        'status',
        'payment_url',
        'metadata',
        'webhook_payload',
        'webhook_signature',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'webhook_payload' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVERSED = 'reversed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
