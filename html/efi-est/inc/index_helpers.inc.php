<?php

function add_submit_html($option_id, $js_id_name, $user_email, $job_name = "") {
    if (!\efi\global_settings::get_submit_enabled())
        return array("Submission is currently disabled due to site maintenance.");

    //$js_fn = "submitOptionForm('$option_id', familySizeHelper, $js_id_name)";
    $is_interactive = is_interactive();
    $color_ssn_data = "";
    $color_ssn_options = array("colorssn" => 1, "cluster" => 1, "nc" => 1, "cr" => 1);
    if (isset($color_ssn_options[$option_id]))
        $color_ssn_data = 'data-color-ssn-option-id="' . $option_id . '"';
    //if ($option_id == "colorssn")
    //    $js_fn = "submitColorSsnForm('colorssn')";
    //else if ($option_id == "cluster")
    //    $js_fn = "submitColorSsnForm('cluster')";
    //else if ($option_id == "nc")
    //    $js_fn = "submitColorSsnForm('nc')";
    //else if ($option_id == "cr")
    //    $js_fn = "submitColorSsnForm('cr')";
    //else if ($option_id == "opt_tax")
    //    $js_fn = "submitTaxonomyForm('opt_tax')";

    $html = "";
    if ($option_id != "colorssn" && $option_id != "cluster" && $option_id != "nc" && $option_id != "cr")
        $html .= <<<HTML
<div style="margin-top: 35px">
    <span class="input-name">
        Job name:
    </span><span class="input-field">
        <input type="text" class="email" name="job-name-$option_id" id="job-name-$option_id" value="$job_name"> (required)
    </span>
</div>
HTML;
    $html .= <<<HTML
<div>
    <span class="input-name">
        E-mail address:
    </span><span class="input-field">
        <input name="email" id="email-$option_id" type="text" value="$user_email" class="email"
HTML;
    if ($is_interactive)
        $html .= "        onfocus='if(!this._haschanged){this.value=\"\"};this._haschanged=true;' ";
    $html .= <<<HTML
         value="">
    </span>
    <p>
    You will be notified by e-mail when your submission has been processed.
    </p>
</div>

<div id="message-$option_id" style="color: red" class="error_message">
</div>
<center>
    <div><button type="button" class="dark submit-job" data-option-id="$option_id" $color_ssn_data>Submit Analysis</button></div>
</center>
HTML;
//    if ($is_interactive)
//        $html .= " onclick=\"$js_fn\"";
//    $html .= <<<HTML
//>Submit Analysis</button></div>
//</center>
//HTML;
    return array($html);
}


function get_advanced_seq_html($option_id) {
    $addl_html = <<<HTML
<div>
    <span class="input-name">
        Sequence identity: 
    </span><span class="input-field">
        <input type="text" class="small" id="seqid-$option_id" name="seqid-$option_id" value="1">
        Sequence identity (&le; 1; default: 1)
    </span>
</div>
<div>
    <span class="input-name">
        Sequence length overlap:
    </span><span class="input-field">
        <input type="text" class="small" id="length-overlap-$option_id" name="length-overlap-$option_id" value="1">
        Sequence length overlap (&le; 1; default: 1)
    </span>
</div>
HTML;
    if ($option_id == "opte") {
        $addl_html .= <<<HTML
<div>
    Minimum Sequence Length: <input type="text" class="small" id="min-seq-len-$option_id" name="min-seq-len-$option_id" value="">
</div>
<div>
    Maximum Sequence Length: <input type="text" class="small" id="max-seq-len-$option_id" name="max-seq-len-$option_id" value="">
</div>
<div>
    Do not demultiplex:
    <input type="checkbox" id="demux-$option_id" name="demux-$option_id" value="1">
    Check to prevent a demultiplex to expand cd-hit clusters (default: demultiplex)
</div>
HTML;
    }
    return $addl_html;
}


function add_ssn_calc_option($option_id) {
    $default_evalue = get_default_evalue();
    $html = <<<HTML
<h3>SSN Edge Calculation Option</h3>
<div>
    <span class="input-name">
        E-Value:
    </span><span class="input-field">
        <input type="text" class="small" id="evalue-$option_id" name="evalue-$option_id" size="5" value="$default_evalue">
        Negative log of e-value for all-by-all BLAST (&ge;1; default $default_evalue)
    </span>
    <div class="input-desc">
Input an alternate e-value for BLAST to calculate similarities/edge alignment 
scores similarities.  The default parameter ($default_evalue) is useful for most sequences.  
However, a larger e-value/smaller negative log should be used for short 
sequences or when low pairwise identities may be useful for separating 
functionally distinct SSN clusters.
    </div>
</div>
HTML;
    return array($html);
}


function add_extra_ram_option($option_id) {
    $html = <<<HTML
<div>
    <span class="input-name">
        Extra RAM:
    </span><span class="input-field">
        <input type="checkbox" id="$option_id-extra-ram" name="$option_id-extra-ram" class="extra-ram-cb" value="1">
        <label for="$option_id-extra-ram">Check to use additional RAM (700GB) [default: off, uses 70GB]</label>
        <br>
        Enter number of edges to manually specify required RAM:
        <input type="text" size="9" id="$option_id-extra-ram-edges" name="$option_id-extra-ram-edges" class="extra-ram-calc-ram-from-edges" data-dest-id="$option_id-extra-ram-val" value="">
        RAM: <input type="text" size="3" id="$option_id-extra-ram-val" name="$option_id-extra-ram-val" class="extra-ram-val" value="700">GB
    </span>
</div>
HTML;
    return $html;
}


function add_fragment_option($option_id) {
    $html = <<<HTML
<h3>Fragment Option</h3>
<div>
<div>
 UniProt designates a Sequence Status for each member: Complete if the encoding DNA sequence has both start and stop codons; Fragment if the start and/or stop codon is missing. Approximately 10% of the entries in UniProt are fragments. 
</div>
    <span class="input-name">
        Fragments:
    </span><span class="input-field">
        <input type="checkbox" id="exclude-fragments-$option_id" name="exclude-fragments-$option_id" value="1">
        <label for="exclude-fragments-$option_id">
            Check to exclude UniProt-defined fragments in the results.
            (default: off)
        </label>
    </span>
    <div class="input-desc">
<p>
For the UniRef90 and UniRef50 databases, clusters are excluded if the cluster 
ID ("representative sequence") is a fragment.   
</p>

<p>
UniProt IDs in UniRef90 and UniRef50 clusters with complete cluster IDs are 
removed from the clusters if they are fragments. 
</p>
    </div>
</div>
HTML;
    return array($html);
}


function add_blast_calc_desc() {
    $html = <<<HTML
            An all-by-all BLAST is performed to obtain the similarities between sequence pairs to
            calculate edge values to generate the SSN.
HTML;
    return array($html);
}


function add_domain_option($option_id, $specify_family = false, $use_advanced_options = false) {
    $specify_region = true;
    $option_text = $specify_family ? "Options" : "Option";
    $html = <<<HTML
<h3>Family Domain Boundary $option_text</h3>
<div>
    <div>
        Pfam and InterPro databases define domain boundaries for members of their families.
    </div>
    <div>
        <span class="input-name">
            Domain:
        </span><span class="input-field">
            <input type="checkbox" id="domain-$option_id" name="domain-$option_id" value="1" class="bigger">
            <label for="domain-$option_id">Sequences trimmed to the domain boundaries defined by the input family will be used for the calculations.</label>
        </span>
    </div>
HTML;
    if ($specify_family) {
        $html .= <<<HTML
    <div>
        <span class="input-name">
            Family:
        </span><span class="input-field">
            <input type="text" name="domain-family-$option_id" id="domain-family-$option_id" style="width: 100px" disabled />
            Use domain boundaries from the specified family (enter only one family).
        </span>
    </div>
HTML;
    }
    if ($specify_region) {
        $html .= <<<HTML
    <div>
        <span class="input-name">
            Region:
        </span><span class="input-field">
            <input type="radio" id="domain-region-nterminal-$option_id" name="domain-region-$option_id" value="nterminal" class="domain-region-$option_id">
            <label for="domain-region-nterminal-$option_id">N-Terminal</label>
            <input type="radio" id="domain-region-domain-$option_id" name="domain-region-$option_id" value="domain" class="domain-region-$option_id">
            <label for="domain-region-domain-$option_id">Domain [default]</label>
            <input type="radio" id="domain-region-cterminal-$option_id" name="domain-region-$option_id" value="cterminal" class="domain-region-$option_id">
            <label for="domain-region-cterminal-$option_id">C-Terminal</label>
        </span>
        <div class="input-desc">
HTML;
        if ($specify_family)
            $html .= "A specified InterPro family must be defined by a single database.\n";
        $html .= <<<HTML
            <i>N-terminal</i> will select the portion of the sequence that is N-terminal to the specified domain to generate the SSN.
            <i>C-terminal</i> will select the portion of the sequence that is C-terminal to the specified domain to generate the SSN.
            <i>Domain</i> will use the specified domain.
        </div>
    </div>
HTML;
    }
    $html .= <<<HTML
</div>
HTML;
    return array($html);
}


function add_family_input_option_family_only($option_id, $show_example = false, $show_desc_text = true) {
    return add_family_input_option_base($option_id, false, "", $show_example, $show_desc_text);
}


function add_family_input_option($option_id, $show_example = false, $show_desc_text = true) {
    list($frac) = get_fraction_html($option_id);
    return add_family_input_option_base($option_id, true, $frac, $show_example, $show_desc_text);
}



function add_family_input_option_base($option_id, $include_intro, $fraction_html, $show_example = false, $show_text = true) {
    $max_full_family = number_format(get_max_full_family_count(), 0);

    $html = "";
    if ($include_intro) {
        $option_text = $fraction_html ? "Options" : "Option";
        $html .= <<<HTML
<h3>Protein Family Addition $option_text</h3>
<div>
    <div>
        Add sequences belonging to Pfam and/or InterPro families to the sequences used to generate the SSN.
    </div>
    <div class="secondary-input">
        <div class="secondary-name">
            Familes:
        </div>
        <div class="secondary-field">
HTML;
    // Don't include intro
    } else {
        $html .= <<<HTML
<div>
    <div class="primary-input">
        <div class="secondary-name">
            Pfam and/or InterPro Families and/or Pfam clans:
        </div>
        <div>
HTML;
    }

    $example_fam = $show_example ? "IPR004184" : "";
    $html .= <<<HTML
            <input type="text" id="families-input-$option_id" name="families-input-$option_id" value="$example_fam">
        </div>
HTML;

    if ($include_intro) {
        $html .= <<<HTML
    </div>
    <div class="input-desc">
HTML;
    }

    $html .= <<<HTML
        <div>
            <input type="checkbox" id="use-uniref-$option_id" class="cb-use-uniref bigger" value="1">
            <label for="use-uniref-$option_id">Use <select id="uniref-ver-$option_id" name="uniref-ver-$option_id" class="bigger"><option value="90">UniRef90</option><option value="50">UniRef50</option></select> cluster ID sequences instead of UniProt IDs (UniProt is default).</label>
            <div style="margin-top: 10px">
HTML;
    $html .= make_pfam_size_box("family-size-container-$option_id", "family-count-table-$option_id", $show_example);
    $html .= <<<HTML
            </div>
        </div>
        <div>
            The input format is a single family or comma/space separated list of families.
            Families should be specified as PFxxxxx (five digits),
            IPRxxxxxx (six digits) or CLxxxx (four digits) for Pfam clans.
        </div>
    </div>
HTML;
    if (!$show_example && $show_text) {
        $html .= <<<HTML
    <div>
<p>
UniRef90 clusters contain UniProt IDs that share ≥90% sequence identity and 
have 80% overlap with the longest sequence in the cluster ("seed sequence"); as 
a result, the UniProt IDs in the cluster usually are functionally homogeneous, 
i.e., orthologues.  UniRef50 clusters contain UniProt IDs that share ≥50% 
sequence identity and have 80% overlap with the seed sequence; as a result, the 
UniProt IDs in the cluster often are functionally heterogeneous, e.g., 
paralogues.
</p>

<p>
The sequences from the UniRef90 and UniRef90 databases are the UniRef90 and 
UniRef50 clusters for which the cluster ID ("representative sequence") matches 
the specified families. The UniProt members in these UniRef90 and Uni/Ref50 
clusters that do not match the specified families are removed from the cluster.
</p>

<!--
        The EST provides access to the UniRef90 and UniRef50 databases to allow the creation
        of SSNs for very large Pfam and/or InterPro families. For families that contain 
        more than $max_full_family sequences, the SSN <b>will be</b> generated 
        using the UniRef50 or UniRef90 databases. In UniRef90, sequences that share &ge;90% sequence identity 
        over 80% of the sequence length are grouped together and represented by a 
        sequence known as the cluster ID. UniRef50 is similar except that the
        sequence identity is &ge;50%. If one of the UniRef databases is used,
        the output SSN is equivalent to a 90% (for UniRef90) or 50% (for UniRef50)
        Representative Node Network with each node corresponding to a UniRef cluster ID; in this
        case an additional node attribute is provided which lists all
        of the sequences represented by the UniRef node.
-->
    </div>
HTML;
    }
    $html .= <<<HTML
$fraction_html
</div>
HTML;
    return array($html);
}


function get_fraction_html($option_id) {
    $default_fraction = get_default_fraction();
    $html = <<<HTML
    <div>
        <span class="input-name">
            Fraction:
        </span><span class="input-field">
            <input type="text" class="small fraction" id="fraction-$option_id" name="fraction-$option_id" value="$default_fraction" size="5">
            <a class="question" title="Either fraction or UniRef can be used, not both.">?</a>
            Reduce the number of sequences used to a fraction of the full family size (&ge; 1; default:
            $default_fraction)
        </span>
        <div class="input-desc">
Selects every Nth sequence in the family; the sequences are assumed to be added 
randomly to UniProtKB, so the selected sequences are assumed to be a 
representative sampling of the family. This allows reduction of the size of the 
SSN.  Sequences in the family with SwissProt annotations will always be 
included; this may result in the size of the resulting data set being slightly 
larger than the fraction specified. 

<!--
            Selects every Nth sequence in the family; the sequences are assumed to be
            added randomly to UniProt, so the selected sequences are assumed to be a
            representative sampling of the family. This allows reduction of the size of the SSN.
            Sequences in the family with Swiss-Prot annotations will always be included;
            this may result in the size of the resulting data set being slightly larger than
            the fraction specified.
-->
        </div>
    </div>
HTML;
    return array($html);
}


function make_pfam_size_box($parentId, $tableId, $show_example = false) {
    $example = $show_example ? '<tr><td>IPR004184</td><td>Pro_racemase</td><td style="text-align: right;">20,232</td><td style="text-align: right;">6,029</td><td style="text-align: right;">1,379</td></tr><tr><td></td><td style="text-align: right;">Total:</td><td style="text-align: right;">13,269</td><td style="text-align: right;">4,853</td><td style="text-align: right;">676</td></tr><tr><td></td><td style="text-align: right; font-weight: bold;">Total Computed:</td><td style="text-align: right; font-weight: bold;">20,232</td><td></td><td></td></tr>' : "";
    $display = $show_example ? "" : "display:none";
    return <<<HTML
                <center>
                        <div style="width:85%;$display" id="$parentId">
                            <table border="0" width="100%" class="family">
                                <thead>
                                    <th>Family</th>
                                    <th>Family Name</th>
                                    <th>Full Size</th>
                                    <th id="$tableId-ur-hdr">UniRef90 Size</th>
                                    <th id="$tableId-ur-hdr">UniRef50 Size</th>
                                </thead>
                                <tbody id="$tableId">$example</tbody>
                            </table>
                        </div>
                </center>
HTML;
}


function add_taxonomy_filter($option_id, $text = "") {
    if (!$text) {
        $text = <<<TEXT
<p>
The user can select "Bacteria, Archaea, Fungi", "Eukaryota, no Fungi", or 
"Fungi" to restrict the retrieved sequences to these taxonomy groups.   
"Bacteria, Archaea, Fungi" and "Fungi" selects organisms that may provide 
genome context (gene clusters/operons) useful for inferring functions. 
</p>

<p>
Also, sequences retrieved from the UniProt, UniRef90, and UniRef50 databases 
can be restricted to taxonomic categories (Superkingdom, Kingdom, Phylum, 
Class, Order, Family, Genus, Species). Multiple conditions are combined to be a 
union of each other. 
</p>

<p>
The retrieved sequences from the UniRef90 and UniRef90 databases are the 
UniRef90 and UniRef50 clusters for which the cluster ID matches the specified 
taxonomic category.
</p>

<p>
The taxonomy filter is applied to the list of UniProt, UniRef90, or UniRef50 
cluster IDs that are identified in the BLAST.
</p>

TEXT;
    }
    $html = <<<HTML
<h3>Filter by Taxonomy</h3>
<div>
    <div>
        $text
    </div>
    <div>Preselected conditions:
        <select class="taxonomy-preselects" id="taxonomy-$option_id-select" data-option-id="$option_id">
            <option disabled selected value>-- select a preset to auto populate --</option>
        </select>
    </div>
    <div id="taxonomy-$option_id-container"></div>
    <div style="display: none">
        <input type="hidden" name="taxonomy-$option_id-preset-name" id="taxonomy-$option_id-preset-name" value="" />
    </div>
    <div>
        <button type="button" class="light add-tax-btn" data-option-id="$option_id" id="taxonomy-$option_id-add-btn">Add Taxonomy category</button>
        <!--<button type="button" class="light" onclick="appTF.addTaxCondition('$option_id')">Add taxonomic condition</button>-->
    </div>
</div>
HTML;
    return array($html);
}


function add_family_filter($option_id, $extra_text = "", $family_filter_post_text = "") {
    if ($extra_text)
        $extra_text = "<div>$extra_text</div>";
    $html = <<<HTML
<h3>Filter by Family</h3>
<div>
    $extra_text
    <div>Input a list of Pfam families, InterPro families, and/or Pfam clans to restrict the UniProt and/or UniRef IDs in the SSN to these families.</div>
    <div class="secondary-input">
        <div class="secondary-name">
            Family(s):
        </div>
        <div class="secondary-field">
            <input type="text" id="family-filter-$option_id" name="family-filter-$option_id" value="" />
        </div>
    </div>
    <div class="input-desc">
        <div>
            The input format is a single family or comma/space separated list of families. Families should be
            specified as PFxxxxx (five digits), IPRxxxxxx (six digits) or CLxxxx (four digits) for Pfam clans.
        </div>
    </div>
    <div>$family_filter_post_text</div>
</div>
HTML;
    return array($html);
}



