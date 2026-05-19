<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentTermsAgreement extends Model
{
    protected $fillable = [
        'user_id',
        'installment_plan_id',
        'terms_version',
        'terms_text_snapshot',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id');
    }
}
