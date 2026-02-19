<?php
    session_start();
    // comprobar si las cookies idioma y tema existen; si es asi, se guarda su valor en su variable correspondiente
    // en caso de no existir, se le asigna un valor por defecto; idioma = esp, tema = claro
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


    // conexión base de datos
    include_once '../BBDD/Conexion.php';

    // recoger los productos de la BD
    if (isset($miPDO)) {
        $sentencia_recoger_productos = 'SELECT * FROM productos';
        $recoger_productos = $miPDO->prepare($sentencia_recoger_productos);
        $recoger_productos->execute();
        $productos = $recoger_productos->fetchAll();
    }
    

    // comprueba si la cookie deseados existe y si existe retorna su valor, si no retorna un array vacio
    function conseguir_deseados() {
        if (isset($_COOKIE['deseados'])) {
            return json_decode($_COOKIE['deseados'], true);
        }
        return [];
    }

    // guarda los productos que se quieran añadir a la lista de deseados en una cookie; dura 7 dias
    function guardar_deseados($deseados) {
        setcookie('deseados', json_encode($deseados), time()+60*60*24*7);
    }

    
    // comprueba si la cookie carrito existe y si existe retorna su valor, si no retorna un array vacio
    function conseguir_carrito() {
        if (isset($_COOKIE['carrito'])) {
            return json_decode($_COOKIE['carrito'], true);
        }
        return [];
    }

    // guarda los productos que se quieran añadir al carrito en una cookie; dura 7 dias
    function guardar_carrito($carrito) {
        setcookie('carrito', json_encode($carrito), time()+60*60*24*7);
    }
    

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // se comprueba si el producto tiene stock
        // al añadir un producto a los deseados se comprueba si ya existe en la lista, en caso de existir se le suma 1 a la cantidad,
        // en caso contrario se añade al array de productos con la id del producto y su contenido, al final se guarda en la cookie
        if (isset($_POST['add_wishlist'])) {
            $album_id = ($_POST['add_wishlist'] - 1);

            if ($productos[$album_id]['stock'] == 'no') {
                $mensaje = $idioma=='esp' ? 'Album no disponible' : 'Album not available';
                echo '<script>alert("' . $mensaje . '")</script>';
            } elseif (isset($album_id)) {
                $deseados = conseguir_deseados();

                if (isset($deseados[$_SESSION['username']][$album_id])) {
                    $deseados[$_SESSION['username']][$album_id]['cantidad']++;
                } else {
                    $deseados[$_SESSION['username']][$album_id] = [
                        'album_name' => $productos[$album_id]['nombre'],
                        'album_price' => $productos[$album_id]['precio'],
                        'cantidad' => 1,
                    ];
                }

                guardar_deseados($deseados);
                header('Location: tienda.php');
                die();
            }
        }

        if (isset($_POST['add_cart'])) {
            $album_id = ($_POST['add_cart'] - 1);

            if ($productos[$album_id]['stock'] == 'no') {
                $mensaje = $idioma=='esp' ? 'Album no disponible' : 'Album not available';
                echo '<script>alert("' . $mensaje . '")</script>';
            } elseif (isset($album_id)) {
                $carrito = conseguir_carrito();

                if (isset($carrito[$album_id])) {
                    $carrito[$_SESSION['username']][$album_id]['cantidad']++;
                } else {
                    $carrito[$_SESSION['username']][$album_id] = [
                        'album_name' => $productos[$album_id]['nombre'],
                        'album_price' => $productos[$album_id]['precio'],
                        'cantidad' => 1,
                    ];
                }

                guardar_carrito($carrito);
                header('Location: tienda.php');
                die();
            }
        }


        // agregar, eliminar o modificar producto
        if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador') {

            if (isset($_POST['añadir_producto'])) {
                header('Location: ./añadir_producto.php');
                die();
            }

            if (isset($_POST['eliminar_producto'])) {
                header('Location: ./eliminar_producto.php');
                die();
            }

            if (isset($_POST['modificar_producto'])) {
                header('Location: ./modificar_producto.php');
                die();
            }
        }
    }
    
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $tema ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $idioma=='esp'?'Tienda':'Shop'; ?></title>

        <link rel="stylesheet" href="../css/tienda.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>
    <body>
        <!-- header -->
        <?php include '../php/header.php' ?>

        <!-- contenido -->
        <div class="contenido">

            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador'): ?>
                <div class="botonesAdmin">
                    <form action="" method="post">
                        <button type="submit" name="añadir_producto"><?php echo $idioma=='esp' ? 'Añadir' : 'Add' ?></button>
                        <button type="submit" name="eliminar_producto"><?php echo $idioma=='esp' ? 'Eliminar' : 'Delete' ?></button>
                        <button type="submit" name="modificar_producto"><?php echo $idioma=='esp' ? 'Modificar' : 'Modify' ?></button>
                    </form>
                </div>
            <?php endif; ?>

            <h1>ALBUMS</h1>
            
            <div class="productos" id="productos">
                <?php if(isset($BD_error)): ?>
                    <p class="bd_error"><?php echo $BD_error ?></p>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                        <?php if ($producto['stock'] == "si"): ?>
                            <form action="" method="post" class="producto">
                                <p class="album_name"><?php echo $producto['nombre'] ?></p>
                                <img class="album_img" src="<?php echo $producto['img'] ?>" alt="Imagen de <?php echo $producto['nombre'] ?>">
                                <p class="album_price">
                                    <?php 
                                        echo $producto['precio'] . '€';
                                    ?>
                                </p>
                                <div class="botones">
                                    <button class="album_add_wishlist" type="submit" name="add_wishlist" value="<?php echo $producto['id_producto'] ?>">
                                        <?php echo $idioma == 'esp' ? 'Añadir a deseados' : 'Add to wishlist'; ?>
                                    </button>

                                    <button class="album_add_cart" type="submit" name="add_cart" value="<?php echo $producto['id_producto'] ?>">
                                        <?php echo $idioma == 'esp' ? 'Añadir al carrito' : 'Add to cart'; ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- footer -->
        <?php include_once '../php/footer.php' ?>
    </body>
   
    
    <script src="../js/menu.js"></script>
</html>