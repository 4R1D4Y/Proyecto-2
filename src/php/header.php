
 <!-- se inicia sesion y se comprueba el rol del usuario -->
<?php
    if (isset($_SESSION['rol'])) {
        
        if ($_SESSION['rol'] == 'administrador') {
            $sesion_rol = $_SESSION['rol'];
        } else {
            $sesion_rol = $_SESSION['rol'];
        }
    }

    if (!isset($_SESSION['username'])) {
        $_SESSION['username'] = 'invitado';
    }
?>

<!-- header -->
<!-- se mostrara en ingles o español segun el idioma seleccionado; por defecto español -->
<header>
    <div class="logo">
        <img src="../images/logo.jpg" alt="logo.jpg">
    </div>
    <button class="menu_toggle" aria-label="Abrir menú">☰</button>
    <menu id="menu">
        <a href="./tienda.php"><?php echo $idioma=='esp' ? 'TIENDA' : 'SHOP'; ?></a>
        <a href="./deseados.php"><?php echo $idioma=='esp' ? 'LISTA DE DESEADOS' : 'WISH LIST'; ?></a>
        <a href="./carrito.php"><?php echo $idioma=='esp' ? 'CARRITO' : 'CART'; ?></a>
        <a href="./compras.php"><?php echo $idioma=='esp' ? 'COMPRAS' : 'PURCHASE'; ?></a>
        <a href="./preferencias.php"><?php echo $idioma=='esp' ? 'PREFERENCIAS' : 'PREFERENCES'; ?></a>
        <?php if (isset($_SESSION['rol'])): ?>
            <a href="./logout.php"><?php echo $idioma=='esp' ? 'CERRAR SESIÓN' : 'LOG OUT'; ?></a>
        <?php else: ?>
            <a href="./login.php"><?php echo $idioma=='esp' ? 'INICIAR SESIÓN' : 'LOG IN'; ?></a>
        <?php endif; ?>
    </menu>
</header>
