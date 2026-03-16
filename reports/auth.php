<?php
session_start();

/*
|--------------------------------------------------------------------------
| Basic Authentication Check
|--------------------------------------------------------------------------
| Ensures the user is logged in before accessing protected pages.
*/

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Role Helper Functions
|--------------------------------------------------------------------------
| These allow pages to restrict access by role.
|
| Example usage in a page:
|
| require_role(["super_admin","analyst"]);
|
| If the user does not have permission, a 403 response is returned.
*/

function require_role($roles) {

    if (!isset($_SESSION["role"])) {
        http_response_code(403);
        echo "403 Forbidden — No role assigned.";
        exit;
    }

    if (!in_array($_SESSION["role"], $roles)) {
        http_response_code(403);
        echo "403 Forbidden — Insufficient permissions.";
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Optional convenience helpers
|--------------------------------------------------------------------------
*/

function is_super_admin() {
    return ($_SESSION["role"] ?? null) === "super_admin";
}

function is_analyst() {
    return ($_SESSION["role"] ?? null) === "analyst";
}

function is_viewer() {
    return ($_SESSION["role"] ?? null) === "viewer";
}