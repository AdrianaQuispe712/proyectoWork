<?php
// FLUJO 2: CLIENTE REALIZA PEDIDO
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['CLIENTE']);

echo "=== FLUJO 2: REALIZAR PEDIDO ===\n";
echo "Cliente: " . $_SESSION['nombre'] . "\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productos_pedido = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];
    
    if (empty($productos_pedido)) {
        echo "ERROR: Debes seleccionar al menos un producto\n";
        exit;
    }
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Crear pedido
        $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, estado) VALUES (?, 'PENDIENTE') RETURNING id");
        $stmt->execute([$_SESSION['usuario_id']]);
        $pedido_id = $stmt->fetch()['id'];
        
        $total = 0;
        
        // Agregar productos al pedido
        foreach($productos_pedido as $index => $producto_id) {
            $cantidad = $cantidades[$index];
            
            // Obtener precio del producto
            $stmt = $pdo->prepare("SELECT precio FROM productos WHERE id = ?");
            $stmt->execute([$producto_id]);
            $precio = $stmt->fetch()['precio'];
            
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
            
            // Insertar detalle
            $stmt = $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$pedido_id, $producto_id, $cantidad, $precio, $subtotal]);
        }
        
        // Actualizar total del pedido
        $stmt = $pdo->prepare("UPDATE pedidos SET total = ? WHERE id = ?");
        $stmt->execute([$total, $pedido_id]);
        
        // Registrar en workflow
        registrar_workflow($pedido_id, 2, 'PEDIDO_CREADO', 'Cliente realizó pedido por $' . $total);
        
        $pdo->commit();
        
        echo "✓ PEDIDO CREADO EXITOSAMENTE\n";
        echo "Número de Pedido: $pedido_id\n";
        echo "Total: $" . number_format($total, 2) . "\n";
        echo "Estado: PENDIENTE\n\n";
        echo "El pedido pasará al FLUJO 3 (Verificación de Existencias)\n";
        echo "<a href='index.php'>Volver al menú</a>\n";
        
    } catch(PDOException $e) {
        $pdo->rollback();
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
} else {
    // Mostrar formulario
    $stmt = $pdo->query("SELECT id, nombre, precio, stock FROM productos WHERE activo = true");
    $productos = $stmt->fetchAll();
    
    echo "<form method='POST'>\n";
    echo "<h3>Seleccionar Productos:</h3>\n";
    
    foreach($productos as $producto) {
        echo "<label>\n";
        echo "<input type='checkbox' name='productos[]' value='{$producto['id']}'>\n";
        echo "{$producto['nombre']} - \${$producto['precio']} (Stock: {$producto['stock']})\n";
        echo "Cantidad: <input type='number' name='cantidades[]' min='1' max='{$producto['stock']}' value='1'>\n";
        echo "</label><br><br>\n";
    }
    
    echo "<input type='submit' value='Crear Pedido'>\n";
    echo "</form>\n";
    echo "<a href='index.php'>Cancelar</a>\n";
}
?>