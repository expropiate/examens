<?php
	include 'is_logged.php'; // Eliminar paréntesis innecesarios en la llamada a include

	/* Inicia validación del lado del servidor */
	if (empty($_POST['mod_id'])) {
        $errors[] = "ID vacío";
    } else if (empty($_POST['mod_codigo'])) {
        $errors[] = "Código vacío";
    } else if (empty($_POST['mod_nombre'])) {
        $errors[] = "Nombre del producto vacío";
    } else if ($_POST['mod_estado'] == "") {
        $errors[] = "Selecciona el estado del producto";
    } else if (empty($_POST['mod_precio'])) {
        $errors[] = "Precio de venta vacío";
    } else if (
        !empty($_POST['mod_id']) &&
        !empty($_POST['mod_codigo']) &&
        !empty($_POST['mod_nombre']) &&
        $_POST['mod_estado'] != "" &&
        !empty($_POST['mod_precio'])
    ) {
        /* Connect To Database */
        require_once "../config/db.php"; // Eliminar paréntesis innecesarios en la llamada a require_once
        require_once "../config/conexion.php"; // Eliminar paréntesis innecesarios en la llamada a require_once

        // Escapando y sanitizando los datos de entrada para evitar inyecciones SQL
        $codigo = mysqli_real_escape_string($con, (strip_tags($_POST["mod_codigo"], ENT_QUOTES)));
        $nombre = mysqli_real_escape_string($con, (strip_tags($_POST["mod_nombre"], ENT_QUOTES)));
        $estado = intval($_POST['mod_estado']);
        $precio_venta = floatval($_POST['mod_precio']);
        $id_producto = $_POST['mod_id'];

        // Usar una consulta preparada para evitar SQL injection
        $sql = "UPDATE products SET codigo_producto=?, nombre_producto=?, status_producto=?, precio_producto=? WHERE id_producto=?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("ssdis", $codigo, $nombre, $estado, $precio_venta, $id_producto); // Vincula las variables a los parámetros
        $query_update = $stmt->execute(); // Ejecuta la consulta preparada

        if ($query_update) {
            $messages[] = "Producto ha sido actualizado satisfactoriamente.";
        } else {
            $errors[] = "Lo siento, algo ha salido mal. Intenta nuevamente." . mysqli_error($con);
        }
    } else {
        $errors[] = "Error desconocido.";
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
