<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sanction extends Model
{
    protected $fillable = [
        'user_id', 'issued_by', 'type', 'reason', 'report_id',
        'expires_at', 'acknowledged_at', 'revoked_at', 'revoked_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function appeal()
    {
        return $this->hasOne(Appeal::class);
    }
}