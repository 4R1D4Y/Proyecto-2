<?php
    session_start();

    if (!isset($_COOKIE['idioma'])) {
        $idioma = 'esp';
    } else {
        $idioma = $_COOKIE['idioma'];
    }

    if (!isset($_COOKIE['modo'])) {
        $tema = 'claro';
    } else {
        $tema = $_COOKIE['modo'];
    }

    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrador') {
        header('Location: ../pages/tienda.php');
        die();
    }

    //bd
    include_once '../BBDD/Conexion.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $errores = [];


        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';

        $patron_nombre = '/^[A-Za-záéíóúÁÉÍÓÚñÑ0-9]+$/';
        if (empty($nombre)) {
            $errores[] = $idioma=='esp' ? 'El nombre no puede estar vacio' : 'The name cant be empty';
        } elseif (!preg_match($patron_nombre, $nombre)) {
            $errores[] = $idioma=='esp' ? 'Nombre no válido' : 'Invalid name';
        }


        if (empty($errores)) {
            if (isset($miPDO)) {
                $sentencia_recoger_productos = 'SELECT nombre FROM productos WHERE nombre=?';
                $recoger_productos = $miPDO->prepare($sentencia_recoger_productos);
                $recoger_productos->execute([$nombre]);
                $productos = $recoger_productos->fetchAll();    

                if (!empty($productos)) {
                    $sentencia_añadir_producto = 'UPDATE productos SET stock="no" WHERE nombre=?';
                    $añadir_producto = $miPDO->prepare($sentencia_añadir_producto);
                    $añadir_producto->execute([$nombre]);
                } else {
                    $errores[] = $idioma=='esp' ? 'No existe un producto con ese nombre' : 'There is no product with that name';
                }
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $tema ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>

        <link rel="stylesheet" href="../css/eliminar_productos.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>

    <?php include_once '../php/header.php' ?>

    <body>
        <div class="eliminarProducto">
            <h1><?php echo $idioma=='esp' ? 'Eliminar un producto' : 'Delete product' ?></h1>
            <?php if (!isset($BD_error)): ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <label>
                        <?php echo $idioma=='esp' ? 'Nombre del Album:' : 'Album name:' ?>
                        <input type="text" name="nombre">
                    </label>
                    <button type="submit">
                        <?php echo $idioma=='esp' ? 'Eliminar' : 'Delete'; ?>
                    </button>
                </form>
            <?php else: ?>
                <p class="bd_error"><?php echo $BD_error; ?><p>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo $error ?></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </body>

    <?php include_once '../php/footer.php' ?>

    <script src="../js/menu.js"></script>
</html>