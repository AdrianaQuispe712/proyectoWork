<?php
// FLUJO 8: RECEPCIÓN DE COMPRA
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['ENCARGADO_COMPRAS']);

echo "=== FLUJO 8: RECEPCIÓN DE COMPRA ===\n";
echo "Encargado: " . $_SESSION['nombre'] . "\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orden_id = $_POST['orden_id'];
    $productos = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];
    
    try {
        // Actualizar stock de productos
        foreach($productos as $index => $producto_id) {
            $cantidad = $cantidades[$index];
            
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$cantidad, $producto_id]);
            
            echo "✓ Stock actualizado: Producto ID $producto_id + $cantidad unidades<br>\n";
        }
        
        // Marcar orden como completada
        $stmt = $pdo->prepare("UPDATE ordenes_compra SET estado = 'COMPLETADA' WHERE id = ?");
        $stmt->execute([$orden_id]);
        
        echo "✓ COMPRA RECIBIDA Y PROCESADA\n";
        echo "Orden $orden_id completada. Stock actualizado.\n";
        
    } catch(PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// Mostrar órdenes pendientes
$stmt = $pdo->query("
    SELECT id, proveedor, fecha, estado
    FROM ordenes_compra 
    WHERE estado = 'PENDIENTE'
    ORDER BY fecha
");
$ordenes = $stmt->fetchAll();

if (empty($ordenes)) {
    echo "No hay órdenes de compra pendientes\n";
} else {
    echo "<h3>Órdenes Pendientes de Recepción:</h3>\n";
    
    foreach($ordenes as $orden) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
        echo "<strong>Orden #{$orden['id']}</strong><br>\n";
        echo "Proveedor: {$orden['proveedor']}<br>\n";
        echo "Fecha: {$orden['fecha']}<br>\n";
        
        echo "<form method='POST'>\n";
        echo "<input type='hidden' name='orden_id' value='{$orden['id']}'>\n";
        echo "<h4>Productos Recibidos:</h4>\n";
        
        // Mostrar productos para actualizar stock
        $stmt = $pdo->query("SELECT id, nombre, stock FROM productos WHERE activo = true");
        $productos = $stmt->fetchAll();
        
        foreach($productos as $producto) {
            echo "<label>\n";
            echo "<input type='checkbox' name='productos[]' value='{$producto['id']}'>\n";
            echo "{$producto['nombre']} (Stock actual: {$producto['stock']})\n";
            echo "Cantidad recibida: <input type='number' name='cantidades[]' min='0' value='0'>\n";
            echo "</label><br>\n";
        }
        
        echo "<input type='submit' value='Procesar Recepción' style='background: green; color: white;'>\n";
        echo "</form>\n";
        echo "</div>\n";
    }
}

echo "<br><a href='index.php'>Volver al menú</a>\n";
?>