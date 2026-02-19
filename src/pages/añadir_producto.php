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
        $img = isset($_FILES['img']['name']) ? $_FILES['img']['name'] : '';
        $precio = isset($_POST['precio']) ? $_POST['precio'] : '';

        $patron_nombre = '/^[A-Za-záéíóúÁÉÍÓÚñÑ0-9]+$/';
        if (empty($nombre)) {
            $errores[] = $idioma=='esp' ? 'El nombre no puede estar vacio' : 'The name cant be empty';
        } elseif (!preg_match($patron_nombre, $nombre)) {
            $errores[] = $idioma=='esp' ? 'Nombre no válido' : 'Invalid name';
        }

        if (empty($img)) {
            $errores[] = $idioma=='esp' ? 'La imagen no puede estar vacia' : 'The image cant be empty';
        } else {
            $directorio = '../images/';
            if (!dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            if (!move_uploaded_file($_FILES['img']['tmp_name'], $directorio . basename($img))) {
                $errores[] = "Error al mover el archivo $img";
            }
            $imagen_movida = $directorio . basename($img);
        }

        $patron_precio = '/^\d+\.\d{2}$/';
        if (empty($precio)) {
            $errores[] = $idioma=='esp' ? 'El precio no puede estar vacio' : 'The price cant be empty';
        } elseif (!preg_match($patron_precio, $precio)) {
            $errores[] = $idioma=='esp' ? 'Precio no valido' : 'Invalid price';
        }


        if (empty($errores)) {
            if (isset($miPDO)) {
                $sentencia_recoger_productos = 'SELECT nombre FROM productos WHERE nombre=?';
                $recoger_productos = $miPDO->prepare($sentencia_recoger_productos);
                $recoger_productos->execute([$nombre]);
                $productos = $recoger_productos->fetchAll();    

                if (empty($productos)) {
                    $sentencia_añadir_producto = 'INSERT INTO productos VALUE (DEFAULT, ?, ?, ?, DEFAULT)';
                    $añadir_producto = $miPDO->prepare($sentencia_añadir_producto);
                    $añadir_producto->execute([$nombre, $imagen_movida, $precio]);
                } else {
                    $errores[] = $idioma=='esp' ? 'Ya existe un producto con ese nombre' : 'A product with that name already exists';
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

        <link rel="stylesheet" href="../css/añadir_productos.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>

    <?php include_once '../php/header.php' ?>

    <body>
        <div class="añadirProducto">
            <h1><?php echo $idioma=='esp' ? 'Añadir un producto' : 'Add product' ?></h1>
            <?php if (!isset($BD_error)): ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <label>
                        <?php echo $idioma=='esp' ? 'Nombre del Album:' : 'Album name:' ?>
                        <input type="text" name="nombre">
                    </label>
                    <label>
                        <?php echo $idioma=='esp' ? 'Portada del album:' : 'Album cover:' ?>
                        <input type="file" name="img">
                    </label>
                    <label>
                        <?php echo $idioma=='esp' ? 'Precio del album:' : 'Album price:' ?>
                        <input type="text" name="precio">
                    </label>
                    <button type="submit">
                        <?php echo $idioma=='esp' ? 'Añadir' : 'Add'; ?>
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