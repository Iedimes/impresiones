<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ManLote extends Model
{
    protected $table = 'IVMTSOV';

    //protected $primaryKey = ['SolPerCod', 'PylCod'];

    protected $connection = 'sqlsrv';

    public $incrementing = false;


}
