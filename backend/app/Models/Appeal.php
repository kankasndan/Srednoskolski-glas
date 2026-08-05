<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appeal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sanction_id', 'user_id', 'explanation', 'status',
        'admin_id', 'admin_response', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function sanction()
    {
        return $this->belongsTo(Sanction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
