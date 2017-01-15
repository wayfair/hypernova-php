<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 10:28 AM
 */

namespace WF\Hypernova\Tests;

class JobTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $arr
     *
     * @dataProvider badFactoryDataProvider
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsMalformedException($arr)
    {
        \WF\Hypernova\Job::fromArray($arr);
    }

    /**
     * @return array
     */
    public function badFactoryDataProvider()
    {
        return [
            [[]],
            [[1, 2, 3]],
            [['foo' => 'bar', 'baz' => 'quux']]
        ];
    }

    /**
     * @return void
     */
    public function testFactoryPopulates()
    {
        $job = \WF\Hypernova\Job::fromArray(['name' => 'my_component', 'data' => ['some' => 'data']]);

        $this->assertEquals('my_component', $job->name);
        $this->assertEquals(['some' => 'data'], $job->data);
    }
}
