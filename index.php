<?php
include("./vendor/autoload.php");

$TARGET_FILE = './test.pdf';

// Parse PDF file and build necessary objects.
$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile($TARGET_FILE);
$text = $pdf->getText();

$interesting_data = explode("PRIME  TTC en  €", $text);
$interesting_data = explode("*  avec  moe de  conception et exécution des travaux", $interesting_data[1]);

$remove_words = ["(dont 15%  commissions)", "De", "DO", "DO + CNR", "DO", "DO + CNR", "Jusqu’à", "+ CNR"];
$interesting_data = trim(str_replace($remove_words, "", $interesting_data[0]));
$interesting_data = str_replace("*", "", $interesting_data);
$interesting_data = explode("\n", $interesting_data);

$lines = [];
$index = 0;

foreach ($interesting_data as $v1) {
    $new_value = trim($v1);

    $new_value = preg_replace_callback('/\s+/', function ($matches) {
        return str_repeat('.', strlen($matches[0]));
    }, $new_value);

    $array = explode("...", $new_value);
    $array2 = [];
    foreach ($array as $v2) {
        if (str_contains($v2, '..')) {
            $custom_v2 = explode("..", $v2);
            foreach ($custom_v2 as $v3) {
                $array2[] = $v3;
            }
        } else {
            $array2[] = $v2;
        }
    }

    $parsed_data = [];

    for ($i = 0; $i < count($array2); $i++) {
        switch ($i) {
            case 0:
                $parsed_data["from"] = str_replace('.', '', $array2[0]);
                break;
            case 1:
                $parsed_data["to"] = str_replace('.', '', $array2[1]);
                break;
            case 2:
                $parsed_data["new_construction_do"]= str_replace('.', '', $array2[2]);
                break;
            case 3:
                $parsed_data["new_construction_do_cnr"] = str_replace('.', '', $array2[3]);
                break;
            case 4:
                $parsed_data["renovation_extension_do"] = str_replace('.', '', $array2[4]);
                break;
            case 5:
                $parsed_data["renovation_extension_do_cnr"] = str_replace('.', '', $array2[5]);
                break;
            default:
                # code...
                break;
            }
    }

    $lines[$index] = $parsed_data;
    $index += 1;
}

if(file_exists("result.json")) unlink("result.json");
file_put_contents("result.json", json_encode($lines));