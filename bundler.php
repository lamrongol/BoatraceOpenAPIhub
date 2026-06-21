<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use BOA\Hub\ProgramSaver;
use Carbon\CarbonImmutable as Carbon;

// コマンドライン引数からバージョンを取得（デフォルトは v3）
$version = $argv[1] ?? 'v3';

// コマンドライン引数から日付を取得（デフォルトは 昨日）
$date = Carbon::parse($argv[2] ?? 'yesterday')->timezone('Asia/Tokyo');

$directoryName = $date->format('Y');
$fileName = $date->format('Ymd');

$programsJson = file_get_contents("https://boatraceopenapi.github.io/programs/{$version}/{$directoryName}/{$fileName}.json");
$programs = json_decode($programsJson, true)['programs'] ?? [];

$previewsJson = file_get_contents("https://boatraceopenapi.github.io/previews/{$version}/{$directoryName}/{$fileName}.json");
$previews = json_decode($previewsJson, true)['previews'] ?? [];

$resultsJson = file_get_contents("https://raw.githubusercontent.com/lamrongol/BoatraceResults/refs/heads/gh-pages/docs/{$version}/{$directoryName}/{$fileName}.json");
$results = json_decode($resultsJson, true)['results'] ?? [];

$oddsJson = file_get_contents("https://raw.githubusercontent.com/lamrongol/BoatraceOdds/refs/heads/gh-pages/docs/v3/{$directoryName}/{$fileName}.json");
$oddsJson = str_replace('"race_date"', '"date"', $oddsJson);
$oddsJson = str_replace('"race_stadium_number"', '"stadium_number"', $oddsJson);
$oddsJson = str_replace('"race_number"', '"number"', $oddsJson);
$odds = json_decode($oddsJson, true)['odds'] ?? [];

$newPrograms = array_map(function ($program) use ($previews, $results, $odds) {
    $program['preview'] = array_find(
        $previews,
        fn($preview) =>
        $preview['stadium_number'] === $program['stadium_number']
        && $preview['number'] === $program['number']
    );

    $program['result'] = array_find(
        $results,
        fn($result) =>
        $result['stadium_number'] === $program['stadium_number']
        && $result['number'] === $program['number']
    );

    $program['odds'] = array_find(
        $odds,
        fn($odds) =>
        $odds['stadium_number'] === $program['stadium_number']
        && $odds['number'] === $program['number']
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
$saver->save($newPrograms, "docs/{$version}/yesterday.json");
