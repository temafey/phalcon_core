<?php

namespace Engine\Tools;


class Inflector {

    /**
     * Plural inflector rules
     *
     * @var array
     */
    protected static $plural = array(
        'rules' => array(
            '/(s)tatus$/i' => '\1\2tatuses',
            '/(quiz)$/i' => '\1zes',
            '/^(ox)$/i' => '\1\2en',
            '/([m|l])ouse$/i' => '\1ice',
            '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(hive)$/i' => '\1s',
            '/(?:([^f])fe|([lre])f)$/i' => '\1\2ves',
            '/sis$/i' => 'ses',
            '/([ti])um$/i' => '\1a',
            '/(p)erson$/i' => '\1eople',
            '/(m)an$/i' => '\1en',
            '/(c)hild$/i' => '\1hildren',
            '/(buffal|tomat)o$/i' => '\1\2oes',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
            '/us$/i' => 'uses',
            '/(alias)$/i' => '\1es',
            '/(ax|cris|test)is$/i' => '\1es',
            '/s$/' => 's',
            '/^$/' => '',
            '/$/' => 's',
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'people'
        ),
        'irregular' => array(
            'atlas' => 'atlases',
            'beef' => 'beefs',
            'brief' => 'briefs',
            'brother' => 'brothers',
            'cafe' => 'cafes',
            'child' => 'children',
            'cookie' => 'cookies',
            'corpus' => 'corpuses',
            'cow' => 'cows',
            'ganglion' => 'ganglions',
            'genie' => 'genies',
            'genus' => 'genera',
            'graffito' => 'graffiti',
            'hoof' => 'hoofs',
            'loaf' => 'loaves',
            'man' => 'men',
            'money' => 'monies',
            'mongoose' => 'mongooses',
            'move' => 'moves',
            'mythos' => 'mythoi',
            'niche' => 'niches',
            'numen' => 'numina',
            'occiput' => 'occiputs',
            'octopus' => 'octopuses',
            'opus' => 'opuses',
            'ox' => 'oxen',
            'penis' => 'penises',
            'person' => 'people',
            'sex' => 'sexes',
            'soliloquy' => 'soliloquies',
            'testis' => 'testes',
            'trilby' => 'trilbys',
            'turf' => 'turfs',
            'potato' => 'potatoes',
            'hero' => 'heroes',
            'tooth' => 'teeth',
            'goose' => 'geese',
            'foot' => 'feet'
        )
    );

    /**
     * Singular inflector rules
     *
     * @var array
     */
    protected static $singular = array(
        'rules' => array(
            '/(s)tatuses$/i' => '\1\2tatus',
            '/^(.*)(menu)s$/i' => '\1\2',
            '/(quiz)zes$/i' => '\\1',
            '/(matr)ices$/i' => '\1ix',
            '/(vert|ind)ices$/i' => '\1ex',
            '/^(ox)en/i' => '\1',
            '/(alias)(es)*$/i' => '\1',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
            '/([ftw]ax)es/i' => '\1',
            '/(cris|ax|test)es$/i' => '\1is',
            '/(shoe|slave)s$/i' => '\1',
            '/(o)es$/i' => '\1',
            '/ouses$/' => 'ouse',
            '/([^a])uses$/' => '\1us',
            '/([m|l])ice$/i' => '\1ouse',
            '/(x|ch|ss|sh)es$/i' => '\1',
            '/(m)ovies$/i' => '\1\2ovie',
            '/(s)eries$/i' => '\1\2eries',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/(tive)s$/i' => '\1',
            '/(hive)s$/i' => '\1',
            '/(drive)s$/i' => '\1',
            '/([le])ves$/i' => '\1f',
            '/([^rfo])ves$/i' => '\1fe',
            '/(^analy)ses$/i' => '\1sis',
            '/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/([ti])a$/i' => '\1um',
            '/(p)eople$/i' => '\1\2erson',
            '/(m)en$/i' => '\1an',
            '/(c)hildren$/i' => '\1\2hild',
            '/(n)ews$/i' => '\1\2ews',
            '/eaus$/' => 'eau',
            '/^(.*us)$/' => '\\1',
            '/s$/i' => ''
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss'
        ),
        'irregular' => array(
            'foes' => 'foe',
            'waves' => 'wave',
        )
    );

    /**
     * Words that should not be inflected
     *
     * @var array
     */
    protected static $uninflected = array(
        'Amoyese', 'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus',
        'carp', 'chassis', 'clippers', 'cod', 'coitus', 'Congoese', 'contretemps', 'corps',
        'debris', 'diabetes', 'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder',
        'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
        'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings',
        'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese', '.*?media',
        'metadata', 'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese',
        'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese',
        'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors',
        'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens', 'species', 'swine', 'testes',
        'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese', 'whiting', 'wildebeest',
        'Yengeese'
    );

    /**
     * Default map of accented and special characters to ASCII characters
     *
     * @var array
     */
    protected static $transliteration = array(
        '/ä|æ|ǽ/' => 'ae',
        '/ö|œ/' => 'oe',
        '/ü/' => 'ue',
        '/Ä/' => 'Ae',
        '/Ü/' => 'Ue',
        '/Ö/' => 'Oe',
        '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
        '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
        '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
        '/ç|ć|ĉ|ċ|č/' => 'c',
        '/Ð|Ď|Đ/' => 'D',
        '/ð|ď|đ/' => 'd',
        '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
        '/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
        '/Ĝ|Ğ|Ġ|Ģ/' => 'G',
        '/ĝ|ğ|ġ|ģ/' => 'g',
        '/Ĥ|Ħ/' => 'H',
        '/ĥ|ħ/' => 'h',
        '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
        '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
        '/Ĵ/' => 'J',
        '/ĵ/' => 'j',
        '/Ķ/' => 'K',
        '/ķ/' => 'k',
        '/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
        '/ĺ|ļ|ľ|ŀ|ł/' => 'l',
        '/Ñ|Ń|Ņ|Ň/' => 'N',
        '/ñ|ń|ņ|ň|ŉ/' => 'n',
        '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
        '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
        '/Ŕ|Ŗ|Ř/' => 'R',
        '/ŕ|ŗ|ř/' => 'r',
        '/Ś|Ŝ|Ş|Ș|Š/' => 'S',
        '/ś|ŝ|ş|ș|š|ſ/' => 's',
        '/Ţ|Ț|Ť|Ŧ/' => 'T',
        '/ţ|ț|ť|ŧ/' => 't',
        '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
        '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
        '/Ý|Ÿ|Ŷ/' => 'Y',
        '/ý|ÿ|ŷ/' => 'y',
        '/Ŵ/' => 'W',
        '/ŵ/' => 'w',
        '/Ź|Ż|Ž/' => 'Z',
        '/ź|ż|ž/' => 'z',
        '/Æ|Ǽ/' => 'AE',
        '/ß/' => 'ss',
        '/Ĳ/' => 'IJ',
        '/ĳ/' => 'ij',
        '/Œ/' => 'OE',
        '/ƒ/' => 'f'
    );

    /**
     * Map of cyrillic characters
     *
     * @var array
     */
    protected static $cyrillicTransliteration = [
        '/а/' => 'a', '/А/' => 'A',
        '/б/' => 'b', '/Б/' => 'B',
        '/в/' => 'v', '/В/' => 'V',
        '/г/' => 'g', '/Г/' => 'G',
        '/д/' => 'd', '/Д/' => 'D',
        '/е/' => 'e', '/Е/' => 'E',
        '/ё/' => 'e', '/Ё/' => 'E',
        '/ж/' => 'j', '/Ж/' => 'J',
        '/з/' => 'z', '/З/' => 'Z',
        '/и/' => 'i', '/И/' => 'I',
        '/й/' => 'y', '/Й/' => 'Y',
        '/к/' => 'k', '/К/' => 'K',
        '/л/' => 'l', '/Л/' => 'L',
        '/м/' => 'm', '/М/' => 'M',
        '/н/' => 'n', '/Н/' => 'N',
        '/о/' => 'o', '/О/' => 'O',
        '/п/' => 'p', '/П/' => 'P',
        '/р/' => 'r', '/Р/' => 'R',
        '/с/' => 's', '/С/' => 'S',
        '/т/' => 't', '/Т/' => 'T',
        '/у/' => 'u', '/У/' => 'U',
        '/ф/' => 'f', '/Ф/' => 'F',
        '/х/' => 'h', '/Х/' => 'H',
        '/ц/' => 'ts', '/Ц/' => 'Ts',
        '/ч/' => 'ch', '/Ч/' => 'Ch',
        '/ш/' => 'sh', '/Ш/' => 'Sh',
        '/щ/' => 'sch', '/Щ/' => 'Sch',
        '/ъ/' => 'y', '/Ъ/' => '',
        '/ы/' => 'yi', '/Ы/' => 'Yi',
        '/ь/' => '', '/Ь/' => '',
        '/э/' => 'e', '/Э/' => 'E',
        '/ю/' => 'yu', '/Ю/' => 'Yu',
        '/я/' => 'ya', '/Я/' => 'Ya'
    ];

    /**
     * Method cache array.
     *
     * @var array
     */
    protected static $cache = array();

    /**
     * The initial state of Inflector so reset() works.
     *
     * @var array
     */
    protected static $initialState = array();

    /**
     * Cache inflected values, and return if already available
     *
     * @param $type
     * @param $key
     * @param bool $value
     * @return bool
     */
    protected static function _cache($type, $key, $value = false) {
        $key = '_' . $key;
        $type = '_' . $type;
        if ($value !== false) {
            self::$cache[$type][$key] = $value;
            return $value;
        }
        if (!isset(self::$cache[$type][$key])) {
            return false;
        }
        return self::$cache[$type][$key];
    }

    /**
     * Clears Inflectors inflected value caches. And resets the inflection
     * rules to the initial values.
     *
     * @return void
     */
    public static function reset() {
        if (empty(self::$initialState)) {
            self::$initialState = get_class_vars('Categoryzator\Core\Inflector');
            return;
        }
        foreach (self::$initialState as $key => $val) {
            if ($key !== '_initialState') {
                self::${$key} = $val;
            }
        }
    }

    /**
     * Adds custom inflection $rules, of either 'plural', 'singular' or 'transliteration' $type.
     *
     * ### Usage:
     *
     * {{{
     * Inflector::rules('plural', array('/^(inflect)or$/i' => '\1ables'));
     * Inflector::rules('plural', array(
     * 'rules' => array('/^(inflect)ors$/i' => '\1ables'),
     * 'uninflected' => array('dontinflectme'),
     * 'irregular' => array('red' => 'redlings')
     * ));
     * Inflector::rules('transliteration', array('/å/' => 'aa'));
     * }}}
     *
     * @param string $type The type of inflection, either 'plural', 'singular' or 'transliteration'
     * @param array $rules Array of rules to be added.
     * @param boolean $reset If true, will unset default inflections for all
     * new rules that are being defined in $rules.
     * @return void
     */
    public static function rules($type, $rules, $reset = false) {
        $var = '_' . $type;

        switch ($type) {
            case 'transliteration':
                if ($reset) {
                    self::$transliteration = $rules;
                } else {
                    self::$transliteration = $rules + self::$transliteration;
                }
                break;

            default:
                foreach ($rules as $rule => $pattern) {
                    if (is_array($pattern)) {
                        if ($reset) {
                            self::${$var}[$rule] = $pattern;
                        } else {
                            if ($rule === 'uninflected') {
                                self::${$var}[$rule] = array_merge($pattern, self::${$var}[$rule]);
                            } else {
                                self::${$var}[$rule] = $pattern + self::${$var}[$rule];
                            }
                        }
                        unset($rules[$rule], self::${$var}['cache' . ucfirst($rule)]);
                        if (isset(self::${$var}['merged'][$rule])) {
                            unset(self::${$var}['merged'][$rule]);
                        }
                        if ($type === 'plural') {
                            self::$cache['pluralize'] = self::$cache['tableize'] = array();
                        } elseif ($type === 'singular') {
                            self::$cache['singularize'] = array();
                        }
                    }
                }
                self::${$var}['rules'] = $rules + self::${$var}['rules'];
        }
    }

    /**
     * Return $word in plural form.
     *
     * @param string $word Word in singular
     * @return string Word in plural
     */
    public static function pluralize($word) {
        if (isset(self::$cache['pluralize'][$word])) {
            return self::$cache['pluralize'][$word];
        }

        if (!isset(self::$plural['merged']['irregular'])) {
            self::$plural['merged']['irregular'] = self::$plural['irregular'];
        }

        if (!isset(self::$plural['merged']['uninflected'])) {
            self::$plural['merged']['uninflected'] = array_merge(self::$plural['uninflected'], self::$uninflected);
        }

        if (!isset(self::$plural['cacheUninflected']) || !isset(self::$plural['cacheIrregular'])) {
            self::$plural['cacheUninflected'] = '(?:' . implode('|', self::$plural['merged']['uninflected']) . ')';
            self::$plural['cacheIrregular'] = '(?:' . implode('|', array_keys(self::$plural['merged']['irregular'])) . ')';
        }

        if (preg_match('/(.*)\\b(' . self::$plural['cacheIrregular'] . ')$/i', $word, $regs)) {
            self::$cache['pluralize'][$word] = $regs[1] . substr($word, 0, 1) . substr(self::$plural['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$cache['pluralize'][$word];
        }

        if (preg_match('/^(' . self::$plural['cacheUninflected'] . ')$/i', $word, $regs)) {
            self::$cache['pluralize'][$word] = $word;
            return $word;
        }

        foreach (self::$plural['rules'] as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                self::$cache['pluralize'][$word] = preg_replace($rule, $replacement, $word);
                return self::$cache['pluralize'][$word];
            }
        }
    }

    /**
     * Return $word in singular form.
     *
     * @param string $word Word in plural
     * @return string Word in singular
     */
    public static function singularize($word) {
        if (isset(self::$cache['singularize'][$word])) {
            return self::$cache['singularize'][$word];
        }

        if (!isset(self::$singular['merged']['uninflected'])) {
            self::$singular['merged']['uninflected'] = array_merge(
                self::$singular['uninflected'],
                self::$uninflected
            );
        }

        if (!isset(self::$singular['merged']['irregular'])) {
            self::$singular['merged']['irregular'] = array_merge(
                self::$singular['irregular'],
                array_flip(self::$plural['irregular'])
            );
        }

        if (!isset(self::$singular['cacheUninflected']) || !isset(self::$singular['cacheIrregular'])) {
            self::$singular['cacheUninflected'] = '(?:' . implode('|', self::$singular['merged']['uninflected']) . ')';
            self::$singular['cacheIrregular'] = '(?:' . implode('|', array_keys(self::$singular['merged']['irregular'])) . ')';
        }

        if (preg_match('/(.*)\\b(' . self::$singular['cacheIrregular'] . ')$/i', $word, $regs)) {
            self::$cache['singularize'][$word] = $regs[1] . substr($word, 0, 1) . substr(self::$singular['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$cache['singularize'][$word];
        }

        if (preg_match('/^(' . self::$singular['cacheUninflected'] . ')$/i', $word, $regs)) {
            self::$cache['singularize'][$word] = $word;
            return $word;
        }

        foreach (self::$singular['rules'] as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                self::$cache['singularize'][$word] = preg_replace($rule, $replacement, $word);
                return self::$cache['singularize'][$word];
            }
        }
        self::$cache['singularize'][$word] = $word;
        return $word;
    }

    /**
     * Returns the given lower_case_and_underscored_word as a CamelCased word.
     *
     * @param string $lowerCaseAndUnderscoredWord Word to camelize
     * @return string Camelized word. LikeThis.
     */
    public static function camelize($lowerCaseAndUnderscoredWord) {
        if (!($result = self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord))) {
            $result = str_replace(' ', '', Inflector::humanize($lowerCaseAndUnderscoredWord));
            self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord, $result);
        }
        return $result;
    }

    /**
     * Returns the given lower_case_and_underscored_word as namespace Camel\Cased word.
     *
     * @param string $lowerCaseAndUnderscoredWord Word to camelize
     * @param boolean $global Return namespace with global \Like\This
     * @return string namespace. Like\This.
     */
    public static function namespaceze($lowerCaseAndUnderscoredWord, $global = false) {
        if (!($result = self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord))) {
            $result = str_replace(' ', '\\', Inflector::humanize($lowerCaseAndUnderscoredWord));
            if ($global) {
                $result = '\\'.$result;
            }
            self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord, $result);
        }
        return $result;
    }

    /**
     * Returns the given front_camel_cased as model CamelCase.
     *
     * @param string $lowerCaseAndUnderscoredWord Word to CamelCase model
     * @return string namespace. Like\This.
     */
    public static function modelize($lowerCaseAndUnderscoredWord) {
        if (!($result = self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord))) {
            $pieces = explode('_', $lowerCaseAndUnderscoredWord);
            array_shift($pieces);
            $result = str_replace(' ', '\\', Inflector::humanize(implode('_', $pieces)));
            self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord, $result);
        }
        return $result;
    }

    /**
     * Returns the given camelCasedWord as an underscored_word.
     *
     * @param string $camelCasedWord Camel-cased word to be "underscorized"
     * @return string Underscore-syntaxed version of the $camelCasedWord
     */
    public static function underscore($camelCasedWord) {
        if (!($result = self::_cache(__FUNCTION__, $camelCasedWord))) {
            $result = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
            self::_cache(__FUNCTION__, $camelCasedWord, $result);
        }
        return $result;
    }

    /**
     * Returns the given underscored_word_group as a Human Readable Word Group.
     * (Underscores are replaced by spaces and capitalized following words.)
     *
     * @param string $lowerCaseAndUnderscoredWord String to be made more readable
     * @return string Human-readable string
     */
    public static function humanize($lowerCaseAndUnderscoredWord) {
        if (!($result = self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord))) {
            $result = ucwords(str_replace('_', ' ', $lowerCaseAndUnderscoredWord));
            self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord, $result);
        }
        return $result;
    }

    public static function slug($string, $replacement = '-')
    {
        $quotedReplacement = preg_quote($replacement, '/');

        $merge = [
            '/[^\s\p{Zs}\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/[\s\p{Zs}]+/mu' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        ];

        $map = self::$transliteration + self::$cyrillicTransliteration + $merge;

        if (function_exists('mb_strtolower')) {
            $string = mb_strtolower($string, 'UTF-8');
        } else {
            $string = strtolower($string);
        }

        return preg_replace(array_keys($map), array_values($map), $string);
    }

}

// Store the initial state
Inflector::reset();