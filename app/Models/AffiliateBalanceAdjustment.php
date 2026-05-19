<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateBalanceAdjustment extends Model
{
    protected $fillable = ['affiliate_id', 'admin_user_id', 'type', 'amount', 'reason'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
