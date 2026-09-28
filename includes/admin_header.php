<?php

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");

if (!isset($_SESSION)) {
    session_start();
}

$nombreUsuario = $_SESSION["usuario_nombre"] ?? "Usuario";
$rolUsuario = $_SESSION["usuario_rol"] ?? "";

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= $tituloPagina ?? "Dashboard" ?> | Infofix Manager</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

<div class="admin-layout">