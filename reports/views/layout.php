<?php
if (!isset($pageTitle)) {
    $pageTitle = "Reports";
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle) ?></title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 font-sans">

<header class="bg-white shadow-sm border-b">

<div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

<div class="flex items-center space-x-6">

<div class="font-semibold text-lg text-gray-800">
CSE 135 Analytics
</div>

<nav class="flex items-center space-x-4 text-sm">

<?php if ($_SESSION["role"] !== "viewer") { ?>

<a href="/reports/dashboard.php"
class="text-gray-600 hover:text-black">
Dashboard
</a>

<a href="/reports/charts.php"
class="text-gray-600 hover:text-black">
Charts
</a>

<?php } ?>

<a href="/reports/reports/index.php"
class="text-gray-600 hover:text-black">
Reports
</a>

<a href="/reports/logout.php"
class="text-red-500 hover:text-red-700">
Logout
</a>

</nav>

</div>

<div class="text-sm text-gray-600">
Logged in as <?= htmlspecialchars($_SESSION["role"] ?? "user") ?>
</div>

</div>

</header>


<main class="max-w-7xl mx-auto px-6 py-8">

<?= $content ?>

</main>

</body>
</html>