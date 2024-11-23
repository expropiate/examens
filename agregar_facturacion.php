<?php
/*-------------------------
Autor: Obed Alvarado
Web: obedalvarado.pw
Mail: info@obedalvarado.pw
---------------------------*/

include 'is_logged.php'; // Archivo verifica que el usuario que intenta acceder a la URL está logueado

$session_id = session_id();
$id = $_POST['id'] ?? null;
$cantidad = $_POST['cantidad'] ?? null;
$precio_venta = $_POST['precio_venta'] ?? null;

/* Conexión a la base de datos */
require_once "../config/db.php"; // Contiene las variables de configuración para conectar a la base de datos
require_once "../config/conexion.php"; // Contiene función que conecta a la base de datos
include "../funciones.php"; // Archivo de funciones PHP

if (!empty($id) && !empty($cantidad) && !empty($precio_venta)) {
    $stmt = $con->prepare("INSERT INTO tmp (id_producto, cantidad_tmp, precio_tmp, session_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $id, $cantidad, $precio_venta, $session_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['id'])) { // Código elimina un elemento del array
    $id_tmp = intval($_GET['id']);
    $stmt = $con->prepare("DELETE FROM tmp WHERE id_tmp = ?");
    $stmt->bind_param("i", $id_tmp);
    $stmt->execute();
    $stmt->close();
}

$simbolo_moneda = get_row('perfil', 'moneda', 'id_perfil', 1);
?>
<table class="table">
<tr>
    <th class='text-center'>CODIGO</th>
    <th class='text-center'>CANT.</th>
    <th>DESCRIPCION</th>
    <th class='text-right'>PRECIO UNIT.</th>
    <th class='text-right'>PRECIO TOTAL</th>
    <th></th>
</tr>
<?php
$sumador_total = 0;
$sql = $con->prepare("SELECT * FROM products, tmp WHERE products.id_producto = tmp.id_producto AND tmp.session_id = ?");
$sql->bind_param("s", $session_id);
$sql->execute();
$result = $sql->get_result();

while ($row = $result->fetch_assoc()) {
    $id_tmp = $row["id_tmp"];
    $codigo_producto = $row['codigo_producto'];
    $cantidad = $row['cantidad_tmp'];
    $nombre_producto = $row['nombre_producto'];
    $precio_venta = $row['precio_tmp'];

    $precio_venta_f = number_format($precio_venta, 2); // Formateo variables
    $precio_total = $precio_venta * $cantidad;
    $precio_total_f = number_format($precio_total, 2); // Precio total formateado
    $sumador_total += $precio_total; // Sumador
    ?>
    <tr>
        <td class='text-center'><?php echo htmlspecialchars($codigo_producto); ?></td>
        <td class='text-center'><?php echo htmlspecialchars($cantidad); ?></td>
        <td><?php echo htmlspecialchars($nombre_producto); ?></td>
        <td class='text-right'><?php echo $precio_venta_f; ?></td>
        <td class='text-right'><?php echo $precio_total_f; ?></td>
        <td class='text-center'><a href="#" onclick="eliminar('<?php echo $id_tmp ?>')"><i class="glyphicon glyphicon-trash"></i></a></td>
    </tr>
    <?php
}
$sql->close();

$impuesto = get_row('perfil', 'impuesto', 'id_perfil', 1);
$subtotal = number_format($sumador_total, 2, '.', '');
$total_iva = ($subtotal * $impuesto) / 100;
$total_iva = number_format($total_iva, 2, '.', '');
$total_factura = $subtotal + $total_iva;
?>
<tr>
    <td class='text-right' colspan=4>SUBTOTAL <?php echo $simbolo_moneda; ?></td>
    <td class='text-right'><?php echo number_format($subtotal, 2); ?></td>
    <td></td>
</tr>
<tr>
    <td class='text-right' colspan=4>IVA (<?php echo $impuesto; ?>)% <?php echo $simbolo_moneda; ?></td>
    <td class='text-right'><?php echo number_format($total_iva, 2); ?></td>
    <td></td>
</tr>
<tr>
    <td class='text-right' colspan=4>TOTAL <?php echo $simbolo_moneda; ?></td>
    <td class='text-right'><?php echo number_format($total_factura, 2); ?></td>
    <td></td>
</tr>
</table>
