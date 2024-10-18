<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'BAMPER';

    protected $primaryKey = 'PerCod';

    public $timestamps = false;

    protected $connection = 'sqlsrv';

    protected $dateFormat = 'Y-m-d H:i:s.v';

    public $incrementing = false;
    
    
}
