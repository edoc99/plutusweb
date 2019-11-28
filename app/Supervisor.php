<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    //making neccessary changes
    public $table = 'supervisor_signup';

    //since,eloquent by default uses timestamp

    public $timestamps = false;
}
