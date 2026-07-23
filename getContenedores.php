<?php

//-------  DATOS  ----------------
$contenedores = [
    [
        "ID"           => "001",
        "Nv_Llenado"   => 85,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1342,
        "Ubi_Y"        => -34.8981,
        "Ruta"         => 1,
        "Tipo_Residuo" => "Generales"
    ],
    [
        "ID"           => "002",
        "Nv_Llenado"   => 40,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1310,
        "Ubi_Y"        => -34.8965,
        "Ruta"         => 3,
        "Tipo_Residuo" => "Reciclables"
    ],
    [
        "ID"           => "003",
        "Nv_Llenado"   => 95,
        "Estado"       => "No funcional",
        "Ubi_X"        => -56.1285,
        "Ubi_Y"        => -34.8992,
        "Ruta"         => 2,
        "Tipo_Residuo" => "Generales"
    ],
    [
        "ID"           => "004",
        "Nv_Llenado"   => 15,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1360,
        "Ubi_Y"        => -34.8940,
        "Ruta"         => 4,
        "Tipo_Residuo" => "Reciclables"
    ],
    [
        "ID"           => "005",
        "Nv_Llenado"   => 70,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1328,
        "Ubi_Y"        => -34.8915,
        "Ruta"         => 1,
        "Tipo_Residuo" => "Generales"
    ],
    [
        "ID"           => "006",
        "Nv_Llenado"   => 90,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1250,
        "Ubi_Y"        => -34.8970,
        "Ruta"         => 2,
        "Tipo_Residuo" => "Generales"
    ],
    [
        "ID"           => "007",
        "Nv_Llenado"   => 30,
        "Estado"       => "No funcional",
        "Ubi_X"        => -56.1298,
        "Ubi_Y"        => -34.9012,
        "Ruta"         => 3,
        "Tipo_Residuo" => "Reciclables"
    ],
    [
        "ID"           => "008",
        "Nv_Llenado"   => 60,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1375,
        "Ubi_Y"        => -34.8955,
        "Ruta"         => 4,
        "Tipo_Residuo" => "Generales"
    ],
    [
        "ID"           => "009",
        "Nv_Llenado"   => 10,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1330,
        "Ubi_Y"        => -34.9030,
        "Ruta"         => 1,
        "Tipo_Residuo" => "Reciclables"
    ],
    [
        "ID"           => "010",
        "Nv_Llenado"   => 82,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1268,
        "Ubi_Y"        => -34.8938,
        "Ruta"         => 2,
        "Tipo_Residuo" => "Generales"
    ],
    [
        "ID"           => "011",
        "Nv_Llenado"   => 50,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1388,
        "Ubi_Y"        => -34.8998,
        "Ruta"         => 3,
        "Tipo_Residuo" => "Reciclables"
    ],
    [
        "ID"           => "012",
        "Nv_Llenado"   => 100,
        "Estado"       => "No funcional",
        "Ubi_X"        => -56.1235,
        "Ubi_Y"        => -34.8950,
        "Ruta"         => 4,
        "Tipo_Residuo" => "Generales"
    ],
    [
        "ID"           => "013",
        "Nv_Llenado"   => 25,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1351,
        "Ubi_Y"        => -34.9021,
        "Ruta"         => 1,
        "Tipo_Residuo" => "Reciclables"
    ],
    [
        "ID"           => "014",
        "Nv_Llenado"   => 78,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1290,
        "Ubi_Y"        => -34.8925,
        "Ruta"         => 2,
        "Tipo_Residuo" => "Generales"
    ],
    [
        "ID"           => "015",
        "Nv_Llenado"   => 65,
        "Estado"       => "Funcional",
        "Ubi_X"        => -56.1315,
        "Ubi_Y"        => -34.8988,
        "Ruta"         => 3,
        "Tipo_Residuo" => "Reciclables"
    ]
];

//------------------------------------------

//   API

header("Content-Type: application/json");

// Busqueda por ID
if (isset($_GET["id"]) && $_GET["id"] !== "") {

    $id = $_GET["id"];

    foreach ($contenedores as $contenedor) {

        if ($contenedor["id"] == $id) {
            echo json_encode($contenedor);
            exit;
        }

    }

}else{

// Sin parámetros devuelve todos los contenedores
echo json_encode($contenedores);
}

