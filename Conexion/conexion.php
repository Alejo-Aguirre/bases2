<?php  
// crear conexion con oracle
$conexion = oci_connect("ALEJO", "root", "localhost/xe"); 
 
if (!$conexion) {    
    $m = oci_error();    
    echo $m['message'], "n";    
    exit; 
} else {    
    echo "Conexión con éxito a Oracle!"; 
}

$query = "SELECT * FROM Profesores WHERE ROWNUM <= 5"; // Cambiado a 5 para obtener más resultados
$stmt = oci_parse($conexion, $query);
oci_execute($stmt);

echo "<h2>Profesores Consultados</h2>";
echo "<table border='1'>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Apellido</th>
<th>Email</th>
<th>Usuario</th>
<th>Contraseña</th> <!-- Suponiendo que 'CONTRA' es la columna para la contraseña -->
</tr>";

while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
    echo "<tr>";
    echo "<td>" . (isset($row['ID']) ? $row['ID'] : "") . "</td>";
    echo "<td>" . (isset($row['NOMBRE']) ? $row['NOMBRE'] : "") . "</td>";
    echo "<td>" . (isset($row['APELLIDO']) ? $row['APELLIDO'] : "") . "</td>";
    echo "<td>" . (isset($row['EMAIL']) ? $row['EMAIL'] : "") . "</td>";
    echo "<td>" . (isset($row['USUARIO']) ? $row['USUARIO'] : "") . "</td>";
    echo "<td>" . (isset($row['CONTRA']) ? $row['CONTRA'] : "") . "</td>"; // Cambiado a 'CONTRA' si es la columna para la contraseña
    echo "</tr>";
}

echo "</table>";

?>
