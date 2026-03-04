<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username === "teamuser" && $password === "tEamDev26!") {

        $_SESSION["user"] = $username;

        header("Location: dashboard.php");
        exit;

    } else {
        $error = "Invalid login";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports Login</title>
</head>

<body>

<h1>Reporting Login</h1>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<form method="POST">

<label>Username</label><br>
<input name="username"><br><br>

<label>Password</label><br>
<input type="password" name="password"><br><br>

<button type="submit">Login</button>

</form>

</body>
</html>