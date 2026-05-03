<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    protected $table = 'words';
    protected $guarded = [];
    protected $fillable = ['key', 'en', 'ru', 'fr', 'it', 'es', 'de'];
    public $timestamps = false;
}
