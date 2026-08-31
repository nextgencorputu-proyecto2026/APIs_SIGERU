<?php

if (isset($_GET["matricula"]) && $_GET["matricula"] !== "") {

    $matricula = $_GET["matricula"];

    $resultado = [];

    foreach ($camiones as $camion) {

        if (stripos($camion["matricula"], $matricula) !== false) {
            $resultado[] = $camion;
        }
    }

    echo json_encode($resultado);

} else {

    echo json_encode($camiones);
    
}