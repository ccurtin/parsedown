<?php

/**
 * Test Parsedown against the Standard Makrdown spec.
 *
 * Some code based on the original JavaScript test runner by jgm.
 *
 * @link http://standardmarkdown.com/ Standard Markdown
 * @link http://git.io/8WtRvQ JavaScript test runner
 */
class StandardMarkdownTest extends PHPUnit_Framework_TestCase
{
    const SPEC_FILEPATH = 'spec.txt';

    const SPEC_URL = 'https://raw.githubusercontent.com/jgm/stmd/master/spec.txt';

    public function getStandardMarkdownRules()
    {
        if (is_file(self::SPEC_FILEPATH) and is_readable(self::SPEC_FILEPATH)) {
            $spec = file_get_contents(self::SPEC_FILEPATH);
        } else {
            $spec = file_get_contents(self::SPEC_URL);
        }

        $tests = array();
        $testsCount = 0;
        $currentSection = '';

        $spec = preg_replace('/^<!-- END TESTS -->(.|[\n])*/m', '', $spec);

        preg_replace_callback(
            '/^\.\n([\s\S]*?)^\.\n([\s\S]*?)^\.$|^#{1,6} *(.*)$/m',
            function($matches) use (&$tests, &$currentSection, &$testsCount) {
                if (isset($matches[3]) and $matches[3]) {
                    $currentSection = $matches[3];
                } else {
                    $testsCount++;
                    $markdown = preg_replace('/→/', "\t", $matches[1]);
                    $tests []= array(
                        $markdown,       // markdown
                        $matches[2],     // html
                        $currentSection, // section
                        $testsCount,     // number
                    );
                }
            },
            $spec
        );

        return $tests;
    }

    /**
     * @dataProvider getStandardMarkdownRules
     */
    public function testAgainstStandardMarkdown($markdown, $expectedHtml, $section, $number)
    {
        $parsedown = new Parsedown();

        $actualHtml = $parsedown->text($markdown);

        // Trim for better compatibility of the HTML output
        $actualHtml = trim($actualHtml);
        $expectedHtml = trim($expectedHtml);

        $this->assertEquals($expectedHtml, $actualHtml);
    }
}