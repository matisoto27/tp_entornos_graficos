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

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['dni-alumno'])) {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: menu_principal.php");
        exit();
    }

    // Recuperar datos del formulario.
    $dni = $_POST['dni-alumno'];

    // Establecer variables para la consulta.
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha_pps_aprobadas = date("Y-m-d H:i:s");

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Prepared statement.
    $stmt = $mysqli->prepare("UPDATE alumnos SET fecha_pps_aprobadas = ? WHERE dni = ?");
    $stmt->bind_param("ss", $fecha_pps_aprobadas, $dni);
    $stmt->execute();

    // Establecer ID de la notificación.
    $stmt = $mysqli->prepare("SELECT COALESCE(MAX(id_notificacion), 0) + 1 AS id FROM notificaciones WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificacion = $result->fetch_assoc();
    $id_notificacion = $notificacion['id'];

    // Establecer variables para la consulta.
    $titulo = 'PPS Aprobadas';
    $mensaje = 'Un administrador ha realizado el registro de la aprobación de tus prácticas profesionales supervisadas. ¡Felicitaciones!';
    $fecha_enviada = date("Y-m-d H:i:s");

    // Prepared statement.
    $stmt = $mysqli->prepare("INSERT INTO notificaciones (dni, id_notificacion, titulo, mensaje, fecha_enviada) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $dni, $id_notificacion, $titulo, $mensaje, $fecha_enviada);
    $stmt->execute();

    // Recuperar nombre, apellido y dni_profesor del alumno.
    $stmt = $mysqli->prepare("SELECT nombre, apellido, dni_profesor FROM usuarios u INNER JOIN alumnos a ON u.dni = a.dni WHERE u.dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    $alumno = $result->fetch_assoc();
    $nombre_alumno = $alumno['nombre'];
    $apellido_alumno = $alumno['apellido'];
    $dni_profesor = $alumno['dni_profesor'];

    // Establecer ID de la notificación.
    $stmt = $mysqli->prepare("SELECT COALESCE(MAX(id_notificacion), 0) + 1 AS id FROM notificaciones WHERE dni = ?");
    $stmt->bind_param("s", $dni_profesor);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificacion = $result->fetch_assoc();
    $id_notificacion = $notificacion['id'];

    // Establecer variables para la consulta.
    $titulo = 'PPS Aprobadas';
    $mensaje = 'Un administrador ha realizado el registro de la aprobación de las prácticas profesionales supervisadas del alumno ' . $nombre_alumno . ' ' . $apellido_alumno . '.';
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha_enviada = date("Y-m-d H:i:s");

    // Prepared statement.
    $stmt = $mysqli->prepare("INSERT INTO notificaciones (dni, id_notificacion, titulo, mensaje, fecha_enviada) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $dni_profesor, $id_notificacion, $titulo, $mensaje, $fecha_enviada);
    $stmt->execute();

    // Establecer mensaje de éxito.
    $_SESSION['mensaje_exito'] = "¡Se ha registrado la aprobación de las PPS del alumno con éxito!";

    // Cerrar la conexión a la base de datos, redireccionar y finalizar el script actual.
    $mysqli->close();
    header("Location: menu_principal.php");
    exit();
} else {

    // Establecer mensaje de error, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
    exit();
}
