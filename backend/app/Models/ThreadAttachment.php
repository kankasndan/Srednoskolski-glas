<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThreadAttachment extends Model
{
    protected $fillable = ["url", "slug", "thread_id"];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
