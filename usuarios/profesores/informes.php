<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'profesores') {
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

// Paginación.
// Definir la cantidad de registros por página.
$registros_por_pagina = 5;
// Determinar la página actual.
if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
    $pagina_actual = $_GET['pagina'];
} else {
    $pagina_actual = 1;
}
// Calcular el desplazamiento (offset) de los registros.
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Contar cantidad de informes.
$stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM informes i INNER JOIN alumnos a ON i.dni_alumno = a.dni WHERE dni_profesor = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_count1 = $stmt->get_result();
$row_count1 = $result_count1->fetch_assoc();
$total1 = $row_count1['total'];

// Contar cantidad de planes de trabajo.
$stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM alumnos WHERE dni_profesor = ? AND fecha_plan_trabajo IS NOT NULL");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_count2 = $stmt->get_result();
$row_count2 = $result_count2->fetch_assoc();
$total2 = $row_count2['total'];

// Contar cantidad de solicitudes de inicio.
$stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM alumnos WHERE dni_profesor = ? AND fecha_confirmacion_solicitud IS NOT NULL");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_count3 = $stmt->get_result();
$row_count3 = $result_count3->fetch_assoc();
$total3 = $row_count3['total'];

// Contar el total de registros.
$total_registros = $total1 + $total2 + $total3;
$total_paginas = ceil($total_registros / $registros_por_pagina);
// Fin Paginación.

// Recuperar todos los informes de los alumnos del profesor.
$stmt = $mysqli->prepare("SELECT u.dni, nombre, apellido, legajo, id_informe, original, nombre_archivo, fecha_subida, s.estado AS estado_informe, s.correcciones, s.final FROM usuarios AS u INNER JOIN alumnos AS a ON u.dni = a.dni INNER JOIN informes AS s ON a.dni = s.dni_alumno WHERE dni_profesor = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_informes = $stmt->get_result();

// Recuperar todos los planes de trabajo de los alumnos del profesor.
$stmt = $mysqli->prepare("SELECT fecha_plan_trabajo, legajo, nombre, apellido, archivo_plan_trabajo, u.dni FROM usuarios u INNER JOIN alumnos a ON u.dni = a.dni WHERE dni_profesor = ? AND fecha_plan_trabajo IS NOT NULL");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_planes_trabajo = $stmt->get_result();

// Recuperar todas las solicitudes de inicio de los alumnos del profesor.
$stmt = $mysqli->prepare("SELECT fecha_confirmacion_solicitud, legajo, nombre, apellido, archivo_plan_trabajo, u.dni FROM usuarios u INNER JOIN alumnos a ON u.dni = a.dni WHERE dni_profesor = ? AND fecha_confirmacion_solicitud IS NOT NULL");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_solicitudes_inicio = $stmt->get_result();

// Mezclar solicitudes de inicio, planes de trabajo e informes en una misma lista.
$alumnos = [];
while ($informe = $result_informes->fetch_assoc()) {
    $alumnos[] = [
        'fecha_subida' => $informe['fecha_subida'],
        'legajo' => $informe['legajo'],
        'id_informe' => $informe['id_informe'],
        'nombre' => $informe['nombre'],
        'apellido' => $informe['apellido'],
        'nombre_archivo' => $informe['nombre_archivo'],
        'dni' => $informe['dni'],
        'original' => $informe['original'],
        'estado_informe' => $informe['estado_informe'],
        'correcciones' => $informe['correcciones'],
        'final' => $informe['final'],
        'tipo' => 'informe',
    ];
}
while ($alu = $result_planes_trabajo->fetch_assoc()) {
    $alumnos[] = [
        'fecha_subida' => $alu['fecha_plan_trabajo'],
        'legajo' => $alu['legajo'],
        'id_informe' => '-',
        'nombre' => $alu['nombre'],
        'apellido' => $alu['apellido'],
        'nombre_archivo' => $alu['archivo_plan_trabajo'],
        'dni' => $alu['dni'],
        'original' => '-',
        'estado_informe' => '-',
        'correcciones' => '-',
        'final' => '-',
        'tipo' => 'plan_trabajo',
    ];
}
while ($alu = $result_solicitudes_inicio->fetch_assoc()) {
    $alumnos[] = [
        'fecha_subida' => $alu['fecha_confirmacion_solicitud'],
        'legajo' => $alu['legajo'],
        'id_informe' => '-',
        'nombre' => $alu['nombre'],
        'apellido' => $alu['apellido'],
        'nombre_archivo' => 'solicitud_inicio_' . strtolower($alu['apellido']) . '_' . strtolower($alu['nombre']) . '.pdf',
        'dni' => $alu['dni'],
        'original' => '-',
        'estado_informe' => '-',
        'correcciones' => '-',
        'final' => '-',
        'tipo' => 'solicitud_inicio',
    ];
}
usort($alumnos, function ($a, $b) {
    $dateA = DateTime::createFromFormat('Y-m-d H:i:s', $a['fecha_subida']);
    $dateB = DateTime::createFromFormat('Y-m-d H:i:s', $b['fecha_subida']);
    return $dateB <=> $dateA;
});

$alumnos = array_slice($alumnos, $offset, $registros_por_pagina);

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

<body class="d-flex flex-column">
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/header.php';
    ?>
    <main class="d-flex justify-content-center align-items-center flex-fill border-top border-bottom">
        <div class="container-fluid br-class alt-background" style="border-radius: 0;">
            <div class="row pt-4 mb-3">
                <div class="col">
                    <h2 class="text-center">PPS Alumnos</h2>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="table-responsive">
                        <table class="table text-center" style="min-width: 1600px;">
                            <thead>
                                <tr>
                                    <th scope="col" class="col-2">Fecha Subida</th>
                                    <th scope="col" class="col-1">Legajo</th>
                                    <th scope="col" class="col-1">Nro. Informe</th>
                                    <th scope="col" class="col-1">Nombre</th>
                                    <th scope="col" class="col-1">Apellido</th>
                                    <th scope="col" class="col-2">Descargar</th>
                                    <th scope="col" class="col-3">Agregar Correcciones (Rechazar)</th>
                                    <th scope="col" class="col-1">Aprobar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $contador = 0;
                                if (!empty($alumnos)) {
                                    foreach ($alumnos as $alumno) {
                                        $contador++;
                                ?>
                                        <tr style="height: 72px;">
                                            <td><?php echo $alumno['fecha_subida'] ?></td>
                                            <td><?php echo $alumno['legajo'] ?></td>
                                            <td><?php echo $alumno['id_informe'] ?></td>
                                            <td><?php echo $alumno['nombre'] ?></td>
                                            <td><?php echo $alumno['apellido'] ?></td>
                                            <td>
                                                <?php
                                                if ($alumno['tipo'] === 'plan_trabajo') {
                                                ?>
                                                    <a href="../../descargar_archivo.php?nombre-archivo=<?php echo $alumno['nombre_archivo'] ?>&es-informe=0">
                                                        <?php echo $alumno['nombre_archivo'] ?>
                                                    </a>
                                                <?php
                                                } elseif ($alumno['tipo'] === 'informe') {
                                                ?>
                                                    <a href="../../descargar_archivo.php?nombre-archivo=<?php echo $alumno['nombre_archivo'] ?>&es-informe=1">
                                                        <?php echo $alumno['nombre_archivo'] ?>
                                                    </a>
                                                <?php
                                                } else {
                                                ?>
                                                    <a href="../../solicitud_inicio_pdf.php?dni=<?php echo $alumno['dni'] ?>" target="_blank">
                                                        <?php echo $alumno['nombre_archivo'] ?>
                                                    </a>
                                                <?php
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                if ($alumno['tipo'] === 'informe') {
                                                    if ($alumno['estado_informe'] === "PENDIENTE" && $alumno['original'] == 1) {
                                                        $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM informes WHERE dni_alumno = ? AND final = 1 AND estado = 'Rechazado'");
                                                        $stmt->bind_param("s", $alumno['dni']);
                                                        $stmt->execute();
                                                        $result = $stmt->get_result();
                                                        $alu = $result->fetch_assoc();
                                                        $total = $alu['total'];
                                                        if ($alu['total'] < 2) {
                                                ?>
                                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-informe" onclick="abrirModal('<?php echo $alumno['dni'] ?>', '<?php echo $alumno['nombre'] ?>', '<?php echo $alumno['apellido'] ?>', '<?php echo $alumno['id_informe'] ?>', '<?php echo $alumno['original'] ?>')">Click aqui</button>
                                                        <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        -
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    -
                                                <?php
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <form action="aprobar_informe.php" method="POST">
                                                    <input type="hidden" value="<?php echo $alumno["dni"] ?>" name="dni-alumno">
                                                    <input type="hidden" value="<?php echo $alumno["id_informe"] ?>" name="id-informe">
                                                    <input type="hidden" value="<?php echo $alumno["original"] ?>" name="original">
                                                    <input type="hidden" name="final" value="<?php echo $alumno["final"] ?>">
                                                    <?php
                                                    if ($alumno['tipo'] === 'informe') {
                                                        if ($alumno['estado_informe'] === "APROBADO") {
                                                    ?>
                                                            <button type="submit" class="btn btn-secondary" disabled>Aprobado</button>
                                                        <?php
                                                        } elseif (!empty($alumno['correcciones'])) {
                                                        ?>
                                                            <button type="submit" class="btn btn-danger" disabled>Rechazado</button>
                                                        <?php
                                                        } else {
                                                        ?>
                                                            <button type="submit" class="btn btn-success">Aprobar</button>
                                                        <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        -
                                                    <?php
                                                    }
                                                    ?>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    // Rellenar las filas restantes si no hay suficientes registros.
                                    while ($contador < $registros_por_pagina) {
                                        $contador++;
                                    ?>
                                        <tr style="height: 72px;">
                                            <td colspan="8"></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    for ($i = 0; $i < $registros_por_pagina; $i++) {
                                    ?>
                                        <tr style="height: 72px;">
                                            <?php
                                            if ($i == 0) {
                                            ?>
                                                <td colspan="8" class="text-center">Todavía no se han subido informes ni planes de trabajo.</td>
                                            <?php
                                            } else {
                                            ?>
                                                <td colspan="8"></td>
                                            <?php
                                            }
                                            ?>
                                        </tr>
                                <?php
                                    }
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
            <div class="row mb-3">
                <div class="col text-center">
                    <button type="button" class="btn btn-primary px-5 py-2" onclick='window.location.href="informe_alumnos_a_cargo.php"'>Descargar informe de alumnos a cargo actualmente</button>
                </div>
            </div>
            <div class="row pb-4">
                <div class="col text-center">
                    <button type="button" class="btn btn-primary px-5 py-2" onclick='window.location.href="informe_alumnos_ciclo_lectivo.php"'>Buscar alumnos tutorizados en un ciclo lectivo determinado</button>
                </div>
            </div>
        </div>
    </main>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/footer.php';
    ?>
    <!-- Modal -->
    <div class="modal fade" id="modal-informe" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-informe-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-informe-title"></h5>
                </div>
                <div class="modal-body">
                    <form id="form-correcciones" action="corregir_informe.php" method="POST">
                        <div class="mb-3">
                            <label for="input-correcciones" class="form-label">Escriba las correcciones a continuación:</label>
                            <input type="text" class="form-control" name="correcciones" id="input-correcciones" required>
                        </div>
                        <input type="hidden" name="dni-alumno" id="input-dni-alumno">
                        <input type="hidden" name="id-informe" id="input-id-informe">
                        <input type="hidden" name="original" id="input-original">
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        function abrirModal(dni, nombre, apellido, id_informe, original) {

            // Establecer el título del modal.
            const modalInformeTitle = document.getElementById('modal-informe-title');
            modalInformeTitle.innerText = 'Agregar correcciones al informe de ' + apellido + ' ' + nombre;

            // Asignar valores a los campos ocultos del formulario.
            document.getElementById('input-dni-alumno').value = dni;
            document.getElementById('input-id-informe').value = id_informe;
            document.getElementById('input-original').value = original;
        }
    </script>

    <?php
    // Cerrar la conexión a la base de datos.
    $mysqli->close();
    ?>
</body>

</html>