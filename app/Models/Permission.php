<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as BaseModel;
use OwenIt\Auditing\Contracts\Auditable;

class Permission extends BaseModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
}
