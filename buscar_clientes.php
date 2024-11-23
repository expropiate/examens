<?php
    /*-------------------------
    Autor: Obed Alvarado
    Web: obedalvarado.pw
    Mail: info@obedalvarado.pw
    ---------------------------*/
    include 'is_logged.php'; // Archivo verifica que el usuario que intenta acceder a la URL está logueado

    /* Conexión a la Base de Datos */
    require_once '../config/db.php'; // Contiene las variables de configuración para conectar a la base de datos
    require_once '../config/conexion.php'; // Contiene función que conecta a la base de datos

    $action = (isset($_REQUEST['action']) && $_REQUEST['action'] != null) ? $_REQUEST['action'] : '';
    if (isset($_GET['id'])) {
        $id_cliente = intval($_GET['id']);
        $query = mysqli_query($con, "SELECT * FROM facturas WHERE id_cliente='" . $id_cliente . "'");
        $count = mysqli_num_rows($query);
        if ($count == 0) {
            if ($delete1 = mysqli_query($con, "DELETE FROM clientes WHERE id_cliente='" . $id_cliente . "'")) {
                ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Aviso!</strong> Datos eliminados exitosamente.
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Error!</strong> Lo siento algo ha salido mal intenta nuevamente.
                </div>
                <?php
            }
        } else {
            ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Error!</strong> No se pudo eliminar este cliente. Existen facturas vinculadas a este cliente.
            </div>
            <?php
        }
    }

    if ($action == 'ajax') {
        // escaping, además de eliminar todo lo que podría ser código (html/javascript)
        $q = mysqli_real_escape_string($con, strip_tags($_REQUEST['q'], ENT_QUOTES));
        $aColumns = ['nombre_cliente']; // Columnas de búsqueda
        $sTable = "clientes";
        $sWhere = "";
        if ($_GET['q'] != "") {
            $sWhere = "WHERE (";
            foreach ($aColumns as $columna) {
                $sWhere .= $columna . " LIKE '%" . $q . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }
        $sWhere .= " ORDER BY nombre_cliente";
        include 'pagination.php'; // Archivo de paginación

        // Variables de paginación
        $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
        $per_page = 10; // Registros por página
        $adjacents = 4; // Espacio entre páginas después de los números
        $offset = ($page - 1) * $per_page;

        // Contar total de filas
        $count_query = mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable $sWhere");
        $row = mysqli_fetch_array($count_query);
        $numrows = $row['numrows'];
        $total_pages = ceil($numrows / $per_page);
        $reload = './clientes.php';

        // Consulta principal para obtener los datos
        $sql = "SELECT * FROM $sTable $sWhere LIMIT $offset, $per_page";
        $query = mysqli_query($con, $sql);

        // Mostrar datos obtenidos
        if ($numrows > 0) {
            ?>
            <div class="table-responsive">
                <table class="table">
                    <tr class="info">
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Estado</th>
                        <th>Agregado</th>
                        <th class='text-right'>Acciones</th>
                    </tr>
                    <?php
                    while ($row = mysqli_fetch_array($query)) {
                        $id_cliente = $row['id_cliente'];
                        $nombre_cliente = $row['nombre_cliente'];
                        $telefono_cliente = $row['telefono_cliente'];
                        $email_cliente = $row['email_cliente'];
                        $direccion_cliente = $row['direccion_cliente'];
                        $status_cliente = $row['status_cliente'];
                        $estado = ($status_cliente == 1) ? "Activo" : "Inactivo";
                        $date_added = date('d/m/Y', strtotime($row['date_added']));
                        ?>
                        <input type="hidden" value="<?php echo $nombre_cliente; ?>" id="nombre_cliente<?php echo $id_cliente; ?>">
                        <input type="hidden" value="<?php echo $telefono_cliente; ?>" id="telefono_cliente<?php echo $id_cliente; ?>">
                        <input type="hidden" value="<?php echo $email_cliente; ?>" id="email_cliente<?php echo $id_cliente; ?>">
                        <input type="hidden" value="<?php echo $direccion_cliente; ?>" id="direccion_cliente<?php echo $id_cliente; ?>">
                        <input type="hidden" value="<?php echo $status_cliente; ?>" id="status_cliente<?php echo $id_cliente; ?>">

                        <tr>
                            <td><?php echo $nombre_cliente; ?></td>
                            <td><?php echo $telefono_cliente; ?></td>
                            <td><?php echo $email_cliente; ?></td>
                            <td><?php echo $direccion_cliente; ?></td>
                            <td><?php echo $estado; ?></td>
                            <td><?php echo $date_added; ?></td>
                            <td><span class="pull-right">
                                <a href="#" class='btn btn-default' title='Editar cliente'
                                   onclick="obtener_datos('<?php echo $id_cliente; ?>');" data-toggle="modal"
                                   data-target="#myModal2"><i class="glyphicon glyphicon-edit"></i></a>
                                <a href="#" class='btn btn-default' title='Borrar cliente'
                                   onclick="eliminar('<?php echo $id_cliente; ?>')"><i class="glyphicon glyphicon-trash"></i></a>
                            </span></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan=7><span class="pull-right">
                            <?php
                            echo paginate($reload, $page, $total_pages, $adjacents);
                            ?>
                        </span></td>
                    </tr>
                </table>
            </div>
            <?php
        }
    }
?>
