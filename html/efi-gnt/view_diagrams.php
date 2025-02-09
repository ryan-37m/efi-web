<?php 
require_once(__DIR__."/../../init.php");

use \efi\gnt\settings;
use \efi\gnt\functions;
use \efi\gnt\bigscape_job;
use \efi\gnt\gnd_params;
use \efi\training\example_config;
use \efi\sanitize;

include(__DIR__."/inc/gnd_make_params.inc.php");
include(__DIR__."/inc/gnd_misc.inc.php");
include(__DIR__."/inc/gnd_tools.inc.php");
include(__DIR__."/inc/gnd_window.inc.php");
include(__DIR__."/inc/gnd_search.inc.php");
include(__DIR__."/inc/gnd_filter.inc.php");
include(__DIR__."/inc/gnd_dialog_direct.inc.php");
include(__DIR__."/inc/gnd_dialog_bigscape.inc.php");
include(__DIR__."/inc/gnd_misc_dialogs.inc.php");





$P = new gnd_params();

$P->is_example = example_config::is_example();
$P->is_bigscape_enabled = settings::get_bigscape_enabled() && !$P->is_example;
$P->is_interpro_enabled = settings::get_interpro_enabled();
$P->num_diagrams = settings::get_num_diagrams_per_page();
$P->supports_download = true;
$P->supports_export = true;
$show_new_features = false;

$key = sanitize::validate_key("key", sanitize::GET);
$gnn_id = sanitize::validate_id("gnn-id", sanitize::GET);
$upload_id = sanitize::validate_id("upload-id", sanitize::GET);
$direct_id = sanitize::validate_id("direct-id", sanitize::GET);
$mode = sanitize::get_sanitize_string("mode", "");

if ($gnn_id !== false) {
    if ($key === false || get_gnn_params($db, $P, $gnn_id, $key) !== true)
        error_404();
} else if ($upload_id !== false && functions::is_diagram_upload_id_valid($upload_id)) {
    if (get_upload_params($db, $P, $upload_id, $key) !== true)
        error_404();
} else if ($direct_id !== false && functions::is_diagram_upload_id_valid($direct_id)) {
    if (get_direct_params($db, $P, $direct_id, $key) !== true)
        error_404();
} else if (isset($_GET["rs-id"]) && isset($_GET["rs-ver"])) {
    if (get_direct_params_rs($db, $P, $_GET["rs-id"], $_GET["rs-ver"], $key) !== true)
        error_404();
} else if ($mode == "rt") {
    get_realtime_params($db, $P);
} else {
    error404();
}

$uniref_version = sanitize::get_sanitize_string("id-type", "");
$uniref_id = sanitize::get_sanitize_string("uniref-id", "");
if ($uniref_version && $uniref_id)
    $P->is_direct_job = true;


if ($P->is_bigscape_enabled) {
    $bss = new bigscape_job($db, $P->gnn_id, $P->bigscape_type);
    $P->bigscape_status = $bss->get_status();
    $P->bigscape_btn_icon = $P->bigscape_status === bigscape_job::STATUS_FINISH ? "fa-sort-amount-down" : "fa-magic";
    $P->bigscape_btn_text = $P->bigscape_status === bigscape_job::STATUS_FINISH ? "Use BiG-SCAPE Synteny" : 
        ($P->bigscape_status === bigscape_job::STATUS_RUNNING ? "Big-SCAPE Pending" : "Run BiG-SCAPE");
    $P->bigscape_modal_close_text = $P->bigscape_status === bigscape_job::STATUS_RUNNING ? "Close" : "No";
}

$js_version = settings::get_js_version();

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">   
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Genome Neighborhood Diagrams<?php echo $P->window_title; ?></title>

        <!-- Bootstrap core CSS -->
        <link href="<?php echo $SiteUrlPrefix; ?>/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo $SiteUrlPrefix; ?>/css/menu-sidebar.css" rel="stylesheet">
        <link href="<?php echo $SiteUrlPrefix; ?>/vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">


        <!-- Custom styles for this template -->
        <link href="css/diagrams.css?v=<?php echo $js_version; ?>" rel="stylesheet">
        <link href="css/alert.css" rel="stylesheet">
<!--
        <script src="js/app.js" type="application/javascript"></script>
        <script src="js/arrows.js" type="application/javascript"></script>
-->
        <style>
            #header-logo { float: left; width: 175px; }
            #header-body { margin-left: 185px; overflow: hidden; height: 70px; }
            #header-body-title  { vertical-align: middle; line-height: normal; padding-left: 15px; }
            /*#header-body-title  { float: left; width: calc(100%-200px); display: inline-block; vertical-align: middle; line-height: normal; width: calc(100%-370px); }*/
            #header-job-info { width: 195px; }
            #header-job-info div { line-height: normal; }
            @font-face {
                font-family:'FontAwesome';
                src:url("<?php echo $SiteUrlPrefix; ?>/vendor/fortawesome/font-awesome/webfonts/fa-solid-900.ttf") format("truetype");
            }
        </style>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>

    <body>

        <header class="header">
            <table style="width:100%;height:70px">
                <tr>
                    <td style="width: 175px">
                        <a href="index.php"><img
                            src="images/efignt_logo55.png" width="157" height="55" alt="EFI GNT Logo" style="margin-left:10px;" /></a>
                    </td>
                    <?php render_page_title_cells($P); ?>
                </tr>
            </table>
        </header>

        <!-- Begin page content -->
        <div id="wrapper" class="">
            <div id="sidebar-wrapper">
                <ul class="sidebar-nav">
                    <li id="advanced-search-panel">
                        <?php render_search_input($P); ?>
                    </li>
                    <li>
                        <?php render_filter_input($P); ?>
                    </li>
                    <li>
                        <?php render_window_tools($P); ?>
                    </li>
                    <li>
                        <?php render_gnd_tools($P); ?>
                    </li>
                </ul>
            </div>

            <div class="container">
                <div id="arrow-container" style="width:100%;height:100%">
                    <br>
                    <svg id="arrow-canvas" width="100%" style="height:70px" viewBox="0 0 10 70" preserveAspectRatio="xMinYMin"></svg>

                    <!-- Progess loading bar at bottom of page -->
                    <div style="margin-top:50px;width:100%;position:fixed;bottom:0;height:50px;margin-bottom:100px">
                        <i id="progress-loader" class="fas fa-sync black fa-spin fa-4x fa-fw hidden-placeholder"></i>
                        <i id="progress-error" class="fas fa-exclamation-circle black fa-4x fa-fw hidden-placeholder"></i>
                        <span id="loader-message"></span><br>
                        <div class="progress hidden">
                            <div class="progress-bar" style="width: 10%" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="progress-bar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <table style="width:100%;height:60px">
                <tr>
                    <td style="width: 275px;">
                        <img src="images/efi_logo45.png" width="150" height="45" alt="EFI Logo" style="margin-left:45px" />
                    </td>
                    <?php render_page_footer_stats_cells($P); ?>
                </tr>
            </table>
        </footer>

        <div id="alert-msg">Unable to show reqeuested diagrams.</div> 


        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->

        <script src="js/snap.svg-min.js" content-type="text/javascript"></script>

        <!-- jQuery -->
        <script src="../vendor/components/jquery/jquery.min.js"></script>
        <!-- Bootstrap Core JavaScript -->
        <script src="<?php echo $SiteUrlPrefix; ?>/vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>

        <script src="js/gnd/color.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/control.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/data.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/filter.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/http.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/message.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/popup.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/ui.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/vars.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/view.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/ui-filter.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/app-specific.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/svg-util.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/gnd/uniref.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script src="js/bigscape.js?v=<?php echo $js_version; ?>" content-type="text/javascript"></script>
        <script type="application/javascript">
            $(document).ready(function() {
                $("#filter-cb-toggle").prop("checked", false);
                $("#filter-anno-toggle").prop("checked", false);
                //$("#advanced-search-use-uniref").prop("checked", false);
                $("#window-size").val(<?php echo $P->nb_size; ?>);
                if (checkBrowserSupport()) {

                    var svgCanvasId = "#arrow-canvas";
                    var pfamFilterContainerId = "#filter-container-pfam";
                    var interproFilterContainerId = "#filter-container-interpro";
                    var legendContainerId = "#active-filter-list";
                    var numDiagramsFilteredId = "#diagram-filter-count-container";
                    var superfamilySupport = <?php echo $P->is_superfamily_job ? "true" : "false"; ?>;
                    var uniRefUiIds = {};
                    uniRefUiIds.uniref50Cb = "uniref50-cb";
                    uniRefUiIds.uniref50Btn = "uniref50-btn";
                    uniRefUiIds.uniprotCb = "uniprot-cb";
                    uniRefUiIds.uniprotBtn = "uniprot-btn";
                    uniRefUiIds.uniref90Cb = "uniref90-cb";
                    uniRefUiIds.uniref90Btn = "uniref90-btn";
                    uniRefUiIds.uniRefTitleId = "cluster-uniref-id";

                    // Create objects
                    var gndVars = new GndVars();
                    // Initialize constant vars
                    gndVars.setPageSize(200);
                    gndVars.setUrlPath("get_gnd_data.php");
                    gndVars.setAuthString("<?php echo $P->id_key_query_string; ?>");
                    gndVars.setWindow(<?php echo $P->nb_size; ?>);
                    if (superfamilySupport)
                        gndVars.setSuperfamilySupport(true);

                    var gndColor = new GndColor();
                    var gndRouter = new GndMessageRouter();
                    var gndHttp = new GndHttp(gndRouter);
                    var popupIds = new GndInfoPopupIds();
                    var bigscape = new BigScape(<?php echo $P->gnn_id; ?>, "<?php echo $P->gnn_key; ?>", "<?php echo $P->bigscape_type; ?>", "<?php echo $P->bigscape_status; ?>");
                    var uniRefSupport = new UniRef(<?php echo ($uniref_version ? $uniref_version : "false"); ?>, "<?php echo ($uniref_id ? $uniref_id : ""); ?>");
                    
                    var gndDb = new GndDb(gndColor);
                    var gndFilter = new GndFilter(gndRouter, gndDb);
                    var gndPopup = new GndInfoPopup(gndRouter, gndDb, popupIds);
                    var gndView = new GndView(gndRouter, gndDb, gndFilter, gndPopup, svgCanvasId, uniRefSupport);

                    var control = new GndController(gndRouter, gndDb, gndHttp, gndVars, gndView, gndFilter, bigscape, uniRefSupport);
                    var filterUi = new GndFilterUi(gndRouter, gndFilter, gndColor, pfamFilterContainerId, interproFilterContainerId, legendContainerId, numDiagramsFilteredId);
                    var ui = new GndUi(gndRouter, control, filterUi, gndVars, uniRefSupport);
<?php if ($P->is_bigscape_enabled) { ?>
                    ui.registerBigScape(bigscape, "#run-bigscape-btn", "#run-bigscape-btn-text", "#run-bigscape-modal", "#run-bigscape-confirm", "#run-bigscape-reject");
<?php } ?>

                    // Add callbacks
                    //gndRouter.addListener(uiFilterUpdate); //TODO

                    // Register hooks to UI
                    ui.registerZoom("#scale-zoom-out-large", "#scale-zoom-out-small", "#scale-zoom-in-small", "#scale-zoom-in-large");
                    ui.registerShowMoreBtn("#show-more-arrows-button");
                    ui.registerShowAllBtn("#show-all-arrows-button");
                    ui.registerWindowUpdateBtn("#refresh-window", "#window-size");
                    ui.registerProgressLoader("#progress-loader");
                    ui.registerErrorLoader("#progress-error");
                    ui.registerFilterControl("#filter-cb-toggle");
                    ui.registerFilterClear("#filter-clear");
                    ui.registerFilterAnnotation("#filter-anno-toggle", "#filter-anno-toggle-text");
                    ui.registerFilterFamilyGroup("#filter-accordion-panel-pfam", "#filter-accordion-panel-interpro");
                    ui.registerDiagramCountField("#diagrams-displayed-count", "#diagrams-total-count");
                    ui.registerLoaderMessage("#loader-message");
                    ui.registerProgressBar("#progress-bar");
                    ui.registerSearchBtn("#advanced-search-cluster-button", "#advanced-search-input", "#start-info", "#advanced-search-panel");
                    ui.registerUniRefControl("#advanced-search-use-uniref-container", "display-id-type", uniRefUiIds);
<?php if ($P->is_direct_job || $P->is_realtime_job) { ?>
                    ui.registerSearchResetToInitialBtn("#advanced-search-reset-button", "#advanced-search-input");
<?php } else { ?>
                    ui.registerSearchClearBtn("#advanced-search-reset-button", "#advanced-search-input");
<?php } ?>

                    $(".zoom-btn").tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});
                    $("#download-data").tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});


<?php if (!$P->is_superfamily_job) { ?>
                    $("#advanced-search-input-container").show();
<?php } ?>

<?php if (!$P->is_direct_job) { ?>
                    $("#start-info").show();
<?php } else { ?>
                    ui.initialDirectJobLoad();
                    $("#show-uniprot-ids").click(function(e) {
                        $("#uniprot-ids-modal").modal("show");
                    });
<?php if ($P->is_blast) { ?>
                    $("#show-blast-sequence").click(function(e) { $("#blast-sequence-modal").modal("show"); });
<?php } ?>
                    
<?php } ?>
                } else {
                    //TODO: nicer message
                    alert("Your browser is not supported.");
                }

<?php if ($P->has_unmatched_ids) { ?>
                $("#show-unmatched-ids").click(function(e) {
                        $("#unmatched-ids-modal").modal("show");
                    });
<?php } ?>

                $("#help-modal-button").click(function(e) {
                    $("#help-modal").modal("show");
                });

                $("#info-modal-button").click(function(e) {
                    $("#info-modal").modal("show");
                });

                $(".tooltip-text").tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});
                $('[data-toggle="tooltip"]').tooltip();

                $("#save-canvas-button").click(function(e) {
                    var svg = escape($("#arrow-canvas")[0].outerHTML);
                    var data = filterUi.getLegendSvg();//TODO
                    var legendSvgMarkup = escape(data[1]);
                    
                    var dlForm = $("<form></form>");
                    dlForm.attr("method", "POST");
                    dlForm.attr("action", "download_diagram_image.php");
                    dlForm.append('<input type="hidden" name="type" value="svg">');
                    dlForm.append('<input type="hidden" name="name" value="<?php echo str_replace("'", "\\'", $P->gnn_name); ?>">');
                    dlForm.append('<input type="hidden" name="svg" value="' + svg + '">');
                    dlForm.append('<input type="hidden" name="legend1-svg" value="' + legendSvgMarkup + '">');
                    $("#download-forms").append(dlForm);
                    dlForm.submit();
                });
                $("#export-gene-graphics-button").click(function(e) {
                    var url = control.getGetUrl(0, control.getMaxViewIndex());
                    url = url.replace("get_gnd_data.php", "get_gene_graphics.php");
                    window.location = url;
                });
            });

            function showAlertMsg() {
                // Get the snackbar DIV
                var x = document.getElementById("alert-msg");
            
                // Add the "show" class to DIV
                x.className = "show";
            
                // After 3 seconds, remove the show class from DIV
                setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);

                alert("Unable to retrieve the selected diagrams: probably because too many were selected.");
            } 
        </script>

<?php
if (!$P->is_realtime_job) {
?>
        <div id="start-info">
            <div><i class="fas fa-arrow-left" aria-hidden="true"></i></div>
            <div>Start by entering a cluster number</div>
        </div>
        <div id="download-forms" style="display:none;">
        </div>
<?php
}

render_popup_dialog($P);

if ($P->is_direct_job) {
    render_direct_job_dialogs($P);
}

if ($P->is_bigscape_enabled) {
    render_bigscape_dialog($P);
}

render_help_dialog($P);

if ($show_new_features) {
    render_new_features_dialog($P);
}

render_license_dialog($P);
?>
    </body>
</html>

