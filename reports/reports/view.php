<?php
require "../auth.php";
require "../db.php";
date_default_timezone_set('America/Los_Angeles');

$report = $_GET["report"] ?? "traffic";
$reportId = $_GET["rid"] ?? uniqid("report_", true);

$stmt = $mysqli->prepare("
INSERT IGNORE INTO reports (report_id, report_name, created_by)
VALUES (?, ?, ?)
");

$stmt->bind_param(
    "sss",
    $reportId,
    $report,
    $_SESSION["user"]
);

$stmt->execute();
$stmt->close();

$generatedAt = date("M j, Y g:i A T");
$pageTitle = "Report";

/*
|--------------------------------------------------------------------------
| Save Analyst Comment
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_SESSION["role"] !== "viewer") {
    $comment = trim($_POST["comment"] ?? "");
    if ($comment !== "") {
        $stmt = $mysqli->prepare("
        INSERT INTO analyst_comments (report_id, report_name, comment)
        VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $reportId, $report, $comment);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: view.php?report=" . urlencode($report) . "&rid=" . urlencode($reportId));
    exit;
}

/*
|--------------------------------------------------------------------------
| Dashboard Metrics
|--------------------------------------------------------------------------
*/

$metrics = $mysqli->query("
SELECT
    COUNT(DISTINCT session_id) AS active_users,
    COUNT(*) AS total_events,
    COUNT(DISTINCT url) AS unique_pages
FROM events
")->fetch_assoc();

$realtime = $mysqli->query("
SELECT COUNT(DISTINCT session_id) AS live_users
FROM events
WHERE created_at >= NOW() - INTERVAL 5 MINUTE
")->fetch_assoc();

$liveUsers = $realtime["live_users"] ?? 0;

$top = $mysqli->query("
SELECT url, COUNT(*) AS pageviews
FROM events
WHERE event_type='page-enter'
GROUP BY url
ORDER BY pageviews DESC
LIMIT 1
")->fetch_assoc();

$totalEvents = $metrics["total_events"] ?? 0;
$uniquePages = $metrics["unique_pages"] ?? 0;
$uniqueVisitors = $metrics["active_users"] ?? 0;
$topPage = $top["url"] ?? "-";
$topVisits = $top["pageviews"] ?? 0;

ob_start();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-6xl mx-auto">

    <h1 class="text-3xl font-bold mb-2">
        <?= ucfirst(htmlspecialchars($report)) ?> Analytics Report
    </h1>

    <div class="text-sm text-gray-500 mb-6">
        Generated: <?= $generatedAt ?><br>
        Report ID: <?= htmlspecialchars($reportId) ?>
    </div>

    <div class="flex gap-4 mb-6">
        <a href="view.php?report=<?= urlencode($report) ?>"
           class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900">
            Generate New Report
        </a>
        <button onclick="exportReport()"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Export PDF
        </button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white shadow rounded-lg p-4">
            <div class="text-sm text-gray-500">Total Events</div>
            <div class="text-2xl font-bold"><?= $totalEvents ?></div>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <div class="text-sm text-gray-500">Unique Pages</div>
            <div class="text-2xl font-bold"><?= $uniquePages ?></div>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <div class="text-sm text-gray-500">Unique Visitors</div>
            <div class="text-2xl font-bold"><?= $uniqueVisitors ?></div>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <div class="text-sm text-gray-500">Live Users</div>
            <div class="text-2xl font-bold"><?= $liveUsers ?></div>
        </div>
        <div class="bg-white shadow rounded-lg p-4 col-span-2 md:col-span-4">
            <div class="text-sm text-gray-500">Top Page</div>
            <div class="text-sm font-semibold truncate"><?= htmlspecialchars($topPage) ?></div>
            <div class="text-xs text-gray-500"><?= $topVisits ?> pageviews</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Chart Visualization</h2>

            <?php
            // FIX 2: Initialize arrays outside the if/else blocks to prevent undefined variable errors
            $labels = [];
            $data = [];

            /*
            |--------------------------------------------------------------------------
            | Traffic Report
            |--------------------------------------------------------------------------
            */
            if ($report === "traffic") {
                $result = $mysqli->query("
                SELECT domain, COUNT(DISTINCT session_id) AS visits
                FROM events
                WHERE event_type='page-enter'
                GROUP BY domain
                ORDER BY visits DESC
                LIMIT 10
                ");

                while ($row = $result->fetch_assoc()) {
                    $labels[] = $row["domain"] ?? "unknown";
                    $data[] = (int)$row["visits"];
                }
                ?>
                <canvas id="trafficChart"></canvas>
                <script>
                    const trafficLabels = <?= json_encode($labels) ?>;
                    const trafficData = <?= json_encode($data) ?>;

                    new Chart(document.getElementById('trafficChart'), {
                        type: 'bar',
                        data: { labels: trafficLabels, datasets: [{ data: trafficData, backgroundColor: '#3b82f6' }] },
                        options: { 
                            animation: false, // FIX 4: Disable animation so export works instantly
                            plugins: { legend: { display: false } }, 
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } 
                        }
                    });
                </script>
                <?php
            }

            /*
            |--------------------------------------------------------------------------
            | Interaction Report
            |--------------------------------------------------------------------------
            */
            elseif ($report === "interaction") {
                $result = $mysqli->query("
                SELECT event_type, COUNT(*) AS total
                FROM events
                GROUP BY event_type
                ORDER BY total DESC
                ");

                while ($row = $result->fetch_assoc()) {
                    $labels[] = $row["event_type"];
                    $data[] = (int)$row["total"];
                }
                ?>
                <canvas id="interactionChart"></canvas>
                <script>
                    const interactionLabels = <?= json_encode($labels) ?>;
                    const interactionData = <?= json_encode($data) ?>;

                    new Chart(document.getElementById('interactionChart'), {
                        type: 'bar',
                        data: { labels: interactionLabels, datasets: [{ data: interactionData, backgroundColor: '#10b981' }] },
                        options: { 
                            animation: false, 
                            plugins: { legend: { display: false } }, 
                            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } 
                        }
                    });
                </script>
                <?php
            }

            /*
            |--------------------------------------------------------------------------
            | Performance Report (FIXED)
            |--------------------------------------------------------------------------
            */
            elseif ($report === "performance") {
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

                while ($row = $result->fetch_assoc()) {
                    $dt = new DateTime($row["created_at"], new DateTimeZone("UTC"));
$dt->setTimezone(new DateTimeZone("America/Los_Angeles"));
$labels[] = $dt->format("H:i:s");
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

                $histLabels = array_keys($buckets);
                $histData = array_values($buckets);
                
                // FIX 1 & 5: Drop out of PHP entirely instead of echoing strings. 
                // Pass variables natively via JSON encode to bypass secondary fetch requests.
                ?>
                <div class='space-y-8'>
                    <div style='height:350px'><canvas id='performanceTrend'></canvas></div>
                    <div style='height:350px'><canvas id='performanceDistribution'></canvas></div>
                </div>

                <script>
                    const trendLabels = <?= json_encode($labels) ?>;
                    const trendData = <?= json_encode($data) ?>;
                    const histLabels = <?= json_encode($histLabels) ?>;
                    const histData = <?= json_encode($histData) ?>;

                    new Chart(document.getElementById('performanceTrend'), {
                        type: 'line',
                        data: {
                            labels: trendLabels,
                            datasets: [{
                                label: 'Page Load Time (ms)',
                                data: trendData,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245,158,11,0.25)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                                borderWidth: 3
                            }]
                        },
                        options: {
                            animation: false,
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: { display: true, text: 'Load Time Trend', font: { size: 18 } },
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Load Time (ms)' } },
                                x: { title: { display: true, text: 'Time' }, grid: { display: false } }
                            }
                        }
                    });

                    new Chart(document.getElementById('performanceDistribution'), {
                        type: 'bar',
                        data: {
                            labels: histLabels,
                            datasets: [{
                                data: histData,
                                backgroundColor: '#3b82f6',
                                borderRadius: 6
                            }]
                        },
                        options: {
                            animation: false,
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: { display: true, text: 'Load Time Distribution', font: { size: 18 } },
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                </script>
                <?php
            }
            ?>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Data Table</h2>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b bg-gray-100">
                        <th class="text-left p-2">Label</th>
                        <th class="text-left p-2">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($labels)) {
                        foreach ($labels as $i => $label) {
                            echo "<tr class='border-b'>";
                            echo "<td class='p-2'>" . htmlspecialchars($label) . "</td>";
                            echo "<td class='p-2'>" . $data[$i] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2' class='p-4 text-center text-gray-500'>No data available.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="bg-white shadow rounded-lg p-6 mt-6">

<h2 class="text-lg font-semibold mb-4">Report Notes</h2>

<?php if ($_SESSION["role"] !== "viewer"): ?>
<form method="POST">

<textarea
name="comment"
rows="4"
class="w-full border rounded p-3 mb-3"
placeholder="Add analyst notes about this report..."
required
></textarea>

<button
type="submit"
class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
>
Save Comment
</button>

</form>
<?php endif; ?>

</div>
</div>

<?php $content = ob_get_clean(); ?>

<script>
// FIX 3: Combine multiple canvases into a single image export
function exportReport() {
    const canvases = document.querySelectorAll("canvas");
    if (canvases.length === 0) {
        alert("No charts available to export.");
        return;
    }

    // Calculate the total height and maximum width needed for the combined image
    let totalHeight = 0;
    let maxWidth = 0;
    
    canvases.forEach(canvas => {
        totalHeight += canvas.height;
        if (canvas.width > maxWidth) {
            maxWidth = canvas.width;
        }
    });

    // Create an off-screen canvas to stitch them together
    const combinedCanvas = document.createElement("canvas");
    combinedCanvas.width = maxWidth;
    combinedCanvas.height = totalHeight;
    const ctx = combinedCanvas.getContext("2d");

    // Fill with a white background so PNG transparency doesn't turn black in PDFs
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, combinedCanvas.width, combinedCanvas.height);

    // Draw each canvas onto the combined canvas
    let currentY = 0;
    canvases.forEach(canvas => {
        ctx.drawImage(canvas, 0, currentY);
        currentY += canvas.height;
    });

    const chartImage = combinedCanvas.toDataURL("image/png");

    // Submit the form
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "export.php";

    const reportInput = document.createElement("input");
    reportInput.type = "hidden";
    reportInput.name = "report";
    reportInput.value = "<?= htmlspecialchars($report) ?>";

    const ridInput = document.createElement("input");
    ridInput.type = "hidden";
    ridInput.name = "rid";
    ridInput.value = "<?= htmlspecialchars($reportId) ?>";

    const imgInput = document.createElement("input");
    imgInput.type = "hidden";
    imgInput.name = "chart";
    imgInput.value = chartImage;

    form.appendChild(reportInput);
    form.appendChild(ridInput);
    form.appendChild(imgInput);

    document.body.appendChild(form);
    form.submit();
}
</script>

<?php require "../views/layout.php"; ?>