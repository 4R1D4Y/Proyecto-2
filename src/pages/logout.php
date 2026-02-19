<?php
    // se inicia sesion para luego destruirla y redirigir a la tienda
    session_start();

    session_destroy();

    header('Location: ../../index.php');
    die();
?>