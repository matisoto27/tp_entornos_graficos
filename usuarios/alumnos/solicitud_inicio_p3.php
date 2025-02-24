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

// Validar si se reciben datos por POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['legajo']) || empty($_POST['carrera'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: solicitud_inicio_p1.php");
        exit();
    }

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['nombre-empresa']) || empty($_POST['direccion-empresa']) || empty($_POST['telefono-empresa']) || empty($_POST['modalidad-trabajo']) || empty($_POST['nombre-jefe']) || empty($_POST['apellido-jefe']) || empty($_POST['email-jefe'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    function quitarTildes($texto)
    {
        $tabla = array(
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
        );
        return strtr($texto, $tabla);
    }

    // Recuperar datos del formulario parte 1.
    $legajo = $_POST['legajo'];
    $carrera = $_POST['carrera'];
    $dni_profesor = empty($_POST['dni-profesor']) ? NULL : $_POST['dni-profesor'];

    // Recuperar datos del formulario parte 2.
    $nombre_empresa = ucwords(quitarTildes(strtolower(trim($_POST['nombre-empresa']))));
    $direccion_empresa = ucwords(quitarTildes(strtolower(trim($_POST['direccion-empresa']))));
    $telefono_empresa = trim($_POST['telefono-empresa']);
    $modalidad_trabajo = $_POST['modalidad-trabajo'];
    $nombre_jefe = ucwords(quitarTildes(strtolower(trim($_POST['nombre-jefe']))));
    $apellido_jefe = ucwords(quitarTildes(strtolower(trim($_POST['apellido-jefe']))));
    $email_jefe = quitarTildes(strtolower(trim($_POST['email-jefe'])));

    // Guardar respuestas.
    $_SESSION['nombre_empresa'] = $_POST['nombre-empresa'];
    $_SESSION['direccion_empresa'] = $_POST['direccion-empresa'];
    $_SESSION['telefono_empresa'] = $_POST['telefono-empresa'];
    $_SESSION['modalidad_trabajo'] = $_POST['modalidad-trabajo'];
    $_SESSION['nombre_jefe'] = $_POST['nombre-jefe'];
    $_SESSION['apellido_jefe'] = $_POST['apellido-jefe'];
    $_SESSION['email_jefe'] = $_POST['email-jefe'];

    // Validar que el nombre de la empresa solo contenga letras.
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $nombre_empresa)) {
        unset($_SESSION['nombre_empresa']);
        $_SESSION['mensaje_error'] = "El nombre de la empresa solo puede contener letras.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Validar que la dirección de la empresa contenga letras y números.
    if (!preg_match("/[a-zA-Z]/", $direccion_empresa) || !preg_match("/[0-9]/", $direccion_empresa)) {
        unset($_SESSION['direccion_empresa']);
        $_SESSION['mensaje_error'] = "La dirección de la empresa no tiene un formato válido.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Validar que la dirección de la empresa tenga una longitud adecuada.
    if (strlen($direccion_empresa) < 10) {
        unset($_SESSION['direccion_empresa']);
        $_SESSION['mensaje_error'] = "La dirección de la empresa está incompleta.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Validar que el teléfono de la empresa sea numérico.
    if (!preg_match('/^[0-9]+$/', $telefono_empresa)) {
        unset($_SESSION['telefono_empresa']);
        $_SESSION['mensaje_error'] = "El teléfono solo puede contener números.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Validar que el teléfono de la empresa tenga una longitud adecuada.
    if (strlen($telefono_empresa) < 9 || strlen($telefono_empresa) > 15) {
        unset($_SESSION['telefono_empresa']);
        $_SESSION['mensaje_error'] = "El teléfono debe tener entre 9 y 15 dígitos.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Lista de modalidades válidas.
    $modalidades_validas = [
        "Presencial",
        "Remoto",
        "Hibrido"
    ];

    // Validar modalidad.
    if (!in_array($modalidad_trabajo, $modalidades_validas)) {
        unset($_SESSION['modalidad_trabajo']);
        $_SESSION['mensaje_error'] = "Por favor, seleccione una modalidad.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Validar que el nombre del jefe solo contenga letras.
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $nombre_jefe)) {
        unset($_SESSION['nombre_jefe']);
        $_SESSION['mensaje_error'] = "El nombre del jefe solo puede contener letras.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Validar que el apellido del jefe solo contenga letras.
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $apellido_jefe)) {
        unset($_SESSION['apellido_jefe']);
        $_SESSION['mensaje_error'] = "El apellido solo puede contener letras.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Validar formato del correo electrónico del jefe.
    if (!filter_var($email_jefe, FILTER_VALIDATE_EMAIL)) {
        unset($_SESSION['email_jefe']);
        $_SESSION['mensaje_error'] = "El correo electrónico no tiene un formato válido.";
        header("Location: solicitud_inicio_p2.php");
        exit();
    }

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Establecer variables para la consulta.
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha_actual = date("Y-m-d H:i:s");
    $fecha_solicitud = date("Y-m-d H:i:s", strtotime("-1 month", strtotime($fecha_actual)));
    $estado_solicitud = "Pendiente";
    $result = $mysqli->query("SELECT MAX(id_ciclo_lectivo) AS id FROM ciclos_lectivos");
    $row = $result->fetch_assoc();
    $id_ciclo_lectivo = $row['id'];

    // Prepared statement.
    $stmt = $mysqli->prepare("UPDATE alumnos SET legajo = ?, carrera = ?, fecha_solicitud = ?, estado_solicitud = ?, nombre_empresa = ?, direccion_empresa = ?, telefono_empresa = ?, modalidad_trabajo = ?, nombre_jefe = ?, apellido_jefe = ?, email_jefe = ?, dni_profesor = ?, id_ciclo_lectivo = ? WHERE dni = ?");
    $stmt->bind_param("sssssssssssssi", $legajo, $carrera, $fecha_solicitud, $estado_solicitud, $nombre_empresa, $direccion_empresa, $telefono_empresa, $modalidad_trabajo, $nombre_jefe, $apellido_jefe, $email_jefe, $dni_profesor, $id_ciclo_lectivo, $dni);
    $stmt->execute();

    // Recuperar nombre y apellido del alumno.
    $stmt = $mysqli->prepare("SELECT nombre, apellido FROM usuarios WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result2 = $stmt->get_result();
    $alumno = $result2->fetch_assoc();

    // Establecer variables para la consulta.
    $titulo = 'Nueva solicitud de inicio de PPS';
    $mensaje = 'Se ha recibido una nueva solicitud de ' . $alumno['nombre'] . ' ' . $alumno['apellido'] . ' para iniciar las Prácticas Profesionales Supervisadas.';
    $fecha_enviada = date("Y-m-d H:i:s");

    // Prepared statement.
    $stmt = $mysqli->prepare("INSERT INTO notificaciones_sistema (titulo, mensaje, fecha_enviada) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $titulo, $mensaje, $fecha_enviada);
    $stmt->execute();

    // Destruir variables de sesión.
    unset($_SESSION['legajo']);
    unset($_SESSION['carrera']);
    unset($_SESSION['dni_profesor']);
    unset($_SESSION['nombre_empresa']);
    unset($_SESSION['direccion_empresa']);
    unset($_SESSION['telefono_empresa']);
    unset($_SESSION['modalidad_trabajo']);
    unset($_SESSION['nombre_jefe']);
    unset($_SESSION['apellido_jefe']);
    unset($_SESSION['email_jefe']);

    // Establecer mensaje de éxito y cerrar la conexión a la base de datos.
    $_SESSION['mensaje_exito'] = "Se ha enviado la solicitud de inicio con éxito.";
    $mysqli->close();
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
}

// Redireccionar y finalizar el script actual.
header("Location: ../menu_principal.php");
exit();
