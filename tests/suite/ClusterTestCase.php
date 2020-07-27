<?php

class ClusterTestCase extends BaumTestCase
{

    public static function setUpBeforeClass(): void
    {
        with(new ClusterMigrator)->up();
    }

    public function setUp(): void
    {
        with(new ClusterSeeder)->run();
    }

    protected function clusters($name, $className = Baum\Tests\Models\Cluster::class)
    {
        return forward_static_call_array(array($className, 'where'), array('name', '=', $name))->first();
    }

}
