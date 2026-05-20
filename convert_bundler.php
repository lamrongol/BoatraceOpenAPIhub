<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use BOA\Hub\ProgramSaver;
use Carbon\CarbonImmutable as Carbon;

// コマンドライン引数から日付を取得（デフォルトは 本日）
$date = new Carbon(file_get_contents("docs/v3/recorded_day.json"))->timezone('Asia/Tokyo');
$date = $date->subDay();
if ($date->isBefore(Carbon::parse('2025-07-15'))) {
    exit;
}


$directoryName = $date->format('Y');
$fileName = $date->format('Ymd');

$programsJson = file_get_contents("https://boatraceopenapi.github.io/programs/v3/{$directoryName}/{$fileName}.json");
$programs = json_decode($programsJson, true)['programs'] ?? [];

$previewsJson = file_get_contents("https://boatraceopenapi.github.io/previews/v2/{$directoryName}/{$fileName}.json");
$previewsJson = str_replace('"race_date"', '"date"', $previewsJson);
$previewsJson = str_replace('"race_stadium_number"', '"stadium_number"', $previewsJson);
$previewsJson = str_replace('"race_number"', '"number"', $previewsJson);
$previewsJson = str_replace('"race_wind"', '"wind_speed"', $previewsJson);
$previewsJson = str_replace('"race_wind_direction_number"', '"wind_direction_number"', $previewsJson);
$previewsJson = str_replace('"race_wave"', '"wave_height"', $previewsJson);
$previewsJson = str_replace('"race_weather_number"', '"weather_number"', $previewsJson);
$previewsJson = str_replace('"race_temperature"', '"air_temperature"', $previewsJson);
$previewsJson = str_replace('"race_water_temperature"', '"water_temperature"', $previewsJson);

$previews = json_decode($previewsJson, true)['previews'] ?? [];

$resultsJson = file_get_contents("https://boatraceopenapi.github.io/results/v2/{$directoryName}/{$fileName}.json");
$resultsJson = str_replace('"race_date"', '"date"', $resultsJson);
$resultsJson = str_replace('"race_stadium_number"', '"stadium_number"', $resultsJson);
$resultsJson = str_replace('"race_number"', '"number"', $resultsJson);
$resultsJson = str_replace('"race_wind"', '"wind_speed"', $resultsJson);
$resultsJson = str_replace('"race_wave"', '"wave_height"', $resultsJson);
$resultsJson = str_replace('"race_weather_number"', '"weather_number"', $resultsJson);
$resultsJson = str_replace('"race_temperature"', '"air_temperature"', $resultsJson);
$resultsJson = str_replace('"race_water_temperature"', '"water_temperature"', $resultsJson);
$resultsJson = str_replace('"race_technique_number"', '"technique_number"', $resultsJson);
$resultsJson = str_replace('"payout"', '"payout"', $resultsJson);

$results = json_decode($resultsJson, true)['results'] ?? [];

$oddsJson = file_get_contents("https://raw.githubusercontent.com/lamrongol/BoatraceOdds/refs/heads/gh-pages/docs/v3/{$directoryName}/{$fileName}.json");
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
$saver->save($newPrograms, "docs/v3/{$directoryName}/{$fileName}.json");

file_put_contents("docs/v3/recorded_day.json", $date->toDateString());
