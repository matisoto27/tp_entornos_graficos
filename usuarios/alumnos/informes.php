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
// Contar el total de registros.
$stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM informes WHERE dni_alumno = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result_count = $stmt->get_result();
$row_count = $result_count->fetch_assoc();
$total_registros = $row_count['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);
// Fin Paginación.

// Validar acceso autorizado.
$stmt = $mysqli->prepare("SELECT * FROM alumnos WHERE dni = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();
if (empty($alumno['fecha_plan_trabajo'])) {
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: ../menu_principal.php");
    exit();
}

// Buscar nombre y apellido del alumno.
$stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE dni = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();

// Buscar todos los informes.
$stmt = $mysqli->prepare("SELECT * FROM informes WHERE dni_alumno = ? ORDER BY fecha_subida DESC LIMIT $offset, $registros_por_pagina");
$stmt->bind_param("s", $dni);
$stmt->execute();

// Validar si existen informes.
$informes_result = $stmt->get_result();
if ($informes_result->num_rows > 0) {

    // Obtener cantidad total de informes originales.
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM informes WHERE dni_alumno = ? AND original = 1");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $cantidad_total = $row[0];

    // Obtener cantidad de informes aprobados.
    $cantidad_aprobados = 0;
    $stmt = $mysqli->prepare("SELECT * FROM informes WHERE dni_alumno = ? AND original = 1");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result_informes = $stmt->get_result();
    while ($informe = $result_informes->fetch_assoc()) {
        if ($informe['estado'] === 'APROBADO') {
            $cantidad_aprobados += 1;
        } elseif ($informe['estado'] === 'RECHAZADO') {
            $id_informe = $informe['id_informe'];
            $stmt = $mysqli->prepare("SELECT * FROM informes WHERE dni_alumno = ? AND id_informe = ? AND original = 0");
            $stmt->bind_param("si", $dni, $id_informe);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $informe_corregido = $result->fetch_assoc();
                if ($informe_corregido['estado'] === 'APROBADO') $cantidad_aprobados += 1;
            }
        }
    }

    // Obtener MAX(id_informe).
    $stmt = $mysqli->prepare("SELECT MAX(id_informe) FROM informes WHERE dni_alumno = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_row();
    $max_id_informe = $row[0];

    // Validar si ha pasado mas de una semana desde que se subio el ultimo informe original.
    $stmt = $mysqli->prepare("SELECT * FROM informes WHERE dni_alumno = ? AND id_informe = ? AND original = 1");
    $stmt->bind_param("si", $dni, $max_id_informe);
    $stmt->execute();
    $result = $stmt->get_result();
    $informe = $result->fetch_assoc();
    $fecha_subida = date("Y-m-d", strtotime($informe['fecha_subida']));
    $fecha_hoy = new DateTime();
    $fecha_hoy->sub(new DateInterval('P7D'));
    $fecha_limite = $fecha_hoy->format('Y-m-d');
    if ($fecha_subida > $fecha_limite) $supera_semana = 0;
    else $supera_semana = 1;

    // Validar si se ha subido un informe final y si este fue rechazado.
    $stmt = $mysqli->prepare("SELECT * FROM informes WHERE dni_alumno = ? AND id_informe = ? AND final = 1");
    $stmt->bind_param("si", $dni, $max_id_informe);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $subio_informe_final = 1;
        $informe_final = $result->fetch_assoc();
        if ($informe_final['estado'] === 'RECHAZADO') $informe_final_rechazado = 1;
        else $informe_final_rechazado = 0;
    } else {
        $subio_informe_final = 0;
    }

    // Obtener la fecha de fin del ciclo lectivo del alumno.
    $stmt = $mysqli->prepare("SELECT * FROM alumnos a INNER JOIN ciclos_lectivos cl ON a.id_ciclo_lectivo = cl.id_ciclo_lectivo WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    $ciclo_lectivo = $result->fetch_assoc();
    $fecha_fin = $ciclo_lectivo['fecha_fin'];
    $fecha_hoy = date('Y-m-d');
    if (strtotime($fecha_hoy) > strtotime($fecha_fin)) $supera_fecha_fin = 1;
    else $supera_fecha_fin = 0;
} else {

    // Inicializar variables.
    $cantidad_total = 0;
    $cantidad_aprobados = 0;
    $supera_semana = 1;
}

// Redirección.
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
        <div class="container br-class alt-background">
            <div class="row pt-4 mb-3">
                <div class="col">
                    <h2 class="text-center">Informes Semanales</h2>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="table-responsive mb-4">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th scope="col" class="col-2">ID</th>
                                    <th scope="col" class="col-4">Nombre Archivo</th>
                                    <th scope="col" class="col-4">Fecha Subida</th>
                                    <th scope="col" class="col-2">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $contador = 0;
                                if ($informes_result->num_rows > 0) {
                                    while ($informe = $informes_result->fetch_assoc()) {
                                        $contador++;
                                ?>
                                        <tr style="height: 60px;">
                                            <th scope="row"><?php echo $informe['id_informe'] ?></th>
                                            <td><?php echo $informe['nombre_archivo'] ?></td>
                                            <td><?php echo $informe['fecha_subida'] ?></td>
                                            <td>
                                                <?php
                                                if ($informe['estado'] === 'APROBADO' && $informe['final'] == 0) {
                                                ?>
                                                    <span class="badge bg-success"><?php echo $informe['estado'] ?></span>
                                                <?php
                                                } elseif ($informe['estado'] === 'APROBADO' && $informe['final'] == 1) {
                                                ?>
                                                    <span class="badge bg-success">FINAL APROBADO</span>
                                                    <?php
                                                } elseif (!empty($informe['correcciones'])) {

                                                    // Validar si existe un informe corregido con el mismo ID.
                                                    $id_informe = $informe['id_informe'];
                                                    $stmt = $mysqli->prepare("SELECT * FROM informes WHERE dni_alumno = ? AND id_informe = ? AND original = 0");
                                                    $stmt->bind_param("si", $dni, $id_informe);
                                                    $stmt->execute();
                                                    $result20 = $stmt->get_result();
                                                    if ($result20->num_rows > 0) $existe_correccion = 1;
                                                    else $existe_correccion = 0;

                                                    if ($informe['final'] == 1) {
                                                    ?>
                                                        <button type="button" class="btn btn-danger badge text-light" data-bs-toggle="modal" data-bs-target="#modal-correcciones" aria-label="Final rechazado, abrir correcciones" onclick="abrirModal('<?php echo $informe['id_informe'] ?>', '<?php echo $informe['correcciones'] ?>', <?php echo $existe_correccion ?>, 1)">Final Rechazado (VER CORRECCION)</button>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <button type="button" class="btn btn-danger badge text-light" data-bs-toggle="modal" data-bs-target="#modal-correcciones" aria-label="Informe rechazado, abrir correcciones" onclick="abrirModal('<?php echo $informe['id_informe'] ?>', '<?php echo $informe['correcciones'] ?>', <?php echo $existe_correccion ?>, 0)">Informe Rechazado (VER CORRECCION)</button>
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <span class="badge bg-warning text-dark"><?php echo $informe['estado'] ?></span>
                                                <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    // Rellenar las filas restantes si no hay suficientes registros.
                                    while ($contador < $registros_por_pagina) {
                                        $contador++;
                                    ?>
                                        <tr style="height: 60px;">
                                            <td colspan="4"></td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    for ($i = 0; $i < $registros_por_pagina; $i++) {
                                    ?>
                                        <tr style="height: 60px;">
                                            <?php
                                            if ($i == 0) {
                                            ?>
                                                <td colspan="4" class="text-center">Todavía no se ha subido ningún informe.</td>
                                            <?php
                                            } else {
                                            ?>
                                                <td colspan="4"></td>
                                            <?php
                                            }
                                            ?>
                                        </tr>
                                    <?php
                                    }
                                }
                                if (($cantidad_total < 3 && $supera_semana) || ($cantidad_total == 3 && $cantidad_aprobados == 3 && $supera_semana && $supera_fecha_fin) || ($cantidad_total > 3 && $cantidad_aprobados == 3 && $supera_semana && $supera_fecha_fin && $informe_final_rechazado)) {
                                    ?>
                                    <tr>
                                        <th scope="row">#</th>
                                        <td colspan="2" id="nombre-archivo">No se ha seleccionado ningún archivo</td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <label for="file-input-informe" class="upload-icon">
                                                    <span class="icon">+</span>
                                                </label>
                                                <input type="file" class="d-none" form="form-subir-informe" name="informe" id="file-input-informe" onchange="actualizarTextoInforme()" required>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    if ($cantidad_total < 3) {
                    ?>
                        <form action="subir_informe.php" method="POST" enctype="multipart/form-data" id="form-subir-informe">
                            <input type="hidden" value="<?php echo $alumno['apellido'] ?>" name="apellido">
                            <input type="hidden" value="<?php echo $alumno['nombre'] ?>" name="nombre">
                            <div class="d-flex flex-column">
                                <div class="d-flex flex-row justify-content-center pb-4">
                                    <button type="submit" class="btn btn-primary" name="subir-informe" id="submit-button-subir-informe" aria-label="Subir informe" disabled>Subir Informe</button>
                                </div>
                                <?php
                                if (!$supera_semana) {
                                ?>
                                    <div class="d-flex flex-row justify-content-center">
                                        <p>El último informe se ha subido hace menos de 1 semana.</p>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        </form>

                        <?php
                    } elseif (($cantidad_total == 3 && $cantidad_aprobados == 3) || ($cantidad_total > 3 && $cantidad_aprobados == 3 && $informe_final_rechazado)) {
                        if ($supera_fecha_fin) {
                        ?>
                            <form action="subir_informe_final.php" method="POST" enctype="multipart/form-data" id="form-subir-informe">
                                <div class="mt-4 d-flex justify-content-center">
                                    <input type="hidden" value="<?php echo $alumno['apellido'] ?>" name="apellido">
                                    <input type="hidden" value="<?php echo $alumno['nombre'] ?>" name="nombre">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex flex-row justify-content-center pb-4">
                                            <button type="submit" class="btn btn-success" name="subir-informe" id="submit-button-subir-informe" aria-label="Subir informe final" disabled>Subir Informe Final</button>
                                        </div>
                                        <?php
                                        if (!$supera_semana) {
                                        ?>
                                            <div class="d-flex flex-row justify-content-center pb-4">
                                                <p>El último informe se ha subido hace menos de 1 semana.</p>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </form>
                        <?php
                        } else {
                        ?>
                            <div class="d-flex flex-column mb-3 align-items-center">
                                <div class="d-flex flex-col text-center mb-3 px-3">Una vez finalizado el ciclo lectivo, podrás subir el informe final.</div>
                                <div class="d-flex flex-col">Fecha de fin del ciclo lectivo</div>
                                <div class="d-flex flex-col"><?php echo $fecha_fin ?></div>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="row pb-4">
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
        </div>
    </main>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . '/usuarios/footer.php';
    ?>
    <!-- Modal -->
    <div class="modal fade" id="modal-correcciones" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modal-correcciones-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-correcciones-title"></h5>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-start" id="modal-mensaje"></div>
                </div>
                <div class="modal-footer">
                    <div class="container">
                        <div class="row mb-3" id="modal-footer-row">
                            <div class="col d-flex justify-content-center align-items-center">
                                <p class="mb-0" id="nombre-informe-corregido">No se ha seleccionado ningún archivo</p>
                                <label for="file-input-informe-corregido" class="upload-icon ms-3"><span class="icon">+</span></label>
                                <input type="file" class="d-none" form="form-subir-informe-corregido" name="informe-corregido" id="file-input-informe-corregido" onchange="actualizarTextoInformeCorregido()" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col d-flex justify-content-center">
                                <form method="POST" action="subir_informe_corregido.php" enctype="multipart/form-data" id="form-subir-informe-corregido">
                                    <input type="hidden" name="id-informe" id="modal-input-id-informe">
                                    <input type="hidden" name="apellido" id="modal-input-apellido">
                                    <input type="hidden" name="nombre" id="modal-input-nombre">
                                    <button type="submit" class="btn btn-primary m-2" id="submit-button-informe-corregido" aria-label="Subir informe corregido" disabled>Subir Informe Corregido</button>
                                    <button type="button" class="btn btn-secondary m-2" data-bs-dismiss="modal" aria-label="Cerrar ventana de correcciones">Cerrar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script>
        function abrirModal(id_informe, correcciones, existe_correccion, es_final) {
            document.getElementById('modal-correcciones-title').innerText = 'Correcciones del Informe N' + id_informe;
            document.getElementById('modal-mensaje').innerText = correcciones;
            if (existe_correccion == 1 || es_final == 1) {
                document.getElementById('modal-footer-row').style.display = 'none';
                document.getElementById('submit-button-informe-corregido').style.display = 'none';
            } else {
                const apellido = "<?php echo $alumno['apellido'] ?>";
                const nombre = "<?php echo $alumno['nombre'] ?>";
                document.getElementById('modal-input-id-informe').value = id_informe;
                document.getElementById('modal-input-apellido').value = apellido;
                document.getElementById('modal-input-nombre').value = nombre;
            }
        }

        function actualizarTextoInforme() {
            var fileInputInforme = document.getElementById('file-input-informe');
            var texto = document.getElementById('nombre-archivo');
            var submitButton = document.getElementById('submit-button-subir-informe');
            if (fileInputInforme.files.length > 0) {
                texto.textContent = fileInputInforme.files[0].name;
                submitButton.removeAttribute('disabled');
            }
        }

        function actualizarTextoInformeCorregido() {
            var fileInput = document.getElementById('file-input-informe-corregido');
            var texto = document.getElementById('nombre-informe-corregido');
            var submitButton = document.getElementById('submit-button-informe-corregido');
            if (fileInput.files.length > 0) {
                texto.textContent = fileInput.files[0].name;
                submitButton.removeAttribute('disabled');
            }
        }

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