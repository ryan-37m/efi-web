<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\gnn;
use \efi\gnt\bigscape_job;
use \efi\gnt\gnd_v2;
use \efi\gnt\job_factory;
use \efi\training\example_config;
use \efi\gnt\gnn_example;
use \efi\send_file;


// This is necessary so that the gnd class environment doesn't get clusttered
// with the dependencies that gnn, etc. need.
class gnd_job_factory extends job_factory {
    function __construct($is_example = false) { $this->is_example = $is_example; }
    public function new_gnn($db, $id) { return $this->is_example !== false ? new \efi\gnt\gnn_example($db, $id, $this->is_example) : new gnn($db, $id); }
    public function new_gnn_bigscape_job($db, $id) { return new \efi\gnt\bigscape_job($db, $id, DiagramJob::GNN); }
    public function new_uploaded_bigscape_job($db, $id) { return new \efi\gnt\bigscape_job($db, $id, DiagramJob::Uploaded); }
    public function new_diagram_data_file($db, $id) { return new \efi\gnt\diagram_data_file($db, $id); }
    public function new_direct_gnd_file($file) { return new \efi\gnt\direct_gnd_file($file); }
}

function is_cli() {
    if (php_sapi_name() == "cli" &&
        defined("STDIN") &&
        (!isset($_SERVER["HTTP_HOST"]) || empty($_SERVER["HTTP_HOST"])) &&
        (!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADDR'])) &&
        (!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT'])) &&
        count($_SERVER['argv']) > 0)
    {
        return true;
    } else {
        return false;
    }
}

// If this is being run from the command line then we parse the command line parameters and put them into _POST so we can use
// that below.
if (is_cli()) {
    parse_str($argv[1], $_GET);
    if (isset($argv[2]) && file_exists($argv[2])) {
        $_GET['console-run-file'] = $argv[2];
    }
}


$PARAMS = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$is_example = example_config::is_example($PARAMS);


$gnd = new gnd_v2($db, $PARAMS, new gnd_job_factory($is_example), $is_example);


if ($gnd->parse_error()) {
    $output = $gnd->create_error_output($gnd->get_error_message());
    die("Invalid  input");
}

$data = $gnd->get_arrow_data();

$output = "Genome\tID\tStart\tStop\tSize (nt)\tStrand\tFunction\tFC\tSS\tSet\n";
$add_ipro = true;
//$add_ipro = is_cli();
foreach ($data["data"] as $row) {
    $A = $row["attributes"];
    $org = $A["organism"];
    $num = $A["num"];
    $query_processed = false;
    foreach ($row["neighbors"] as $N) {
        if ($N["num"] > $num && !$query_processed) {
            $query_processed = true;
            $output .= get_line($org, $A, $add_ipro);
        }
        $output .= get_line($org, $N, $add_ipro);
    }
}


$gnn_name = $gnd->get_job_name();

$file_name = "${gnn_name}_gene_graphics.tsv";
send_file::send_text($output, $file_name);



function get_line($organism, $data, $add_ipro = false) {
    if (!isset($data["accession"])) {
        return "";
    }
    
    $family = implode("; ", $data["family_desc"]);
    if (!$family)
        $family = "none";
    $ipro = "";
    if ($add_ipro && is_array($data["ipro_family"])) {
        $ipro = implode("; ", preg_grep("/^(?!none)/", $data["ipro_family"]));
        if ($ipro)
            $ipro = "; InterPro=$ipro";
    }

    $line = $organism;
    $line .= "\t" . $data["accession"];
    $line .= "\t" . round($data["start"] / 3);
    $line .= "\t" . round($data["stop"] / 3);
    $line .= "\t" . $data["seq_len"];
    $line .= "\t" . ($data["direction"] == "complement" ? "-" : "+");
    $line .= "\t" . $family . $ipro;
    $line .= "\t" . "";
    $line .= "\t" . "";
    $line .= "\t" . "";
    $line .= "\n";
    return $line;
}





