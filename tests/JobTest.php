<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 10:28 AM
 */

namespace WF\Hypernova\Tests;


class JobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider badFactoryDataProvider
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsMalformedException($arr) {
        \WF\Hypernova\Job::fromArray($arr);
    }

    public function badFactoryDataProvider() {
        return [
            [[]],
            [[1,2,3]],
            [['foo' => 'bar', 'baz' => 'quux']]
        ];
    }

    public function testFactoryPopulates() {
        $job = \WF\Hypernova\Job::fromArray(['myView' => ['name' => 'my_component', 'data' => ['some' => 'data']]]);

        $this->assertEquals('myView', $job->id);
        $this->assertEquals('my_component', $job->name);
        $this->assertEquals(['some' => 'data'], $job->data);
    }

}