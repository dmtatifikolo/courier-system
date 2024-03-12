<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Location extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable =
    [
        'name',
        'region_id'
    ];

    public function regions()
    {
        return $this->belongsTo(Region::class);
    }
}
