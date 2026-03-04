<?php
require "auth.php";
require "db.php";

$pageTitle = "Charts";

/* Events by Type */
$result1 = $mysqli->query("
SELECT event_type, COUNT(*) AS count
FROM events
GROUP BY event_type
");

$type_labels = [];
$type_data = [];

while ($row = $result1->fetch_assoc()) {
$type_labels[] = $row["event_type"];
$type_data[] = $row["count"];
}

/* HTTP Method Breakdown */
$result2 = $mysqli->query("
SELECT request_method, COUNT(*) AS count
FROM events
GROUP BY request_method
");

$method_labels = [];
$method_data = [];

while ($row = $result2->fetch_assoc()) {
$method_labels[] = $row["request_method"];
$method_data[] = $row["count"];
}

/* Events Per Minute */
$result3 = $mysqli->query("
SELECT DATE_FORMAT(created_at,'%H:%i') AS minute, COUNT(*) AS count
FROM events
GROUP BY minute
ORDER BY minute
");

$minute_labels = [];
$minute_data = [];

while ($row = $result3->fetch_assoc()) {
$minute_labels[] = $row["minute"];
$minute_data[] = $row["count"];
}

ob_start();
?>

<h1>Analytics Charts</h1>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:30px">

<div>
<h3>Events by Type</h3>
<canvas id="typeChart"></canvas>
</div>

<div>
<h3>HTTP Method Distribution</h3>
<canvas id="methodChart"></canvas>
</div>

</div>

<br><br>

<div>
<h3>Events Per Minute</h3>
<canvas id="timelineChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const typeLabels = <?php echo json_encode($type_labels); ?>;
const typeData = <?php echo json_encode($type_data); ?>;

new Chart(document.getElementById("typeChart"),{
type:"bar",
data:{
labels:typeLabels,
datasets:[{
label:"Events by Type",
data:typeData
}]
}
});

const methodLabels = <?php echo json_encode($method_labels); ?>;
const methodData = <?php echo json_encode($method_data); ?>;

new Chart(document.getElementById("methodChart"),{
type:"pie",
data:{
labels:methodLabels,
datasets:[{
data:methodData
}]
}
});

const minuteLabels = <?php echo json_encode($minute_labels); ?>;
const minuteData = <?php echo json_encode($minute_data); ?>;

new Chart(document.getElementById("timelineChart"),{
type:"line",
data:{
labels:minuteLabels,
datasets:[{
label:"Events per Minute",
data:minuteData,
fill:false
}]
}
});

</script>

<?php
$content = ob_get_clean();
require "views/layout.php";