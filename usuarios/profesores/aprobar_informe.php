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
    if (empty($_POST['dni-alumno']) || empty($_POST['id-informe']) || !isset($_POST['original']) || !isset($_POST['final'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: ../menu_principal.php");
        exit();
    }

    // Recuperar datos a través del método POST.
    $dni_alumno = $_POST['dni-alumno'];
    $id_informe = $_POST['id-informe'];
    $original = $_POST['original'];
    $final = $_POST['final'];

    // Establecer variables para la consulta.
    $estado = 'APROBADO';
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha_calificacion = date("Y-m-d H:i:s");

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Prepared statement.
    $stmt = $mysqli->prepare("UPDATE informes SET estado = ?, fecha_calificacion = ? WHERE dni_alumno = ? AND id_informe = ? AND original = ?");
    $stmt->bind_param("sssii", $estado, $fecha_calificacion, $dni_alumno, $id_informe, $original);
    $stmt->execute();

    // Validar si el informe es un informe final.
    if ($final == 1) {

        // Establecer ID de la notificación.
        $stmt = $mysqli->prepare("SELECT MAX(id_notificacion) AS id FROM notificaciones WHERE dni = ?");
        $stmt->bind_param("s", $dni_alumno);
        $stmt->execute();
        $result = $stmt->get_result();
        $notificacion = $result->fetch_assoc();
        $id_notificacion = $notificacion['id'];

        // Establecer variables para la consulta.
        $titulo = 'Informe final aprobado';
        $mensaje = 'Tu informe final ha sido aprobado por el profesor. Además, se ha enviado una notificación al responsable de las PPS para que revise todo el proceso y registre su aprobación.';
        $fecha_enviada = date("Y-m-d H:i:s");

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO notificaciones (dni, id_notificacion, titulo, mensaje, fecha_enviada) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $dni_alumno, $id_notificacion, $titulo, $mensaje, $fecha_enviada);
        $stmt->execute();

        // Recuperar nombre y apellido del alumno.
        $stmt = $mysqli->prepare("SELECT nombre, apellido FROM usuarios WHERE dni = ?");
        $stmt->bind_param("s", $dni_alumno);
        $stmt->execute();
        $result = $stmt->get_result();
        $alumno = $result->fetch_assoc();

        // Establecer variables para la consulta.
        $titulo = 'Informe Final Aprobado';
        $mensaje = 'Se ha aprobado el informe final de ' . $alumno['nombre'] . ' ' . $alumno['apellido'] . '.';
        $fecha_enviada = date("Y-m-d H:i:s");

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO notificaciones_sistema (titulo, mensaje, fecha_enviada) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $titulo, $mensaje, $fecha_enviada);
        $stmt->execute();

        // Establecer mensaje de éxito.
        $_SESSION['mensaje_exito'] = "Se ha aprobado el informe final con éxito y se ha enviado una notificacion al responsable.";
    } else {

        // Establecer ID de la notificación.
        $stmt = $mysqli->prepare("SELECT COALESCE(MAX(id_notificacion), 0) + 1 AS id FROM notificaciones WHERE dni = ?");
        $stmt->bind_param("s", $dni_alumno);
        $stmt->execute();
        $result = $stmt->get_result();
        $notificacion = $result->fetch_assoc();
        $id_notificacion = $notificacion['id'];

        // Establecer variables para la consulta.
        $titulo = 'Informe aprobado';
        $mensaje = 'Tu informe número ' . $id_informe . ' ha sido aprobado. ¡Felicitaciones!';
        $fecha_enviada = date("Y-m-d H:i:s");

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO notificaciones (dni, id_notificacion, titulo, mensaje, fecha_enviada) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $dni_alumno, $id_notificacion, $titulo, $mensaje, $fecha_enviada);
        $stmt->execute();

        // Establecer mensaje de éxito.
        $_SESSION['mensaje_exito'] = "Se ha aprobado el informe con éxito.";
    }

    // Cerrar la conexión a la base de datos.
    $mysqli->close();
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
}

// Redireccionar y finalizar el script actual.
header("Location: ../menu_principal.php");
exit();
