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
$result_profesores = $mysqli->query("SELECT * FROM usuarios u INNER JOIN profesores p ON u.dni = p.dni ORDER BY apellido, nombre");
$profesores = [];
if ($result_profesores->num_rows > 0) {
    while ($profesor = $result_profesores->fetch_assoc()) {

        // Contar la cantidad de alumnos que tiene asignado el profesor para validar su disponibilidad.
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM alumnos WHERE dni_profesor = ?");
        $stmt->bind_param("s", $profesor['dni']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $disponible = ($row[0] > 9 || $profesor['activo'] == 0) ? 0 : 1;

        // Agregar profesor a la lista con su disponibilidad.
        $profesores[] = [
            'dni' => $profesor['dni'],
            'nombre' => $profesor['nombre'],
            'apellido' => $profesor['apellido'],
            'disponible' => $disponible
        ];
    }
}
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
            <h4 class="text-center mb-4">Datos Personales</h4>
            <form method="POST" action="solicitud_inicio_p2.php" class="mx-auto" style="width: 300px;">
                <div class="mb-3">
                    <label for="input-legajo" class="form-label">Legajo</label>
                    <input type="text" class="form-control" name="legajo" id="input-legajo" required>
                </div>
                <div class="mb-3">
                    <label for="select-carrera" class="form-label">Carrera</label>
                    <select class="form-control" name="carrera" id="select-carrera" required>
                        <option value="" disabled selected>Seleccione una Carrera</option>
                        <option value="Ingeniería Civil">Ingeniería Civil</option>
                        <option value="Ingeniería en Energía Eléctrica">Ingeniería en Energía Eléctrica</option>
                        <option value="Ingeniería Mecánica">Ingeniería Mecánica</option>
                        <option value="Ingeniería Química">Ingeniería Química</option>
                        <option value="Ingeniería en Sistemas de Información">Ingeniería en Sistemas de Información</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="select-lista-profesores" class="form-label">Profesor de Preferencia (Opcional)</label>
                    <select class="form-control" name="lista-profesores" id="select-lista-profesores">
                        <option value="" selected>Sin preferencia</option>
                        <?php
                        if (!empty($profesores)) {
                            foreach ($profesores as $profesor) {
                                if ($profesor['disponible']) {
                        ?>
                                    <option value="<?php echo $profesor['nombre'] . ' ' . $profesor['apellido'] ?>" data-dni="<?php echo $profesor['dni'] ?>"><?php echo $profesor['nombre'] . ' ' . $profesor['apellido'] ?></option>
                        <?php
                                }
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" name="dni-profesor" id="input-dni-profesor">
                </div>
                <div class="pb-4">
                    <button type="submit" class="btn btn-primary w-100">Siguiente</button>
                </div>
            </form>
        </div>
    </main>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        document.getElementById('select-lista-profesores').addEventListener('change', function() {
            var opcion_seleccionada = this.options[this.selectedIndex];
            var dni_profesor = opcion_seleccionada.getAttribute('data-dni');
            document.getElementById('input-dni-profesor').value = dni_profesor;
        });
    </script>
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