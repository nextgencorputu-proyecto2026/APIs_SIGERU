<?php

session_start();

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

$usuarioEncontrado = null;

foreach ($usuarios as $usuario) {
    if ($usuario["email"] === $email && $usuario["password"] === $password) {
        $usuarioEncontrado = $usuario;
    }
}

if ($usuarioEncontrado) {

    header("Location: ../../../Frontend_SIGERU/home.html");

} else {

    echo "Usuario o contraseña incorrectos.";

}