<?php

use Baum\Tests\Models\SoftCategory;

class CategorySoftDeletesTest extends CategoryTestCase
{

    public function testReload()
    {
        /** @var SoftCategory $node */
        $node = $this->categories('Child 3', SoftCategory::class);
        $node->delete();
        $this->assertTrue($node->trashed());
        $this->assertTrue($node->exists);
    }

    public function testDeleteMaintainsTreeValid()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child3 = $this->categories('Child 3', SoftCategory::class);
        $child3->delete();

        $this->assertTrue($child3->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());
    }

    public function testDeleteMaintainsTreeValidWithSubtrees()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2 = $this->categories('Child 2', SoftCategory::class);
        $child2->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = array(
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        );
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());
    }

    public function testDeleteShiftsIndexes()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Child 1', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = array(
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        );
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(8, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 2', SoftCategory::class)->getLeft());
        $this->assertEquals(5, $this->categories('Child 2', SoftCategory::class)->getRight());

        $this->assertEquals(3, $this->categories('Child 2.1', SoftCategory::class)->getLeft());
        $this->assertEquals(4, $this->categories('Child 2.1', SoftCategory::class)->getRight());

        $this->assertEquals(6, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(7, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testDeleteShiftsIndexesSubtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Child 2', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = array(
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        );
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(6, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 1', SoftCategory::class)->getLeft());
        $this->assertEquals(3, $this->categories('Child 1', SoftCategory::class)->getRight());

        $this->assertEquals(4, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(5, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testDeleteShiftsIndexesFullSubtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Root 1', SoftCategory::class)->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->assertEmpty($this->categories('Root 2', SoftCategory::class)->getSiblings()->all());
        $this->assertEquals(1, $this->categories('Root 2', SoftCategory::class)->getLeft());
        $this->assertEquals(2, $this->categories('Root 2', SoftCategory::class)->getRight());
    }

    public function testRestoreMaintainsTreeValid()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child3 = $this->categories('Child 3', SoftCategory::class);
        $child3->delete();

        $this->assertTrue($child3->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child3->reload();
        $child3->restore();

        $this->assertFalse($child3->trashed());
        $this->assertTrue(SoftCategory::isValidNestedSet());
    }

    public function testRestoreMaintainsTreeValidWithSubtrees()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2 = $this->categories('Child 2', SoftCategory::class);
        $child2->delete();
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $child2->reload();
        $child2->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = array(
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        );
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());
    }

    public function testRestoreUnshiftsIndexes()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Child 1', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::withTrashed()->where('name', 'Child 1')->first()->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = array(
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        );
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(10, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 1', SoftCategory::class)->getLeft());
        $this->assertEquals(3, $this->categories('Child 1', SoftCategory::class)->getRight());

        $this->assertEquals(4, $this->categories('Child 2', SoftCategory::class)->getLeft());
        $this->assertEquals(7, $this->categories('Child 2', SoftCategory::class)->getRight());
        $this->assertEquals(5, $this->categories('Child 2.1', SoftCategory::class)->getLeft());
        $this->assertEquals(6, $this->categories('Child 2.1', SoftCategory::class)->getRight());

        $this->assertEquals(8, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(9, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testRestoreUnshiftsIndexesSubtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Child 2', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::withTrashed()->where('name', 'Child 2')->first()->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = array(
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        );
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(10, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 1', SoftCategory::class)->getLeft());
        $this->assertEquals(3, $this->categories('Child 1', SoftCategory::class)->getRight());

        $this->assertEquals(4, $this->categories('Child 2', SoftCategory::class)->getLeft());
        $this->assertEquals(7, $this->categories('Child 2', SoftCategory::class)->getRight());
        $this->assertEquals(5, $this->categories('Child 2.1', SoftCategory::class)->getLeft());
        $this->assertEquals(6, $this->categories('Child 2.1', SoftCategory::class)->getRight());

        $this->assertEquals(8, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(9, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testRestoreUnshiftsIndexesFullSubtree()
    {
        $this->assertTrue(SoftCategory::isValidNestedSet());

        $this->categories('Root 1', SoftCategory::class)->delete();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        SoftCategory::withTrashed()->where('name', 'Root 1')->first()->restore();

        $this->assertTrue(SoftCategory::isValidNestedSet());

        $expected = array(
            $this->categories('Child 1', SoftCategory::class),
            $this->categories('Child 2', SoftCategory::class),
            $this->categories('Child 2.1', SoftCategory::class),
            $this->categories('Child 3', SoftCategory::class)
        );
        $this->assertEquals($expected, $this->categories('Root 1', SoftCategory::class)->getDescendants()->all());

        $this->assertEquals(1, $this->categories('Root 1', SoftCategory::class)->getLeft());
        $this->assertEquals(10, $this->categories('Root 1', SoftCategory::class)->getRight());

        $this->assertEquals(2, $this->categories('Child 1', SoftCategory::class)->getLeft());
        $this->assertEquals(3, $this->categories('Child 1', SoftCategory::class)->getRight());

        $this->assertEquals(4, $this->categories('Child 2', SoftCategory::class)->getLeft());
        $this->assertEquals(7, $this->categories('Child 2', SoftCategory::class)->getRight());
        $this->assertEquals(5, $this->categories('Child 2.1', SoftCategory::class)->getLeft());
        $this->assertEquals(6, $this->categories('Child 2.1', SoftCategory::class)->getRight());

        $this->assertEquals(8, $this->categories('Child 3', SoftCategory::class)->getLeft());
        $this->assertEquals(9, $this->categories('Child 3', SoftCategory::class)->getRight());
    }

    public function testAllStatic()
    {
        $expected = array('Root 1', 'Child 1', 'Child 2', 'Child 2.1', 'Child 3', 'Root 2');

        $this->assertArraysAreEqual($expected, SoftCategory::all()->pluck('name')->toArray());
    }

    public function testAllStaticWithSoftDeletes()
    {
        $this->categories('Child 1', SoftCategory::class)->delete();
        $this->categories('Child 3', SoftCategory::class)->delete();

        $expected = array('Root 1', 'Child 2', 'Child 2.1', 'Root 2');
        $this->assertArraysAreEqual($expected, SoftCategory::all()->pluck('name')->toArray());
    }

}
