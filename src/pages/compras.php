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

    if (isset($miPDO)) {
        $sentencia_recoger_compras = 'SELECT * FROM compras WHERE id_usuario=(SELECT id_usuario FROM usuarios WHERE nombre=?)';
        $recoger_compras = $miPDO->prepare($sentencia_recoger_compras);
        $recoger_compras->execute([$_SESSION['username']]);
        $compras = $recoger_compras->fetchAll();
    }

?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $tema ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $idioma=='esp'?'Compras':'Purchase'; ?></title>

        <link rel="stylesheet" href="../css/compras.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>
    <body>
        <!-- header -->
        <?php include_once '../php/header.php' ?>

        <!-- contenido -->
        <div class="contenido">
            <h2><?php echo $idioma=='esp' ? 'Lista de compras' : 'Purchase list' ?></h2>

            
            <div id="mostrar_compras">
                <?php if (isset($BD_error)): ?>
                    <p class="bd_error"><?php echo $BD_error; ?><p>
                <?php elseif (empty($compras)): ?>
                    <p><?php echo $idioma=='esp' ? 'No ha realizado compras' :  'You haven´t made any purchases'?></p>
                <?php else: ?>
                    <?php foreach ($compras as $key => $valor):
                        $fecha = $valor['fecha'];

                        $sentencia_recoger_producto = 'SELECT * FROM productos WHERE id_producto=?';
                        $recoger_producto = $miPDO->prepare($sentencia_recoger_producto);
                        $recoger_producto->execute([$valor['id_producto']]);
                        $producto = $recoger_producto->fetchAll();


                        $nombre_producto = $producto[0]['nombre'];
                        $cantidad = $valor['cantidad'];
                        $precio_unitario = $valor['total'] / $valor['cantidad'];
                        $precio_total = $valor['total'];
                    ?>
                        <div class="detalle-compra">
                            <h3><?php echo $idioma == 'esp' ? 'Fecha de compra - ' . $fecha : 'Purchase Date - ' . $fecha ?></h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php echo $idioma == 'esp' ? 'Producto' : 'Product' ?></th>
                                        <th><?php echo $idioma == 'esp' ? 'Precio unitario' : 'Unit price' ?></th>
                                        <th><?php echo $idioma == 'esp' ? 'Cantidad' : 'Amount' ?></th>
                                        <th><?php echo $idioma == 'esp' ? 'Total' : 'Total' ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo $nombre_producto ?></td>
                                        <td><?php echo $precio_unitario ?>€</td>
                                        <td><?php echo $cantidad ?></td>
                                        <td><?php echo $precio_total ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- footer -->
        <?php include_once '../php/footer.php' ?>
    </body>
    <!-- <script src="../js/cookies.js"></script> -->
    <script src="../js/menu.js"></script>
    <!-- <script src="../js/compras.js"></script>
    <script src="../js/mostrar_compras.js"></script> -->
</html>