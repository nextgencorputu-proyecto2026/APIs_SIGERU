<?php

header("Content-Type: application/json");

$nombre = isset($_POST["nombre"]) ? trim($_POST["nombre"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
$rol = isset($_POST["rol"]) ? trim($_POST["rol"]) : "";

$errores = [];

if ($nombre === "") {
    $errores[] = "El nombre es obligatorio";
}

if ($email === "") {
    $errores[] = "El email es obligatorio";
}

if ($password === "") {
    $errores[] = "La contraseña es obligatoria";
}

if ($rol === "") {
    $errores[] = "El rol es obligatorio";
}

if (!empty($errores)) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Faltan datos",
        "errores" => $errores
    ]);
} else {
    echo json_encode([
        "success" => true,
        "mensaje" => "Usuario registrado correctamente",
        "data" => [
            "nombre" => $nombre,
            "email" => $email,
            "rol" => $rol
        ]
    ]);
}