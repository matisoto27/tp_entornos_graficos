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
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no estén vacíos.
    if (empty($_POST['correcciones']) || empty($_POST['dni-alumno']) || empty($_POST['id-informe'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: ../menu_principal.php");
        exit();
    }

    // Recuperar datos a través del método POST.
    $correcciones = $_POST['correcciones'];
    $dni_alumno = $_POST['dni-alumno'];
    $id_informe = $_POST['id-informe'];

    // Establecer variables para la consulta.
    $estado = 'RECHAZADO';
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha_calificacion = date("Y-m-d H:i:s");

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Prepared statement.
    $stmt = $mysqli->prepare("UPDATE informes SET correcciones = ?, estado = ?, fecha_calificacion = ? WHERE dni_alumno = ? AND id_informe = ?");
    $stmt->bind_param("ssssi", $correcciones, $estado, $fecha_calificacion, $dni_alumno, $id_informe);
    $stmt->execute();

    // Establecer ID de la notificación.
    $stmt = $mysqli->prepare("SELECT COALESCE(MAX(id_notificacion), 0) + 1 AS id FROM notificaciones WHERE dni = ?");
    $stmt->bind_param("s", $dni_alumno);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificacion = $result->fetch_assoc();
    $id_notificacion = $notificacion['id'];

    // Establecer variables para la consulta.
    $titulo = 'Informe rechazado';
    $mensaje = 'Tu informe número ' . $id_informe . ' ha sido rechazado. Por favor, realiza las correcciones indicadas y vuelve a subirlo.';
    $fecha_enviada = date("Y-m-d H:i:s");

    // Prepared statement.
    $stmt = $mysqli->prepare("INSERT INTO notificaciones (dni, id_notificacion, titulo, mensaje, fecha_enviada) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $dni_alumno, $id_notificacion, $titulo, $mensaje, $fecha_enviada);
    $stmt->execute();

    // Establecer mensaje de éxito, cerrar la conexión a la base de datos, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_exito'] = "Se han registrado las correcciones del informe con éxito.";
    $mysqli->close();
    header("Location: ../menu_principal.php");
    exit();
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: ../menu_principal.php");
    exit();
}
