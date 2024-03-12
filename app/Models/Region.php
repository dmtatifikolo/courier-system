<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Region extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable =
    [
        'name',
        'code'
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    } 
}
