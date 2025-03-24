<?php
// Lógica.
$stmt = $mysqli->prepare("SELECT * FROM notificaciones WHERE dni = ? AND fecha_recibida IS NULL");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_notificaciones = $stmt->get_result();
if ($result_notificaciones->num_rows > 0) {
    $nro = $result_notificaciones->num_rows;
}
?>

<header>
    <div class="bg-white">
        <div class="container px-sm-0" style="min-width: 225px;">
            <nav class="navbar navbar-expand-lg px-0">
                <div class="container-fluid px-0">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarExample" aria-controls="navbarExample" aria-expanded="false" aria-label="Abrir o cerrar el menú de navegación">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <button class="btn bg-white" style="border: 1px solid rgba(0,0,0, 0.4);" id="home-button" aria-label="Volver al inicio" onclick='window.location.href="/usuarios/menu_principal.php"'>Volver al Inicio</button>
                    <div class="collapse navbar-collapse d-lg-flex" id="navbarExample">
                        <a class="navbar-brand col-lg-2 me-0" href="/usuarios/menu_principal.php"><img src="/usuarios/logo.png" alt="Logo de la Universidad Tecnológica Nacional" height="40"></a>
                        <ul class="navbar-nav col-lg-8 justify-content-lg-center align-items-lg-center">
                            <li class="nav-item">
                                <a class="nav-link <?php if (empty($href_pps)) echo 'disabled' ?>" href="<?php echo $href_pps ?>">Tramites PPS</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php if (empty($href_lista_profesores)) echo 'disabled' ?>" href="<?php echo $href_lista_profesores ?>">Lista de Profesores</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php if (empty($href_notificaciones)) echo 'disabled' ?>" href="<?php echo $href_notificaciones ?>">
                                    <div style="<?php if (empty($href_notificaciones)) echo 'color: rgba(0,0,0,0.3);'; ?>" class="d-flex align-items-center" id="notificaciones-nav-link">
                                        Notificaciones <?php if (isset($nro)) echo $nro; ?>
                                        <i class="bi bi-bell ps-1" style="font-size: 1.5rem;<?php if (isset($nro)) echo ' color: red;';
                                                                                            if (empty($href_notificaciones)) echo 'color: rgba(0,0,0,0.3);'; ?>"></i>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php if (empty($href_modificar_perfil)) echo 'disabled' ?>" href="<?php echo $href_modificar_perfil ?>">Mi Perfil</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav col-lg-2 justify-content-lg-end" id="ul-logout">
                            <li class="nav-item">
                                <a class="nav-link px-lg-0" href="<?php echo $href_cerrar_sesion ?>" id="logout-hyperlink">Cerrar Sesión</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</header>