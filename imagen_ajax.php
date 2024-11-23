<?php
/* Conexión a la base de datos */
require_once "../config/db.php";
require_once "../config/conexion.php";

if (isset($_FILES["imagefile"])) {
    $target_dir = "../img/";
    
    // Sanitizar el nombre de la imagen para evitar vulnerabilidades
    $image_name = time() . "_" . basename($_FILES["imagefile"]["name"]);
    $image_name = preg_replace("/[^a-zA-Z0-9_-]/", "", $image_name); // Eliminar caracteres no deseados
    $target_file = $target_dir . $image_name;
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
    $imageFileSize = $_FILES["imagefile"]["size"];

    /* Validación de archivos */
    if (($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") || $imageFileSize > 1048576) {
        $errors[] = "<p>Lo sentimos, sólo se permiten archivos JPG, JPEG, PNG y GIF de menos de 1MB.</p>";
    } else {
        // Verificar si el archivo es realmente una imagen
        $image_info = getimagesize($_FILES["imagefile"]["tmp_name"]);
        if ($image_info === false) {
            $errors[] = "<p>Lo sentimos, el archivo no es una imagen válida.</p>";
        }
    }

    /* Si no hay errores, procesar la carga del archivo */
    if (empty($errors)) {
        move_uploaded_file($_FILES["imagefile"]["tmp_name"], $target_file);
        
        // Usar sentencia preparada para prevenir inyecciones SQL
        $stmt = $con->prepare("UPDATE perfil SET logo_url = ? WHERE id_perfil = ?");
        $logo_url = "img/$image_name"; // URL del logo
        $id_perfil = 1; // Asumiendo que el id_perfil es estático, sino sanitizarlo también
        $stmt->bind_param("si", $logo_url, $id_perfil);
        $stmt->execute();

        // Verificar si la actualización fue exitosa
        if ($stmt->affected_rows > 0) {
            echo "<img class='img-responsive' src='img/$image_name' alt='Logo'>";
        } else {
            $errors[] = "Lo sentimos, la actualización falló. Intente nuevamente.";
        }
        $stmt->close();
    }
}
?>

<?php 
if (isset($errors)) {
?>
    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error! </strong>
        <?php
        // Mostrar todos los errores acumulados
        foreach ($errors as $error) {
            echo $error;
        }
        ?>
    </div>  
<?php
}
?>
