<!-- footer  -->
<!-- comprueba si se a iniciado sesion; si es así, se muestra un mensaje segun el rol del usuario -->
<!-- se mostrara en ingles o español segun el idioma seleccionado; por defecto español -->
<footer>
    <?php
        if (isset($_SESSION['rol'])) {
            if ($_SESSION['rol'] == 'administrador') {
                echo $idioma == 'esp' ? '<p>Bienvenido administrador</p>' : '<p>Welcome administrator<p>';
            } else {
                echo $idioma == 'esp' ? '<p>Bienvenido ' . $_SESSION['username'] . '</p>' : '<p>Welcome ' . $_SESSION['username'] . '<p>';
            }
        }
    ?>
    <p><?php echo $idioma == 'esp' ? 'Página creada por: Ariday y Omar' : 'Page made by: Ariday & Omar' ?></p>
</footer>
