<?php
    // iniciar una sesion
    session_start();

    // preferencias de la página
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

    // comprobar si el rol es admin
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'administrador') {
        header('Location: ../pages/tienda.php');
        die();
    }

    //bd
    include_once '../BBDD/Conexion.php';
    

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $errores = [];
        $num_vacio = 0;
        
        $nombre_modificar = isset($_POST['nombre_modificar']) ? $_POST['nombre_modificar'] : '';

        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
        $img = isset($_FILES['img']['name']) ? $_FILES['img']['name'] : '';
        $precio = isset($_POST['precio']) ? $_POST['precio'] : '';
        $stock = isset($_POST['stock']) ? $_POST['stock'] : '';


        $patron_nombre = '/^[A-Za-záéíóúÁÉÍÓÚñÑ0-9]+$/';
        if (empty($nombre_modificar)) {
            $errores[] = $idioma=='esp' ? 'El nombre del producto a modificar no puede estar vacio' : 'The name of the product to modify cant be empty';
        } elseif (!preg_match($patron_nombre, $nombre_modificar)) {
            $errores[] = $idioma=='esp' ? 'Nombre no válido' : 'Invalid name';
        }

        if (isset($miPDO)) {
            $sentencia_recoger_productos = 'SELECT * FROM productos WHERE nombre=?';
            $recoger_productos = $miPDO->prepare($sentencia_recoger_productos);
            $recoger_productos->execute([$nombre_modificar]);
            $productos = $recoger_productos->fetchAll();
        }

        if (!empty($nombre) && !preg_match($patron_nombre, $nombre)) {
            $errores[] = $idioma=='esp' ? 'Nombre no válido' : 'Invalid name';
        } elseif (empty($nombre)) {
            if (isset($productos) && !empty($productos)){
                $nombre = $productos[0]['nombre'];
            }
            $num_vacio++;
        }


        if (!empty($img)) {
            $directorio = '../images/';
            if (!dir($directorio)) {
                mkdir($directorio, 0755, true);
            }

            if (!move_uploaded_file($_FILES['img']['tmp_name'], $directorio . basename($img))) {
                $errores[] = "Error al mover el archivo $img";
            }
            $img = $directorio . basename($img);
        } elseif (empty($img)) {
            if (isset($productos) && !empty($productos)){
                $img = $productos[0]['img'];
            }
            $num_vacio++;
        }
        
    
        $patron_precio = '/^\d+\.\d{2}$/';
        if (!empty($precio) && !preg_match($patron_precio, $precio)) {
            $errores[] = $idioma=='esp' ? 'Precio no valido' : 'Invalid price';
        } elseif (empty($precio))  {
            if (isset($productos) && !empty($productos)){
                $precio = $productos[0]['precio'];
            }
            $num_vacio++;
        }


        if (empty($stock)) {
            if (isset($productos) && !empty($productos)){
                $stock = $productos[0]['stock'];
            }
            $num_vacio++;
        }


        if ($num_vacio == 4) {
            $errores[] = $idioma=='esp' ? 'Debe modificar algo' : 'Must modify something';
            if (empty($productos)) {
                $errores[] = $idioma=='esp' ? 'No existe un producto con ese nombre' : 'There is no product with that name';
            }
        }

        if (empty($errores)) {
            if (isset($miPDO)) {
                $sentencia_recoger_productos = 'SELECT nombre FROM productos WHERE nombre=?';
                $recoger_productos = $miPDO->prepare($sentencia_recoger_productos);
                $recoger_productos->execute([$nombre_modificar]);
                $productos = $recoger_productos->fetchAll();    

                if (!empty($productos)) {
                    $sentencia_modificar_producto = 'UPDATE productos SET nombre=?, img=?, precio=?, stock=? WHERE nombre="' . $nombre_modificar .'"';
                    $modificar_producto = $miPDO->prepare($sentencia_modificar_producto);
                    $modificar_producto->execute([$nombre, $img, $precio, $stock]);
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

        <link rel="stylesheet" href="../css/modificar_productos.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>

    <?php include_once '../php/header.php' ?>

    <body>
        <div class="modificarProducto">
            <h1><?php echo $idioma=='esp' ? 'Modificar un producto' : 'Modify product' ?></h1>
            <?php if (!isset($BD_error)): ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <label>
                        <?php echo $idioma=='esp' ? 'Nombre del Album a modificar:' : 'Album name to modify:' ?>
                        <input type="text" name="nombre_modificar">
                    </label>
                    <hr>
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
                    <label>
                        Stock:
                        <label>
                            Si
                            <input type="radio" name="stock" value="si">
                        </label>
                        <label>
                            No
                            <input type="radio" name="stock" value="no">
                        </label>
                    </label>
                    <button type="submit">
                        <?php echo $idioma=='esp' ? 'Modificar' : 'Modify'; ?>
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