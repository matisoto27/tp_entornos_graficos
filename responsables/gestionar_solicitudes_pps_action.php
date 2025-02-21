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

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no estén vacíos.
    if (empty($_POST['dni-profesor']) || empty($_POST['dni-alumno'])) {
        $_SESSION['mensaje_error'] = "Por favor, seleccione un profesor.";
        header("Location: menu_principal.php");
        exit();
    }

    // Recuperar datos a través del método POST.
    $dni_profesor = $_POST['dni-profesor'];
    $dni_alumno = $_POST['dni-alumno'];

    // Establecer variables para la consulta.
    $estado_solicitud = 'Confirmada';
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha_confirmacion_solicitud = date("Y-m-d H:i:s");

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Prepared statement.
    $stmt = $mysqli->prepare("UPDATE alumnos SET estado_solicitud = ?, fecha_confirmacion_solicitud = ?, dni_profesor = ? WHERE dni = ?");
    $stmt->bind_param("ssss", $estado_solicitud, $fecha_confirmacion_solicitud, $dni_profesor, $dni_alumno);
    $stmt->execute();

    // Prepared statement.
    $stmt = $mysqli->prepare("SELECT nombre, apellido FROM usuarios WHERE dni = ?");
    $stmt->bind_param("s", $dni_alumno);
    $stmt->execute();
    $result = $stmt->get_result();
    $alumno = $result->fetch_assoc();

    // Establecer ID de la notificación.
    $stmt = $mysqli->prepare("SELECT COALESCE(MAX(id_notificacion), 0) + 1 AS id FROM notificaciones WHERE dni = ?");
    $stmt->bind_param("s", $dni_profesor);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificacion = $result->fetch_assoc();
    $id_notificacion = $notificacion['id'];

    // Establecer variables para la consulta.
    $titulo = "Has sido seleccionado como tutor";
    $mensaje = $alumno['nombre'] . ' ' . $alumno['apellido'] . ' te ha seleccionado como su tutor para realizar las practicas profesionales supervisadas.';
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha_enviada = date("Y-m-d H:i:s");

    // Prepared statement.
    $stmt = $mysqli->prepare("INSERT INTO notificaciones (dni, id_notificacion, titulo, mensaje, fecha_enviada) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $dni_profesor, $id_notificacion, $titulo, $mensaje, $fecha_enviada);
    $stmt->execute();

    // Cerrar la conexión a la base de datos, redireccionar y finalizar el script actual.
    $mysqli->close();
    header("Location: gestionar_solicitudes_pps.php");
    exit();
} else {

    // Establecer mensaje de error, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
    exit();
}
