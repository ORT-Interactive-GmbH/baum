<?php

namespace Baum\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftCluster extends Cluster
{

    use SoftDeletes;

    public $timestamps = true;

    protected $dates = ['deleted_at'];

}
