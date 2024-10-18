<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoteManz extends Model
{
    protected $table = 'SHMCER';

    //protected $primaryKey = ['SolPerCod', 'PylCod'];

    protected $connection = 'sqlsrv';

    public $incrementing = false;


}
