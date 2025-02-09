<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use efi\est\settings;
use efi\est\est_user_jobs_shared;


class user_jobs extends \efi\user_auth {

    // Sort by generate job time completion
    const SORT_TIME_COMPLETED = 1;
    // Sort by generate/analysis/job group time activity
    const SORT_TIME_ACTIVITY = 2;
    // Sort by generate job ID
    const SORT_ID = 3;
    const SORT_ID_REVERSE = 4;

    private $user_token = "";
    private $user_email = "";
    private $user_groups = array();
    private $is_admin = false;
    private $jobs = array();
    private $training_jobs = array();
    private $analysis_jobs = array();
    private $load_job_types;
    private $exclude_job_types;

    public function __construct($job_types = null, $exclude_types = null) {
        if (is_array($job_types))
            $this->load_job_types = $job_types;
        if (isset($exclude_job_types) && is_array($exclude_job_types))
            $this->exclude_job_types = $exclude_types;
    }

    public function load_jobs($db, $token, $sort_order = self::SORT_TIME_COMPLETED) {
        $this->user_token = $token;
        $this->user_email = self::get_email_from_token($db, $token);
        if (!$this->user_email)
            return;

        $this->user_groups = self::get_user_groups($db, $this->user_token);
        array_unshift($this->user_groups, \efi\global_settings::get_default_group_name());
        $this->is_admin = self::get_user_admin($db, $this->user_email);

        $this->load_generate_jobs($db, $sort_order);
        $this->load_analysis_jobs($db);
        $this->load_training_jobs($db);
    }

    // Returns true if the user has exceeded their 24-hr limit
    public static function check_for_job_limit($db, $email) {
        $is_admin = self::get_user_admin($db, $email);
        if ($is_admin)
            return false;

        $limit_period = 24; // hours
        $dt = new \DateTime();
        $past_dt = $dt->sub(new \DateInterval("PT${limit_period}H"));
        $mysql_date = $past_dt->format("Y-m-d H:i:s");
        
        $sql = "SELECT COUNT(*) AS count FROM generate WHERE generate_time_created >= '$mysql_date' AND generate_email = '$email' AND (generate_status = '" . __RUNNING__ . "' OR generate_status = '" . __NEW__ . "')";
        $results = $db->query($sql);

        $num_job_limit = \efi\global_settings::get_num_job_limit();
        if (count($results) && $results[0]["count"] >= $num_job_limit)
            return true;
        else
            return false;
    }

    private static function get_select_statement() {
        $sql = "SELECT generate.generate_id, generate_key, generate_time_completed, generate_status, generate_type, generate_params FROM generate ";
        return $sql;
    }

    private static function get_group_select_statement($group_clause) {
        $group_clause .= " AND";
        $sql = self::get_select_statement() .
            "LEFT OUTER JOIN job_group ON generate.generate_id = job_group.job_id " .
            "WHERE job_group.job_type = 'EST' AND $group_clause generate_status = 'FINISH' AND (generate_is_tax_job = 0 OR generate_is_tax_job IS NULL) " .
            "ORDER BY generate_status, generate_time_completed DESC";
        return $sql;
    }

    private function get_job_type_clause() {
        $type_param_str = "";
        $job_types = array();
        if (is_array($this->exclude_job_types)) {
            $type_param_str .= "generate_type NOT IN (" . implode(",", array_fill(0, count($this->exclude_job_types), "?")) . ") AND";
            $job_types = array_merge($job_types, $this->exclude_job_types);
        }
        if (is_array($this->load_job_types)) {
            $type_param_str .= "generate_type IN (" . implode(",", array_fill(0, count($this->load_job_types), "?")) . ") AND";
            $job_types = array_merge($job_types, $this->load_job_types);
        }
        return array($type_param_str, $job_types);
    }

    private function load_generate_jobs($db, $sort_order) {
        $email = $this->user_email;
        $expDate = self::get_start_date_window();

        $order_by = "generate_status, generate_time_completed DESC";
        if ($sort_order == self::SORT_ID) {
            $order_by = "generate_status, generate_id DESC";
        } else if ($sort_order == self::SORT_ID_REVERSE) {
            $order_by = "generate_status, generate_id ASC";
        }

        list($job_type_clause, $job_type_params) = $this->get_job_type_clause();

        $sql = self::get_select_statement() .
            "WHERE (generate_email = '$email') AND generate_status != 'ARCHIVED' AND generate_status != 'CANCELLED' AND $job_type_clause (generate_is_tax_job = 0 OR generate_is_tax_job IS NULL) AND " .
            "(generate_time_completed >= '$expDate' OR (generate_time_created >= '$expDate' AND (generate_status = 'NEW' OR generate_status = 'RUNNING' OR generate_status = 'FAILED'))) " .
            "ORDER BY $order_by";
        $rows = $db->query($sql, $job_type_params);

        $familyLookupFn = function($family_id) {};
        $includeFailedAnalysisJobs = true;
        $includeAnalysisJobs = true;
        $this->jobs = est_user_jobs_shared::process_load_generate_rows($db, $rows, $includeAnalysisJobs, $includeFailedAnalysisJobs, $familyLookupFn, $sort_order);
    }

    private function load_training_jobs($db) {
        $func = function($val) { return "user_group = '$val'"; };
        $group_clause = implode(" OR ", array_map($func, $this->user_groups));
        if ($group_clause) {
            $group_clause = "($group_clause)";
        } else {
            $this->training_jobs = array();
            return;
        }

        $sql = self::get_group_select_statement($group_clause);
        $rows = $db->query($sql);

        $familyLookupFn = function($family_id) {};
        $includeFailedAnalysisJobs = false;
        $includeAnalysisJobs = true;
        $this->training_jobs = est_user_jobs_shared::process_load_generate_rows($db, $rows, $includeAnalysisJobs, $includeFailedAnalysisJobs, $familyLookupFn);
    }

    private static function lookup_family_name($db, $family_id) {
        $sql = "SELECT short_name FROM family_info WHERE family = '$family_id'";
        $rows = $db->query($sql);
        if ($rows) {
            return $rows[0]["short_name"];
        } else {
            return "";
        }
    }

    private function load_analysis_jobs($db) {
        $expDate = self::get_start_date_window();
        $func = function($val) { return "user_group = '$val'"; };
        $email = $this->user_email;
        $group_clause = "";

        $sql = "SELECT analysis_id, analysis_generate_id, generate_key, analysis_time_completed, analysis_status, generate_type FROM analysis " .
            "LEFT JOIN generate ON analysis_generate_id = generate_id " .
            "WHERE (generate_email = '$email' $group_clause) AND " .
            "(analysis_time_completed >= '$expDate' OR (analysis_time_created >= '$expDate' AND (analysis_status = 'NEW' OR analysis_status = 'RUNNING'))) " .
            "ORDER BY analysis_status, analysis_time_completed DESC";
        $rows = $db->query($sql);

        foreach ($rows as $row) {
            $comp = $row["analysis_time_completed"];
            $status = $row["analysis_status"];
            $is_completed = false;
            if ($status == "FAILED") {
                $comp = "FAILED";
            } elseif (!$comp || substr($comp, 0, 4) == "0000") {
                $comp = $row["analysis_status"]; // "RUNNING";
                if ($comp == "NEW")
                    $comp = "PENDING";
            } else {
                $comp = date_format(date_create($comp), "n/j h:i A");
                $is_completed = true;
            }

            $job_name = $row["generate_type"];

            array_push($this->analysis_jobs, array("id" => $row["analysis_generate_id"], "key" => $row["generate_key"],
                    "job_name" => $job_name, "is_completed" => $is_completed, "analysis_id" => $row["analysis_id"],
                    "date_completed" => $comp));
        }
    }

    public function get_cookie() {
        return self::get_cookie_shared($this->user_token);
    }

    public function get_jobs() {
        return $this->jobs;
    }

    public function get_training_jobs() {
        return $this->training_jobs;
    }

    public function get_analysis_jobs() {
        return $this->analysis_jobs;
    }

    public function get_email() {
        return $this->user_email;
    }

    public function is_admin() {
        return $this->is_admin;
    }

    public function get_groups() {
        return $this->user_groups;
    }
}


