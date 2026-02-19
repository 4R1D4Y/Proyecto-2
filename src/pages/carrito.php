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

    //bd
    include_once '../BBDD/Conexion.php';
    
    date_default_timezone_set('Europe/Madrid');

    // se comprueba que la cookie carrito exista, en caso afirmativo se retorna su valor, en caso contrario un array vacio
    function conseguir_carrito() {
        if (isset($_COOKIE['carrito'])) {
            return json_decode($_COOKIE['carrito'], true);
        }
        return [];
    }

    // se guardan los productos en el carrito en una cookie; dura 7 dias
    function guardar_carrito($carrito) {
        setcookie('carrito', json_encode($carrito), time()+60*60*24*7);
    }
    
    // recoger el carrito de la cookie
    $carrito = conseguir_carrito();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        if (isset($_POST['eliminar'])) {
            // eliminar 1
            /*
                al pulsar eliminar 1 se resta 1 de la cantidad de productos
                si la cantidad es menor a 1 se elimina de la lista de deseados
            */
            $prod_id = $_POST['eliminar'];
            
            if (isset($carrito[$_SESSION['username']][$prod_id])) {
                
                if ($carrito[$_SESSION['username']][$prod_id]['cantidad'] > 1) {
                    $carrito[$_SESSION['username']][$prod_id]['cantidad']--;
                } else {
                    unset($carrito[$_SESSION['username']][$prod_id]);
                }

                if (empty($carrito[$_SESSION['username']])) {
                    unset($carrito[$_SESSION['username']]);
                }
                guardar_carrito($carrito);
                
                if (empty($carrito)) {
                    setcookie('carrito', '', time()-60*60*60*60);
                }

                
            }
        } 
        if (isset($_POST['añadir'])) {
            // añadir 1
            /*
                al pulsar añadir 1 se suma 1 de la cantidad de productos
            */
            $prod_id = $_POST['añadir'];

            if (isset($carrito[$_SESSION['username']][$prod_id])) {
                $carrito[$_SESSION['username']][$prod_id]['cantidad']++;
                guardar_carrito($carrito);
            }
        } 
        if (isset($_POST['borrar'])) {
            // borrar
            /*
                al pulsar borrar se elimina la cookie que contiene la lista de deseados
            */
            unset($carrito[$_SESSION['username']]);
            guardar_carrito($carrito);
            
            if (empty($carrito)) {
                setcookie('carrito', '', time()-60*60*60*60);
            }
        } 
        if (isset($_POST['comprar_uno'])) {
            if (isset($_SESSION['username']) && $_SESSION['username'] != 'invitado') {
                $prod_id = $_POST['comprar_uno'];

                if (isset($carrito[$_SESSION['username']][$prod_id])) {
                    $fecha_actual = date("d/m/Y");
                    $total = $carrito[$_SESSION['username']][$prod_id]['album_price'] * $carrito[$_SESSION['username']][$prod_id]['cantidad'];

                    if (isset($miPDO)) {
                        $sentecia_realizar_compra = 'INSERT INTO compras VALUE (DEFAULT, (SELECT id_usuario FROM usuarios WHERE nombre=?), (SELECT id_producto FROM productos WHERE id_producto=?), ?, ?, ?)';
                        // 'INSERT INTO compras VALUE 
                        // (DEFAULT, 
                        // (SELECT id_usuario FROM usuarios WHERE nombre="' . $_SESSION["username"] . '"), 
                        // (SELECT id_producto FROM productos WHERE id_producto=' . $prod_id . '), 
                        // ' . $fecha_actual . ', 
                        // ' . $carrito[$prod_id]['cantidad'] . ', 
                        // ' . $total .')';
                        $realizar_compra = $miPDO->prepare($sentecia_realizar_compra);
                        $realizar_compra->execute([$_SESSION['username'],$prod_id+1,$fecha_actual,$carrito[$_SESSION['username']][$prod_id]['cantidad'],$total]);

                        unset($carrito[$_SESSION['username']][$prod_id]);
                        guardar_carrito($carrito);

                        if (empty($carrito)) {
                            setcookie('carrito', '', time()-60*60*24*60);
                        }
                    }
                }
            } else {
                header('Location: ./login.php');
                die();
            }
        }
        header('Location: carrito.php');
        die();
    }

    $precio_total = 0;
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $tema ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $idioma=='esp'?'Carrito':'Cart'; ?></title>

        <link rel="stylesheet" href="../css/carrito.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>
    <body>
        <!-- header -->
        <?php include '../php/header.php' ?>

        <!-- contenido -->
            <div class="mostrarCarrito">
                <h2><?php echo $idioma == 'esp' ? 'Tu carrito de compras' : 'Your shopping cart' ?></h2>
                
                <div id="mostrar_carrito">
                    <?php if (empty($carrito[$_SESSION['username']])): ?>
                        <p><?php echo $idioma=='esp' ? 'Su carrito esta vacio' : 'Your shopping cart is empty'; ?></p>
                    <?php else: ?>
                        <!-- <div class="mostrar_deseados"> -->
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php echo $idioma=='esp' ? 'Producto' : 'Product'; ?></th>
                                        <th><?php echo $idioma=='esp' ? 'Precio unitario' : 'Unit price'; ?></th>
                                        <th><?php echo $idioma=='esp' ? 'Cantidad' : 'Amount'; ?></th>
                                        <th><?php echo $idioma=='esp' ? 'Subtotal' : 'Subtotal'; ?></th>
                                        <th><?php echo $idioma=='esp' ? 'Acción' : 'Action'; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        foreach ($carrito[$_SESSION['username']] as $album_id => $item):
                                        $subtotal = $item['album_price'] * $item['cantidad'];
                                        $precio_total += $subtotal;
                                    ?>
                                    <tr>
                                        <td data-label="<?php echo $idioma == 'esp' ? 'Producto' : 'Product' ?>"><?php echo $item['album_name']; ?></td>
                                        <td data-label="<?php echo $idioma == 'esp' ? 'Precio' : 'Price' ?>"><?php echo $item['album_price']; ?>€</td>
                                        <td data-label="<?php echo $idioma == 'esp' ? 'Cantidad' : 'Amount' ?>"><?php echo $item['cantidad']; ?></td>
                                        <td data-label="<?php echo $idioma == 'esp' ? 'Subtotal' : 'Subtotal' ?>"><?php echo $subtotal; ?>€</td>
                                        <td data-label="<?php echo $idioma == 'esp' ? 'Acción' : 'Action' ?>">
                                            <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
                                                <button type="submit" name="añadir" value="<?php echo $album_id; ?>"><?php echo $idioma=='esp' ? 'Añadir 1' : 'Add 1'; ?></button>
                                                <button type="submit" name="eliminar" value="<?php echo $album_id; ?>"><?php echo $idioma=='esp' ? 'Eliminar 1' : 'Delete 1'; ?></button>
                                                <button type="submit" name="comprar_uno" value="<?php echo $album_id; ?>"><?php echo $idioma=='esp' ? 'Comprar' : 'Comprar'; ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" data-label="<?php echo $idioma == 'esp' ? 'Precio total' : 'Total price' ?>"></td>
                                        <td><strong><?php echo number_format($precio_total, 2); ?>€</strong></td>
                                        <td>
                                            <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
                                                <button type="submit" name="borrar"><?php echo $idioma=='esp' ? 'Eliminar todo' : 'Delete all'; ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        <!-- </div> -->
                    <?php endif; ?>
                </div>
            </div>
        <!-- footer -->
        <?php include_once '../php/footer.php' ?>
    </body>
    <script src="../js/compras.js"></script>
    <!-- <script src="../js/carrito.js"></script> -->
    <!-- <script src="../js/cookies.js"></script> -->
    <!-- <script src="../js/mostrar_carrito.js"></script> -->
    <!-- <script src="../js/action_carrito.js"></script> -->
    <script src="../js/menu.js"></script>
</html>