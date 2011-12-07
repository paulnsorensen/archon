<?php
/**
 * ResearchCart template
 *
 * The appointment form must contain at minimum an ArrivalDateString field
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

echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n<div id='researchcartwrapper'>\n");
$_ARCHON->Security->Session->ResearchCart->getCart();
if(!$_ARCHON->Security->Session->ResearchCart->getCartCount())
{
   echo("<div id='researchcart' class='mdround center'><strong>Your research materials cart is currently empty.</strong>");
   if($_ARCHON->Security->isAuthenticated())
   {
      echo("<strong> You may wish to add something to it before setting up an appointment.</strong><br/>");
      echo("<span style='font-size: x-small;'>Add materials by searching the collections and clicking the icons next to item names.<br/>Preparing a cart helps us to have your materials ready for you when you arrive for an appointment.</span>");
   }
   echo("<br/><br/></div>");
}
else
{
   echo("<div class='listitemhead bold'>Your cart holds:</div>");
}

research_displaycart();
?>
<div id="researchcartinfo" class="bground">
<?php
if(!$_ARCHON->Security->isAuthenticated())
{
?>
   <span class='bold'><a href="?p=core/register">Register an account to set up a research appointment</a></span>
   <br/><br/>
   <span style="font-size:90%;">Registering an account and submitting research requests through this system helps us to have your materials ready for you before you arrive for your appointment.</span><br/><br/>
   <span style="font-size:80%;">(If you already have an account, please login below.)</span>

<?php
}
else
{
   $arrAppointmentPurposes = $_ARCHON->getAllResearchAppointmentPurposes();

   $arrRepositories = $_ARCHON->Security->Session->ResearchCart->getCartRepositories();

   if($_REQUEST['arrivaltimestamp'])
   {
      $_REQUEST['arrivaldatestring'] = date(CONFIG_CORE_DATE_FORMAT, $_REQUEST['arrivaldatestring']);
   }

   if($_REQUEST['departuretimestamp'])
   {
      $_REQUEST['departuredatestring'] = date(CONFIG_CORE_DATE_FORMAT, $_REQUEST['departuredatestring']);
   }
?>
   <div class='userformbox mdround'>
      <p class="center bold">
         Make An Appointment
      </p>
      <div class="userformpair">
         <div class="userformlabel"><label for="RepositoryIDField">Repository:</label></div>
         <div class="userforminput">
            
<?php
   if(!empty($arrRepositories))
   {
      if(count($arrRepositories) > 1)
      {

         ?>
            <select id="RepositoryIDField" name="RepositoryID">
               <option value="0">(Select One)</option>
               <?

      foreach($arrRepositories as $objRepository)
      {
         $selected = $objRepository->ID == $_REQUEST['repositoryid'] ? 'selected' : '';

         echo("        <option value=\"$objRepository->ID\" $selected>" . $objRepository->toString() . "</option>");
      }
      ?>
                           </select>
<?php
   }
   else
   {
      $objRepository = current($arrRepositories);
      ?>
            <input type="hidden" name="RepositoryID" id="RepositoryIDField" value="<?php echo($objRepository->ID); ?>" />
            <?php echo($objRepository->toString()); ?>
      <?php
   }
   }
?>
         </div>
      </div>
      <div class="userformpair">
         <div class="userformlabel"><label for="ArrivalDateStringField">Date/Time of Arrival <span class='userformdate'>(eg. 4/30/<?php echo(date('Y')); ?> 10:00 AM)</span>:</label></div>
         <div class="userforminput"><input type="text" size="40" id="ArrivalDateStringField" name="ArrivalDateString" value="<?php echo(encode($_REQUEST['arrivaldatestring'], ENCODE_HTML)); ?>" /></div>
      </div>
      <div class="userformpair">
         <div class="userformlabel"><label for="DepartureDateStringField">Estimated Date/Time of Departure <span class='userformdate'>(eg. 4/30/<?php echo(date('Y')); ?> 1:30 PM)</span>:</label></div>
         <div class="userforminput"><input type="text" size="40" id="DepartureDateStringField" name="DepartureDateString" value="<?php echo(encode($_REQUEST['departuredatestring'], ENCODE_HTML)); ?>" /></div>
      </div>
      <div class="userformpair">
         <div class="userformlabel"><label for="PurposeField">Purpose:</label></div>
         <div class="userforminput">
            <select id="PurposeField" name="AppointmentPurposeID">
               <option value="0">(Select One)</option>
<?php
               if(!empty($arrAppointmentPurposes))
               {
                  foreach($arrAppointmentPurposes as $objAppointmentPurpose)
                  {
                     $selected = $objAppointmentPurpose->ID == $_REQUEST['appointmentpurposeid'] ? 'selected' : '';

                     echo("        <option value=\"$objAppointmentPurpose->ID\" $selected>" . $objAppointmentPurpose->toString() . "</option>");
                  }
               }
?>
            </select>
         </div>
      </div>
      <div class="userformpair">
         <div class="userformlabel"><label for="TopicField">Topic of Research:</label></div>
         <div class="userforminput"><input type="text" size="40" maxlength="100" id="TopicField" name="Topic" value="<?php echo(encode($_REQUEST['topic'], ENCODE_HTML)); ?>" /></div>
      </div>
      <div class="userformpair">
         <div class="userformlabel"><label for="ResearcherCommentsField">Additional Comments for the Archivist:</label></div>
         <div class="userforminput"><textarea id="ResearcherCommentsField" name="ResearcherComments" cols="33" rows="5"><?php echo(encode($_REQUEST['researchercomments'], ENCODE_HTML)); ?></textarea></div>
      </div>
      <div id="userformsubmit">
         <input type="submit" value="Next" class="button" />
      </div>
   </div>
</div>
<script type="text/javascript">
   $(function(){
      var repoid = $('#RepositoryIDField');

      if(repoid.val() != 0){
         $('.repogrp').hide();
         $('#repo' + repoid.val()).fadeIn();
      }

      repoid.bind('change',function(){
         if(repoid.val() != 0){
            $('.repogrp').hide();
            $('#repo' + repoid.val()).fadeIn();
         }else{
            $('.repogrp').fadeIn();
         }
      });
   })
</script>
<?php
            }
            echo("</div>\n");
?>
</div>
