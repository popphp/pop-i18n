pop-i18n
========

[![Build Status](https://github.com/popphp/pop-i18n/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-i18n/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-i18n)](http://cc.popphp.org/pop-i18n/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Alternates](#alternates)
* [Advanced](#advanced)
* [Additional Methods](#additional-methods)
* [Errors](#errors)

Overview
--------
`pop-i18n` is a component for internationalization and localization. It provides the features for
translating and managing different languages and locales that may be required for an application.
It also provides for parameters to be injected into the text for personalization.

`pop-i18n` is a component of the [Pop PHP Framework](https://www.popphp.org/).

[Top](#pop-i18n)

Install
-------

Install `pop-i18n` using Composer.

    composer require popphp/pop-i18n

Or, require it in your composer.json file

    "require": {
        "popphp/pop-i18n" : "^4.1.0"
    }

[Top](#pop-i18n)

Quickstart
----------

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

Notice the file is named `fr.xml` — after the *language* only, not the full locale. A single language file
can hold more than one `<locale>` (XML) or `locale` (JSON) entry, one per region, and `pop-i18n` picks whichever
one's `region` matches the locale you pass in. So `fr_FR` and `fr_CA` would both live as separate `<locale>`
blocks inside that same `fr.xml`, not as two separate files.

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

If no language file matches, or a given string isn't found in the loaded catalog, `__()`/`_e()` simply return
the original string unchanged. It's always safe to call them on any string — translated or not.

[Top](#pop-i18n)

Alternates
----------

A `source` string can have more than one possible `output` — useful for alternate phrasings, formality levels,
and the like:

##### fr.xml

```xml
<language src="en" output="fr" name="French" native="Français">
    <locale region="FR" name="France" native="France">
        <text>
            <source>Hello, how are you?</source>
            <output>
                <output>Bonjour, comment allez-vous?</output>
                <output alt="informal">Salut, ça va?</output>
            </output>
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
                    "source" : "Hello, how are you?",
                    "output" : {
                        "formal"   : "Bonjour, comment allez-vous?",
                        "informal" : "Salut, ça va?"
                    }
                }
            ]
        }]
    }
}
```

Pass a third argument to `__()`/`_e()` to select an alternate by its `alt` key (XML) or object key (JSON).
Alternates with no `alt` attribute (XML) or in a plain JSON array are selected by their numeric position
(`0`, `1`, `2`, ...) instead. If no variation is given, or the given variation doesn't match anything, the
first defined output is used.

```php
$lang = new I18n('fr_FR', '/path/to/language/files');

echo $lang->__('Hello, how are you?');                    // Bonjour, comment allez-vous?  (default)
echo $lang->__('Hello, how are you?', null, 'informal');  // Salut, ça va?
```

[Top](#pop-i18n)

Advanced
--------

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
Format\Xml::createFile($lang, $locales, '/path/to/language/files/de.xml');

// Create in JSON format
Format\Json::createFile($lang, $locales, '/path/to/language/files/de.json');
```

Also, if you have a source text file and an output text file with a 1:1 line-by-line ratio,
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

Note that `createFragment()` produces a **fragment**, not a complete, loadable language file — `I18n` can't
load it directly. The XML fragment is a series of bare `<text>` elements with no surrounding
`<language>`/`<locale>` wrapper:

```xml
        <text>
            <source>This field is required.</source>
            <output>Dieses Feld ist erforderlich.</output>
        </text>
        <text>
            <source>Please enter your name.</source>
            <output>Bitte geben Sie Ihren Namen ein.</output>
        </text>
```

and the JSON fragment is a bare `"text": [...]` array, which isn't valid JSON on its own. Paste the fragment's
`<text>` elements into the `<locale>...</locale>` block of a hand-assembled XML file, or the fragment's entries
into the `text` array of the `$locales` structure you pass to `createFile()`, to produce a file `I18n` can
actually load.

[Top](#pop-i18n)

Additional Methods
-------------------

### Getting the List of Languages

`I18n::getLanguages()` is a static method that scans every `.xml`/`.json` file in a directory and returns a
sorted map of locale key to display name — handy for populating a language-selector dropdown. It doesn't
require an `I18n` instance:

```php
use Pop\I18n\I18n;

$languages = I18n::getLanguages('/path/to/language/files');
```

    [
        'fr_FR' => 'Français, France (French, France)',
    ]

### Loading a File Directly

Normally, the directory and language passed to the constructor are enough — `I18n` finds and loads the
matching file automatically. If you need to load a specific file directly instead, bypassing that
auto-detection, use `loadFile()`:

```php
use Pop\I18n\I18n;

$lang = new I18n('fr_FR');
$lang->loadFile('/path/to/language/files/fr.xml');
```

[Top](#pop-i18n)

Errors
------

`Pop\I18n\Exception` is thrown by `I18n::loadFile()` (and so, indirectly, by the constructor) when the given
path doesn't exist or isn't a `.xml`/`.json` file.

`Pop\I18n\Format\Exception` is thrown by `Format\Xml`/`Format\Json`'s `createFile()` and `createFragment()`
methods when the given data doesn't match the expected shape — missing `src`/`output`/`region`/`text`/`source`/
`output` keys, or missing source/target files or directories.

Note that malformed XML passed to `loadFile()` throws PHP's built-in `\Exception` directly rather than
`Pop\I18n\Exception`, since it comes from `SimpleXMLElement`'s own parser.
