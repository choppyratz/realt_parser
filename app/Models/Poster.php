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
                    'name', 
                    'description', 
                    'link',
                    'price',
                    'code',
                    'update_date',
                    'contact_face',
                    'email',
                    'address',
                    'city',
                    'region',
                    'price_id'
                ];

    public function saveOrUpdatePoster() {
        var_dump($this->attributes);
        $poster = Poster::where('code', $this->code)->first();
        if ($poster == null) {
            $this->save();
        }else {
            Poster::where('code', $this->code)->update($this->attributes);
        }
    }
}
