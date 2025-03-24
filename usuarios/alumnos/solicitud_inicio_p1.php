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

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Validar acceso autorizado.
$stmt = $mysqli->prepare("SELECT legajo FROM alumnos WHERE dni = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();
if (!empty($alumno['legajo'])) {
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

// Lógica.
$result_profesores = $mysqli->query("SELECT p.dni AS dni, nombre, apellido, COUNT(a.dni_profesor) AS total FROM profesores p INNER JOIN usuarios u ON p.dni = u.dni LEFT JOIN alumnos a ON p.dni = a.dni_profesor WHERE activo = 1 GROUP BY p.dni ORDER BY apellido, nombre");
$profesores = [];
if ($result_profesores->num_rows > 0) {
    while ($profesor = $result_profesores->fetch_assoc()) {

        // Validar si el profesor tiene más de 9 alumnos.
        $disponible = ($profesor['total'] > 9) ? 0 : 1;

        // Agregar a la lista los profesores disponibles.
        if ($disponible) {
            $profesores[] = [
                'dni' => $profesor['dni'],
                'nombre' => $profesor['nombre'],
                'apellido' => $profesor['apellido'],
            ];
        }
    }
}

// Respuestas guardadas.
$legajo = isset($_SESSION['legajo']) ? $_SESSION['legajo'] : '';
$carrera = isset($_SESSION['carrera']) ? $_SESSION['carrera'] : '';
$dni_profesor = isset($_SESSION['dni_profesor']) ? $_SESSION['dni_profesor'] : '';
?>

<!doctype html>
<html lang="es">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body class="d-flex flex-column">
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/header.php';
    ?>
    <main class="d-flex justify-content-center align-items-center flex-fill border-top border-bottom">
        <div class="container br-class alt-background form-container">
            <h1 class="text-center pt-4 mb-2">Solicitud de Inicio PPS P.1</h1>
            <h2 class="text-center mb-4">Datos Personales</h2>
            <form method="POST" action="solicitud_inicio_p2.php" class="mx-auto" style="width: 300px;">
                <div class="mb-3">
                    <label for="input-legajo" class="form-label">Legajo</label>
                    <input type="text" class="form-control" name="legajo" id="input-legajo" value="<?php echo $legajo ?>" required>
                </div>
                <div class="mb-3">
                    <label for="select-carrera" class="form-label">Carrera</label>
                    <select class="form-control" name="carrera" id="select-carrera" required>
                        <option value="" disabled selected>Seleccione una Carrera</option>
                        <option value="Ingeniería Civil" <?php echo $carrera == 'Ingeniería Civil' ? 'selected' : '' ?>>Ingeniería Civil</option>
                        <option value="Ingeniería en Energía Eléctrica" <?php echo $carrera == 'Ingeniería en Energía Eléctrica' ? 'selected' : '' ?>>Ingeniería en Energía Eléctrica</option>
                        <option value="Ingeniería Mecánica" <?php echo $carrera == 'Ingeniería Mecánica' ? 'selected' : '' ?>>Ingeniería Mecánica</option>
                        <option value="Ingeniería Química" <?php echo $carrera == 'Ingeniería Química' ? 'selected' : '' ?>>Ingeniería Química</option>
                        <option value="Ingeniería en Sistemas de Información" <?php echo $carrera == 'Ingeniería en Sistemas de Información' ? 'selected' : '' ?>>Ingeniería en Sistemas de Información</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="select-lista-profesores" class="form-label">Profesor de Preferencia (Opcional)</label>
                    <select class="form-control" name="lista-profesores" id="select-lista-profesores">
                        <option value="" selected>Sin preferencia</option>
                        <?php
                        if (!empty($profesores)) {
                            foreach ($profesores as $profesor) {
                        ?>
                                <option value="<?php echo $profesor['dni'] ?>" <?php echo $dni_profesor == $profesor['dni'] ? 'selected' : '' ?>><?php echo $profesor['nombre'] . ' ' . $profesor['apellido'] ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="pb-4">
                    <button type="submit" class="btn btn-primary w-100" aria-label="Ir al siguiente paso">Siguiente</button>
                </div>
            </form>
        </div>
    </main>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/footer.php';
    ?>
    <?php
    if (!empty($mensaje)) {
    ?>
        <div class="modal fade" id="modal-message" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-message-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-message-title"><?php echo $titulo ?></h5>
                    </div>
                    <div class="modal-body"><?php echo $mensaje ?></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Cerrar mensaje">Cerrar</button>
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

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>