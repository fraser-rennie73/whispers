 <?php

/*require_once __DIR__ . '/Scripts/vendor/autoload.php';*/
     
require_once __DIR__ . '/Scripts/vendor/PHPWord/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\IOFactory;

$content = '';
$error = '';

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

                    foreach ($section->getElements() as $element) {

                        // Paragraphs
                        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                            $text = '';

                            foreach ($element->getElements() as $child) {
                                if ($child instanceof \PhpOffice\PhpWord\Element\Text) {
                                    $text .= $child->getText();
                                }
                            }

                            if (trim($text) !== '') {
                                $content .= '<p>' .
                                    nl2br(htmlspecialchars($text)) .
                                    '</p>';
                            }
                        }

                        // Simple text elements
                        elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                            $content .= '<p>' .
                                nl2br(htmlspecialchars($element->getText())) .
                                '</p>';
                        }

                        // Tables
                        elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                            $content .= '<table>';

                            foreach ($element->getRows() as $row) {
                                $content .= '<tr>';

                                foreach ($row->getCells() as $cell) {
                                    $cellText = '';

                                    foreach ($cell->getElements() as $cellElement) {
                                        if ($cellElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                            $cellText .= $cellElement->getText();
                                        } elseif ($cellElement instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                            foreach ($cellElement->getElements() as $child) {
                                                if ($child instanceof \PhpOffice\PhpWord\Element\Text) {
                                                    $cellText .= $child->getText();
                                                }
                                            }
                                        }
                                    }

                                    $content .= '<td>' .
                                        nl2br(htmlspecialchars($cellText)) .
                                        '</td>';
                                }

                                $content .= '</tr>';
                            }

                            $content .= '</table>';
                        }
                    }
                }

            } catch (Exception $e) {
                $error = 'Unable to read the DOCX file: ' . $e->getMessage();
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
        
        p { border: 1px solid red; }
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

