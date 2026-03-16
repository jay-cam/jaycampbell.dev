<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    // ------------------------------------------------
    // SUPER ADMIN (existing grader account)
    // ------------------------------------------------
    if ($username === "teamuser" && $password === "tEamDev26!") {

        $_SESSION["user"] = $username;
        $_SESSION["role"] = "super_admin";

        header("Location: dashboard.php");
        exit;
    }

    // ------------------------------------------------
    // ANALYST ACCOUNT
    // ------------------------------------------------
    elseif ($username === "analyst" && $password === "analystCSE135!") {

        $_SESSION["user"] = $username;
        $_SESSION["role"] = "analyst";

        header("Location: dashboard.php");
        exit;
    }

    // ------------------------------------------------
    // VIEWER ACCOUNT
    // ------------------------------------------------
    elseif ($username === "viewer" && $password === "viewerCSE135!") {

        $_SESSION["user"] = $username;
        $_SESSION["role"] = "viewer";

        header("Location: reports/index.php");
        exit;
    }

    else {
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