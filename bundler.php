<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use BOA\Hub\ProgramSaver;
use Carbon\CarbonImmutable as Carbon;

// コマンドライン引数からバージョンを取得（デフォルトは v3）
$version = $argv[1] ?? 'v3';
$day_specified = ($argc == 3 && $argv[2] == 'True');

// コマンドライン引数から日付を取得（デフォルトは 昨日）
$date = Carbon::yesterday('Asia/Tokyo');
if ($day_specified) {
    $date = new Carbon(file_get_contents("docs/{$version}/recorded_day.json"))->timezone('Asia/Tokyo');
    $date = $date->subDay();
    if ($date->isBefore(Carbon::parse('2020-01-01'))) {
        exit;
    }
}

$directoryName = $date->format('Y');
$fileName = $date->format('Ymd');

$programsJson = file_get_contents("https://boatraceopenapi.github.io/programs/{$version}/{$directoryName}/{$fileName}.json");
$tmp = json_decode($programsJson, true);
$programs = ($day_specified ? $tmp['programs'] : $tmp['today']['programs']) ?? [];
if (count($programs) == 0) {
    exit(1);
}
$previewsJson = file_get_contents("https://raw.githubusercontent.com/lamrongol/BoatracePreviews/refs/heads/gh-pages/docs/{$version}/{$directoryName}/{$fileName}.json");
$previews = json_decode($previewsJson, true)['previews'] ?? [];
if (count($previews) == 0) {
    exit(1);
}

$resultsJson = file_get_contents("https://raw.githubusercontent.com/lamrongol/BoatraceResults/refs/heads/gh-pages/docs/{$version}/{$directoryName}/{$fileName}.json");
$results = json_decode($resultsJson, true)['results'] ?? [];
if (count($results) == 0) {
    exit(1);
}

$oddsJson = file_get_contents("https://raw.githubusercontent.com/lamrongol/BoatraceOdds/refs/heads/gh-pages/docs/v3/{$directoryName}/{$fileName}.json");
//古い形式のための処置
$oddsJson = str_replace('"race_date"', '"date"', $oddsJson);
$oddsJson = str_replace('"race_stadium_number"', '"stadium_number"', $oddsJson);
$oddsJson = str_replace('"race_number"', '"number"', $oddsJson);
$odds = json_decode($oddsJson, true)['odds'] ?? [];
if (count($odds) == 0) {
    exit(1);
}

$expectJson = file_get_contents("https://raw.githubusercontent.com/lamrongol/BoatraceExpect/refs/heads/main/docs/{$version}/{$directoryName}/{$fileName}.json");
$expect = json_decode($expectJson, true)['expect'] ?? [];
if (count($expect) == 0) {
    exit(1);
}

$newPrograms = array_map(function ($program) use ($previews, $results, $odds, $expect) {
    $program['expect'] = array_find(
        $expect,
        fn($expect) =>
        $expect['stadium_number'] === $program['stadium_number']
        && $expect['number'] === $program['number']
    );

    $program['preview'] = array_find(
        $previews,
        fn($preview) =>
        $preview['stadium_number'] === $program['stadium_number']
        && $preview['number'] === $program['number']
    );

    $program['odds'] = array_find(
        $odds,
        fn($odds) =>
        $odds['stadium_number'] === $program['stadium_number']
        && $odds['number'] === $program['number']
    );

    $program['result'] = array_find(
        $results,
        fn($result) =>
        $result['stadium_number'] === $program['stadium_number']
        && $result['number'] === $program['number']
    );

    return $program;
}, $programs);


// 出走表データが取得できなかった場合は処理終了
if (empty($newPrograms ?? [])) {
    exit;
}

// 出走表データを JSON ファイルとして保存
// 日付付きの JSON ファイルとして保存（例: docs/v3/2026/20260322.json）
// 最新データとして today.json にも保存
$saver = new ProgramSaver();
$saver->save($newPrograms, "docs/{$version}/{$directoryName}/{$fileName}.json");
if ($day_specified) {
    file_put_contents("docs/{$version}/recorded_day.json", $date->toDateString());
} else {
    $saver->save($newPrograms, "docs/{$version}/yesterday.json");
}

