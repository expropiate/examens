<?php
include 'is_logged.php'; // Archivo verifica que el usuario que intenta acceder a la URL esté logueado

// Checking for minimum PHP version
if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");
} elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
    // If you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
    require_once "../libraries/password_compatibility_library.php";
}

if (empty($_POST['user_id_mod'])) {
    $errors[] = "ID vacío";
} elseif (empty($_POST['user_password_new3']) || empty($_POST['user_password_repeat3'])) {
    $errors[] = "La contraseña no puede estar vacía";
} elseif ($_POST['user_password_new3'] !== $_POST['user_password_repeat3']) {
    $errors[] = "La contraseña y la repetición de la contraseña no son lo mismo";
} elseif (
    !empty($_POST['user_id_mod']) &&
    !empty($_POST['user_password_new3']) &&
    !empty($_POST['user_password_repeat3']) &&
    ($_POST['user_password_new3'] === $_POST['user_password_repeat3'])
) {
    require_once "../config/db.php"; // Contiene las variables de configuración para conectar a la base de datos
    require_once "../config/conexion.php"; // Contiene la función que conecta a la base de datos

    $user_id = intval($_POST['user_id_mod']);
    $user_password = $_POST['user_password_new3'];

    // Crypt the user's password with PHP 5.5's password_hash() function
    $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

    // Prepare the SQL query to avoid SQL injection
    $stmt = $con->prepare("UPDATE users SET user_password_hash = ? WHERE user_id = ?");
    $stmt->bind_param("si", $user_password_hash, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $messages[] = "La contraseña se ha actualizado correctamente.";
    } else {
        $errors[] = "Hubo un error al actualizar la contraseña. Por favor, intente nuevamente.";
    }
} else {
    $errors[] = "Un error desconocido ocurrió.";
}

if (isset($errors)) {
    ?>
    <div class="alert alert-danger" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error!</strong> 
        <?php
        foreach ($errors as $error) {
            echo $error;
        }
        ?>
    </div>
    <?php
}

if (isset($messages)) {
    ?>
    <div class="alert alert-success" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>¡Bien hecho!</strong>
        <?php
        foreach ($messages as $message) {
            echo $message;
        }
        ?>
    </div>
    <?php
}
?>
