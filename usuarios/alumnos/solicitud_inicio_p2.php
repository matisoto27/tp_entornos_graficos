<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'alumnos') {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['dni'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: ../login.php");
    exit();
} else {
    $dni = $_SESSION['dni'];
}

// Validar si se reciben datos por POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['legajo']) || empty($_POST['carrera'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: ../menu_principal.php");
        exit();
    }

    // Recuperar datos del formulario.
    $legajo = trim($_POST['legajo']);
    $carrera = $_POST['carrera'];
    $dni_profesor = $_POST['dni-profesor'];

    // Validar que el legajo tenga 5 dígitos.
    if (!preg_match('/^\d{5}$/', $legajo)) {
        $_SESSION['mensaje_error'] = "El legajo debe tener 5 dígitos.";
        header("Location: ../menu_principal.php");
        exit();
    }

    // Lista de carreras válidas.
    $carreras_validas = [
        "Ingeniería Civil",
        "Ingeniería en Energía Eléctrica",
        "Ingeniería Mecánica",
        "Ingeniería Química",
        "Ingeniería en Sistemas de Información"
    ];

    // Validar carrera.
    if (!in_array($carrera, $carreras_validas)) {
        $_SESSION['mensaje_error'] = "Por favor, seleccione una carrera.";
        header("Location: ../menu_principal.php");
        exit();
    }
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: ../menu_principal.php");
    exit();
}

// Redireccionar.
$href_pps = '';
$href_lista_profesores = '/usuarios/lista_profesores.php';
$href_notificaciones = '/usuarios/notificaciones.php';
$href_modificar_perfil = '/usuarios/modificar_perfil.php';
$href_cerrar_sesion = '/cerrar_sesion.php';

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';
?>

<!doctype html>
<html lang="en">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/header.php';
    ?>
    <main>
        <div class="mt-5 container background-border form-container">
            <h3 class="text-center pt-4 mb-2">Solicitud de Inicio PPS</h3>
            <h4 class="text-center mb-4">Datos de la Empresa</h4>
            <form method="POST" action="solicitud_inicio_p3.php" class="mx-auto" style="width: 300px;">
                <div class="mb-3">
                    <label for="input-nombre-empresa" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre-empresa" id="input-nombre-empresa" required>
                </div>
                <div class="mb-3">
                    <label for="input-direccion-empresa" class="form-label">Direccion</label>
                    <input type="text" class="form-control" name="direccion-empresa" id="input-direccion-empresa" required>
                </div>
                <div class="mb-3">
                    <label for="input-telefono-empresa" class="form-label">Telefono</label>
                    <input type="text" class="form-control" name="telefono-empresa" id="input-telefono-empresa" required>
                </div>
                <div class="mb-5">
                    <label for="select-modalidad-trabajo" class="form-label">Modalidad de Trabajo</label>
                    <select class="form-control" name="modalidad-trabajo" id="select-modalidad-trabajo" required>
                        <option value="" disabled selected>Seleccione una Modalidad</option>
                        <option value="Presencial">Presencial</option>
                        <option value="Remoto">Remoto</option>
                        <option value="Hibrido">Hibrido</option>
                    </select>
                </div>
                <h4 class="text-center mb-4">Datos del Jefe o Superior</h4>
                <div class="mb-3">
                    <label for="input-nombre-jefe" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre-jefe" id="input-nombre-jefe" required>
                </div>
                <div class="mb-3">
                    <label for="input-apellido-jefe" class="form-label">Apellido</label>
                    <input type="text" class="form-control" name="apellido-jefe" id="input-apellido-jefe" required>
                </div>
                <div class="mb-4">
                    <label for="input-email-jefe" class="form-label">Correo Electronico</label>
                    <input type="email" class="form-control" name="email-jefe" id="input-email-jefe" required>
                </div>
                <div class="mb-3">
                    <button type="submit" class="btn btn-success w-100">Enviar Solicitud PPS</button>
                </div>
                <div class="pb-4">
                    <button type="button" class="btn btn-primary w-100" onclick='window.location.href="solicitud_inicio_p1.php"'>Volver</button>
                </div>
                <input type="hidden" value="<?php echo $legajo ?>" name="legajo">
                <input type="hidden" value="<?php echo $carrera ?>" name="carrera">
                <input type="hidden" value="<?php echo $dni_profesor ?>" name="dni-profesor">
            </form>
        </div>
    </main>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        // Esperamos a que el documento esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            // Seleccionamos el toggle y el contenedor que queremos ocultar/mostrar
            const navbarToggle = document.querySelector('.navbar-toggler');
            const mainContainer = document.querySelector('main');

            // Verificamos si ambos elementos existen
            if (navbarToggle && mainContainer) {
                // Detectamos cuando se abre o se cierra el menú
                const navbarCollapse = document.getElementById('navbarExample');

                // Cuando el menú se muestra, ocultamos el contenedor 'main'
                navbarCollapse.addEventListener('show.bs.collapse', function() {
                    mainContainer.style.display = 'none';
                });

                // Cuando el menú se oculta, mostramos el contenedor 'main'
                navbarCollapse.addEventListener('hidden.bs.collapse', function() {
                    mainContainer.style.display = 'block';
                });
            }
        });
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>