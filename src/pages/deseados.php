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


    // deseados
    // se comprueba que la cookie deseados exista, en caso afirmativo se retorna su valor, en caso contrario un array vacio
    function conseguir_deseados() {
        if (isset($_COOKIE['deseados'])) {
            return json_decode($_COOKIE['deseados'], true);
        }
        return [];
    }

    // se guardan los deseados en una cookie; dura 7 dias
    function guardar_deseados($deseados) {
        setcookie('deseados', json_encode($deseados), time()+60*60*24*7);
    }
    
    // recoger los deseados de la cookie
    $deseados = conseguir_deseados();


    //carrito
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
            
            if (isset($deseados[$_SESSION['username']][$prod_id])) {
                
                if ($deseados[$_SESSION['username']][$prod_id]['cantidad'] > 1) {
                    $deseados[$_SESSION['username']][$prod_id]['cantidad']--;
                } else {
                    unset($deseados[$_SESSION['username']][$prod_id]);
                }
                
                if (empty($deseados[$_SESSION['username']])) {
                    unset($deseados[$_SESSION['username']]);
                }
                guardar_deseados($deseados);

                if (empty($deseados)) {
                    setcookie('deseados', '', time()-60*60*60*100);
                }
            }
        } elseif (isset($_POST['añadir'])) {
            // añadir 1
            /*
                al pulsar añadir 1 se suma 1 de la cantidad de productos
            */
            $prod_id = $_POST['añadir'];

            if (isset($deseados[$_SESSION['username']][$prod_id])) {
                $deseados[$_SESSION['username']][$prod_id]['cantidad']++;
                guardar_deseados($deseados);
            }
        } elseif (isset($_POST['borrar'])) {
            // borrar
            /*
                al pulsar borrar se elimina la cookie que contiene la lista de deseados
            */
            unset($deseados[$_SESSION['username']]);
            guardar_deseados($deseados);
            if (empty($deseados)) {
                setcookie('deseados', '', time()-60*60*60*100);
            }
        } elseif (isset($_POST['añadir_carrito'])) {
            // añadir al carrito
            /*
                se comprueba que lo que se quiera añadir al carrito exita, si exite se elimina de deseados y se guarda de nuevo
                en la cookie; para añadirlos al carrito usamos una funcion en javascript
            */
            $prod_id = $_POST['añadir_carrito'];
            if ($deseados[$_SESSION['username']][$prod_id]) {
                // obtenemos el carrito actual del localStorage
                $carrito = conseguir_carrito(); 

                if ($carrito[$_SESSION['username']][$prod_id]) {
                    // si ya existe en el carrito, incrementamos la cantidad
                    $carrito[$_SESSION['username']][$prod_id]['cantidad'] += $deseados[$_SESSION['username']][$prod_id]['cantidad'];
                } else {
                    // si no existe en el carrito, lo añadimos
                    $carrito[$_SESSION['username']][$prod_id] = $deseados[$_SESSION['username']][$prod_id]; 
                }

                guardar_carrito($carrito);
            }

            if (isset($deseados[$_SESSION['username']][$prod_id])) {
                unset($deseados[$_SESSION['username']][$prod_id]);
                guardar_deseados($deseados);
            }
            if (empty($deseados)) {
                setcookie('deseados', '', time()-60*60*24*7);
            }
        }
        header('Location: deseados.php');
        die();
    }

    $precio_total = 0;
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $tema ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $idioma=='esp'?'Lista de deseados':'Wish list'; ?></title>

        <link rel="stylesheet" href="../css/deseados.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>
    <body>
        <!-- header -->
        <?php include '../php/header.php' ?>

        <!-- contenido -->
        <div class="contenido">
            <h2><?php echo $idioma=='esp' ? 'Tu lista de deseados' : 'Your wish list'; ?></h2>

            <!-- 
                si la lista de deseados esta vacia se advierte de ello
                si no lo esta, se muestra su contenido
            -->
            <?php if (empty($deseados[$_SESSION['username']])): ?>
                <p><?php echo $idioma=='esp' ? 'Su lista de deseados esta vacia' : 'Your wish list is empty'; ?></p>
            <?php else: ?>
                <div class="mostrar_deseados">
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
                            <?php foreach ($deseados[$_SESSION['username']] as $album_id => $item): 
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
                                        <button type="submit" name="añadir_carrito" value="<?php echo $album_id; ?>"><?php echo $idioma=='esp' ? 'Añadir al carrito' : 'Add to cart'; ?></button>
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
                </div>
            <?php endif; ?>
        </div>
        <!-- footer -->
        <?php include_once '../php/footer.php' ?>
    </body>
    <script src="../js/menu.js"></script>
</html>