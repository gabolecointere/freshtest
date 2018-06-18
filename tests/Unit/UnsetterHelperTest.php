<?php
declare(strict_types=1);
namespace Tests\Unit;
use Tests\TestCase;
/*
 * Unsets properties on (nested) objects.
 *
 * @param  object $object
 * @param  string $property
 *
 * @return object|null
 */
function unsetter(object $object, string $property): ?object
{
    // keep it short
    // keep it clean
    //
    // don't use eval()
    // if possible avoid using foreach constructs

    if (strpos($property, '->') !== false) {
        $props = explode('->', $property );

        if (count($props) > 0 && isset($object->{$props[0]})) {
          $leftProps = implode('->', array_slice($props, 1));
          $object->{$props[0]} = unsetter($object->{$props[0]}, $leftProps);

          if (!$object->{$props[0]}) unset($object->{$props[0]});
        }

    } else unset($object->$property);

    return !count((array) $object) ? null : $object;
}
class UnsetterHelperTest extends TestCase
{
    public function testUnsettingRootProperty()
    {
        $object = (object) [
            'one' => 'foo',
            'two' => 'bar',
        ];
        $result = (object) [
            'one' => 'foo',
        ];
        $this->assertEquals(
            unsetter($object, 'two'), $result
        );
    }
    public function testEmptyObjectReturnsNull()
    {
        $object = (object) [
            'foo' => 'bar',
        ];
        $this->assertNull(
            unsetter($object, 'foo')
        );
    }
    public function testUnsettingNestedProperty()
    {
        $object = (object) [
            'one' => (object) [
                'two' => (object) [
                    'foobar' => 'bar',
                    'foobaz' => 'baz',
                ],
            ],
        ];
        $result = (object) [
            'one' => (object) [
                'two' => (object) [
                    'foobar' => 'bar',
                ],
            ],
        ];
        $this->assertEquals(
            unsetter($object, 'one->two->foobaz'), $result
        );
    }
    public function testUnsettingNestedPropertyIsRemovedWhenEmpty()
    {
        $object = (object) [
            'foo' => 'bar',
            'one' => (object) [
                'two' => (object) [
                    'three' => 'foo',
                ],
            ],
        ];
        $result = (object) [
            'foo' => 'bar',
        ];
        $this->assertEquals(
            unsetter($object, 'one->two->three'), $result
        );
    }
    public function testUnsettingNestedPropertyWithTheSameName()
    {
        $object = (object) [
            'foo' => (object) [
                'foo' => (object) [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
            ],
        ];
        $result = (object) [
            'foo' => (object) [
                'foo' => (object) [
                    'bar' => 'baz',
                ],
            ],
        ];
        $this->assertEquals(
            unsetter($object, 'foo->foo->foo'), $result
        );
    }
}
