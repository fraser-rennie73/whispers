 <?php

require_once __DIR__ . '/Scripts/vendor/PHPWord/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\ListItemRun;

$content = '';
$error = '';

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
                $text = '<strong>' . $text . '</strong>';
            }

            if ($style->isItalic()) {
                $text = '<em>' . $text . '</em>';
            }

            if ($style->getUnderline() !== 'none') {
                $text = '<u>' . $text . '</u>';
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
        return (int)$matches[1];
    }

    // Some documents use Title / Subtitle.
    if ($styleName === 'title') {
        return 1;
    }

    if ($styleName === 'subtitle') {
        return 2;
    }

    return 0;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['docx'])) {

    if ($_FILES['docx']['error'] !== UPLOAD_ERR_OK) {
        $error = 'There was a problem uploading the file.';
    } else {

        $fileName = $_FILES['docx']['name'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($extension !== 'docx') {
            $error = 'Please upload a DOCX file.';
        } else {

            try {

                $phpWord = IOFactory::load($_FILES['docx']['tmp_name']);

                foreach ($phpWord->getSections() as $section) {

                    $listOpen = false;

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

                                if ($listOpen) {
                                    $content .= '</ul>';
                                    $listOpen = false;
                                }

                                $content .= '<h' . $headingLevel . '>'
                                    . $paragraphHtml
                                    . '</h' . $headingLevel . '>';

                                continue;
                            }


                            /*
                             * Bullet/list items
                             */
                            if ($element instanceof ListItemRun) {
                                $content .= '<p>• ' . $paragraphHtml . '</p>';
                                continue;
                            }
                            if (isListItem($element)) {

                                if (!$listOpen) {
                                    $content .= '<ul>';
                                    $listOpen = true;
                                }

                                $content .= '<li>'
                                    . $paragraphHtml
                                    . '</li>';

                                continue;
                            }


                            /*
                             * Normal paragraph
                             */
                            if ($listOpen) {
                                $content .= '</ul>';
                                $listOpen = false;
                            }

                            $content .= '<p>'
                                . get_class( $element ) . "<br/>"
                                . $paragraphHtml
                                . '</p>';
                        }


                        /*
                         * Tables
                         */
                        elseif ($element instanceof Table) {

                            if ($listOpen) {
                                $content .= '</ul>';
                                $listOpen = false;
                            }

                            $content .= '<table>';

                            foreach ($element->getRows() as $row) {

                                $content .= '<tr>';

                                foreach ($row->getCells() as $cell) {

                                    $cellHtml = '';

                                    foreach ($cell->getElements() as $cellElement) {

                                        if (
                                            $cellElement instanceof Text ||
                                            $cellElement instanceof TextRun
                                        ) {
                                            $cellHtml .= paragraphToHtml($cellElement);
                                        }
                                    }

                                    $content .= '<td>'
                                        . $cellHtml
                                        . '</td>';
                                }

                                $content .= '</tr>';
                            }

                            $content .= '</table>';
                        }
                    }

                    if ($listOpen) {
                        $content .= '</ul>';
                    }
                }

            } catch (Exception $e) {
                $error = 'Unable to read the DOCX file: ' .
                    $e->getMessage();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DOCX Viewer</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 40px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        .upload-box,
        .document {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        h1 {
            margin-top: 0;
        }

        input[type="file"] {
            margin: 15px 0;
        }

        button {
            background: #007bff;
            color: white;
            border: 0;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .error {
            background: #ffe0e0;
            color: #900;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .document {
            line-height: 1.6;
        }

        .document h1 {
            font-size: 2em;
            margin-top: 1em;
        }

        .document h2 {
            font-size: 1.6em;
            margin-top: 1em;
        }

        .document h3 {
            font-size: 1.3em;
            margin-top: 1em;
        }

        .document h4,
        .document h5,
        .document h6 {
            margin-top: 1em;
        }

        .document ul {
            margin-top: 0;
            margin-bottom: 1em;
            padding-left: 30px;
        }

        .document li {
            margin-bottom: 4px;
            border: 1px solid red;
        }

        .document table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .document td {
            border: 1px solid #ccc;
            padding: 8px;
            vertical-align: top;
        }
        
        p { border: 1px solid blue; }

    </style>

</head>

<body>

<div class="container">

    <div class="upload-box">

        <h1>Upload a DOCX File</h1>

        <?php if ($error): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <input
                type="file"
                name="docx"
                accept=".docx"
                required
            >

            <br>

            <button type="submit">
                Upload &amp; View
            </button>

        </form>

    </div>


    <?php if ($content): ?>

        <div class="document">

            <h2>Document Contents</h2>

            <?= $content ?>

        </div>

    <?php endif; ?>

</div>

</body>
</html>



