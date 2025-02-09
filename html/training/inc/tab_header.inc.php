<?php if ($has_advanced_options) { ?>
<div class="beta"><big>Tutorials and Workshops Tabs Are Only On Dev Site For Now</big></div>
<?php } ?>

<?php
$use_legacy_tab = true;
$tab_class = "";
if ($use_legacy_tab) {
    $tab_class = "tab active";
if (!isset($active_tab))
    $active_tab = "";
?>


<div class="tabs">
    <ul class="tab-headers">
        <li class="<?php echo $active_tab === "pubs" ? "active" : ""; ?>"><a href="index.php">Publications</a></li>
        <li class="<?php echo $active_tab === "biochem" ? "active" : ""; ?>"><a href="example.php?id=biochem">From The Bench</a></li>
        <li class="<?php echo $active_tab === "2022" ? "active" : ""; ?>"><a href="example.php?id=2022">JMB Resources</a></li>
        <li class="<?php echo $active_tab === "videos" ? "active" : ""; ?>"><a href="videos.php">Videos</a></li>
<?php if ($has_advanced_options) { ?>
        <li class="<?php echo $active_tab === "tutorial" ? "active" : ""; ?>"><a href="../efi-est/tutorial.php">Tutorial</a></li>
        <li class="<?php echo $active_tab === "refguide" ? "active" : ""; ?>"><a href="refguide.php">Reference Guide</a></li>
        <li class="<?php echo $active_tab === "workshops" ? "active" : ""; ?>"><a href="workshops.php">Workshops</a></li>
<?php } ?>
    </ul>

<?php
} else {
    $tab_class = "ui-tabs-panel ui-widget-content";
?>

<div class="tabs-efihdr ui-tabs ui-widget-content" id="main-tabs">
    <ul class="ui-tabs-nav ui-widget-header">
        <li class="ui-tabs-active"><a href="#video">Videos</a></li>
<?php if ($has_advanced_options) { ?>
        <li><a href="#tutorial">Tutorial</a></li>
        <li><a href="#refguide">Reference Guide</a></li>
        <li><a href="#workshops">Workshops</a></li>
<?php } ?>
    </ul>

<?php } ?>


