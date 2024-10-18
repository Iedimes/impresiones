<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'BAMDPT';

    protected $primaryKey = 'DptoId';

    public $incrementing = false;

    protected $connection = 'sqlsrv';





}
