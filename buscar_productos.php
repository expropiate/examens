<?php
    /*-------------------------
    Autor: Obed Alvarado
    Web: obedalvarado.pw
    Mail: info@obedalvarado.pw
    ---------------------------*/
    include 'is_logged.php'; // Archivo verifica que el usuario que intenta acceder a la URL está logueado

    /* Connect To Database */
    require_once "../config/db.php"; // Contiene las variables de configuración para conectar a la base de datos
    require_once "../config/conexion.php"; // Contiene función que conecta a la base de datos

    // Archivo de funciones PHP
    include "../funciones.php";

    $action = (isset($_REQUEST['action']) && $_REQUEST['action'] != NULL) ? $_REQUEST['action'] : '';

    if (isset($_GET['id'])) {
        $id_producto = intval($_GET['id']);
        $query = mysqli_query($con, "SELECT * FROM detalle_factura WHERE id_producto='" . $id_producto . "'");
        $count = mysqli_num_rows($query);

        if ($count == 0) {
            if ($delete1 = mysqli_query($con, "DELETE FROM products WHERE id_producto='" . $id_producto . "'")) {
                ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Aviso!</strong> Datos eliminados exitosamente.
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>Error!</strong> Lo siento algo ha salido mal, intenta nuevamente.
                </div>
                <?php
            }
        } else {
            ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <strong>Error!</strong> No se pudo eliminar este producto. Existen cotizaciones vinculadas a este producto. 
            </div>
            <?php
        }
    }

    if ($action == 'ajax') {
        // Escaping, además de eliminar cualquier código (html/javascript)
        $q = mysqli_real_escape_string($con, strip_tags($_REQUEST['q'], ENT_QUOTES));
        $aColumns = array('codigo_producto', 'nombre_producto'); // Columnas de búsqueda
        $sTable = "products";
        $sWhere = "";

        if ($_GET['q'] != "") {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $sWhere .= $aColumns[$i] . " LIKE '%" . $q . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }
        $sWhere .= " ORDER BY id_producto DESC";

        include 'pagination.php'; // Incluir archivo de paginación

        // Variables de paginación
        $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
        $per_page = 10; // Cantidad de registros por página
        $adjacents = 4; // Espacio entre páginas adyacentes
        $offset = ($page - 1) * $per_page;

        // Contar el número total de filas en la tabla
        $count_query = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
        $row = mysqli_fetch_array($count_query);
        $numrows = $row['numrows'];
        $total_pages = ceil($numrows / $per_page);
        $reload = './productos.php';

        // Consulta principal para obtener los datos
        $sql = "SELECT * FROM $sTable $sWhere LIMIT $offset,$per_page";
        $query = mysqli_query($con, $sql);

        // Iterar sobre los datos obtenidos
        if ($numrows > 0) {
            $simbolo_moneda = get_row('perfil', 'moneda', 'id_perfil', 1);
            ?>
            <div class="table-responsive">
                <table class="table">
                    <tr class="info">
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Estado</th>
                        <th>Agregado</th>
                        <th class='text-right'>Precio</th>
                        <th class='text-right'>Acciones</th>
                    </tr>
                    <?php
                    while ($row = mysqli_fetch_array($query)) {
                        $id_producto = $row['id_producto'];
                        $codigo_producto = $row['codigo_producto'];
                        $nombre_producto = $row['nombre_producto'];
                        $status_producto = $row['status_producto'];
                        $estado = ($status_producto == 1) ? "Activo" : "Inactivo";
                        $date_added = date('d/m/Y', strtotime($row['date_added']));
                        $precio_producto = $row['precio_producto'];
                        ?>
                        <input type="hidden" value="<?php echo $codigo_producto; ?>" id="codigo_producto<?php echo $id_producto; ?>">
                        <input type="hidden" value="<?php echo $nombre_producto; ?>" id="nombre_producto<?php echo $id_producto; ?>">
                        <input type="hidden" value="<?php echo $estado; ?>" id="estado<?php echo $id_producto; ?>">
                        <input type="hidden" value="<?php echo number_format($precio_producto, 2, '.', ''); ?>" id="precio_producto<?php echo $id_producto; ?>">
                        <tr>
                            <td><?php echo $codigo_producto; ?></td>
                            <td><?php echo $nombre_producto; ?></td>
                            <td><?php echo $estado; ?></td>
                            <td><?php echo $date_added; ?></td>
                            <td><?php echo $simbolo_moneda; ?><span class='pull-right'><?php echo number_format($precio_producto, 2); ?></span></td>
                            <td><span class="pull-right">
                                <a href="#" class='btn btn-default' title='Editar producto' onclick="obtener_datos('<?php echo $id_producto; ?>');" data-toggle="modal" data-target="#myModal2"><i class="glyphicon glyphicon-edit"></i></a> 
                                <a href="#" class='btn btn-default' title='Borrar producto' onclick="eliminar('<?php echo $id_producto; ?>')"><i class="glyphicon glyphicon-trash"></i></a>
                                </span>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan=6><span class="pull-right">
                        <?php echo paginate($reload, $page, $total_pages, $adjacents); ?>
                        </span></td>
                    </tr>
                </table>
            </div>
            <?php
        }
    }
?>
