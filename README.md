pop-i18n
========

[![Build Status](https://travis-ci.org/popphp/pop-i18n.svg?branch=master)](https://travis-ci.org/popphp/pop-i18n)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-i18n)](http://cc.popphp.org/pop-i18n/)

OVERVIEW
--------
`pop-i18n` is a component for internationalization and localization. It provides the features for
translating and managing different languages and locales that may be required for an application.
It also provides for parameters to be injected into the text for personalization.

`pop-i18n` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-i18n` using Composer.

    composer require popphp/pop-i18n

BASIC USAGE
-----------

First, you will have to create your language and locale files. The accepted formats are either XML or JSON:

##### fr.xml

```xml
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE language [
        <!ELEMENT language ANY>
        <!ELEMENT locale ANY>
        <!ELEMENT text (source,output)>
        <!ELEMENT source ANY>
        <!ELEMENT output ANY>
        <!ATTLIST language
                src       CDATA    #REQUIRED
                output    CDATA    #REQUIRED
                name      CDATA    #REQUIRED
                native    CDATA    #REQUIRED
                >
        <!ATTLIST locale
                region    CDATA    #REQUIRED
                name      CDATA    #REQUIRED
                native    CDATA    #REQUIRED
                >
        ]>
<language src="en" output="fr" name="French" native="Français">
    <locale region="FR" name="France" native="France">
        <text>
            <source>Hello, my name is %1. I love to program %2.</source>
            <output>Bonjour, mon nom est %1. Je aime programmer %2.</output>
        </text>
    </locale>
</language>
```

##### fr.json

```json
{
    "language"  : {
        "src"    : "en",
        "output" : "fr",
        "name"   : "French",
        "native" : "Français",
        "locale" : [{
            "region" : "FR",
            "name"   : "France",
            "native" : "France",
            "text"   : [
                {
                    "source" : "Hello, my name is %1. I love to program %2.",
                    "output" : "Bonjour, mon nom est %1. Je aime programmer %2."
                }
            ]
        }]
    }
}
```

From there, you can create your I18n object and give it the folder with the language files in it.
It will auto-detect which file to load based on the language passed.

```php
use Pop\I18n\I18n;

$lang = new I18n('fr_FR', '/path/to/language/files');

$string = $lang->__('Hello, my name is %1. I love to program %2.', ['Nick', 'PHP']);
echo $string;
```

    Bonjour, mon nom est Nick. Je aime programmer PHP.

Alternatively, you can directly echo the string out like this:

```php
$lang->_e('Hello, my name is %1. I love to program %2.', ['Nick', 'PHP']);
```

You can set the language and locale when you instantiate the I18n object like above,
or if you prefer, you can set it in your application as a constant `POP_LANG` and
the I18n object will look for that as well. The default is `en_US`.


ADVANCED USAGE
--------------

The `pop-i18n` component comes with the functionality to assist you in generating your
required language files. Knowing the time and possibly money required to translate
your application's text into multiple languages, the component can help with assembling
the language files once you have the content.

You can give it arrays of data to generate complete files:

```php
use Pop\I18n\Format;

$lang = [
    'src'    => 'en',
    'output' => 'de',
    'name'   => 'German',
    'native' => 'Deutsch'
];

$locales = [
    [
        'region' => 'DE',
        'name'   => 'Germany',
        'native' => 'Deutschland',
        'text' => [
            [
                'source' => 'This field is required.',
                'output' => 'Dieses Feld ist erforderlich.'
            ],
            [
                'source' => 'Please enter your name.',
                'output' => 'Bitte geben Sie Ihren Namen ein.'
            ]
        ]
    ]
];

// Create the XML format
Format\Xml::createFile($lang, $locale, '/path/to/language/files/de.xml');

// Create in JSON format
Format\Json::createFile($lang, $locale, '/path/to/language/files/de.json');
```

Also, if you have a a source text file and an output text file with a 1:1 line-by-line ratio,
then you can create the language files in fragment set and merge them as needed. An example
of a 1:1 ratio source-to-output text files:

| source/en.txt           | output/de.txt                    |
|-------------------------|----------------------------------|
| This field is required. | Dieses Feld ist erforderlich.    |
| Please enter your name. | Bitte geben Sie Ihren Namen ein. |

So then, you can do this:

```php
use Pop\I18n\Format;

// Create the XML format fragment
Format\Xml::createFragment('source/en.txt', 'output/de.txt', '/path/to/files/');

// Create the JSON format fragment
Format\Json::createFragment('source/en.txt', 'output/de.txt', '/path/to/files/');
```

And merge the fragments into a main language file.
