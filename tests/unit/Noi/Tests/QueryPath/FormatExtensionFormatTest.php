<?php
namespace Noi\Tests\QueryPath;

class FormatExtensionFormatTest extends FormatExtensionTestCase
{
    /** @test */
    public function GetsIteratorFromQuery()
    {
        // Expect
        $this->mockQueryPath->expects($this->once())
                ->method('getIterator');

        // Act
        $this->formatter->format($this->mockCallback);
    }

    /** @test */
    public function ReturnsCurrentQueryInstance()
    {
        // Act
        $result = $this->formatter->format($this->mockCallback);

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
        $this->formatter->format($this->mockCallback);
    }

    /** @test */
    public function InvokesGivenCallbackOnEachElement()
    {
        // Setup
        $this->queryStorage->removeAll($this->queryStorage);
        $this->queryStorage->attach($e1 = $this->createMockQueryPath());
        $this->queryStorage->attach($e2 = $this->createMockQueryPath());
        $this->queryStorage->attach($e3 = $this->createMockQueryPath());

        // Expect
        $this->mockCallback->expects($this->exactly(3))
                ->method('__invoke');

        $e1->expects($this->atLeastOnce())
                ->method('text');
        $e2->expects($this->atLeastOnce())
                ->method('text');
        $e3->expects($this->atLeastOnce())
                ->method('text');

        // Act
        $this->formatter->format($this->mockCallback);
    }

    /** @test */
    public function InvokesGivenCallbackWithUnformattedText()
    {
        // Setup
        $testText = 'Unformatted Text';
        $this->mockElement->expects($this->any())
                ->method('text')
                ->will($this->returnValueMap(array(
                    array(null, $testText),
                )));

        // Expect
        $this->mockCallback->expects($this->once())
                ->method('__invoke')->with($testText);

        // Act
        $this->formatter->format($this->mockCallback);
    }

    /** @test */
    public function InvokesGivenCallbackWithGivenArguments()
    {
        // Setup
        $testText = 'Test Text';
        $testArg1 = 'Test Arg1';
        $testArg2 = 'Test Arg2';

        $this->mockElement->expects($this->any())
                ->method('text')
                ->will($this->returnValue($testText));

        // Expect
        $this->mockCallback->expects($this->once())
                ->method('__invoke')->with($testText, $testArg1, $testArg2);

        // Act
        $this->formatter->format($this->mockCallback, array($testArg1, $testArg2));
    }

    /** @test */
    public function InvokesGivenCallbackWithVariableLengthArguments()
    {
        // Setup
        $testText = 'Test Text';
        $testArg1 = 'Test Arg1';
        $testArg2 = 'Test Arg2';

        $this->mockElement->expects($this->any())
                ->method('text')
                ->will($this->returnValue($testText));

        // Expect
        $this->mockCallback->expects($this->once())
                ->method('__invoke')->with($testText, $testArg1, $testArg2);

        // Act
        $this->formatter->format($this->mockCallback, $testArg1, $testArg2);
    }

    /** @test */
    public function InvokesFunctionCallback_WithOffsetSpecified()
    {
        // Setup
        $callback = 'preg_replace[2]';
        $args = array('/t/i', '*', 2);
        $testText = 'Test Text';
        $expected = '*es* Text';  // preg_replace('/t/i', '*', $testText, 2)

        $this->mockElement->expects($this->any())
                ->method('text')
                ->will($this->returnValue($testText));

        // Expect
        $this->mockElement->expects($this->exactly(2))
                ->method('text')->with($this->logicalOr($expected, $this->isNull()));

        // Act
        $this->formatter->format($callback, $args);
    }

    /** @test */
    public function InvokesMethodCallback_WithOffsetSpecified()
    {
        // Setup
        $callback = array($this->mockCallback, '__invoke', 3);
        $args = array('Arg1', 'Arg2', 'Arg3');

        $testText = 'Test Text';
        $this->mockElement->expects($this->any())
                ->method('text')
                ->will($this->returnValue($testText));

        // Expect
        $this->mockCallback->expects($this->once())
                ->method('__invoke')->with($args[0], $args[1], $args[2], $testText);

        // Act
        $this->formatter->format($callback, $args);
    }

    /** @test */
    public function SetsFormattedText()
    {
        // Setup
        $testText = 'Formatted Text';
        $this->mockCallback->expects($this->any())
                ->method('__invoke')
                ->will($this->returnValue($testText));

        // Expect
        $this->mockElement->expects($this->exactly(2))
                ->method('text')->with($this->logicalOr($testText, $this->isNull()));

        // Act
        $this->formatter->format($this->mockCallback);
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
        $this->formatter->format($testCallback);
    }
}
