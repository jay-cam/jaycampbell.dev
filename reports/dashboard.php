<?php
require "auth.php";
require "db.php";

$pageTitle = "Dashboard";

/*
|--------------------------------------------------------------------------
| Time Range Filter
|--------------------------------------------------------------------------
*/

$range = $_GET["range"] ?? "7d";

$where = "";

switch ($range) {

case "1h":
$where = "WHERE created_at >= NOW() - INTERVAL 1 HOUR";
break;

case "24h":
$where = "WHERE created_at >= NOW() - INTERVAL 1 DAY";
break;

case "30d":
$where = "WHERE created_at >= NOW() - INTERVAL 30 DAY";
break;

case "all":
$where = "";
break;

default:
$where = "WHERE created_at >= NOW() - INTERVAL 7 DAY";
$range = "7d";
}

/*
|--------------------------------------------------------------------------
| Pagination + Sorting
|--------------------------------------------------------------------------
*/

$limit = 10;
$page = max(1, intval($_GET["page"] ?? 1));
$offset = ($page - 1) * $limit;

$sort = $_GET["sort"] ?? "id";
$order = $_GET["order"] ?? "desc";

$allowedSort = ["id","session_id","request_method","event_type","url","created_at"];

if (!in_array($sort,$allowedSort)) {
$sort = "id";
}

$order = strtolower($order) === "asc" ? "asc" : "desc";

/*
|--------------------------------------------------------------------------
| Metrics
|--------------------------------------------------------------------------
*/

$totalEvents = $mysqli->query("
SELECT COUNT(*) AS total
FROM events
$where
")->fetch_assoc()["total"];

$uniqueSessions = $mysqli->query("
SELECT COUNT(DISTINCT session_id) AS total
FROM events
$where
")->fetch_assoc()["total"];

$uniquePages = $mysqli->query("
SELECT COUNT(DISTINCT url) AS total
FROM events
$where
")->fetch_assoc()["total"];

$eventTypes = $mysqli->query("
SELECT COUNT(DISTINCT event_type) AS total
FROM events
$where
")->fetch_assoc()["total"];

/*
|--------------------------------------------------------------------------
| Query Events
|--------------------------------------------------------------------------
*/

$result = $mysqli->query("
SELECT id, session_id, request_method, event_type, url, created_at
FROM events
$where
ORDER BY $sort $order
LIMIT $limit OFFSET $offset
");

$totalRows = $mysqli->query("
SELECT COUNT(*) AS total
FROM events
$where
")->fetch_assoc()["total"];

$totalPages = ceil($totalRows / $limit);

ob_start();
?>

<div class="max-w-6xl mx-auto">

<h1 class="text-2xl font-semibold mb-4">Analytics Dashboard</h1>

<div class="mb-6">

<form method="GET">

<select
name="range"
onchange="this.form.submit()"
class="border rounded px-3 py-1 text-sm">

<option value="1h" <?= $range==="1h"?"selected":"" ?>>Last 1 Hour</option>
<option value="24h" <?= $range==="24h"?"selected":"" ?>>Last 24 Hours</option>
<option value="7d" <?= $range==="7d"?"selected":"" ?>>Last 7 Days</option>
<option value="30d" <?= $range==="30d"?"selected":"" ?>>Last 30 Days</option>
<option value="all" <?= $range==="all"?"selected":"" ?>>All Time</option>

</select>

</form>

</div>


<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">

<div class="bg-white shadow rounded-lg p-4">
<div class="text-sm text-gray-500">Total Events</div>
<div class="text-2xl font-bold"><?= $totalEvents ?></div>
</div>

<div class="bg-white shadow rounded-lg p-4">
<div class="text-sm text-gray-500">Unique Sessions</div>
<div class="text-2xl font-bold"><?= $uniqueSessions ?></div>
</div>

<div class="bg-white shadow rounded-lg p-4">
<div class="text-sm text-gray-500">Unique Pages</div>
<div class="text-2xl font-bold"><?= $uniquePages ?></div>
</div>

<div class="bg-white shadow rounded-lg p-4">
<div class="text-sm text-gray-500">Event Types</div>
<div class="text-2xl font-bold"><?= $eventTypes ?></div>
</div>

</div>


<div class="bg-white shadow rounded-lg overflow-hidden">

<table class="min-w-full text-sm">

<thead class="bg-gray-100 border-b">

<tr>

<th class="px-3 py-2 text-left">
<a href="?range=<?= $range ?>&sort=id&order=<?= $order==="asc"?"desc":"asc" ?>">ID</a>
</th>

<th class="px-3 py-2 text-left">
<a href="?range=<?= $range ?>&sort=session_id&order=<?= $order==="asc"?"desc":"asc" ?>">Session</a>
</th>

<th class="px-3 py-2 text-left">
<a href="?range=<?= $range ?>&sort=request_method&order=<?= $order==="asc"?"desc":"asc" ?>">Method</a>
</th>

<th class="px-3 py-2 text-left">
<a href="?range=<?= $range ?>&sort=event_type&order=<?= $order==="asc"?"desc":"asc" ?>">Type</a>
</th>

<th class="px-3 py-2 text-left">
<a href="?range=<?= $range ?>&sort=url&order=<?= $order==="asc"?"desc":"asc" ?>">URL</a>
</th>

<th class="px-3 py-2 text-left">
<a href="?range=<?= $range ?>&sort=created_at&order=<?= $order==="asc"?"desc":"asc" ?>">Created</a>
</th>

</tr>

</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()) { ?>

<tr class="border-b odd:bg-gray-50 hover:bg-gray-100">

<td class="px-3 py-2"><?= $row["id"] ?></td>

<td class="px-3 py-2 font-mono text-xs">
<?= htmlspecialchars($row["session_id"]) ?>
</td>

<td class="px-3 py-2"><?= htmlspecialchars($row["request_method"]) ?></td>

<td class="px-3 py-2"><?= htmlspecialchars($row["event_type"]) ?></td>

<td class="px-3 py-2 truncate max-w-xs text-blue-700">
<?= htmlspecialchars($row["url"]) ?>
</td>

<td class="px-3 py-2"><?= $row["created_at"] ?></td>

</tr>

<?php } ?>

</tbody>
</table>

</div>


<div class="flex gap-2 mt-6">

<?php for ($i=1; $i <= $totalPages; $i++) { ?>

<a
href="?page=<?= $i ?>&sort=<?= $sort ?>&order=<?= $order ?>&range=<?= $range ?>"
class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white' ?>"
>
<?= $i ?>
</a>

<?php } ?>

</div>

</div>

<?php
$content = ob_get_clean();
require "views/layout.php";
?>