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
}
