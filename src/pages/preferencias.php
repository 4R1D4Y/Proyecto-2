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
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $tema ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $idioma=='esp'?'Preferencias':'Preferences'; ?></title>

        <link rel="stylesheet" href="../css/preferencias.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>
    <body>
        <!-- header -->
        <?php include '../php/header.php' ?>

        <!-- contenido -->
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
            <h1><?php echo $idioma=='esp' ? 'Preferencias' : 'Preferences'; ?></h1>
            <label>
                <p><?php echo $idioma=='esp' ? 'Tema:' : 'Theme:'; ?></p>
                <select name="modoPantalla">
                    <option value="claro"><?php echo $idioma=='esp' ? 'Claro' : 'Light'; ?></option>
                    <option value="oscuro"><?php echo $idioma=='esp' ? 'Oscuro' : 'Dark'; ?></option>
                </select>
            </label>
            <label>
                <p><?php echo $idioma=='esp' ? 'Idioma:' : 'Language'; ?></p>
                <select name="idioma">
                    <option value="esp"><?php echo $idioma=='esp' ? 'Español' : 'Spanish'; ?></option>
                    <option value="eng"><?php echo $idioma=='esp' ? 'Inglés' : 'English'; ?></option>
                </select>
            </label>
            <input type="submit" name="button" value="<?php echo $idioma=='esp' ? 'Restablecer' : 'Restore'; ?>">
            <input type="submit" name="button" value="<?php echo $idioma=='esp' ? 'Aplicar' : 'Apply'; ?>">
        </form>
        
        <!-- footer -->
        <?php include_once '../php/footer.php' ?>

        <?php
            // creacion de cookies
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // al pulsar Aplicar, se guardan la preferencias seleccionadas en su respectiva cookie
                if ($_POST['button'] == 'Aplicar' || $_POST['button'] == 'Apply') {
                    $modoPantalla = isset($_POST['modoPantalla']) ? $_POST['modoPantalla'] : '';
                    $idioma = isset($_POST['idioma']) ? $_POST['idioma'] : '';

                    if (isset($modoPantalla)) {
                        setcookie('modo', $modoPantalla, time()+60*60*24*7);
                    }

                    if (isset($idioma)) {
                        setcookie('idioma', $idioma, time()+60*60*24*7);
                    }

                    header('Location: preferencias.php');
                    die();

                // al pulsar Restablecer, se eliminan las cookies y se vuelve al valor por defecto
                } elseif ($_POST['button'] == 'Restablecer' || $_POST['button'] == 'Restore') {
                    if (isset($_COOKIE['modo'])) {
                        setcookie('modo', '', time()-100000000000);
                    }

                    if (isset($_COOKIE['idioma'])) {
                        setcookie('idioma', '', time()-100000000000);
                    }

                    header('Location: preferencias.php');
                    die();
                }
            }
        ?>
    </body>
    <script src="../js/menu.js"></script>
</html>

