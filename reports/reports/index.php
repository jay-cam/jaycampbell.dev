<?php
require "../auth.php";
require "../db.php";

$pageTitle = "Reports";

/*
|--------------------------------------------------------------------------
| Permissions
|--------------------------------------------------------------------------
*/

$canDelete = in_array($_SESSION["role"], ["super_admin","analytics"]);

/*
|--------------------------------------------------------------------------
| Pagination + Sorting
|--------------------------------------------------------------------------
*/

$limit = 10;
$page = max(1, intval($_GET["page"] ?? 1));
$offset = ($page - 1) * $limit;

$sort = $_GET["sort"] ?? "created_at";
$order = $_GET["order"] ?? "desc";

$allowedSort = ["report_name","created_at", "report_id"];

if (!in_array($sort,$allowedSort)) {
    $sort = "created_at";
}

$order = strtolower($order) === "asc" ? "asc" : "desc";

/*
|--------------------------------------------------------------------------
| Delete Single Report
|--------------------------------------------------------------------------
*/

if ($canDelete && isset($_GET["delete"])) {

    $stmt = $mysqli->prepare("
        DELETE FROM reports
        WHERE report_id = ?
    ");

    $stmt->bind_param("s", $_GET["delete"]);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("
        DELETE FROM analyst_comments
        WHERE report_id = ?
    ");

    $stmt->bind_param("s", $_GET["delete"]);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Delete All Reports
|--------------------------------------------------------------------------
*/

if ($canDelete && isset($_GET["delete_all"])) {

    $mysqli->query("DELETE FROM reports");
    $mysqli->query("DELETE FROM analyst_comments");

    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Fetch Reports
|--------------------------------------------------------------------------
*/

$result = $mysqli->query("
    SELECT report_id, report_name, created_at
    FROM reports
    ORDER BY $sort $order
    LIMIT $limit OFFSET $offset
");

$totalRows = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM reports
")->fetch_assoc()["total"];

$totalPages = ceil($totalRows / $limit);

ob_start();
?>

<div class="max-w-6xl mx-auto">

<h1 class="text-3xl font-bold mb-6">Reports Dashboard</h1>

<div class="bg-white shadow rounded-lg p-6 mb-6">

<h2 class="text-xl font-semibold mb-4">Generate New Report</h2>

<div class="flex gap-4">

<a href="view.php?report=traffic"
class="px-4 py-2 bg-blue-600 text-white rounded">
Traffic
</a>

<a href="view.php?report=interaction"
class="px-4 py-2 bg-green-600 text-white rounded">
Interaction
</a>

<a href="view.php?report=performance"
class="px-4 py-2 bg-yellow-600 text-white rounded">
Performance
</a>

</div>

</div>


<div class="bg-white shadow rounded-lg p-6">

<div class="flex justify-between items-center mb-4">

<h2 class="text-xl font-semibold">Saved Reports</h2>

<?php if ($canDelete) { ?>

<a
href="?delete_all=1"
onclick="return confirm('Delete ALL reports?')"
class="text-red-600 text-sm">
Delete All
</a>

<?php } ?>

</div>


<table class="min-w-full text-sm">

<thead class="bg-gray-100 border-b">

<tr>

<th class="px-3 py-2 text-left">
<a class="flex items-center gap-1 hover:text-blue-600"
href="?sort=report_name&order=<?= $order==="asc"?"desc":"asc" ?>">
Report Type
<span>↕</span>
</a>
</th>

<th class="px-3 py-2 text-left">
<a class="flex items-center gap-1 hover:text-blue-600"
href="?sort=report_id&order=<?= $order==="asc"?"desc":"asc" ?>">
Report ID
<span>↕</span>
</a>
</th>

<th class="px-3 py-2 text-left">
<a class="flex items-center gap-1 hover:text-blue-600"
href="?sort=created_at&order=<?= $order==="asc"?"desc":"asc" ?>">
Created
<span>↕</span>
</a>
</th>

<th class="px-3 py-2 text-left">
Open
</th>

<?php if ($canDelete) { ?>
<th class="px-3 py-2 text-left">
Delete
</th>
<?php } ?>

</tr>

</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()) { ?>

<tr class="border-b odd:bg-gray-50 hover:bg-gray-100">

<td class="px-3 py-2 capitalize">
<?= htmlspecialchars($row["report_name"]) ?>
</td>

<td class="px-3 py-2 font-mono text-xs">
<?= htmlspecialchars($row["report_id"]) ?>
</td>

<td class="px-3 py-2">
<?= $row["created_at"] ?>
</td>

<td class="px-3 py-2">

<a
href="view.php?report=<?= urlencode($row["report_name"]) ?>&rid=<?= urlencode($row["report_id"]) ?>"
class="text-blue-600">
Open
</a>

</td>

<?php if ($canDelete) { ?>

<td class="px-3 py-2">

<a
href="?delete=<?= urlencode($row["report_id"]) ?>"
onclick="return confirm('Delete this report?')"
class="text-red-600">
Delete
</a>

</td>

<?php } ?>

</tr>

<?php } ?>

</tbody>

</table>


<div class="flex gap-2 mt-6">

<?php for ($i=1;$i<=$totalPages;$i++) { ?>

<a
href="?page=<?= $i ?>&sort=<?= $sort ?>&order=<?= $order ?>"
class="px-3 py-1 border rounded <?= $i==$page ? 'bg-blue-600 text-white':'bg-white' ?>">
<?= $i ?>
</a>

<?php } ?>

</div>

</div>

</div>

<?php
$content = ob_get_clean();
require "../views/layout.php";
?>