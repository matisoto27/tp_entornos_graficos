<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['alumnos', 'profesores'])) {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['dni']) || empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['fecha-nacimiento']) || empty($_POST['email']) || empty($_POST['repetir-email'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: signup.php");
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

    // Recuperar datos del formulario.
    $dni = trim($_POST['dni']);
    $nombre = ucwords(quitarTildes(strtolower(trim($_POST['nombre']))));
    $apellido = ucwords(quitarTildes(strtolower(trim($_POST['apellido']))));
    $fecha_nacimiento = $_POST['fecha-nacimiento'];
    $email = quitarTildes(strtolower(trim($_POST['email'])));
    $repetir_email = quitarTildes(strtolower(trim($_POST['repetir-email'])));
    $rol = $_SESSION['rol'];

    // Validar que el DNI tenga 8 dígitos.
    if (!preg_match('/^\d{8}$/', $dni)) {
        $_SESSION['mensaje_error'] = "El DNI debe tener 8 dígitos.";
        header("Location: signup.php");
        exit();
    }

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Validar que no exista un usuario con el DNI proporcionado.
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['total'] > 0) {
        $_SESSION['mensaje_error'] = "Ya existe un usuario con el DNI proporcionado.";
        header("Location: signup.php");
        exit();
    }

    // Validar que el nombre solo contenga letras.
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $nombre)) {
        $_SESSION['mensaje_error'] = "El nombre solo puede contener letras.";
        header("Location: signup.php");
        exit();
    }

    // Validar que el apellido solo contenga letras.
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $apellido)) {
        $_SESSION['mensaje_error'] = "El apellido solo puede contener letras.";
        header("Location: signup.php");
        exit();
    }

    // Validar que sea mayor de 18 años.
    $fecha_actual = new DateTime();
    $edad = $fecha_actual->diff(new DateTime($fecha_nacimiento))->y;
    if ($edad < 18) {
        $_SESSION['mensaje_error'] = "Debes ser mayor de 18 años para registrarte.";
        header("Location: signup.php");
        exit();
    }

    // Validar que los correos electrónicos coincidan.
    if ($email !== $repetir_email) {
        $_SESSION['mensaje_error'] = "Los correos electrónicos no coinciden.";
        header("Location: signup.php");
        exit();
    }

    // Validar formato del correo electrónico.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensaje_error'] = "El correo electrónico no tiene un formato válido.";
        header("Location: signup.php");
        exit();
    }

    if ($_SESSION['rol'] === 'alumnos') {

        // Establecer variables para la consulta.
        $contrasena = '12341234';

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO usuarios (dni, nombre, apellido, fecha_nacimiento, email, rol, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $dni, $nombre, $apellido, $fecha_nacimiento, $email, $rol, $contrasena);
        $stmt->execute();
        $stmt = $mysqli->prepare("INSERT INTO alumnos (dni) VALUES (?)");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $_SESSION['mensaje_exito'] = "¡Te has registrado con éxito! Hemos enviado a tu correo electrónico la contraseña que debes utilizar para iniciar sesión.";
    } else {

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO usuarios (dni, nombre, apellido, fecha_nacimiento, email, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $dni, $nombre, $apellido, $fecha_nacimiento, $email, $rol);
        $stmt->execute();
        $_SESSION['mensaje_exito'] = "¡Gracias por registrarte! Nuestro equipo administrativo responsable de las PPS verificará la información proporcionada. Luego, enviará a tu correo electrónico la contraseña que debes utilizar para iniciar sesión.";
    }

    // Cerrar la conexión a la base de datos y redireccionar.
    $mysqli->close();
    header("Location: login.php");
    exit();
} else {

    // Establecer mensaje de error y redireccionar.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: signup.php");
    exit();
}
