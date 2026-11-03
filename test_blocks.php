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
    public ?int $level;

    public function __construct(string $text, int $level = 0) {
        $this->text = $text;
        $this->level = null;
        if ($level > 0) {
            $this->level = $level;
        }
    }
}

class DocBlock {
    const PARAGRAPH = 'paragraph';
    const HEADER = 'header';

    public string $id;
    public string $type;
    //public DocData $data;
    
    public function __construct() {
        $this->id = uniqid();
        $this->type = '';
    }

    public function populate(string $type, string $text, int $level = 0) {
        $this->type = $type;

        if ($type !== 'HEADER') {
            $level = 0;
        }
        //$this->data = new DocData($text, $level);
    }
}

$content = 'CONTENT';
$error = '';


$blocks = array();
    
//array_push($blocks, 'ARRAY PUSH');

$paragraphHtml = 'Heading One';
$headingLevel = 2;

$b = new DocBlock();
$b->populate( DocBlock::HEADER, $paragraphHtml, $headingLevel );
array_push( $blocks, $b );

/*
$paragraphHtml = 'List Item One';

$b = new DocBlock( DocBlock::PARAGRAPH, '• ' . $paragraphHtml );
array_push( $blocks, $b );

$paragraphHtml = 'This is a block of text.';

$b = new DocBlock( DocBlock::PARAGRAPH, $paragraphHtml );
array_push( $blocks, $b );
*/
    
$content = json_encode($blocks);
//$content = 'CONTENT';

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

            <p>
            <?= htmlspecialchars($content) ?>
            </p>

        </div>

    <?php endif; ?>

</div>

</body>
</html>


