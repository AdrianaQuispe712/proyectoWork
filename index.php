<?php
// MENÚ PRINCIPAL SEGÚN ROL
require_once 'verifica_login.php';
require_once 'conexion.php';

echo "=== SISTEMA DE WORKFLOW - TIENDA ===\n";
echo "Usuario: " . $_SESSION['nombre'] . " (" . $_SESSION['rol'] . ")\n";
echo "========================================\n\n";

switch($_SESSION['rol']) {
    case 'CLIENTE':
        echo "=== MENÚ CLIENTE ===\n";
        echo "1. <a href='realizar_pedido.php'>Realizar Nuevo Pedido (FLUJO 2)</a>\n";
        echo "2. <a href='ver_mis_pedidos.php'>Ver Mis Pedidos</a>\n";
        echo "3. <a href='recibir_pedido.php'>Confirmar Recepción de Pedido (FLUJO 6)</a>\n";
        break;
        
    case 'ALMACENERO':
        echo "=== MENÚ ALMACENERO ===\n";
        echo "1. <a href='verificar_existencias.php'>Verificar Existencias (FLUJO 3)</a>\n";
        echo "2. <a href='enviar_pedido.php'>Enviar Pedidos (FLUJO 5)</a>\n";
        echo "3. <a href='ver_stock.php'>Ver Stock de Productos</a>\n";
        break;
        
    case 'GERENTE':
        echo "=== MENÚ GERENTE ===\n";
        echo "1. <a href='aprobar_pedidos.php'>Aprobar/Rechazar Pedidos (FLUJO 4)</a>\n";
        echo "2. <a href='reportes.php'>Ver Reportes</a>\n";
        echo "3. <a href='ver_workflow.php'>Ver Flujos de Trabajo</a>\n";
        break;
        
    case 'ENCARGADO_COMPRAS':
        echo "=== MENÚ ENCARGADO DE COMPRAS ===\n";
        echo "1. <a href='crear_orden_compra.php'>Crear Orden de Compra (FLUJO 7)</a>\n";
        echo "2. <a href='recepcion_compra.php'>Recepción de Compra (FLUJO 8)</a>\n";
        echo "3. <a href='ver_ordenes.php'>Ver Órdenes de Compra</a>\n";
        break;
        
    default:
        echo "ERROR: Rol no reconocido\n";
}

echo "\n<br><br>";
echo "<a href='logout.php'>Cerrar Sesión</a>\n";
?>