<?php
namespace efi\cgfp;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\global_settings;
use \efi\cgfp\functions;
use \efi\cgfp\settings;
use \efi\file_types;


abstract class cgfp_shared {

    const DEFAULT_DIAMOND_SENSITIVITY = "sensitive";
    const DEFAULT_CDHIT_SID = "85";
    const REFDB_UNIPROT = "uniprot";
    const REFDB_UNIREF90 = "uniref90";
    const REFDB_UNIREF50 = "uniref50";
    const DEFAULT_REFDB = "uniprot";

    const Quantify = 1;
    const Identify = 2;

    private $id;
    private $identify_id; // Same as $id for identify jobs
    private $pbs_number;
    private $key;
    private $status;
    private $email;
    private $parent_id = 0;
    private $search_type = "";
    private $filename = "";
    private $min_seq_len = "";
    private $max_seq_len = "";
    private $use_prefix = true;

    private $db;
    private $beta;
    protected $is_debug = false;


    // These are to allow us to determine where to get the files from if the job is a quantify job.
    private $itypes = array(
        file_types::FT_sb_cdhit => 1,
        file_types::FT_sb_markers => 1,
        file_types::FT_sb_meta_cluster_sizes => 1,
        file_types::FT_sb_meta_sp_clusters => 1,
        file_types::FT_sb_meta_cp_sing => 1,
        file_types::FT_sb_ssn => 1,
    );
    private $qtypes = array(
        file_types::FT_sbq_protein_abundance_median => 1,
        file_types::FT_sbq_cluster_abundance_median => 1,
        file_types::FT_sbq_protein_abundance_norm_median => 1,
        file_types::FT_sbq_cluster_abundance_norm_median => 1,
        file_types::FT_sbq_protein_abundance_genome_norm_median => 1,
        file_types::FT_sbq_cluster_abundance_genome_norm_median => 1,
        file_types::FT_sbq_protein_abundance_mean => 1,
        file_types::FT_sbq_cluster_abundance_mean => 1,
        file_types::FT_sbq_protein_abundance_norm_mean => 1,
        file_types::FT_sbq_cluster_abundance_norm_mean => 1,
        file_types::FT_sbq_protein_abundance_genome_norm_mean => 1,
        file_types::FT_sbq_cluster_abundance_genome_norm_mean => 1,
        file_types::FT_sbq_meta_info => 1,
        file_types::FT_sbq_ssn => 1,
    );






    protected $eol = PHP_EOL;
    protected $is_example = false;

    function __construct($db, $table_name, $is_example = false, $is_debug = false) {
        $this->db = $db;
        $this->is_example = $is_example ? true : false;
        $this->beta = settings::get_release_status();
        $this->table_name = $table_name;
        $this->is_debug = $is_debug;
    }

    protected function make_job_status_obj() { $this->job_status_obj = new \efi\job_status($this->db, $this); }
    protected function get_job_status_obj() { return $this->job_status_obj; }

    public function get_table_name() {
        return $this->table_name;
    }

    public function get_id() {
        return $this->id;
    }
    public function set_id($theId) {
        $this->id = $theId;
    }
    protected function get_identify_id() {
        return $this->identify_id;
    }
    protected function set_identify_id($id) {
        $this->identify_id = $id;
    }

    public function get_key() {
        return $this->key;
    }
    public function set_key($newKey) {
        $this->key = $newKey;
    }

    protected function set_pbs_number($theNumber) {
        $this->pbs_number = $theNumber;
        $this->update_pbs_number();
    }

    protected function set_email($email) {
        $this->email = $email;
    }
    protected function get_email() {
        return $this->email;
    }

    protected function set_parent_id($parent_id) {
        $this->parent_id = $parent_id;
    }
    public function get_parent_id() {
        return $this->parent_id;
    }

    public function get_search_type() {
        return $this->search_type;
    }
    public function set_search_type($search_type) {
        return $this->search_type = $search_type;
    }
    
    public function get_filename() {
        return $this->filename;
    }
    protected function set_filename($filename) {
        $this->filename = $filename;
    }
    
    public function get_min_seq_len() {
        return $this->min_seq_len;
    }
    public function get_max_seq_len() {
        return $this->max_seq_len;
    }
    protected function set_min_seq_len($min_seq_len) {
        $this->min_seq_len = $min_seq_len;
    }
    protected function set_max_seq_len($max_seq_len) {
        $this->max_seq_len = $max_seq_len;
    }

    protected function set_job_complete() {
        $this->get_job_status_obj()->complete();
        $this->email_completed();
    }

    protected function set_job_failed() {
        $this->get_job_status_obj()->failed();
        $this->email_failure();
    }

    protected function set_job_started() {
        $this->get_job_status_obj()->start();
        $this->email_started();
    }


    protected abstract function load_job();

    protected function load_job_shared($result, $params) {
        $table = $this->get_table_name();
        $this->status = $result["${table}_status"];
        $this->pbs_number = $result["${table}_pbs_number"];
        $parent_field = "${table}_parent_id";
        if (isset($result[$parent_field]) && $result[$parent_field])
            $this->parent_id = $result[$parent_field];

        if (isset($params["${table}_search_type"]) && settings::get_diamond_enabled())
            $this->search_type = $params["${table}_search_type"];
        else
            $this->search_type = "";

        $this->filename = $params["identify_filename"];
        $this->min_seq_len = isset($params['identify_min_seq_len']) ? $params['identify_min_seq_len'] : "";
        $this->max_seq_len = isset($params['identify_max_seq_len']) ? $params['identify_max_seq_len'] : "";
    }

    protected static function insert_new($db, $table_name, $insert_array) {
        $new_id = $db->build_insert($table_name, $insert_array);
        if ($new_id)
            \efi\job_status::insert_new_manual($db, $new_id, $table_name);
        return $new_id;
    }



    protected function is_job_running() {
        $sched = settings::get_cluster_scheduler();

        $job_num = $this->pbs_number;
        $output = "";
        $exit_status = "";
        $exec = "";

        if ($sched == "slurm") {
            $exec = "squeue --job $job_num 2> /dev/null | grep $job_num";
        } else {
            $exec = "qstat $job_num 2> /dev/null | grep $job_num";
        }

        exec($exec,$output,$exit_status);

        if (count($output) == 1) {
            return true;
        } else {
            return false;
        }
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // EMAIL FUNCTIONS
    //
    
    protected function get_job_info() {
        $message = "EFI-CGFP Job ID: " . $this->get_id() . $this->eol;
        return $message;
    }

    protected abstract function get_email_started_subject();
    protected abstract function get_email_started_message();
    protected abstract function get_email_failure_subject();
    protected abstract function get_email_failure_message($result);
    protected abstract function get_email_cancelled_subject();
    protected abstract function get_email_cancelled_message();
    protected abstract function get_email_completed_subject();
    protected abstract function get_email_completed_message();
    protected abstract function get_completed_url();
    protected function get_completed_url_params() {
        return array();
    }

    protected function email_started() {
        $subject = $this->beta . $this->get_email_started_subject();

        $plain_email = "";
        $plain_email .= $this->get_email_started_message();
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        if (!$this->is_debug) {
            $this->send_email($subject, $plain_email);
        } else {
            print("Would have sent email_started\n");
        }
    }

    protected function email_failure($result = "") {
        $subject = $this->beta . $this->get_email_failure_subject();

        $plain_email = "";
        $plain_email .= $this->get_email_failure_message($result);
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        if (!$this->is_debug) {
            $this->send_email($subject, $plain_email);
        } else {
            print("Would have sent email_failure $result\n");
        }
    }

    protected function email_cancelled() {
        $subject = $this->beta . $this->get_email_cancelled_subject();

        $plain_email = "";
        $plain_email .= $this->get_email_cancelled_message();
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        if (!$this->is_debug) {
            $this->send_email($subject, $plain_email);
        } else {
            print("Would have sent cancelled email\n");
        }
    }

    protected function email_admin_failure($result = "") {
        $subject = $this->beta . $this->get_email_failure_subject();

        $plain_email = "";
        $plain_email .= "FAILED TO START JOB: $result" . $this->eol . $this->eol;
        $plain_email .= $this->get_email_failure_message($result);
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $to = global_settings::get_error_admin_email();
        if (!$this->is_debug) {
            $this->send_email($subject, $plain_email, "", $to);
        } else {
            print("Would have sent email_admin_failure $result\n");
        }
    }

    protected function email_completed($result = "") {
        $subject = $this->beta . $this->get_email_completed_subject();

        $url = $this->get_completed_url();
        $params = $this->get_completed_url_params();
        $query_params = array('id'=>$this->get_id(), 'key'=>$this->get_key());
        if (count($params)) {
            $query_params = $params;
        }
        $full_url = $url . "?" . http_build_query($query_params);

        $plain_email = "";
        $plain_email .= $this->get_email_completed_message();
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        
        $plain_email .= "Cite us:" . $this->eol . $this->eol;
        $plain_email .= "R&eacute;mi Zallot, Nils Oberg, and John A. Gerlt, ";
        $plain_email .= "The EFI Web Resource for Genomic Enzymology Tools: Leveraging Protein, Genome, and Metagenome Databases to Discover Novel Enzymes and Metabolic Pathways. ";
        $plain_email .= "Biochemistry 2019 58 (41), 4169-4182. BIOCHEM_DOI"; 
        $plain_email .= $this->eol . $this->eol;
        //$plain_email .= "R&eacute;mi Zallot, Nils Oberg, John A. Gerlt, ";
        //$plain_email .= "\"Democratized\" genomic enzymology web tools for functional assignment, ";
        //$plain_email .= "Current Opinion in Chemical Biology, Volume 47, 2018, Pages 77-85, GNT_DOI";
        //$plain_email .= $this->eol . $this->eol;
        $plain_email .= "These data will only be retained for " . settings::get_retention_days() . " days." . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        if (!$this->is_debug) {
            $this->send_email($subject, $plain_email, $full_url);
        } else {
            print("Would have sent email_completed $result\n");
        }
    }

    private function send_email($subject, $plain_email, $full_url = "", $to = "") {
        if ($this->beta)
            $plain_email = "Thank you for using the EFI beta site." . $this->eol . $plain_email;

        if (!$to)
            $to = $this->get_email();
        $from = settings::get_admin_email();
        $from_name = "EFI CGFP";

        $html_email = nl2br($plain_email, false);

        if ($full_url) {
            $plain_email = str_replace("THE_URL", $full_url, $plain_email);
            $html_email = str_replace("THE_URL", "<a href='" . htmlentities($full_url) . "'>" . $full_url . "</a>", $html_email);
        }

        $biochem_doi_url = "https://doi.org/10.1021/acs.biochem.9b00735";
        $gnt_doi_url = "https://doi.org/10.1016/j.cbpa.2018.09.009";
        $plain_email = str_replace("GNT_DOI", $gnt_doi_url, $plain_email);
        $html_email = str_replace("GNT_DOI", "<a href=\"" . htmlentities($gnt_doi_url) . "\">" . $gnt_doi_url. "</a>", $html_email);
        $plain_email = str_replace("BIOCHEM_DOI", $biochem_doi_url, $plain_email);
        $html_email = str_replace("BIOCHEM_DOI", "<a href=\"" . htmlentities($biochem_doi_url) . "\">" . $biochem_doi_url. "</a>", $html_email);

        \efi\email::send_email($to, $from, $subject, $plain_email, $html_email, $from_name);
    }



    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // TIME FUNCTIONS
    //

    public function is_expired() {
        if (!$this->is_example && $this->get_job_status_obj()->is_expired()) {
            return true;
        } else {
            return false;
        }
    }



    private function update_pbs_number() {
        $table_name = $this->get_table_name();
        $sql = "UPDATE ${tableName} SET ${tableName}_pbs_number='" . $this->pbs_number . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->id . "'";
        $this->db_query($sql);
    }

    private function db_query($sql, $params = array()) {
        if (!$this->is_debug) {
            $this->db->non_select_query($sql, $params);
        } else {
            print("SQL: $sql\n");
        }
    }



    // Returns Time Started/Finished
    protected function get_extra_metadata($id, $tab_data) {
        $qid_col = "quantify_identify_id";
        if (!$id) {
            $id = $this->id;
            $qid_col = "quantify_id";
        }

        //HACK: to get the num_unique_seq text right and show/hide the num_filtered_seq line
        $table = $this->get_table_name();
        $sql = "SELECT identify_params, identify_time_created, identify_time_started AS time_started, identify_time_completed AS time_completed FROM identify WHERE identify_id = $id";
        if ($table == "quantify")
            $sql = "SELECT quantify_id, quantify_time_created, quantify_time_started AS time_started, quantify_time_completed AS time_completed, identify_params FROM quantify JOIN identify ON quantify_identify_id = identify_id WHERE $qid_col = $id";
        $result = $this->db->query($sql);

        $time_data = array();

        if ($result && isset($result[0]["identify_params"])) {
            $iparams = global_functions::decode_object($result[0]["identify_params"]);
            if (isset($iparams["identify_min_seq_len"]))
                $tab_data["min_seq_len"] = $iparams["identify_min_seq_len"];
            if (isset($iparams["identify_max_seq_len"]))
                $tab_data["max_seq_len"] = $iparams["identify_max_seq_len"];

            $time_data = array("Time Started -- Finished", functions::format_short_date($result[0]["time_started"]) . " -- " .
                                                        functions::format_short_date($result[0]["time_completed"]));
        }

        return $time_data;
    }

    protected function get_metadata_shared($meta_file, $id = 0) { // for quantify jobs, pass in the identify_id

        $tab_data = self::read_kv_tab_file($meta_file);
        $table_data = array();

        if (!$this->is_example) {
            $time_data = $this->get_extra_metadata($id, $tab_data);
            if (count($time_data) > 0)
                $table_data[0] = $time_data;
        }

        $pos_start = 1;
        foreach ($tab_data as $key => $value) {
            $attr = "";
            $pos = $pos_start;
            if ($key == "time_period" && !$this->is_example) {
                $attr = "Time Started/Finished";
                $pos = max(0, $pos_start - 1);
            } elseif ($key == "num_ssn_clusters") {
                $attr = "Number of SSN clusters";
                $pos = $pos_start + 0;
            } elseif ($key == "num_ssn_singletons") {
                $attr = "Number of SSN singletons";
                $pos = $pos_start + 1;
            } elseif ($key == "is_uniref") {
                $attr = "SSN sequence source";
                $value = $value ? "UniRef$value" : "UniProt";
                $pos = $pos_start + 2;
            } elseif ($key == "num_metanodes") {
                $attr = "Number of SSN (meta)nodes";
                $pos = $pos_start + 3;
            } elseif ($key == "num_raw_accessions") {
                $attr = "Number of accession IDs in SSN";
                $pos = $pos_start + 4;
            # These are included elsewhere
            #} elseif ($key == "min_seq_len" && $value != "none") {
            #    $attr = "Minimum sequence length filter";
            #    $pos = $pos_start + 7;
            #} elseif ($key == "max_seq_len" && $value != "none") {
            #    $attr = "Maximum sequence length filter";
            #    $pos = $pos_start + 8;
            } elseif ($key == "num_cdhit_clusters") {
                $attr = "Number of CD-HIT ShortBRED families";
                if ($this->parent_id)
                    $attr .= " (from parent)";
                $pos = $pos_start + 9;
            } elseif ($key == "num_markers") {
                $attr = "Number of markers";
                if ($this->parent_id)
                    $attr .= " (from parent)";
                $pos = $pos_start + 10;
            } elseif ($key == "num_cons_seq_with_hits") {
                $attr = "Number of consensus sequences with hits";
                $pos = $pos_start + 100;
            } elseif (!$this->parent_id) {
                if ($key == "num_unique_seq") {
                    $attr = "Number of unique sequences in SSN";
                    if ($tab_data["min_seq_len"] != "none" || $tab_data["max_seq_len"] != "none")
                        $attr .= " after length filter";
                    $pos = $pos_start + 6;
                } elseif ($key == "num_filtered_seq" && ($tab_data["min_seq_len"] != "none" || $tab_data["max_seq_len"] != "none")) {
                    $attr = "Number of sequences after length filter";
                    $pos = $pos_start + 5;
                }
            }

            if ($attr)
                $table_data[$pos] = array($attr, $value);
        }

        return $table_data;
    }

    private static function read_kv_tab_file($file) {
        $delim = "\t";
        $fh = fopen($file, "r");
        $data = array();
        while (!feof($fh)) {
            $line = trim(fgets($fh, 1000));
            if (!$line)
                continue;

            $row = str_getcsv($line, $delim);
            $data[$row[0]] = $row[1];
        }
        fclose($fh);
        return $data;
    }



    //public function get_file_info($file_type) {
    //    if (isset($this->itypes[$file_type])) {
    //        $file_name = $this->get_file_name_base($file_type, self::Identify);
    //        $file_path = $this->get_file_path_base($file_type, self::Identify);
    //    } else if (isset($this->qtypes[$file_type])) {
    //        $file_name = $this->get_file_name_base($file_type, self::Quantify);
    //        $file_path = $this->get_file_path_base($file_type, $q_type, self::Quantify);
    //    }
    //    if (isset($file_name) && isset($file_path)) {
    //        return array("file_name" => $file_name, "file_path" => $file_path);
    //    } else {
    //        return false;
    //    }
    //}

    // Public, because it's used in get_sbq_data.php
    public abstract function get_identify_output_path($parent_id = 0);
    protected abstract function get_quantify_output_path($parent_id = 0);

    protected function get_file_name_base($file_type, $result_type, $is_legacy = 0) {
        // this->get_filename returns the basic name of the file, here we add the suffix
        $ext = file_types::ext($file_type);
        if ($ext === false)
            return false;
        $suffix = file_types::suffix($file_type);
        if ($suffix === false)
            return false;

        if ($file_type !== file_types::FT_sb_ssn && $file_type !== file_types::FT_sbq_ssn) {
            $name = $this->get_filename();
            $name .= "_$suffix.$ext";
        } else {
            $identify_id = $this->get_identify_id();
            if ($is_legacy) {
                if ($file_type == file_types::FT_sb_ssn)
                    $suffix = "markers";
                else
                    $suffix = "quantify";
            }
            $name = $this->make_ssn_name($identify_id, $suffix);
            $name .= ".$ext";
        }

        return $name; 
    }
    protected function get_file_path_base($file_type, $result_type) {
        $ext = file_types::ext($file_type);
        if ($ext === false)
            return false;
        $name = file_types::suffix($file_type);
        if ($name === false)
            return false;

        $base_dir = "";
        if ($result_type == self::Quantify) {
            if (isset($this->itypes[$file_type]))
                $base_dir = $this->get_identify_output_path();
            else
                $base_dir = $this->get_quantify_output_path();
        } else {
            $base_dir = $this->get_identify_output_path();
        }

        // Try the simple naming convention first
        $file_path = "$base_dir/$name.$ext";

        // Then try legacy file naming convention if the new convention doesn't exist
        if (!file_exists($file_path)) {
            $file_name = $this->get_file_name_base($file_type, $result_type);
            $file_path = "$base_dir/${file_name}";
            // Legacy
            if (!file_exists($file_path)) {
                $file_name = $this->get_file_name_base($file_type, $result_type, 1);
                $file_path = "$base_dir/${file_name}";
            }
        }

        if (!file_exists($file_path))
            return false;
        return $file_path;
    }
    protected function get_file_size_base($file_type, $result_type) {
        $file_path = $this->get_file_path_base($file_type, $result_type);
        if ($file_path === false || !file_exists($file_path))
            return 0;

        $size = filesize($file_path);
        $mb_size = global_functions::bytes_to_megabytes(filesize($file_path));

        if ($mb_size)
            return $mb_size;
        else
            return "<1";
    }

    public function get_is_valid_type($type) {
        return (isset($this->itypes[$type]) || isset($this->qtypes[$type]));
    }

    protected function make_ssn_name($id, $suffix = "") {
        $filename = $this->get_filename();
        if ($suffix)
            $suffix = "_$suffix";
        $name = preg_replace("/.zip$/", ".xgmml", $filename);
        $name = preg_replace("/.xgmml$/", "$suffix.xgmml", $name);
        $prefix = $this->use_prefix ? "${id}_" : "";
        return "$prefix$name";
    }

    // For static training job
    protected function set_use_prefix($use_prefix = true) {
        $this->use_prefix = $use_prefix;
    }

    public function get_time_completed() {
        return $this->get_job_status_obj()->get_time_completed();
    }
    public function get_time_completed_formatted() {
        return $this->get_job_status_obj()->get_time_completed_formatted();
    }
}


