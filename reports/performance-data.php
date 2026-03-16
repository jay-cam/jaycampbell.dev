<?php

header('Content-Type: application/json');

require "../auth.php";
require "../db.php";

$result = $mysqli->query("
SELECT
created_at,
JSON_UNQUOTE(JSON_EXTRACT(payload,'$.data.totalLoadTime')) AS load_time
FROM events
WHERE event_type='performance'
AND JSON_EXTRACT(payload,'$.data.totalLoadTime') IS NOT NULL
ORDER BY created_at ASC
LIMIT 100
");

$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {

$labels[] = date("H:i:s", strtotime($row["created_at"]));
$data[] = (int)$row["load_time"];

}

$buckets = [
"0-500ms" => 0,
"500-1000ms" => 0,
"1000-2000ms" => 0,
"2000ms+" => 0
];

foreach ($data as $t) {

if ($t < 500) $buckets["0-500ms"]++;
elseif ($t < 1000) $buckets["500-1000ms"]++;
elseif ($t < 2000) $buckets["1000-2000ms"]++;
else $buckets["2000ms+"]++;

}

echo json_encode([
"trend" => [
"labels" => $labels,
"data" => $data
],
"hist" => [
"labels" => array_keys($buckets),
"data" => array_values($buckets)
]
]);