<?php

namespace Pop\I18n\Test;

use Pop\I18n\I18n;
use Pop\I18n\Format\Json;

class JsonFormatTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateFile()
    {
        $lang = [
            "src"    => "en",
            "output" => "fr",
            "name"   => "French",
            "native" => "Française"
        ];

        $locales = [
            [
                "region" => "FR",
                "name"   => "France",
                "native" => "France",
                "text"   => [
                    [
                        "source" => "Hello, how are you?",
                        "output" => "Bonjour, comment allez-vous?"
                    ],
                    [
                        "source" => "Hello, how are you, %1?",
                        "output" => "Bonjour, comment allez-vous, %1?"
                    ],
                    [
                        "source" => "I'm fine, %1. How's %2?",
                        "output" => "Je vais bien, %1. Comment est %2?"
                    ]
                ]
            ]
        ];

        Json::createFile($lang, $locales, __DIR__ . '/fr.json');
        $this->assertFileExists(__DIR__ . '/fr.json');

        $i18n = new I18n('fr');
        $i18n->loadFile(__DIR__ . '/fr.json');
        $this->assertEquals('Bonjour, comment allez-vous?', $i18n->__('Hello, how are you?'));

        unlink(__DIR__ . '/fr.json');
    }

    public function testCreateFileNoSrcException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        $lang = [
            "output" => "fr",
            "name"   => "French",
            "native" => "Française"
        ];

        $locales = [];
        Json::createFile($lang, $locales, __DIR__ . '/fr.json');
    }

    public function testCreateFileNoOutputException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        $lang = [
            "src"    => "en",
            "name"   => "French",
            "native" => "Française"
        ];

        $locales = [];
        Json::createFile($lang, $locales, __DIR__ . '/fr.json');
    }

    public function testCreateFileNoRegionException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        $lang = [
            "src"    => "en",
            "output" => "fr",
            "name"   => "French",
            "native" => "Française"
        ];

        $locales = [
            [
                "name"   => "France",
                "native" => "France",
                "text"   => [
                    [
                        "source" => "Hello, how are you?",
                        "output" => "Bonjour, comment allez-vous?"
                    ]
                ]
            ]
        ];
        Json::createFile($lang, $locales, __DIR__ . '/fr.json');
    }

    public function testCreateFileNoTextException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        $lang = [
            "src"    => "en",
            "output" => "fr",
            "name"   => "French",
            "native" => "Française"
        ];

        $locales = [
            [
                "region" => "FR",
                "name"   => "France",
                "native" => "France"
            ]
        ];
        Json::createFile($lang, $locales, __DIR__ . '/fr.json');
    }

    public function testCreateFileTextNotArrayException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        $lang = [
            "src"    => "en",
            "output" => "fr",
            "name"   => "French",
            "native" => "Française"
        ];

        $locales = [
            [
                "region" => "FR",
                "name"   => "France",
                "native" => "France",
                "text"   => 123
            ]
        ];
        Json::createFile($lang, $locales, __DIR__ . '/fr.json');
    }

    public function testCreateFragment()
    {
        Json::createFragment(__DIR__ . '/fragments/en.txt', __DIR__ . '/fragments/fr.txt');
        $this->assertFileExists(__DIR__ . '/fragments/fr.json');
        unlink(__DIR__ . '/fragments/fr.json');
    }

    public function testCreateFragmentNoSourceException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        Json::createFragment(__DIR__ . '/fragments/bad.txt', __DIR__ . '/fragments/fr.txt');
    }

    public function testCreateFragmentNoOutputException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        Json::createFragment(__DIR__ . '/fragments/en.txt', __DIR__ . '/fragments/bad.txt');
    }

    public function testCreateFragmentNoTargetDirException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        Json::createFragment(__DIR__ . '/fragments/en.txt', __DIR__ . '/fragments/fr.txt', 'baddir');
    }

}