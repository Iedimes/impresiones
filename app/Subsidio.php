<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subsidio extends Model
{
    protected $table = 'SHMCER';

    protected $primaryKey = 'CerNro';

    public $timestamps = false;

    protected $connection = 'sqlsrv';

    protected $dateFormat = 'Y-m-d H:i:s.v';

    public $incrementing = false;
    
    
}
