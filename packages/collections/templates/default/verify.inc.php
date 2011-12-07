<?php
/**
 * Verify template (for research carts)
 *
 * The appointment form must contain at minimum an ArrivalTime field
 *
 * The Archon API is available through the variable:
 *
 *  $_ARCHON
 *
 * Refer to the Archon class definition in lib/archon.inc.php
 * for available properties and methods.
 *
 * @package Archon
 * @author Kyle Fox
 */

isset($_ARCHON) or die();

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");
research_displaycart();
?>
<div class='userformbox bground'>
<input type="hidden" name="ArrivalTime" value="<?php echo($ArrivalTimestamp); ?>" />
<input type="hidden" name="DepartureTime" value="<?php echo($DepartureTimestamp); ?>" />
<input type="hidden" name="AppointmentPurposeID" value="<?php echo($objAppointmentPurpose->ID); ?>" />
<input type="hidden" name="Topic" value="<?php echo(encode($_REQUEST['topic'], ENCODE_HTML)); ?>" />
<textarea name="ResearcherComments" style="display: none;"><?php echo(encode($_REQUEST['researchercomments'], ENCODE_HTML)); ?></textarea>
  <p class="center">
    <span class="bold">Verify Your Appointment</span><br/>
    (To make changes, click Back in your browser.)
  </p>
  <div class="userformpair">
    <div class="userformlabel">Date/Time of Arrival:</div>
    <div class="userforminput"><?php echo(date(CONFIG_CORE_DATE_FORMAT, $ArrivalTimestamp)); ?></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel">Estimated Date/Time of Departure:</div>
    <div class="userforminput"><?php if($DepartureTimestamp) { echo(date(CONFIG_CORE_DATE_FORMAT, $DepartureTimestamp)); } else { echo("Unspecified"); } ?></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel">Purpose:</div>
    <div class="userforminput"><?php if($objAppointmentPurpose->ID) { echo($objAppointmentPurpose->toString()); } ?></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel">Topic of Research:</div>
    <div class="userforminput"><?php echo(encode($_REQUEST['topic'], ENCODE_HTML)); ?></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel">Additional Comments for the Archivist:</div>
    <div class="userforminput"><?php echo(nl2br(encode($_REQUEST['researchercomments'], ENCODE_HTML))); ?></div>
  </div>
  <div id="userformsubmit">
    <input type="submit" value="Finalize Appointment Request" class="button">
  </div>
</div>
<script type="text/javascript">
   $(function(){
      var repoid = $('#RepositoryIDField');

      if(repoid.val() != 0){
         $('.repogrp').hide();
         $('#repo' + repoid.val()).fadeIn();
      }
   });
</script>