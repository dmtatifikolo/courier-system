<?php

namespace App\Models;

use Spatie\Permission\Models\Role as BaseModel;
use OwenIt\Auditing\Contracts\Auditable;

class Role extends BaseModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
}
