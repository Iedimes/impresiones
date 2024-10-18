<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    protected $table = 'LOCALIDA';

    protected $primaryKey = 'CiuId';

    public $incrementing = false;


    protected $connection = 'sqlsrv';



}
