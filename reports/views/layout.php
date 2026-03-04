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
<title><?= $pageTitle ?></title>

<style>

body{
font-family: Arial, Helvetica, sans-serif;
margin:20px;
}

nav{
margin-bottom:15px;
display:flex;
justify-content:space-between;
align-items:center;
}

nav a{
margin-right:15px;
text-decoration:none;
color:#0056b3;
font-weight:500;
}

.user{
font-size:14px;
color:#444;
}

.stats{
margin-bottom:15px;
font-size:14px;
color:#333;
}

table{
border-collapse:collapse;
width:100%;
}

th,td{
border:1px solid #ddd;
padding:4px;
font-size:14px;
}

th{
background:#f0f0f0;
}

</style>

</head>

<body>

<nav>

<div>
<a href="dashboard.php">Dashboard</a>
<a href="charts.php">Charts</a>
<a href="logout.php">Logout</a>
</div>

<div class="user">
Logged in as <?= $_SESSION["user"] ?? "user" ?>
</div>

</nav>

<?= $content ?>

</body>
</html>