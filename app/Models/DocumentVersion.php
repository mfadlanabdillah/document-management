<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\UsesUuid;

class DocumentVersion extends Model
{
    use UsesUuid;

    protected $fillable = [
        'document_id',
        'version_number',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
