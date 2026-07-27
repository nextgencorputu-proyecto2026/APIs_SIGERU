<?php

session_start();


$usuarios = [
    "sebastian@sigeru.com"      => "123456",
    "leonardo@sigeru.com"  => "123456",
    "mateo@sigeru.com"       => "123456",
    "profe@sigeru.com"      => "123456",
    "guillermo@sigeru.com"   => "123456"
];

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

if (isset($usuarios[$email]) && $usuarios[$email] === $password) {

    header("Location: ../Frontend_SIGERU/home.html");
    exit();

} else {

    echo "Usuario o contraseña incorrectos.";

}