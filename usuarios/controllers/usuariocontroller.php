<?php

session_start();

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

if (isset($usuarios[$email]) && $usuarios[$email] === $password) {

    header("Location: ../../../Frontend_SIGERU/home.html");
    exit();

} else {

    echo "Usuario o contraseña incorrectos.";

}