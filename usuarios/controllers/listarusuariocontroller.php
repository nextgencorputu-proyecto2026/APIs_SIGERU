<?php

header("Content-Type: application/json");

if (isset($_GET["nombre"]) && $_GET["nombre"] !== "") {

    $nombre = $_GET["nombre"];

    $resultado = [];

    foreach ($usuarios as $usuario) {

        if (stripos($usuario["nombre"], $nombre) !== false) {
            $resultado[] = $usuario;
        }

    }

    echo json_encode($resultado);

} else {

    echo json_encode($usuarios);

}