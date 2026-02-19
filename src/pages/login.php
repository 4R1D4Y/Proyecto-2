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

    include_once '../BBDD/Conexion.php';


?>

<!DOCTYPE html>
<html lang="en" class="<?php echo $tema; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $idioma=='esp'?'Inicio de secion':'Log in'; ?></title>

        <link rel="stylesheet" href="../css/login.css">
        <link rel="stylesheet" href="../css/header.css">
        <link rel="stylesheet" href="../css/footer.css">
    </head>
    <body>
        <!-- header -->
        <?php include_once '../php/header.php' ?>

        <!-- contenido -->
        <div class="login">
            <?php if(isset($BD_error)): ?>
                <p class="bd_error"><?php echo $BD_error ?></p>
            <?php else: ?>
                <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
                    <label>
                        <p><?php echo $idioma=='esp' ? 'Nombre de usuario' : 'Username'; ?></p>
                        <input type="text" name="username">
                    </label>
                    <label>
                        <p><?php echo $idioma=='esp' ? 'Contraseña' : 'Password'; ?></p>
                        <input type="password" name="password">
                    </label>
                    <button name="logearse" type="submit"><?php echo $idioma=='esp' ? 'Iniciar sesión' : 'Log in'; ?></button>
                    <button name="registrarse" type="submit"><?php echo $idioma=='esp' ? 'Registrarse' : 'Sign in'; ?></button>
                </form>
            <?php endif; ?>
            
            <!-- Validar datos, mostrar errores o iniciar sesion -->
            <?php 
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    // usuario y contraseña admin
                    // $admin_user = 'admin';
                    // $admin_password = '1234';

                    // recoger los valores insertados
                    $input_user = $_POST['username'] ?? '';
                    $input_password = $_POST['password'] ?? '';

                    // array para los errores
                    $errores = [];

                    if(isset($BD_error)) {
                        echo '<p class="bd_error">' . $BD_error . '</p>';
                    }
                    // comprobar que el usuario insertado no este vacio y que tenga al menos 3 caracteres;
                    // se guarda un error en caso afirmativo
                    if (empty($input_user)) {
                        $errores[] = $idioma == 'esp' ? 'El nombre de usurio no puede estar vacio' : 'The username cant be empty';
                    } elseif (strlen($input_user) <= 2) {
                        $errores[] = $idioma == 'esp' ? 'El nombre debe tener al menos tres caracteres' : 'The username must have at least three characters long';
                    }

                    // comprobar que la contraseña insertada no este vacia y que tenga al menos 4 caracteres;
                    // se guarda un error en caso afirmativo
                    if (empty($input_password)) {
                        $errores[] = $idioma == 'esp' ? 'La contraseña de usurio no puede estar vacia' : 'The password cant be empty';
                    } elseif (strlen($input_password) <= 3) {
                        $errores[] = $idioma == 'esp' ? 'La contraseña de usurio debe tener al menos cuatro caracteres' : 'The password must have at least four characters long';
                    }
                    
                    // se muestran los errores en caso de haber
                    // si no hay, se inicia sesion segun las credenciales introducidas
                    if (!empty($errores)) {
                        echo '<ul>';
                        foreach ($errores as $error) {
                            echo "<li>$error</li>";
                        }
                        echo '</ul>';
                    } else {
                        $sentencia_recoger_usuario = 'SELECT * FROM usuarios WHERE nombre="' . $input_user . '"';
                        $recoger_usuario = $miPDO->prepare($sentencia_recoger_usuario);
                        $recoger_usuario->execute();
                        $usuario = $recoger_usuario->fetchAll();
                        if (isset($_POST['logearse'])) {
                            if (!isset($usuario[0]['nombre']) || !isset($usuario[0]['password'])) {
                                $errores[] = $idioma == 'esp' ? 'El usuario o la contraseña no son correctos' : 'The username or password is incorrect';
                            } elseif ($input_user == $usuario[0]['nombre'] && $input_password == $usuario[0]['password']) {
                                session_start();
                                $_SESSION['rol'] = $usuario[0]['rol'];
                                $_SESSION['username'] = $usuario[0]['nombre'];

                                header('Location: ./tienda.php');
                                die();
                            }
                        }

                        if (isset($_POST['registrarse'])) {
                            if (isset($usuario[0]['nombre']) && $input_user == $usuario[0]['nombre']) {
                                $errores[] = $idioma == 'esp' ? 'Ya hay un usuario registrado con ese nombre' : 'There is already a registered user with that username';
                            }

                            if (!isset($usuario[0]['nombre'])) {
                                $sentencio_insertar_usuario = 'INSERT INTO usuarios VALUE (DEFAULT, "' . $input_user . '", "' . $input_password . '", DEFAULT)';
                                $insertar_usuario = $miPDO->prepare($sentencio_insertar_usuario);
                                $insertar_usuario->execute();
                            }
                        }

                        if (!empty($errores)) {
                            echo '<ul>';
                            foreach ($errores as $error) {
                                echo "<li>$error</li>";
                            }
                            echo '</ul>';
                        }
                    }
                }
            ?>
        </div>
        
        <!-- footer -->
        <?php include_once '../php/footer.php' ?>
    </body>
    <script src="../js/menu.js"></script>
</html>