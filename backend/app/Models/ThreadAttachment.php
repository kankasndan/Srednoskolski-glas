<?php

namespace App\Models;

use App\Facades\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThreadAttachment extends Model
{
    protected $fillable = ['url', 'slug', 'thread_id', 'provider', 'file_id'];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Delete the underlying file from whichever provider stored it.
     */
    public function deleteFile(): bool
    {
        if ($this->file_id === null) {
            return false;
        }

        return Media::driver($this->provider)->delete($this->file_id);
    }
}
