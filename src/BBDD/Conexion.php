<?php
    //Variables
    $hostDB = '127.0.0.1';
    $nombreDB = 'tienda';
    $usuarioDB = 'root';
    $contraDB = '';
    $hostPDO = "mysql:host=$hostDB;dbname=$nombreDB";

    //Establecer conexión
    try{
        $miPDO = new PDO($hostPDO, $usuarioDB, $contraDB);
    } catch (PDOException $e){
        $BD_error = "Error al conectar con la base de datos";
        // print "¡Error!: " . $e->getMessage() . "<br>";
        // die();
    }
?>
