<?php
require "auth.php";
require "db.php";

$pageTitle = "Dashboard";

$result = $mysqli->query("
SELECT id, session_id, request_method, event_type, url, created_at
FROM events
ORDER BY id DESC
LIMIT 100
");

$countResult = $mysqli->query("SELECT COUNT(*) AS total FROM events");
$totalEvents = $countResult->fetch_assoc()["total"];

ob_start();
?>

<h1>Analytics Dashboard</h1>

<div class="stats">
Total Events Recorded: <strong><?php echo $totalEvents; ?></strong>
</div>

<table>

<tr>
<th>ID</th>
<th>Session</th>
<th>Method</th>
<th>Type</th>
<th>URL</th>
<th>Created</th>
</tr>

<?php while ($row = $result->fetch_assoc()) { ?>

<tr>
<td><?php echo $row["id"]; ?></td>
<td><?php echo $row["session_id"]; ?></td>
<td><?php echo $row["request_method"]; ?></td>
<td><?php echo $row["event_type"]; ?></td>
<td><?php echo $row["url"]; ?></td>
<td><?php echo $row["created_at"]; ?></td>
</tr>

<?php } ?>

</table>

<script>
setTimeout(function(){
location.reload();
},10000);
</script>

<?php
$content = ob_get_clean();
require "views/layout.php";