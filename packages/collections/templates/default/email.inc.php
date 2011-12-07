<?php
/**
 * ResearchEmail template
 *
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
?>


<div class="userformbox bground">

  <p class="bold"><?php echo($_ARCHON->PublicInterface->Title); ?></p>
      Fields marked with an asterisk (<span style="color:red">*</span>) are required.
      
  <div class="userformpair">
    <label for="name"><?php echo($strFromName); ?>:</label><br/>
    <input type="text" name="FromName" id="name" size="30" value="<?php echo($strName); ?>" />
  </div>
  <div class="userformpair">
    <label for="email"><span style="color:red">*</span> <?php echo($strFromAddress); ?>:</label><br/>
    <input type="text" name="FromAddress" id="email" size="25" value="<?php echo($strFrom); ?>" />
  </div>
  <div class="userformpair">
    <label for="phone"><?php echo($strFromPhone); ?>:</label><br/>
    <input type="text" name="FromPhone" id="phone" size="20" value="<?php echo($strPhone); ?>" />
  </div>
  <div class="userformpair">
    <label for="subject"><?php echo($strSubject); ?>:</label><br/>
    <input type="text" name="subject" id="subject" size="40" value="<?php echo(encode($_REQUEST['subject'], ENCODE_HTML)); ?>" />
  </div>
  <div class="userformpair">
    <label for="message"><span style="color:red">*</span> <?php echo($strMessage); ?>:</label><br/>
    <textarea name="message" id="message" cols="38" rows="5"><?php echo(encode($_REQUEST['message'], ENCODE_HTML)); ?></textarea>
  </div>
  <div id="userformsubmit">
    <input type="submit" value="<?php echo($strSendEmail); ?>" class="button" />
  </div>
  
</div>

<?php
    $_ARCHON->Security->Session->ResearchCart->getCart();
    if($_ARCHON->Security->Session->ResearchCart->getCartCount())
    {
?>
<br/><div class='listitemhead bold'><?php echo($strCartAppend); ?></div>
<?php
        research_displaycart();
    }
    

?>
