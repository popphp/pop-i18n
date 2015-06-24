<?php

namespace Pop\I18n\Test;

use Pop\I18n\I18n;

class I18nTest extends \PHPUnit_Framework_TestCase
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

    public function testLoadDefaultJsonFile()
    {
        $i18n = new I18n('es', __DIR__ . '/tmp');
        $this->assertEquals('Hola, cómo estás?', $i18n->__('Hello, how are you?'));
    }

    public function testLoadFileDoesNotExistException()
    {
        $this->setExpectedException('Pop\I18n\Exception');
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
        $this->setExpectedException('Exception');
        $i18n = new I18n('it', __DIR__ . '/tmp2');
    }

}