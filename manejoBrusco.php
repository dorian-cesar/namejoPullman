<?php
set_time_limit(1200);
function manejo($user, $pasw, $ayer)
{
  include __DIR__."conexion.php";

  // Preparar la consulta SQL con parámetros
  $stmt = $mysqli->prepare("SELECT hash FROM masgps.hash WHERE user=? AND pasw=?");
  $stmt->bind_param("ss", $user, $pasw);
  $stmt->execute();
  $resultado = $stmt->get_result();
  $data = $resultado->fetch_assoc();
  $hash = $data['hash'];
  $stmt->close();

  include __DIR__."listado.php";

  // Inicializar cURL
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/report/tracker/generate',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => http_build_query(array(
      'hash' => $hash,
      'title' => 'Informe de evento',
      'trackers' => $ids,
      'from' => "$ayer 00:00:00",
      'to' => "$ayer 23:59:59",
      'time_filter' => json_encode(array(
        'from' => '00:00',
        'to' => '23:59',
        'weekdays' => [1, 2, 3, 4, 5, 6, 7]
      )),
      'plugin' => json_encode(array(
        'hide_empty_tabs' => true,
        'plugin_id' => 11,
        'show_seconds' => false,
        'group_by_type' => false,
        'event_types' => ['speedup', 'harsh_driving']
      ))
    )),
    CURLOPT_HTTPHEADER => array(
      'Accept: */*',
      'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
      'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36'
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);

  $arreglo = json_decode($response);
  $reporte = $arreglo->id;

  do {
    sleep(10);

    // Inicializar cURL para la recuperación del reporte
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/report/tracker/retrieve',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => http_build_query(array(
        'hash' => $hash,
        'report_id' => $reporte
      )),
      CURLOPT_HTTPHEADER => array(
        'Accept: */*',
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36'
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $datos = json_decode($response);
  } while (!isset($datos->report->sheets));

  $sheets = $datos->report->sheets;
  $insertValues = [];
  $insertCount = 0;

  foreach ($sheets as $sheet) {
    $patente = $sheet->header;
    $id_tracker = $sheet->entity_ids[0];

    foreach ($sheet->sections[0]->data[0]->rows as $row) {
      $evento = $row->event->v;
      $fecha_original = $row->time->v;


      // Eliminar el espacio inicial si existe
      $fecha_original = trim($fecha_original);

      // Crear un objeto DateTime a partir de la fecha y hora
      $fecha_formateada = DateTime::createFromFormat('d/m/Y H:i', $fecha_original);

      // Formatear la fecha y hora al formato deseado
      $fecha_final = $fecha_formateada->format('Y-m-d H:i');




      $lat = $row->address->location->lat;
      $lng = $row->address->location->lng;

      $insertValues[] = "('$user', '$id_tracker', '$patente', '$ayer', '$fecha_final', '$lat', '$lng', '$evento')";
      $insertCount++;

      if ($insertCount >= 50) {
        $query = "INSERT INTO `masgps`.`manejoBrusco2` (`cuenta`, `id_tracker`, `patente`, `date`, `time`, `lat`, `lng`, `evento`) 
               VALUES " . implode(', ', $insertValues);
        mysqli_query($mysqli, $query);

        $insertValues = [];
        $insertCount = 0;
      }
    }
  }

  if ($insertCount > 0) {
    $query = "INSERT INTO `masgps`.`manejoBrusco2` (`cuenta`, `id_tracker`, `patente`, `date`, `time`, `lat`, `lng`, `evento`) 
      VALUES " . implode(', ', $insertValues);
    mysqli_query($mysqli, $query);
  }
  echo "ok";
}
