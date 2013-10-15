<?php
namespace Noi\QueryPath\Tests;

use QueryPath;

class FormatExtensionIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        QueryPath::enable('Noi\QueryPath\FormatExtension');
    }

    protected function assertDomEqualsXmlString($expectedXml, $actualDom)
    {
        $this->assertXmlStringEqualsXmlString($expectedXml, $actualDom->top()->xml());
    }

    public function testFormat_FormatsNothing_IfQueryIsEmpty()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root />';

        // Act
        $qp = qp($testXML)->find('item')->format('strtoupper');

        // Assert
        $this->assertDomEqualsXmlString($testXML, $qp);
    }

    public function testFormatAttr_FormatsNothing_IfQueryIsEmpty()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root />';

        // Act
        $qp = qp($testXML)->find('item')->formatAttr('test', 'strtoupper');

        // Assert
        $this->assertDomEqualsXmlString($testXML, $qp);
    }

    public function testFormat_FormatsTextOfTargetNode_SingleNode()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><item>Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item>TEST</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->format('strtoupper');

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormatAttr_FormatsAttributeOfTargetNode_SingleNode()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><item title="test">Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item title="TEST">Test</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->formatAttr('title' , 'strtoupper');

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormatAttr_SetsEmptyValue_IfAttributeIsUndefined()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><item>Test</item></root>';
        $expected = '<?xml version="1.0"?><root><item title="">Test</item></root>';

        // Act
        $qp = qp($testXML)->find('item')->formatAttr('title' , 'strtoupper');

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormat_FormatsTextsOfTargetNodes_MultipleNodes()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root>' .
                '<div><item>1234567890</item></div>' .
                '<div><item>9876543210</item></div>' .
                '</root>';
        $expected = '<?xml version="1.0"?><root>' .
                '<div><item>1,234,567,890.00</item></div>' .
                '<div><item>9,876,543,210.00</item></div>' .
                '</root>';

        // Act
        $qp = qp($testXML)->find('item')->format('number_format', 2);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormatAttr_FormatsAttributesOfTargetNodes_MultipleNodes()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root>' .
                '<div><item value="1234567890">Test</item></div>' .
                '<div><item value="9876543210">Test</item></div>' .
                '</root>';
        $expected = '<?xml version="1.0"?><root>' .
                '<div><item value="1,234,567,890.00">Test</item></div>' .
                '<div><item value="9,876,543,210.00">Test</item></div>' .
                '</root>';

        // Act
        $qp = qp($testXML)->find('item')->formatAttr('value', 'number_format', 2);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormat_FormatsTextOfRemovedNode()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root>' .
                '<from><item>*&lt;Test&gt;*</item></from>' .
                '<to/>' .
                '</root>';
        $expected = '<?xml version="1.0"?><root>' .
                '<from/>' .
                '<to><item>&lt;TEST&gt;</item></to>' .
                '</root>';

        $qp = qp($testXML);
        $item = $qp->remove('item');

        // Act
        $item->format(function ($value) {
            return strtoupper(trim($value, '*'));
        });
        $qp->find('to')->append($item);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormatAttr_FormatsAttributeOfRemovedNode()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root>' .
                '<from><item title="*&lt;Test&gt;*">Test</item></from>' .
                '<to/>' .
                '</root>';
        $expected = '<?xml version="1.0"?><root>' .
                '<from/>' .
                '<to><item title="&lt;TEST&gt;">Test</item></to>' .
                '</root>';

        $qp = qp($testXML);
        $item = $qp->remove('item');

        // Act
        $item->formatAttr('title', function ($value) {
            return strtoupper(trim($value, '*'));
        });
        $qp->find('to')->append($item);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormat_InvokesFunctionCallback_WithOffsetSpecified()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><div>' .
                'Test1' . "\n" .
                'Test2' . "\n" .
                'Test3' .
                '</div></root>';
        $expected = '<?xml version="1.0"?><root><div>' .
                'Test1 Test2 Test3' .
                '</div></root>';

        // Act
        $qp = qp($testXML)->find('div')->format('str_replace[2]', array("\r\n", "\r", "\n"), ' ');

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormatAttr_InvokesFunctionCallback_WithOffsetSpecified()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><div title="' .
                'Test1' . "\n" .
                'Test2' . "\n" .
                'Test3' .
                '">Test</div></root>';
        $expected = '<?xml version="1.0"?><root><div title="' .
                'Test1 Test2 Test3' .
                '">Test</div></root>';

        // Act
        $qp = qp($testXML)->find('div')->formatAttr('title', 'str_replace[2]', array("\r\n", "\r", "\n"), ' ');

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormat_InvokesMethodCallback_WithOffsetSpecified()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><items>' .
                '<item>Pomo</item>' .
                '<item>Persiko</item>' .
                '<item>Citrono</item>' .
                '</items></root>';
        $expected = '<?xml version="1.0"?><root><items>' .
                '<item>Apple</item>' .
                '<item>Peach</item>' .
                '<item>Lemon</item>' .
                '</items></root>';

        // Act
        $qp = qp($testXML)->find('item')->format(array($this, 'translateName', 1), 'en');

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function testFormatAttr_InvokesMethodCallback_WithOffsetSpecified()
    {
        // Setup
        $testXML = '<?xml version="1.0"?><root><items>' .
                '<item title="Pomo">Test1</item>' .
                '<item title="Persiko">Test2</item>' .
                '<item title="Citrono">Test3</item>' .
                '</items></root>';
        $expected = '<?xml version="1.0"?><root><items>' .
                '<item title="Apple">Test1</item>' .
                '<item title="Peach">Test2</item>' .
                '<item title="Lemon">Test3</item>' .
                '</items></root>';

        // Act
        $qp = qp($testXML)->find('item')->formatAttr('title', array($this, 'translateName', 1), 'en');

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }

    public function translateName($lang, $name)
    {
        $fruits = array(
            'en' => array(
                'pomo' => 'Apple',
                'persiko' => 'Peach',
                'citrono' => 'Lemon',
            ),
            // ...
        );
        return $fruits[strtolower($lang)][strtolower($name)];
    }

    public function test_FormatsHTML()
    {
        // Setup
        $testHTML = '<html><body>' .
                '<div id="result">' .
                '<div class="line"><span class="name">alice</span>: <span class="score">n/a</span></div>' .
                '<div class="line"><span class="name">BOB</span>: <span class="score">n/a</span></div>' .
                '</div>'.
                '</body></html>';

        $expected = '<html><body>' .
                '<div id="result">' .
                '<div class="line"><span class="name">Alice</span>: <span class="score" title="9,876.54">9,877</span></div>' .
                '<div class="line"><span class="name">Bob</span>: <span class="score" title="5,432.10">5,432</span></div>' .
                '</div>'.
                '</body></html>';

        $qp = htmlqp($testHTML);
        $data = array('alice' => 9876.54, 'bob' => 5432.1);

        // assing values
        $qp->find('.line')->each(function ($unused, $node) use ($data) {
            $cur = htmlqp($node);

            $name = $cur->branch()->find('.name')->text();
            $score = $data[strtolower($name)];

            $cur->find('.score')
                    ->text($score)
                    ->attr('title', $score);
        });

        // format values
        $qp->find('.name')->format(function ($name) {
            return ucfirst(strtolower($name));
        });
        $qp->find('.score')
                ->format('number_format')
                ->formatAttr('title', 'number_format', 2);

        // Assert
        $this->assertDomEqualsXmlString($expected, $qp);
    }
}
