<?php

namespace Pop\I18n\Test;

use Pop\I18n\I18n;
use Pop\I18n\Format\Xml;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class XmlFormatTest extends TestCase
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

        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
        $this->assertFileExists(__DIR__ . '/fr.xml');

        $i18n = new I18n('fr');
        $i18n->loadFile(__DIR__ . '/fr.xml');
        $this->assertEquals('Bonjour, comment allez-vous?', $i18n->__('Hello, how are you?'));

        unlink(__DIR__ . '/fr.xml');
    }

    public function testCreateFileWithAlts()
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
                        "output" => [
                            "Bonjour, comment allez-vous?",
                            "Bonjour, mon amie, comment allez-vous?"
                        ]
                    ],
                    [
                        "source" => "Hello, how are you, %1?",
                        "output" => "Bonjour, comment allez-vous, %1?"
                    ],
                    [
                        "source" => "I'm fine, %1. How's %2?",
                        "output" => [
                            "primary"   => "Je vais bien, %1. Comment est %2?",
                            "secondary" => "Bien, %1. Comment est %2?",
                        ]
                    ]
                ]
            ]
        ];

        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
        $this->assertFileExists(__DIR__ . '/fr.xml');

        $i18n = new I18n('fr');
        $i18n->loadFile(__DIR__ . '/fr.xml');
        $this->assertEquals('Bonjour, mon amie, comment allez-vous?', $i18n->__('Hello, how are you?', null, 1));
        $this->assertEquals('Bien, Nick. Comment est Krissy?', $i18n->__("I'm fine, %1. How's %2?", ['Nick', 'Krissy'], 'secondary'));

        unlink(__DIR__ . '/fr.xml');
    }

    public function testCreateFileEscapesXmlSpecialCharacters()
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
                        "source" => "AT&T < IBM",
                        "output" => "AT&T < IBM (FR)"
                    ]
                ]
            ]
        ];

        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
        $this->assertFileExists(__DIR__ . '/fr.xml');

        $i18n = new I18n('fr');
        $i18n->loadFile(__DIR__ . '/fr.xml');
        $this->assertEquals('AT&T < IBM (FR)', $i18n->__('AT&T < IBM'));

        unlink(__DIR__ . '/fr.xml');
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
        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
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
        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
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
        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
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
        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
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
        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
    }

    public function testCreateFileNoTextSourceException()
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
                "text"   => [
                    [
                        "output" => "Bonjour, comment allez-vous?"
                    ]
                ]
            ]
        ];

        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
    }

    public function testCreateFileNoTextOutputException()
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
                "text"   => [
                    [
                        "source" => "Hello, how are you?"
                    ]
                ]
            ]
        ];

        Xml::createFile($lang, $locales, __DIR__ . '/fr.xml');
    }

    public function testCreateFragmentEscapesXmlSpecialCharacters()
    {
        $sourceFile = __DIR__ . '/fragments/escape-en.txt';
        $outputFile = __DIR__ . '/fragments/escape-fr.txt';

        file_put_contents($sourceFile, 'AT&T < IBM');
        file_put_contents($outputFile, 'AT&T < IBM (FR)');

        Xml::createFragment($sourceFile, $outputFile);

        $fragment = file_get_contents(__DIR__ . '/fragments/escape-fr.xml');
        $xml      = new SimpleXMLElement('<root>' . $fragment . '</root>');

        $this->assertEquals('AT&T < IBM', (string)$xml->text->source);
        $this->assertEquals('AT&T < IBM (FR)', (string)$xml->text->output);

        unlink($sourceFile);
        unlink($outputFile);
        unlink(__DIR__ . '/fragments/escape-fr.xml');
    }

    public function testCreateFragment()
    {
        Xml::createFragment(__DIR__ . '/fragments/en.txt', __DIR__ . '/fragments/fr.txt');
        $this->assertFileExists(__DIR__ . '/fragments/fr.xml');
        unlink(__DIR__ . '/fragments/fr.xml');
    }

    public function testCreateFragmentWithBareFilenameOutput()
    {
        $cwd = getcwd();
        chdir(__DIR__ . '/fragments');

        try {
            file_put_contents('bare-en.txt', 'Hello');
            file_put_contents('bare-fr.txt', 'Bonjour');

            Xml::createFragment('bare-en.txt', 'bare-fr.txt');

            $this->assertFileExists('bare-fr.xml');

            unlink('bare-en.txt');
            unlink('bare-fr.txt');
            unlink('bare-fr.xml');
        } finally {
            chdir($cwd);
        }
    }

    public function testCreateFragmentWithBackslashInOutputPath()
    {
        $cwd = getcwd();
        chdir(__DIR__ . '/fragments');

        try {
            // A backslash is a valid filename character on Linux, so this
            // exercises the Windows-style-path branch without relying on OS.
            $outputName = 'win\\bslash-fr.txt';

            file_put_contents('bslash-en.txt', 'Hello');
            file_put_contents($outputName, 'Bonjour');

            Xml::createFragment('bslash-en.txt', $outputName);

            $this->assertFileExists('bslash-fr.xml');

            unlink('bslash-en.txt');
            unlink($outputName);
            unlink('bslash-fr.xml');
        } finally {
            chdir($cwd);
        }
    }

    public function testCreateFragmentSkipsBlankLines()
    {
        $sourceFile = __DIR__ . '/fragments/blank-en.txt';
        $outputFile = __DIR__ . '/fragments/blank-fr.txt';

        file_put_contents($sourceFile, 'Hello' . PHP_EOL . '' . PHP_EOL . 'Goodbye');
        file_put_contents($outputFile, 'Bonjour' . PHP_EOL . '' . PHP_EOL . 'Au revoir');

        Xml::createFragment($sourceFile, $outputFile);

        $fragment = file_get_contents(__DIR__ . '/fragments/blank-fr.xml');
        $this->assertSame(2, substr_count($fragment, '<text>'));

        unlink($sourceFile);
        unlink($outputFile);
        unlink(__DIR__ . '/fragments/blank-fr.xml');
    }

    public function testCreateFragmentNoSourceException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        Xml::createFragment(__DIR__ . '/fragments/bad.txt', __DIR__ . '/fragments/fr.txt');
    }

    public function testCreateFragmentNoOutputException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        Xml::createFragment(__DIR__ . '/fragments/en.txt', __DIR__ . '/fragments/bad.txt');
    }

    public function testCreateFragmentNoTargetDirException()
    {
        $this->expectException('Pop\I18n\Format\Exception');
        Xml::createFragment(__DIR__ . '/fragments/en.txt', __DIR__ . '/fragments/fr.txt', 'baddir');
    }

}