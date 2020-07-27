<?php

namespace Baum\Tests\Models;

class MultiScopedCluster extends Cluster
{

    protected $scoped = array('company_id', 'language');

}
