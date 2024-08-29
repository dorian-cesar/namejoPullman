<?php

function manejo($user,$pasw,$ayer){

  include "conexion.php";

$consulta="SELECT hash FROM masgps.hash where user='$user' and pasw='$pasw'";

$resutaldo= mysqli_query($mysqli,$consulta);

$data=mysqli_fetch_array($resutaldo);

$hash=$data['hash'];

include "listado.php";



// title: Informe de evento
// trackers: [10182708,10196192,10196218,10196242,10196418,10196431,10196471,10196473,10196474,10196487,10196489,10196494,10196521,10196551,10196578,10196581,10196593]
// from: 2023-07-01 00:00:00
// to: 2023-07-31 23:59:59
// time_filter: {"from":"00:00","to":"23:59","weekdays":[1,2,3,4,5,6,7]}
// plugin: {"hide_empty_tabs":true,"plugin_id":11,"show_seconds":false,"group_by_type":false,"event_types":["speedup","harsh_driving"]}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/report/tracker/generate',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'hash='.$hash.'&title=Informe%20de%20evento&trackers='.$ids.'&from='.$ayer.'%2000%3A00%3A00&to='.$ayer.'%2023%3A59%3A59&time_filter=%7B%22from%22%3A%2200%3A00%22%2C%22to%22%3A%2223%3A59%22%2C%22weekdays%22%3A%5B1%2C2%2C3%2C4%2C5%2C6%2C7%5D%7D&plugin=%7B%22hide_empty_tabs%22%3Atrue%2C%22plugin_id%22%3A11%2C%22show_seconds%22%3Afalse%2C%22group_by_type%22%3Afalse%2C%22event_types%22%3A%5B%22speedup%22%2C%22harsh_driving%22%5D%7D',
  CURLOPT_HTTPHEADER => array(
    'Accept: */*',
    'Accept-Language: es-419,es;q=0.9,en;q=0.8',
    'Connection: keep-alive',
    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
    'Cookie: _ga=GA1.2.728367267.1665672802; _gid=GA1.2.2094478458.1691524041; session_key=0546367ce85a765c7e7aff60ecce2cbe; locale=es; _ga_XXFQ02HEZ2=GS1.2.1691604884.15.1.1691605004.0.0.0',
    'Origin: http://www.trackermasgps.com',
    'Referer: http://www.trackermasgps.com/pro/applications/reports/index.html?newuiwrap=1',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

$arreglo=json_decode($response);

$reporte=$arreglo->id;



Loop :

sleep(10);

extraer :

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://www.trackermasgps.com/api-v2/report/tracker/retrieve',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'hash='.$hash.'&report_id='.$reporte,
  CURLOPT_HTTPHEADER => array(
    'Accept: */*',
    'Accept-Language: es-419,es;q=0.9,en;q=0.8',
    'Connection: keep-alive',
    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
    'Cookie: _ga=GA1.2.728367267.1665672802; _gid=GA1.2.183718605.1679328823; locale=es; session_key=cf290712c61924284913e1af01cfaded; check_audit=cf290712c61924284913e1af01cfaded; date_format=m-d-Y; date_format_moment=MM-DD-YYYY',
    'Origin: http://www.trackermasgps.com',
    'Referer: http://www.trackermasgps.com/pro/applications/reports/index.html?newuiwrap=1',
    'User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

$datos=json_decode($response);

if(isset($datos->report->sheets)){

    $sheets=$datos->report->sheets;

    foreach ($sheets as $sheet){

        $patente=$sheet->header ; 
        $id_tracker=$sheet->entity_ids[0];
 

        foreach ($sheet->sections[0]->data[0]->rows as $row){
            
           
           $evento=$row->event->v ;
         
           $time=$row->time->v;

           $nuevaFecha = date('Y-m-d H:i', strtotime($time));

           $lat=$row->address->location->lat;
           $lng=$row->address->location->lng;
          
          
           $Qry="INSERT INTO `masgps`.`manejoBrusco2` (`cuenta`, `id_tracker`, `patente`, `date`, `time`, `lat`, `lng`, `evento`) 
           VALUES ('$user', '$id_tracker', '$patente', '$ayer', '$nuevaFecha', '$lat', '$lng','$evento');
           ";
          
           $ejecutar = mysqli_query($mysqli, $Qry);


        }

    }



}else{
    echo "$response";
    goto Loop ;
}


}