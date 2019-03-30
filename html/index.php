<?php
require_once("../includes/main.inc.php");
require_once("../libs/user_auth.class.inc.php");
require_once("../includes/login_check.inc.php");

$NoAdmin = true;
require_once("inc/header.inc.php");

$useShortbred = global_settings::get_shortbred_enabled();

?>


<p style="margin-top: 30px">
This website contains a collection of webtools for creating and interacting with sequence similarity
networks (SSNs) and genome neighborhood networks (GNNs).
These tools originated in the Enzyme Function Initiative, a NIH-funded research
project to develop a sequence / structure-based strategy for facilitating discovery of <i>in vitro</i>
enzymatic and <i>in vivo</i> metabolic / physiological functions of unknown enzymes discovered in
genome projects.
</p>

<p>
The Enzyme Function Initiative tools are hosted at the <a href="http://igb.illinois.edu">Carl R.
Woese Institute for Genomic Biology (IGB)</a>, at the <a href="http://illinois.edu">University of
Illinois at Urbana-Champaign</a>.
The development of these tools is headed by John A. Gerlt (PI), aided by scientist R&eacute;mi Zallot
and software engineers Dan Davidson, David Slater, and Nils Oberg.  The computational infrastracture
is supported by the <a href="https://www.igb.illinois.edu/facilities-services/computer-network">Computer
and Network Resource Group</a> at IGB.
</p>

<div class="image-bar">
<div><img src="images/jg2.jpg" height="165"><br>John Gerlt</div>
<div><img src="images/rz.jpg" height="165"><br>R&eacute;mi Zallot</div>
<div><img src="images/dd.jpg" height="165"><br>Dan Davidson</div>
<div><img src="images/ds.jpg" height="165"><br>David Slater</div>
<div><img src="images/no.jpg" height="165"><br>Nils Oberg</div>
</div>

<p>
<a name="citeus"></a>
If you use the EFI web tools, please cite us:
</p>

<p>
R&eacute;mi Zallot, Nils Oberg, John A. Gerlt, <b>"Democratized" genomic enzymology web 
tools for functional assignment</b>, Current Opinion in Chemical Biology, Volume 
47, 2018, Pages 77-85,
<a href="https://doi.org/10.1016/j.cbpa.2018.09.009">https://doi.org/10.1016/j.cbpa.2018.09.009</a>
</p>

<p>
John A. Gerlt, Jason T. Bouvier, Daniel B. Davidson, Heidi J. Imker, Boris 
Sadkhin, David R. Slater, Katie L. Whalen, <b>Enzyme Function Initiative-Enzyme 
Similarity Tool (EFI-EST): A web tool for generating protein sequence 
similarity networks</b>, Biochimica et Biophysica Acta (BBA) - Proteins and 
Proteomics, Volume 1854, Issue 8, 2015, Pages 1019-1037, ISSN 1570-9639, 
<a href="https://dx.doi.org/10.1016/j.bbapap.2015.04.015">https://dx.doi.org/10.1016/j.bbapap.2015.04.015</a>
</p>

<p style="margin-top: 30px">
<table border="0" class="nav-blocks">
<tr>
    <td>
        <a href="efi-est/">
            <span class="block-hdr">SSN Creation</span>
            <img src="images/about_ssn.png" width="450">
            SSN creation from sequences, PFam or InterPro famil(ies), and
            UniProt and/or NCBI protein accession IDs.
        </a>
<!--        SSN creation from sequence BLAST, PFam or InterPro famil(ies), FASTA sequences, and
        UniProt and/or NCBI protein accession IDs.</a>-->
    </td>
    <td>
        <a href="efi-gnt/">
            <span class="block-hdr">GNN Creation</span>
            <img src="images/about_gnn.png" width="450">
            GNN creation from sequence similarity networks.
        </a>
    </td>
</tr>
<tr>
    <td>
        <a href="efi-gnt/">
            <span class="block-hdr">Genome neighborhood diagrams</span>
            <img src="images/about_gnd.png" width="450">
            Genome neighborhood diagrams from sequences, sequence IDs, or GNNs.</a>
    </td>
    <td>
<?php if (isset($IncludeShortBred) && $IncludeShortBred) { ?>
        <a href="shortbred/">
            <span class="block-hdr">Functional Profiling</span>
            <img src="images/about_heatmap.png" width="450">
            Computationally-guided functional profiling using ShortBRED and the CGFP tools from
            the Balskus and Huttenhower Labs at Harvard University.
        </a>
<?php } ?>
    </td>
</tr>
</table>


<p style="margin-bottom: 60px">
These webtools use <a href="https://blast.ncbi.nlm.nih.gov/Blast.cgi">NCBI BLAST</a> and
<a href="http://weizhongli-lab.org/cd-hit/">CD-HIT</a> to create SSNs and GNNs.
<?php if ($useShortbred) { ?>
The computationally-guided
functional profiling tool uses the CGFP programs from the Balskus Lab
(<a href="https://bitbucket.org/biobakery/cgfp/src">https://bitbucket.org/biobakery/cgfp/src</a>)
and ShortBRED from the Huttenhower Lab
(<a href="http://huttenhower.sph.harvard.edu/shortbred">http://huttenhower.sph.harvard.edu/shortbred</a>).
<?php } ?>
The data used originate from the <a href="https://www.uniprot.org/">UniProt Consortium</a> databases and the
<a href="https://www.ebi.ac.uk/interpro/">InterPro</a> and <a href="https://www.ebi.ac.uk/ena">ENA</a>
databases from EMBL-EBI.
</p>

<script>
$(document).ready(function() {
}).tooltip();
</script>

<?php require_once("inc/footer.inc.php"); ?>


