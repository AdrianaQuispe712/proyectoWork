<?php
// CERRAR SESIÓN
session_start();
session_destroy();

echo "=== SESIÓN CERRADA ===\n";
echo "Has cerrado sesión exitosamente\n";
echo "<a href='login.php'>Iniciar Sesión Nuevamente</a>\n";
?>