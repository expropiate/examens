<?php
    include 'is_logged.php'; //Archivo verifica que el usuario que intenta acceder a la URL está logueado

    /* Inicia validación del lado del servidor */
    if (empty($_POST['nombre'])) {
        $errors[] = "Nombre vacío";
    } else if (!empty($_POST['nombre'])) {
        /* Connect To Database */
        require_once "../config/db.php"; // Contiene las variables de configuración para conectar a la base de datos
        require_once "../config/conexion.php"; // Contiene función que conecta a la base de datos
        
        // escaping, adicionalmente eliminando todo lo que podría ser código (html/javascript)
        $nombre = mysqli_real_escape_string($con, (strip_tags($_POST["nombre"], ENT_QUOTES)));
        $telefono = mysqli_real_escape_string($con, (strip_tags($_POST["telefono"], ENT_QUOTES)));
        $email = mysqli_real_escape_string($con, (strip_tags($_POST["email"], ENT_QUOTES)));
        $direccion = mysqli_real_escape_string($con, (strip_tags($_POST["direccion"], ENT_QUOTES)));
        $estado = intval($_POST['estado']);
        $date_added = date("Y-m-d H:i:s");
        
        $sql = "INSERT INTO clientes (nombre_cliente, telefono_cliente, email_cliente, direccion_cliente, status_cliente, date_added) 
                VALUES ('$nombre','$telefono','$email','$direccion','$estado','$date_added')";
        
        $query_new_insert = mysqli_query($con, $sql);
        
        if ($query_new_insert) {
            $messages[] = "Cliente ha sido ingresado satisfactoriamente.";
        } else {
            $errors[] = "Lo siento algo ha salido mal, intenta nuevamente." . mysqli_error($con);
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
