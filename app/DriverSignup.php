<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriverSignup extends Model
{
    //making neccessary changes
    public $table = 'driver_signup';

    //since,eloquent by default uses timestamp

    public $timestamps = false;
}
