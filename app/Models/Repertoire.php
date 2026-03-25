<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repertoire extends Model
{
    use HasFactory;

    protected $table = 'repertoire';

    protected $fillable = [
        'title',
        'composer',
        'pdf_path',
        'audio_url',
        'period_tag',
        'difficulty_tag',
    ];
}
