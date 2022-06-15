<?php

namespace Performing\TwigComponents\Lexer;

/**
 * Content of this file is mostly taken from Laravel Component Tag Compiler.
 *
 * https://github.com/illuminate/view/blob/master/Compilers/ComponentTagCompiler.php
 */
class ComponentTagCompiler
{
    protected $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function compile(): string
    {
        $value = $this->source;
        $value = $this->compileSlots($value);
        $value = $this->compileSelfClosingTags($value);
        $value = $this->compileOpeningTags($value);
        $value = $this->compileClosingTags($value);

        return $value;
    }

    /**
     * Compile the slot tags within the given string.
     *
     * @param  string  $value
     * @return string
     */
    public function compileSlots(string $value)
    {
        $pattern = "/
            <
                \s*
                x[\-\:]slot
                \s+
                (:?)name=(?<name>(\"[^\"]+\"|\\\'[^\\\']+\\\'|[^\s>]+))
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                \{\{\s*attributes(?:.+?)?\s*\}\}
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";

        $value = preg_replace_callback($pattern, function ($matches) {
            $name = $this->stripQuotes($matches['name']);
            $attributes = $this->getAttributesFromAttributeString($matches['attributes'] ?? []);

            return "{% slot:{$name} with {$attributes} %}";
        }, $value);

        return preg_replace('/<\/\s*x[\-\:]slot[^>]*>/', '{% endslot %}', $value);
    }

    /**
     * Compile the opening tags within the given string.
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function compileOpeningTags(string $value)
    {
        $pattern = "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                \{\{\s*attributes(?:.+?)?\s*\}\}
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";

        return preg_replace_callback(
            $pattern,
            function (array $matches) {
                $attributes = $this->getAttributesFromAttributeString($matches['attributes']);
                $name = $matches[1];

                return "{% x:$name with $attributes %}";
            },
            $value
        );
    }

    /**
     * Compile the closing tags within the given string.
     *
     * @param string $value
     *
     * @return string
     */
    protected function compileClosingTags(string $value)
    {
        return preg_replace("/<\/\s*x[-\:][\w\-\:\.]*\s*>/", '{% endx %}', $value);
    }

    /**
     * Compile the self-closing tags within the given string.
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function compileSelfClosingTags(string $value)
    {
        $pattern = "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                \s*
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                \{\{\s*attributes(?:.+?)?\s*\}\}
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
            \/>
        /x";

        return preg_replace_callback(
            $pattern,
            function (array $matches) {
                $attributes = $this->getAttributesFromAttributeString($matches['attributes']);
                $name = $matches[1];

                return "{% x:$name with $attributes %}{% endx %}";
            },
            $value
        );
    }

    protected function getAttributesFromAttributeString(string $attributeString)
    {
        $attributeString = $this->parseAttributeBag($attributeString);

        $pattern = '/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x';

        if (! preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER)) {
            return '{}';
        }


        $attributes = [];

        foreach ($matches as $match) {
            $attribute = $match['attribute'];
            $value = $match['value'] ?? null;

            if (is_null($value)) {
                $value = 'true';
            }


            if (strpos($attribute, ":") === 0) {
                $attribute = str_replace(":", "", $attribute);
                $value = $this->stripQuotes($value);
            }

            $valueWithoutQuotes = $this->stripQuotes($value);

            if ((strpos($valueWithoutQuotes, '{{') === 0) && (strpos($valueWithoutQuotes, '}}') === strlen($valueWithoutQuotes) - 2)) {
                $value = substr($valueWithoutQuotes, 2, -2);
            } else {
                $value = $value;
            }

            $attributes[$attribute] = $value;
        }

        $out = "{";
        foreach ($attributes as $key => $value) {
            $key = "'$key'";
            $out .= "$key: $value,";
        };

        return rtrim($out, ',') . "}";
    }

    /**
     * Strip any quotes from the given string.
     *
     * @param  string  $value
     * @return string
     */
    public function stripQuotes(string $value)
    {
        return strpos($value, '"') === 0 || strpos($value, '\'') === 0
            ? substr($value, 1, -1)
            : $value;
    }

    /**
     * Parse the attribute bag in a given attribute string into it's fully-qualified syntax.
     *
     * @param  string  $attributeString
     * @return string
     */
    protected function parseAttributeBag(string $attributeString)
    {
        $pattern = "/
            (?:^|\s+)                                        # start of the string or whitespace between attributes
            \{\{\s*(attributes(?:.+?(?<!\s))?)\s*\}\} # exact match of attributes variable being echoed
        /x";

        return preg_replace($pattern, ' :attributes="$1"', $attributeString);
    }
}
