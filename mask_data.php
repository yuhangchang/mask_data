<?php
require_once("vendor/autoload.php");
use JamesGordo\CSV\Parser;
use League\CLImate\CLImate;

function downloadFile()
{
    $maskDataUrl = "http://data.nhi.gov.tw/Datasets/Download.ashx?rid=A21030000I-D50001-001&l=https://data.nhi.gov.tw/resource/mask/maskdata.csv";
    if (time() - filemtime("maskdata.csv") > 300) {
        unlink("maskdata.csv");
    }
    if (is_file("maskdata.csv") === false) {
        if (file_put_contents("maskdata.csv", file_get_contents($maskDataUrl))) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function userInput($climate)
{
    $input = "";
    while ($input == "") {
        $input = $climate->input("請輸入地址關鍵字");
        $input = $input->prompt();
    }
    return $input;
}


if (downloadFile() === false) {
    print("下載檔案錯誤");
    exit();
}

$datas = new Parser("maskdata.csv");
$outPutDatas = [];

$climate = new CLImate();
$input = userInput($climate);

foreach ($datas->all() as $data) {
    if (strpos($data->醫事機構地址, $input) !== false && $data->成人口罩剩餘數 != 0) {
        $temp = $data;
        unset($temp->醫事機構代碼, $temp->醫事機構電話, $temp->兒童口罩剩餘數, $temp->來源資料時間);
        $outPutDatas[] = (array)$temp;
    }
}

usort($outPutDatas, function ($a, $b) {
    return $b['成人口罩剩餘數'] - $a['成人口罩剩餘數'];
});

if ($outPutDatas) {
    $climate->table($outPutDatas);
} else {
    printf("查無資料\n");
}
