<?php
require "auth.php";
require "db.php";

$pageTitle = "Charts";

ob_start();

$types = $mysqli->query("
SELECT event_type, COUNT(*) as total
FROM events
GROUP BY event_type
");

$typeLabels = [];
$typeCounts = [];

while ($row = $types->fetch_assoc()) {
$typeLabels[] = $row["event_type"];
$typeCounts[] = (int)$row["total"];
}

$methods = $mysqli->query("
SELECT request_method, COUNT(*) as total
FROM events
GROUP BY request_method
");

$methodLabels = [];
$methodCounts = [];

while ($row = $methods->fetch_assoc()) {
$methodLabels[] = $row["request_method"];
$methodCounts[] = (int)$row["total"];
}

$timeline = $mysqli->query("
SELECT DATE_FORMAT(created_at,'%H:%i') as minute, COUNT(*) as total
FROM events
GROUP BY minute
ORDER BY minute
");

$timeLabels = [];
$timeCounts = [];

while ($row = $timeline->fetch_assoc()) {
$timeLabels[] = $row["minute"];
$timeCounts[] = (int)$row["total"];
}

?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-6xl mx-auto">

<h1 class="text-2xl font-semibold mb-6">Analytics Charts</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

<div class="bg-white shadow rounded-lg p-6">
<h2 class="font-semibold mb-4">Events by Type</h2>
<canvas id="typeChart"></canvas>
</div>

<div class="bg-white shadow rounded-lg p-6">
<h2 class="font-semibold mb-4">HTTP Method Distribution</h2>
<canvas id="methodChart"></canvas>
</div>

</div>

<div class="bg-white shadow rounded-lg p-6 mt-6">
<h2 class="font-semibold mb-4">Events Per Minute</h2>
<canvas id="timelineChart"></canvas>
</div>

</div>

<script>

new Chart(document.getElementById('typeChart'),{

type:'bar',

data:{
labels:<?= json_encode($typeLabels) ?>,
datasets:[{
label:'Events',
data:<?= json_encode($typeCounts) ?>,
backgroundColor:'#3b82f6'
}]
},

options:{
plugins:{legend:{display:false}},
scales:{y:{beginAtZero:true}}
}

});

new Chart(document.getElementById('methodChart'),{

type:'pie',

data:{
labels:<?= json_encode($methodLabels) ?>,
datasets:[{
data:<?= json_encode($methodCounts) ?>,
backgroundColor:['#3b82f6','#ef4444','#10b981','#f59e0b']
}]
}

});

new Chart(document.getElementById('timelineChart'),{

type:'line',

data:{
labels:<?= json_encode($timeLabels) ?>,
datasets:[{
label:'Events per Minute',
data:<?= json_encode($timeCounts) ?>,
borderColor:'#3b82f6',
backgroundColor:'rgba(59,130,246,0.2)',
tension:0.3
}]
},

options:{
scales:{y:{beginAtZero:true}}
}

});

</script>

<?php
$content = ob_get_clean();
require "views/layout.php";
?>