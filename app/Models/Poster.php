<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poster extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'posters';
    protected $fillable = [
                    'id',
                    'name', 
                    'description', 
                    'link',
                    'price',
                    'code',
                    'update_date',
                    'contact_face',
                    'email',
                    'adress',
                    'city',
                    'region',
                    'price_id'
                ];
}
