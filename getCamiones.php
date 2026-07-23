<?php
$camiones = [
    [
        "id" => 1,
        "matricula" => "ABC123",
        "tipo" => "Residuos mezclados",
        "ruta" => "Ruta 1",
        "estado" => "En servicio",
        "capacidad" => "10 toneladas"
    ],
    [
        "id" => 2,
        "matricula" => "DEF456",
        "tipo" => "Residuos reciclables",
        "ruta" => "Ruta 2",
        "estado" => "En mantenimiento",
        "capacidad" => "5 toneladas"
    ],
    [
        "id" => 3,
        "matricula" => "GHI789",
        "tipo" => "Residuos mezclados",
        "ruta" => "Ruta 3",
        "estado" => "Disponible",
        "capacidad" => "15 toneladas"
    ],
    [
        "id" => 4,
        "matricula" => "JKL012",
        "tipo" => "Residuos mezclados",
        "ruta" => "Ruta 4",
        "estado" => "En servicio",
        "capacidad" => "8 toneladas"
    ],
    [
        "id" => 5,
        "matricula" => "CDE123",
        "tipo" => "Residuos reciclables",
        "ruta" => "Ruta 5",
        "estado" => "Disponible",
        "capacidad" => "3 toneladas"
    ],
    [
        "id" => 6,
        "matricula" => "PQR678",
        "tipo" => "Residuos mezclados",
        "ruta" => "Ruta 6",
        "estado" => "En mantenimiento",
        "capacidad" => "20 toneladas"
    ],
    [
        "id" => 7,
        "matricula" => "ABC456",
        "tipo" => "Residuos mezclados",
        "ruta" => "Ruta 7",
        "estado" => "Disponible",
        "capacidad" => "12 toneladas"
    ]
];

header("Content-Type: application/json");

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
