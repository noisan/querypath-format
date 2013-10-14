<?php
namespace Noi\Tests\QueryPath;

class FormatExtensionFormatAttrTest extends FormatExtensionTestCase
{
    /** @test */
    public function GetsIteratorFromQuery()
    {
        // Expect
        $this->mockQueryPath->expects($this->once())
                ->method('getIterator');

        // Act
        $this->formatter->formatAttr($this->unused, $this->mockCallback);
    }

    /** @test */
    public function ReturnsCurrentQueryInstance()
    {
        // Act
        $result = $this->formatter->formatAttr($this->unused, $this->mockCallback);

        // Assert
        $this->assertSame($this->mockQueryPath, $result);
    }

    /** @test */
    public function DoesNotInvokeGivenCallback_IfQueryIsEmpty()
    {
        // Setup
        $this->queryStorage->removeAll($this->queryStorage);

        // Expect
        $this->mockCallback->expects($this->never())
                ->method('__invoke');

        // Act
        $this->formatter->formatAttr($this->unused, $this->mockCallback);
    }

    /** @test */
    public function InvokesGivenCallbackOnEachElement()
    {
        // Setup
        $testAttr = 'test';
        $this->queryStorage->removeAll($this->queryStorage);
        $this->queryStorage->attach($e1 = $this->createMockQueryPath());
        $this->queryStorage->attach($e2 = $this->createMockQueryPath());
        $this->queryStorage->attach($e3 = $this->createMockQueryPath());

        // Expect
        $this->mockCallback->expects($this->exactly(3))
                ->method('__invoke');

        $e1->expects($this->atLeastOnce())
                ->method('attr')->with($testAttr);
        $e2->expects($this->atLeastOnce())
                ->method('attr')->with($testAttr);
        $e3->expects($this->atLeastOnce())
                ->method('attr')->with($testAttr);

        // Act
        $this->formatter->formatAttr($testAttr, $this->mockCallback);
    }

    /** @test */
    public function InvokesGivenCallbackWithAttributeValue()
    {
        // Setup
        $testAttrName = 'test_attr';
        $testAttrValue = 'Unformatted Text';
        $this->mockElement->expects($this->any())
                ->method('attr')->with($testAttrName)
                ->will($this->returnValue($testAttrValue));

        // Expect
        $this->mockCallback->expects($this->once())
                ->method('__invoke')->with($testAttrValue);

        // Act
        $this->formatter->formatAttr($testAttrName, $this->mockCallback);
    }

    /** @test */
    public function InvokesGivenCallbackWithGivenArguments()
    {
        // Setup
        $testArg1 = 'Test Arg1';
        $testArg2 = 'Test Arg2';

        $testName = 'test';
        $testValue = 'Test Attribute';
        $this->mockElement->expects($this->any())
                ->method('attr')->with($testName)
                ->will($this->returnValue($testValue));

        // Expect
        $this->mockCallback->expects($this->once())
                ->method('__invoke')->with($testValue, $testArg1, $testArg2);

        // Act
        $this->formatter->formatAttr($testName, $this->mockCallback, array($testArg1, $testArg2));
    }

    /** @test */
    public function InvokesGivenCallbackWithVariableLengthArguments()
    {
        // Setup
        $testArg1 = 'Test Arg1';
        $testArg2 = 'Test Arg2';

        $testName = 'test';
        $testValue = 'Test Attribute';
        $this->mockElement->expects($this->any())
                ->method('attr')->with($testName)
                ->will($this->returnValue($testValue));

        // Expect
        $this->mockCallback->expects($this->once())
                ->method('__invoke')->with($testValue, $testArg1, $testArg2);

        // Act
        $this->formatter->formatAttr($testName, $this->mockCallback, $testArg1, $testArg2);
    }

    /** @test */
    public function InvokesFunctionCallback_WithOffsetSpecified()
    {
        // Setup
        $callback = 'preg_replace[2]';
        $args = array('/t/i', '*', 2);

        $testName = 'test';
        $testValue = 'Test Text';
        $expected = '*es* Text';  // preg_replace('/t/i', '*', $testText, 2)

        $this->mockElement->expects($this->any())
                ->method('attr')->with($testName)
                ->will($this->returnValue($testValue));

        // Expect
        $this->mockElement->expects($this->exactly(2))
                ->method('attr')->with($testName, $this->logicalOr($expected, $this->isNull()));

        // Act
        $this->formatter->formatAttr($testName, $callback, $args);
    }

    /** @test */
    public function InvokesMethodCallback_WithOffsetSpecified()
    {
        // Setup
        $callback = array($this->mockCallback, '__invoke', 3);
        $args = array('Arg1', 'Arg2', 'Arg3');

        $testName = 'test';
        $testValue = 'Test Text';
        $this->mockElement->expects($this->any())
                ->method('attr')->with($testName)
                ->will($this->returnValue($testValue));

        // Expect
        $this->mockCallback->expects($this->once())
                ->method('__invoke')->with($args[0], $args[1], $args[2], $testValue);

        // Act
        $this->formatter->formatAttr($testName, $callback, $args);
    }

    /** @test */
    public function SetsFormattedValue()
    {
        // Setup
        $testName = 'test';
        $testValue = 'Formatted Value';
        $this->mockCallback->expects($this->any())
                ->method('__invoke')
                ->will($this->returnValue($testValue));

        // Expect
        $this->mockElement->expects($this->exactly(2))
                ->method('attr')->with($testName, $this->logicalOr($testValue, $this->isNull()));

        // Act
        $this->formatter->formatAttr($testName, $this->mockCallback);
    }

    /**
     * @test
     * @expectedException \QueryPath\Exception
     */
    public function ThrowsException_OnInvalidCallback()
    {
        // Setup
        $testCallback = 'invalid_callback';

        // Act
        $this->formatter->formatAttr($this->unused, $testCallback);
    }
}
