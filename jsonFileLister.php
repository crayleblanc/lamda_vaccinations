var jsonFiles = <?php $out = array();
//this program will list out the names of the json files within the json_vaccine_files folder and store them as a javascript array
foreach (glob('json_vaccine_files/*.json') as $filename) {
    $p = pathinfo($filename);
    $out[] = $p['filename'];
}
echo json_encode($out); ?>;
