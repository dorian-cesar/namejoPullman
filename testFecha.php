<?php
$fecha_original = '27/08/2024 23:07';

// Crear un objeto DateTime a partir de la fecha y hora
$fecha_formateada = DateTime::createFromFormat('d/m/Y H:i', $fecha_original);

// Formatear la fecha y hora al formato deseado
$fecha_final = $fecha_formateada->format('Y-m-d H:i');

echo $fecha_final; // Imprimirá: 2024-08-27 23:07
