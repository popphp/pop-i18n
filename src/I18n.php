<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
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
     * Language content
     * @var array
     */
    protected array $content = [
        'source' => [],
        'output' => []
    ];

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
     * Load language content from an XML file
     *
     * @param  string $langFile
     * @throws Exception|\Exception
     * @return void
     */
    public function loadFile(string $langFile): void
    {
        // If an XML file
        if (file_exists($langFile) && (stripos($langFile, '.xml') !== false)) {
            if (($xml =@ new SimpleXMLElement($langFile, LIBXML_NOWARNING, true)) !== false) {
                $key    = 0;
                $length = count($xml->locale);

                // Find the locale node key
                for ($i = 0; $i < $length; $i++) {
                    if ($this->locale == (string)$xml->locale[$i]->attributes()->region) {
                        $key = $i;
                    }
                }

                // If the locale node matches the current locale
                if ($this->locale == (string)$xml->locale[$key]->attributes()->region) {
                    foreach ($xml->locale[$key]->text as $text) {
                        if (isset($text->source) && isset($text->output)) {
                            $this->content['source'][] = (string)$text->source;
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

                                $this->content['output'][] = $alternates;
                            } else {
                                $this->content['output'][] = (string)$text->output;
                            }
                        }
                    }
                }
            }
        // Else if a JSON file
        } else if (file_exists($langFile) && (stripos($langFile, '.json') !== false)) {
            $json = json_decode(file_get_contents($langFile), true);

            $key    = 0;
            $length = count($json['language']['locale']);

            // Find the locale node key
            for ($i = 0; $i < $length; $i++) {
                if ($this->locale == $json['language']['locale'][$i]['region']) {
                    $key = $i;
                }
            }

            if ($this->locale == $json['language']['locale'][$key]['region']) {
                foreach ($json['language']['locale'][$key]['text'] as $text) {
                    if (isset($text['source']) && isset($text['output'])) {
                        $this->content['source'][] = (string)$text['source'];
                        $this->content['output'][] = (is_array($text['output'])) ? $text['output'] : (string)$text['output'];
                    }
                }
            }
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
     * Get languages from the XML files
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
                if (stripos($file, '.xml')) {
                    if (($xml =@ new SimpleXMLElement($langDirectory . DIRECTORY_SEPARATOR . $file, LIBXML_NOWARNING, true)) !== false) {
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
                    }
                } else if (stripos($file, '.json')) {
                    $json = json_decode(file_get_contents($langDirectory . DIRECTORY_SEPARATOR . $file), true);
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
    protected function translate(string $str, string|array|null$params = null, mixed $variation = null): string
    {
        $key   = array_search($str, $this->content['source']);
        $trans = null;

        if (($key !== false) && isset($this->content['output'][$key])) {
            if (($variation !== null) && isset($this->content['output'][$key][$variation])) {
                $trans = $this->content['output'][$key][$variation];
            } else {
                $trans = (is_array($this->content['output'][$key])) ?
                    reset($this->content['output'][$key]) : $this->content['output'][$key];
            }
        }

        if ($trans === null) {
            $trans = $str;
        }

        if ($params !== null) {
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $trans = str_replace('%' . ($key + 1), $value, $trans);
                }
            } else {
                $trans = str_replace('%1', $params, $trans);
            }
        }

        return $trans;
    }

    /**
     * Get language content from the XML file
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

}
