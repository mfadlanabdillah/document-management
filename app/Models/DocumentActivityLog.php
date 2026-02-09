<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;
use App\Models\User;

class DocumentActivityLog extends Model
{
    use UsesUuid;

    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'action',
        'performed_by',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
