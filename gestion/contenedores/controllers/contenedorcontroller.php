<?php 

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