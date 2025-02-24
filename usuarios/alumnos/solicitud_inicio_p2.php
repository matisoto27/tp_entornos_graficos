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

// Validar mensajes.
if (!empty($_SESSION['mensaje_error'])) {
    $titulo = 'ERROR';
    $mensaje = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']);
} else {
    $titulo = '';
    $mensaje = '';
}

$nombre_empresa = isset($_SESSION['nombre_empresa']) ? $_SESSION['nombre_empresa'] : '';
$direccion_empresa = isset($_SESSION['direccion_empresa']) ? $_SESSION['direccion_empresa'] : '';
$telefono_empresa = isset($_SESSION['telefono_empresa']) ? $_SESSION['telefono_empresa'] : '';
$modalidad_trabajo = isset($_SESSION['modalidad_trabajo']) ? $_SESSION['modalidad_trabajo'] : '';
$nombre_jefe = isset($_SESSION['nombre_jefe']) ? $_SESSION['nombre_jefe'] : '';
$apellido_jefe = isset($_SESSION['apellido_jefe']) ? $_SESSION['apellido_jefe'] : '';
$email_jefe = isset($_SESSION['email_jefe']) ? $_SESSION['email_jefe'] : '';

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Validar si se reciben datos por POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['legajo']) || empty($_POST['carrera'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: solicitud_inicio_p1.php");
        exit();
    }

    // Recuperar datos del formulario.
    $legajo = trim($_POST['legajo']);
    $carrera = $_POST['carrera'];
    $dni_profesor = $_POST['lista-profesores'];

    // Guardar respuestas.
    $_SESSION['legajo'] = $_POST['legajo'];
    $_SESSION['carrera'] = $_POST['carrera'];
    $_SESSION['dni_profesor'] = $_POST['lista-profesores'];

    // Validar que el legajo tenga 5 dígitos.
    if (!preg_match('/^\d{5}$/', $legajo)) {
        unset($_SESSION['legajo']);
        $_SESSION['mensaje_error'] = "El legajo debe tener 5 dígitos.";
        header("Location: solicitud_inicio_p1.php");
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
        unset($_SESSION['carrera']);
        $_SESSION['mensaje_error'] = "Por favor, seleccione una carrera.";
        header("Location: solicitud_inicio_p1.php");
        exit();
    }

    // Recuperar todos los profesores disponibles.
    $result_profesores = $mysqli->query("SELECT p.dni AS dni, nombre, apellido, COUNT(a.dni_profesor) AS total FROM profesores p INNER JOIN usuarios u ON p.dni = u.dni LEFT JOIN alumnos a ON p.dni = a.dni_profesor WHERE activo = 1 GROUP BY p.dni ORDER BY apellido, nombre");
    $profesores = [];
    if ($result_profesores->num_rows > 0) {
        while ($profesor = $result_profesores->fetch_assoc()) {

            // Validar si el profesor tiene más de 9 alumnos.
            $disponible = ($profesor['total'] > 9) ? 0 : 1;

            // Agregar a la lista los DNI de los profesores disponibles.
            if ($disponible) {
                $profesores[] = $profesor['dni'];
            }
        }
    }

    // Validar profesor.
    if (!empty($dni_profesor) && !in_array($dni_profesor, $profesores)) {
        unset($_SESSION['dni_profesor']);
        $_SESSION['mensaje_error'] = "Por favor, seleccione una opción de la lista de profesores.";
        header("Location: solicitud_inicio_p1.php");
        exit();
    }
} elseif (!empty($nombre_empresa) || !empty($direccion_empresa) || !empty($telefono_empresa) || !empty($modalidad_trabajo) || !empty($nombre_jefe) || !empty($apellido_jefe) || !empty($email_jefe)) {

    // Guardar respuestas.
    $legajo = $_SESSION['legajo'];
    $carrera = $_SESSION['carrera'];
    $dni_profesor = $_SESSION['dni_profesor'];
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
                    <input type="text" class="form-control" name="nombre-empresa" id="input-nombre-empresa" value="<?php echo $nombre_empresa ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-direccion-empresa" class="form-label">Direccion</label>
                    <input type="text" class="form-control" name="direccion-empresa" id="input-direccion-empresa" value="<?php echo $direccion_empresa ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-telefono-empresa" class="form-label">Telefono</label>
                    <input type="text" class="form-control" name="telefono-empresa" id="input-telefono-empresa" value="<?php echo $telefono_empresa ?>" required>
                </div>
                <div class="mb-5">
                    <label for="select-modalidad-trabajo" class="form-label">Modalidad de Trabajo</label>
                    <select class="form-control" name="modalidad-trabajo" id="select-modalidad-trabajo" required>
                        <option value="" disabled selected>Seleccione una Modalidad</option>
                        <option value="Presencial" <?php echo $modalidad_trabajo == 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                        <option value="Remoto" <?php echo $modalidad_trabajo == 'Remoto' ? 'selected' : '' ?>>Remoto</option>
                        <option value="Hibrido" <?php echo $modalidad_trabajo == 'Hibrido' ? 'selected' : '' ?>>Hibrido</option>
                    </select>
                </div>
                <h4 class="text-center mb-4">Datos del Jefe o Superior</h4>
                <div class="mb-3">
                    <label for="input-nombre-jefe" class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre-jefe" id="input-nombre-jefe" value="<?php echo $nombre_jefe ?>" required>
                </div>
                <div class="mb-3">
                    <label for="input-apellido-jefe" class="form-label">Apellido</label>
                    <input type="text" class="form-control" name="apellido-jefe" id="input-apellido-jefe" value="<?php echo $apellido_jefe ?>" required>
                </div>
                <div class="mb-4">
                    <label for="input-email-jefe" class="form-label">Correo Electronico</label>
                    <input type="email" class="form-control" name="email-jefe" id="input-email-jefe" value="<?php echo $email_jefe ?>" required>
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
    <?php
    if (!empty($mensaje)) {
    ?>
        <div class="modal fade" id="modal-message" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-message-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $titulo ?></h5>
                    </div>
                    <div class="modal-body"><?php echo $mensaje ?></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function() {
                const modal = new bootstrap.Modal(document.getElementById('modal-message'));
                modal.show();
            };
        </script>
    <?php
    }
    ?>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggle = document.querySelector('.navbar-toggler');
            const mainContainer = document.querySelector('main');
            if (navbarToggle && mainContainer) {
                const navbarCollapse = document.getElementById('navbarExample');
                navbarCollapse.addEventListener('show.bs.collapse', function() {
                    mainContainer.style.display = 'none';
                });
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