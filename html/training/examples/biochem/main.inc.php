
<div id="biochem" class="">

<div style="text-align: center; font-size: 2em; font-weight: bold;">
EFI Genomic Enzymology Tools
</div>
<div>
<img src="examples/biochem/paper_banner1.png" width="100%" />
</div>
<div>
<div style="width: 33%; display: inline-block; font-weight: bold; font-size: 1.5em; text-align: center" class="hl-est">
EFI-EST
</div>
<div style="width: 33%; display: inline-block; font-weight: bold; font-size: 1.5em; text-align: center" class="hl-gnt">
EFI-GNT
</div>
<div style="width: 33%; display: inline-block; font-weight: bold; font-size: 1.5em; text-align: center" class="hl-cgfp">
EFI-CGFP
</div>
</div>

<p>
A "From The Bench"
article was published in Biochemistry in 2019 describing the use of the EFI web 
tools with an analysis of the glycyl radical enzyme superfamily (GRE; 
IPR004184).  PDF files of the article and the Supplementary Information that 
includes a detailed description of the tools are available for download.  
</p>

<p>
Please use the following to cite the EFI tools:
<div style="margin-left: 50px">
R&eacute;mi Zallot, Nils Oberg, and John A. Gerlt,
<b>The EFI Web Resource for Genomic Enzymology Tools: Leveraging Protein, Genome, and Metagenome 
Databases to Discover Novel Enzymes and Metabolic Pathways.</b>
Biochemistry 2019 58 (41), 4169-4182.
<a href="https://doi.org/10.1021/acs.biochem.9b00735">https://doi.org/10.1021/acs.biochem.9b00735</a>
</div>
</p>

<p>
<a href="examples/biochem/FromTheBench2019.pdf">Download the paper in PDF format.</a>
</p>

<p>
<a href="examples/biochem/FromTheBench2019_Supplementary_Methods.pdf">Download the supplementary methods (201 pages).</a>
</p>

<p>
<a href="examples/biochem/FromTheBench2019_Supplementary_Figures_and_Tables.pdf">Download the supplementary figures and tables (25 pages).</a>
</p>

<p>
We also provide access to the EFI-EST, EFI-GNT and EFI-CGFP jobs that were generated in the analysis of the GRE superfamily.
</p>


<?php

// $config is from the file we are including this from

use \efi\est\est_ui;
use \efi\gnt\gnt_ui;
use \efi\cgfp\cgfp_ui;


echo "<h4>EST Jobs</h4>\n";
$est_jobs = $config->get_est_jobs("est.jobs");
if (isset($est_jobs["order"]) && count($est_jobs["order"]) > 0) {
    echo est_ui::output_job_list($est_jobs, false, false, "biochem");
}

echo "<h4 style=\"margin-top:50px\">GNT Job</h4>\n";
$gnt_jobs = $config->get_gnt_jobs("gnt.jobs");
if (count($gnt_jobs) > 0) {
    echo gnt_ui::output_job_list($gnt_jobs, "biochem");
}

echo "<h4 style=\"margin-top:50px\">CGFP Job</h4>\n";
$cgfp_jobs = $config->get_cgfp_jobs("cgfp.jobs");
if (count($cgfp_jobs) > 0) {
    echo cgfp_ui::output_job_list($cgfp_jobs, false, "biochem");
}

?>

    </div>
</div>



