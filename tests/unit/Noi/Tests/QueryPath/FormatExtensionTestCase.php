<?php
namespace Noi\Tests\QueryPath;

use Noi\QueryPath\FormatExtension;
use PHPUnit_Framework_TestCase;
use SplObjectStorage;

abstract class FormatExtensionTestCase extends PHPUnit_Framework_TestCase
{
    protected $formatter;
    protected $mockQueryPath;
    protected $mockElement;
    protected $mockCallback;
    protected $queryStorage;
    protected $unused = null;

    public function setUp()
    {
        $this->mockElement = $this->createMockQueryPath();
        $this->queryStorage = $this->createQueryStorage(array($this->mockElement));
        $this->mockQueryPath = $this->createMockQueryPath($this->queryStorage);
        $this->formatter = $this->createFormatExtension($this->mockQueryPath);

        $this->mockCallback = $this->createMockCallback();
    }

    protected function createFormatExtension($qp)
    {
        return new FormatExtension($qp);
    }

    protected function createMockQueryPath($storage = null)
    {
        $mock = $this->getMockBuilder('QueryPath\DOMQuery')
                ->disableOriginalConstructor()->getMock();

        if ($storage) {
            $mock->expects($this->any())
                    ->method('getIterator')
                    ->will($this->returnValue($storage));
        }

        return $mock;
    }

    protected function createMockCallback()
    {
        return $this->getMockBuilder('stdClass')
                ->setMethods(array('__invoke'))->getMock();
    }

    protected function createQueryStorage($elements = array())
    {
        $storage = new SplObjectStorage();
        foreach ($elements as $e) {
            $storage->attach($e);
        }
        return $storage;
    }
}
