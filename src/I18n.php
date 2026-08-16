<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\I18n;

use SimpleXMLElement;

/**
 * I18n and l10n class
 *
 * @category   Pop
 * @package    Pop_I18n
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class I18n
{

    /**
     * Directory with language files in it
     * @var ?string
     */
    protected ?string $directory = null;

    /**
     * Default system language
     * @var ?string
     */
    protected ?string $language = null;

    /**
     * Default system locale
     * @var string
     */
    protected ?string $locale = null;

    /**
     * Language content, keyed by source string. Each value is either the
     * translated output string, or an array of alternate outputs (keyed by
     * their 'alt' name when named, otherwise numerically).
     * @var array
     */
    protected array $content = [];

    /**
     * Constructor
     *
     * Instantiate the I18n object
     *
     * @param  ?string $lang
     * @param  ?string $dir
     */
    public function __construct(?string $lang = null, ?string $dir = null)
    {
        if ($lang === null) {
            $lang = (defined('POP_LANG')) ? POP_LANG : 'en_US';
        }

        if (str_contains($lang, '_')) {
            [$language, $locale] = explode('_', $lang);
            $this->language = $language;
            $this->locale   = $locale;
        } else {
            $this->language = $lang;
            $this->locale   = strtoupper($lang);
        }

        $this->directory = (($dir !== null) && file_exists($dir)) ? realpath($dir) . DIRECTORY_SEPARATOR
            : __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR;

        $this->loadCurrentLanguage();
    }

    /**
     * Get current language setting
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Get current locale setting
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Load language content from an XML or JSON file
     *
     * @param  string $langFile
     * @throws Exception|\Exception
     * @return void
     */
    public function loadFile(string $langFile): void
    {
        if (file_exists($langFile) && (stripos($langFile, '.xml') !== false)) {
            $this->loadXmlFile($langFile);
        } else if (file_exists($langFile) && (stripos($langFile, '.json') !== false)) {
            $this->loadJsonFile($langFile);
        } else {
            throw new Exception('Error: The language file ' . $langFile . ' does not exist or is not valid.');
        }
    }

    /**
     * Return the translated string
     *
     * @param  string            $str
     * @param  string|array|null $params
     * @param  mixed             $variation
     * @return string
     */
    public function __(string $str, string|array|null $params = null, mixed $variation = null): string
    {
        return $this->translate($str, $params, $variation);
    }

    /**
     * Echo the translated string
     *
     * @param  string            $str
     * @param  string|array|null $params
     * @param  mixed             $variation
     * @return void
     */
    public function _e(string $str, string|array|null $params = null, mixed $variation = null): void
    {
        echo $this->translate($str, $params, $variation);
    }

    /**
     * Get languages from the language files in the directory
     *
     * @param string $dir
     * @return array
     * @throws \Exception
     */
    public static function getLanguages(string $dir): array
    {
        $langsAry      = [];
        $langDirectory = $dir;

        if (file_exists($langDirectory)) {
            $files = scandir($langDirectory);
            foreach ($files as $file) {
                if (stripos($file, '.xml') !== false) {
                    $langsAry = array_merge($langsAry, self::getXmlLanguages($langDirectory . DIRECTORY_SEPARATOR . $file));
                } else if (stripos($file, '.json') !== false) {
                    $langsAry = array_merge($langsAry, self::getJsonLanguages($langDirectory . DIRECTORY_SEPARATOR . $file));
                }
            }
        }

        ksort($langsAry);
        return $langsAry;
    }

    /**
     * Translate and return the string
     *
     * @param  string            $str
     * @param  string|array|null $params
     * @param  mixed             $variation
     * @return string
     */
    protected function translate(string $str, string|array|null $params = null, mixed $variation = null): string
    {
        $trans = null;

        if (isset($this->content[$str])) {
            $output = $this->content[$str];
            if (($variation !== null) && is_array($output) && isset($output[$variation])) {
                $trans = $output[$variation];
            } else {
                $trans = (is_array($output)) ? reset($output) : $output;
            }
        }

        if ($trans === null) {
            $trans = $str;
        }

        if ($params !== null) {
            $replacements = [];
            foreach ((array)$params as $key => $value) {
                $replacements['%' . ($key + 1)] = $value;
            }
            $trans = strtr($trans, $replacements);
        }

        return $trans;
    }

    /**
     * Get language content from the current language/locale's file, if it exists
     *
     * @throws Exception
     * @return void
     */
    protected function loadCurrentLanguage(): void
    {
        if (file_exists($this->directory . $this->language . '.xml')) {
            $this->loadFile($this->directory . $this->language . '.xml');
        } else if (file_exists($this->directory . $this->language . '.json')) {
            $this->loadFile($this->directory . $this->language . '.json');
        }
    }

    /**
     * Load language content from an XML file into $content
     *
     * @param  string $langFile
     * @throws \Exception
     * @return void
     */
    protected function loadXmlFile(string $langFile): void
    {
        $xml = @new SimpleXMLElement($langFile, LIBXML_NOWARNING, true);
        $key = null;
        $i   = 0;

        // Find the locale node key matching the current locale
        // (SimpleXMLElement's foreach key is the tag name, not a position, so track it manually)
        foreach ($xml->locale as $locale) {
            if ($this->locale == (string)$locale->attributes()->region) {
                $key = $i;
                break;
            }
            $i++;
        }

        if ($key !== null) {
            foreach ($xml->locale[$key]->text as $text) {
                if (isset($text->source) && isset($text->output)) {
                    $source = (string)$text->source;

                    if (isset($text->output->output)) {
                        $alternates = [];

                        foreach ($text->output->output as $output) {
                            $alt = $output->attributes()->alt;
                            if ($alt !== null) {
                                $alternates[(string)$alt] = (string)$output;
                            } else {
                                $alternates[] = (string)$output;
                            }
                        }

                        $this->content[$source] = $alternates;
                    } else {
                        $this->content[$source] = (string)$text->output;
                    }
                }
            }
        }
    }

    /**
     * Load language content from a JSON file into $content
     *
     * @param  string $langFile
     * @return void
     */
    protected function loadJsonFile(string $langFile): void
    {
        $json = json_decode(file_get_contents($langFile), true);
        $key  = null;

        // Find the locale node key matching the current locale
        foreach ($json['language']['locale'] as $i => $locale) {
            if ($this->locale == $locale['region']) {
                $key = $i;
                break;
            }
        }

        if ($key !== null) {
            foreach ($json['language']['locale'][$key]['text'] as $text) {
                if (isset($text['source']) && isset($text['output'])) {
                    $this->content[(string)$text['source']] = (is_array($text['output'])) ? $text['output'] : (string)$text['output'];
                }
            }
        }
    }

    /**
     * Get language info from a single XML language file
     *
     * @param  string $file
     * @return array
     * @throws \Exception
     */
    protected static function getXmlLanguages(string $file): array
    {
        $langsAry = [];

        $xml        =@ new SimpleXMLElement($file, LIBXML_NOWARNING, true);
        $lang       = (string)$xml->attributes()->output;
        $langName   = (string)$xml->attributes()->name;
        $langNative = (string)$xml->attributes()->native;

        foreach ($xml->locale as $locale) {
            $region = (string)$locale->attributes()->region;
            $name   = (string)$locale->attributes()->name;
            $native = (string)$locale->attributes()->native;
            $native .= ' (' . $langName . ', ' . $name . ')';
            $langsAry[$lang . '_' . $region] = $langNative . ', ' . $native;
        }

        return $langsAry;
    }

    /**
     * Get language info from a single JSON language file
     *
     * @param  string $file
     * @return array
     */
    protected static function getJsonLanguages(string $file): array
    {
        $langsAry = [];

        $json       = json_decode(file_get_contents($file), true);
        $lang       = $json['language']['output'];
        $langName   = $json['language']['name'];
        $langNative = $json['language']['native'];

        foreach ($json['language']['locale'] as $locale) {
            $region = $locale['region'];
            $name   = $locale['name'];
            $native = $locale['native'];
            $native .= ' (' . $langName . ', ' . $name . ')';
            $langsAry[$lang . '_' . $region] = $langNative . ', ' . $native;
        }

        return $langsAry;
    }

}
