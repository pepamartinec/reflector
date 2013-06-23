<?php
namespace Reflector\Iterator;

use Reflector\Tokenizer\Tokenizer;
use Reflector\AliasResolver;
use Reflector\ReflectionFactory;
use Reflector\Reflection\Statik\StaticClassReflection;
use Reflector\Reflection\Runtime\RuntimeClassReflection;

class ClassInterfaceIteratorTest extends \PHPUnit_Framework_TestCase
{
    const SOURCE_CODE = <<<EOC


interface Foo {}
interface Bar {}
interface Other {}
interface Foobar extends Foo, Bar {}
interface Barfoo extends Bar, Foo {}

class Test1 implements Foo, Bar {}
class Test2 implements Bar, Foo {}

class Test3 extends Test1 {}
class Test4 extends Test2 {}

class Test5 extends Test1 implements Other {}

class Test6 implements Foobar, Other {}
class TestO implements Other {}
class Test7 extends TestO implements Barfoo {}

EOC;

    /**
     * @var ClassInterfaceIterator
     */
    protected $staticIterator;

    /**
     * @var \Iterator
     */
    protected $runtimeIterator;

    public function testIterators()
    {return;
        $matches = array();
        preg_match_all('/class\ (\w+)/', self::SOURCE_CODE, $matches);
        $classNames = $matches[1];

        $staticIterators   = array();
        $originalIterators = array();
        $runtimeIterators  = array();

        // first do static analysis and pick the static reflections
        $factory       = new ReflectionFactory();
        $aliasResolver = new AliasResolver($factory);
        $tokenizer     = Tokenizer::fromCode('<?php '.self::SOURCE_CODE);
        $factory->analyzeCode($tokenizer, $aliasResolver);

        foreach ($classNames as $className) {
        	$staticIterators[$className] = $factory->getClass($className)->getInterfaceIterator();
        }

        // now eval and pick the runtime reflections
        eval(self::SOURCE_CODE);

        foreach ($classNames as $className) {
        	$reflection = new \ReflectionClass($className);
        	$originalIterators[$className] = new \ArrayIterator($reflection->getInterfaces());

        	$reflection = new RuntimeClassReflection($reflection, $factory);
        	$runtimeIterators[$className] = $reflection->getInterfaceIterator();
        }

        foreach ($classNames as $className) {
            echo "Class {$className} implements:";

            echo "\n\toriginal: ";
            $oi = $originalIterators[$className]; /* @var $oi \ArrayIterator */
            while ($oi->valid()) {
                echo $oi->current()->getName().', ';
                $oi->next();
            }

            echo "\n\truntime: ";
            $ri = $runtimeIterators[$className]; /* @var $ri Reflector\Iterator\ClassInterfaceIterator */
            while ($ri->valid()) {
                echo $ri->current()->getName().', ';
                $ri->next();
            }

            echo "\n\tstatic: ";
            $si = $staticIterators[$className]; /* @var $si Reflector\Iterator\ClassInterfaceIterator */
            while ($si->valid()) {
            	echo $si->current()->getName().', ';
            	$si->next();
            }

            echo "\n\n";
        }

        exit;
    }
}
