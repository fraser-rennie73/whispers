 <?php

require_once __DIR__ . '/Scripts/vendor/PHPWord/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\ListItemRun;

class DocData {
    public ?string $text;

    public function __construct(string $text) {
        $this->text = $text;
    }
}

class DocDataHeader extends DocData {
    public int $level;

    public function __construct(string $text, int $level) {
        $this->text = $text;
        $this->level = $level;
    }
}

class DocBlock {
    const PARAGRAPH = 'paragraph';
    const HEADER = 'header';

    public string $id;
    public string $type;
    public DocData $data;

    public function __construct(string $type, string $text, int $level = 0) {
        $this->id = uniqid();
        $this->type = $type;

        if ($type === DocBlock::HEADER) {
            $this->data = new DocDataHeader($text, $level);
        }
        else {
             $this->data = new DocData($text);
        }
    }
}

class DocBlocks {
    public int $time;
    public array $blocks;
    public string $version;

    public function __construct(array $blocks) {
        $this->time = time();
        $this->blocks = $blocks;
        $this->version = '2.31.6';
    }
}

/**
 * Convert a PHPWord Text element into HTML.
 */
function textElementToHtml($element)
{
    if ($element instanceof Text) {
        $text = htmlspecialchars($element->getText(), ENT_QUOTES, 'UTF-8');

        $style = $element->getFontStyle();

        if ($style) {
            if ($style->isBold()) {
                $text = '<b>' . $text . '</b>';
            }

            if ($style->isItalic()) {
                $text = '<em>' . $text . '</em>';
            }

            if ($style->getUnderline() !== 'none') {
                $text = '<b>' . $text . '</b>';
            }
        }

        return $text;
    }

    if ($element instanceof TextRun) {
        $html = '';

        foreach ($element->getElements() as $child) {
            $html .= textElementToHtml($child);
        }

        return $html;
    }

    return '';
}


/**
 * Convert a paragraph/TextRun into HTML.
 */
function paragraphToHtml($element)
{
    $html = '';

    if ($element instanceof Text) {
        $html = textElementToHtml($element);
    } elseif ($element instanceof TextRun) {
        $html = textElementToHtml($element);
    }

    return $html;
}


/**
 * Get the paragraph style name.
 */
function getParagraphStyleName($element)
{
    if (!method_exists($element, 'getParagraphStyle')) {
        return '';
    }

    $style = $element->getParagraphStyle();

    if (is_string($style)) {
        return $style;
    }

    if (is_object($style) && method_exists($style, 'getStyleName')) {
        return $style->getStyleName();
    }

    return '';
}


/**
 * Determine whether a paragraph is a list item.
 */
function isListItem($element)
{
    if ($element instanceof ListItemRun) {
        return true;
    }
    
    if (!method_exists($element, 'getParagraphStyle')) {
        return false;
    }

    $style = $element->getParagraphStyle();

    if (is_object($style)) {

        // PHPWord versions that expose list information
        if (method_exists($style, 'getNumStyle')) {
            return $style->getNumStyle() !== null;
        }

        if (method_exists($style, 'getNumId')) {
            return $style->getNumId() !== null;
        }
    }

    return false;
}


/**
 * Determine heading level from Word paragraph style.
 */
function getHeadingLevel($element)
{
    $styleName = strtolower(getParagraphStyleName($element));

    if (preg_match('/heading\s*([1-6])/', $styleName, $matches)) {
        $level = (int)$matches[1];
        if ($level == 1) {
            $level = 2;
        }
        return $level;
    }

    // Some documents use Title / Subtitle.
    if ($styleName === 'title') {
        return 2;
    }

    if ($styleName === 'subtitle') {
        return 3;
    }

    return 0;
}

function docxToBlocks(string $fromFilename) {

                $phpWord = IOFactory::load($fromFilename);

                $blocks = array();

                foreach ($phpWord->getSections() as $section) {

                    foreach ($section->getElements() as $element) {

                        /*
                         * Paragraph / TextRun
                         */
                        if (
                            $element instanceof TextRun ||
                            $element instanceof Text
                        ) {

                            $paragraphHtml = paragraphToHtml($element);

                            if (trim(strip_tags($paragraphHtml)) === '') {
                                continue;
                            }

                            /*
                             * Headings
                             */
                            $headingLevel = getHeadingLevel($element);

                            if ($headingLevel > 0) {
                                $b = new DocBlock( DocBlock::HEADER, $paragraphHtml, $headingLevel );
                                array_push( $blocks, $b );
                                continue;
                            }

                            /*
                             * Bullet/list items
                             */
                            if (isListItem($element)) {
                                $b = new DocBlock( DocBlock::PARAGRAPH, 'â€¢ ' . $paragraphHtml );
                                array_push( $blocks, $b );
                                continue;
                            }

                            if ($paragraphHtml == '<b>' . strip_tags($paragraphHtml) . '</b>') {
                                $b = new DocBlock( DocBlock::HEADER, strip_tags( $paragraphHtml ), 2 );
                                array_push( $blocks, $b );
                                continue;
                            }

                            /*
                             * Normal paragraph
                             */
                            $b = new DocBlock( DocBlock::PARAGRAPH, $paragraphHtml );
                            array_push( $blocks, $b );
                        }
                    }
                }
                $bb = new DocBlocks($blocks);
                return json_encode($bb);
}

?>


