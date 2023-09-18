<?php
require_once './vendor/autoload.php';

$fileTmp = './test.docx';

$phpWord = \PhpOffice\PhpWord\IOFactory::load($fileTmp);
$htmlWriter = new \PhpOffice\PhpWord\Writer\HTML($phpWord);
$htmlWriter->save('test.html');
