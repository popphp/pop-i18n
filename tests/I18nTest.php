<?php

namespace Pop\I18n\Test;

use Pop\I18n\I18n;
use PHPUnit\Framework\TestCase;

class I18nTest extends TestCase
{

    public function testConstructor()
    {
        $i18n = new I18n();
        $this->assertInstanceOf('Pop\I18n\I18n', $i18n);
        $this->assertEquals('en', $i18n->getLanguage());
        $this->assertEquals('US', $i18n->getLocale());
    }

    public function testConstructorNoLocale()
    {
        $i18n = new I18n('fr');
        $this->assertEquals('fr', $i18n->getLanguage());
        $this->assertEquals('FR', $i18n->getLocale());
    }

    public function testLoadXmlFile()
    {
        $i18n = new I18n('fr', __DIR__ . '/tmp');

        ob_start();
        $i18n->_e('Hello, how are you?');
        $result = ob_get_clean();

        $this->assertEquals('Bonjour, comment allez-vous?', $i18n->__('Hello, how are you?'));
        $this->assertEquals('Bonjour, comment allez-vous?', $result);
        $this->assertEquals('Bonjour, comment allez-vous, Nick?', $i18n->__('Hello, how are you, %1?', 'Nick'));
        $this->assertEquals('Je vais bien, Nick. Comment est Krissy?', $i18n->__("I'm fine, %1. How's %2?", ['Nick', 'Krissy']));
    }

    public function testLoadJsonFile()
    {
        $i18n = new I18n('fr');
        $i18n->loadFile(__DIR__ . '/tmp/fr.json');

        ob_start();
        $i18n->_e('Hello, how are you?');
        $result = ob_get_clean();

        $this->assertEquals('Bonjour, comment allez-vous?', $i18n->__('Hello, how are you?'));
        $this->assertEquals('Bonjour, comment allez-vous?', $result);
        $this->assertEquals('Bonjour, comment allez-vous, Nick?', $i18n->__('Hello, how are you, %1?', 'Nick'));
        $this->assertEquals('Je vais bien, Nick. Comment est Krissy?', $i18n->__("I'm fine, %1. How's %2?", ['Nick', 'Krissy']));
    }

    public function testLoadXmlFileWithXmlAlts()
    {
        $i18n = new I18n('fr', __DIR__ . '/tmp3');

        $this->assertEquals('Bonjour, je aime programmer PHP. Mon nom est Nick', $i18n->__('Hello, my name is %1. I love to program %2.', ['Nick', 'PHP'], 'secondary'));
        $this->assertEquals('Depuis 20 ans, je programme PHP', $i18n->__('I have been programming %1 for %2 years.', ['PHP', '20'], 1));
    }

    public function testLoadXmlFileWithXmlAltsNoFind()
    {
        $i18n = new I18n('fr', __DIR__ . '/tmp3');

        $this->assertEquals('Hello everyone, my name is Nick. I love to program PHP.', $i18n->__('Hello everyone, my name is %1. I love to program %2.', ['Nick', 'PHP'], 'secondary'));
    }

    public function testLoadDefaultJsonFile()
    {
        $i18n = new I18n('es', __DIR__ . '/tmp');
        $this->assertEquals('Hola, cómo estás?', $i18n->__('Hello, how are you?'));
    }

    public function testLoadFileDoesNotExistException()
    {
        $this->expectException('Pop\I18n\Exception');
        $i18n = new I18n('fr');
        $i18n->loadFile(__DIR__ . '/tmp/bad.xml');
    }

    public function testGetLanguages()
    {
        $langs = I18n::getLanguages(__DIR__ . '/tmp');
        $this->assertTrue(isset($langs['fr_FR']));
        $this->assertEquals('Française, France (French, France)', $langs['fr_FR']);
    }

    public function testLoadFileBadXmlException()
    {
        $this->expectException('Exception');
        $i18n = new I18n('it', __DIR__ . '/tmp2');
    }

}