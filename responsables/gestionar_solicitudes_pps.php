<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'responsables') {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['codigo']) || empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: login.php");
    exit();
}

// Abrir la conexión a la base de datos.
include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

// Paginación.
// Definir la cantidad de registros por página.
$registros_por_pagina = 7;
// Determinar la página actual.
if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
    $pagina_actual = $_GET['pagina'];
} else {
    $pagina_actual = 1;
}
// Calcular el desplazamiento (offset) de los registros.
$offset = ($pagina_actual - 1) * $registros_por_pagina;
// Contar el total de registros.
$result_count = $mysqli->query("SELECT COUNT(*) AS total FROM alumnos WHERE estado_solicitud = 'Pendiente'");
$row_count = $result_count->fetch_assoc();
$total_registros = $row_count['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);
// Fin Paginación.

// Lógica.
$result = $mysqli->query("SELECT u.dni, nombre, apellido, activo FROM usuarios u INNER JOIN profesores p ON u.dni = p.dni WHERE (SELECT COUNT(*) FROM alumnos WHERE dni_profesor = u.dni) < 10 AND activo = 1 ORDER BY apellido, nombre");
$profesores_dni = [];
while ($profesor = $result->fetch_assoc()) {
    $profesores_dni[$profesor['dni']] = $profesor;
}
$result = $mysqli->query("SELECT u.dni AS dni_alumno, u.nombre, u.apellido, u.email, a.legajo, a.carrera, a.fecha_solicitud, a.dni_profesor, p.nombre AS nombre_profesor, p.apellido AS apellido_profesor, activo FROM usuarios u INNER JOIN alumnos a ON u.dni = a.dni LEFT JOIN usuarios p ON a.dni_profesor = p.dni LEFT JOIN profesores p2 ON a.dni_profesor = p2.dni WHERE estado_solicitud = 'Pendiente' ORDER BY fecha_solicitud, legajo LIMIT $offset, $registros_por_pagina");
?>

<!doctype html>
<html lang="es">

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body>
    <main>
        <div class="d-flex justify-content-center align-items-center min-vh-100">
            <div class="container-fluid br-class bg-white min-vh-xs-100">
                <div class="row pt-4 mb-3">
                    <div class="col">
                        <h1 class="text-center">Solicitudes PPS</h1>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <div class="table-responsive">
                            <table class="table table-striped text-center" style="min-width: 1850px;">
                                <thead>
                                    <tr>
                                        <th scope="col" class="col-1">Fecha Enviada</th>
                                        <th scope="col" class="col-1">Legajo</th>
                                        <th scope="col" class="col-1">Nombre</th>
                                        <th scope="col" class="col-1">Apellido</th>
                                        <th scope="col" class="col-2">Carrera</th>
                                        <th scope="col" class="col-2">Correo Electronico</th>
                                        <th scope="col" class="col-2">Profesor de Preferencia</th>
                                        <th scope="col" class="col-1">Asignar Profesor</th>
                                        <th scope="col" class="col-1">Terminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 0;
                                    if ($result->num_rows > 0) {
                                        while ($alumno = $result->fetch_assoc()) {
                                            $contador++;
                                    ?>
                                            <tr style="height: 77px;" id="row<?php echo $alumno["legajo"] ?>">
                                                <td><?php echo date('Y-m-d', strtotime($alumno["fecha_solicitud"])); ?></td>
                                                <td><?php echo $alumno["legajo"] ?></td>
                                                <td><?php echo $alumno["nombre"] ?></td>
                                                <td><?php echo $alumno["apellido"] ?></td>
                                                <td><?php echo $alumno["carrera"] ?></td>
                                                <td><?php echo $alumno["email"] ?></td>
                                                <?php
                                                if (empty($alumno['dni_profesor'])) {
                                                ?>
                                                    <td class="profesor_asignado">Sin Preferencia</td>
                                                <?php
                                                } else {
                                                ?>
                                                    <td class="profesor_asignado" <?php if ($alumno['activo'] == 0) echo 'style="color: red !important;"'; ?> id="nombre-profesor"><?php echo $alumno["nombre_profesor"] . ' ' . $alumno["apellido_profesor"] ?></td>
                                                <?php
                                                }
                                                ?>
                                                <td><button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-profesor" aria-label="Seleccionar profesor" onclick="setAlumno('<?php echo $alumno['legajo'] ?>', '<?php echo $alumno['nombre'] ?>', '<?php echo $alumno['apellido'] ?>', '<?php echo $alumno['dni_profesor'] ?>')">Seleccionar Profesor</button></td>
                                                <td>
                                                    <form action="gestionar_solicitudes_pps_action.php" method="POST">
                                                        <input type="hidden" name="dni-alumno" value="<?php echo $alumno["dni_alumno"] ?>">
                                                        <input type="hidden" name="dni-profesor" value="<?php if ($alumno['activo'] == 1) echo $alumno["dni_profesor"] ?>">
                                                        <button type="submit" class="btn btn-success btn-sm" aria-label="Confirmar solicitud">Confirmar Solicitud</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        // Rellenar las filas restantes si no hay suficientes registros.
                                        while ($contador < $registros_por_pagina) {
                                            $contador++;
                                        ?>
                                            <tr style="height: 77px;">
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No hay solicitudes pendientes</td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col">
                        <nav class="d-flex flex-column justify-content-center h-100" aria-label="Paginación">
                            <ul class="pagination justify-content-center" style="margin-bottom: 0;">
                                <!-- Paginación: Anterior -->
                                <?php if ($pagina_actual > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">Anterior</span>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Anterior</span>
                                    </li>
                                <?php endif; ?>

                                <!-- Paginación: Mostrar páginas -->
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <?php if ($i == $pagina_actual): ?>
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link"><?php echo $i; ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <!-- Paginación: Siguiente -->
                                <?php if ($pagina_actual < $total_paginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">Siguiente</span>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Siguiente</span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col text-center">
                        <button type="button" class="btn btn-primary p-2" style="width: 250px;" aria-label="Volver al menú principal" onclick='window.location.href="menu_principal.php"'>Volver</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Modal -->
    <div class="modal fade" id="modal-profesor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-profesor-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="modal-profesor-title"></h5>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="profesor" class="form-label">Seleccione un profesor</label>
                            <select name="profesor" id="profesor" class="form-select ml-2" <?php if (empty($profesores_dni)) echo 'disabled' ?> required>
                                <?php
                                if (!empty($profesores_dni)) {
                                    foreach ($profesores_dni as $dni => $profesor) {
                                        echo '<option value="' . $dni . '">' . $profesor['nombre'] . ' ' . $profesor['apellido'] . '</option>';
                                    }
                                } else {
                                    echo '<option selected>No hay profesores disponibles</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <input type="hidden" name="legajo" id="modal-input-legajo">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" aria-label="Confirmar profesor seleccionado" onclick="guardarProfesor()" <?php if (empty($profesores_dni)) echo 'disabled' ?>>Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Cerrar ventana">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        function setAlumno(legajo, nombre, apellido, dniProfesor) {
            // Recuperar valores del modal.
            document.getElementById('modal-profesor-title').innerText = 'Asignar un profesor a ' + apellido + ' ' + nombre;
            document.getElementById('modal-input-legajo').value = legajo;

            // Si el dniProfesor coincide, seleccionar esa opción.
            let profesorSelect = document.getElementById('profesor');
            for (let option of profesorSelect.options) {
                if (option.value === dniProfesor) {
                    option.selected = true;
                    break;
                }
            }
        }

        function guardarProfesor() {
            // Definir variables.
            var dniProfesor = document.getElementById('profesor').value;
            var legajo = document.getElementById('modal-input-legajo').value;
            var nombreProfesor = document.querySelector('#profesor option[value="' + dniProfesor + '"]').text;

            // Actualizar el valor del profesor en la fila.
            document.querySelector('#row' + legajo + ' .profesor_asignado').textContent = nombreProfesor;
            document.querySelector('#row' + legajo + ' .profesor_asignado').style.color = '';
            document.querySelector('#row' + legajo + ' form input[name="dni-profesor"]').value = dniProfesor;

            // Cerrar modal.
            var modalElement = document.getElementById('modal-profesor');
            var modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();
        }
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>