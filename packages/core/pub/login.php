<?php
/**
 * Login form
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

if ($_ARCHON->config->ForceHTTPS && !$_ARCHON->Security->Session->isSecureConnection())
{
   die('<html><body onLoad="location.href=\'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '\';"></body></html>');
}

$go = $_REQUEST['go'] ? $_REQUEST['go'] : '';
$go = str_replace('f=logout', '', $go);

if($_ARCHON->Security->isAuthenticated())
{
    header("Location: ?p=$go");
}

$PublicPhrasePhraseInputTypeID = $_ARCHON->getPhraseTypeIDFromString('Public Phrase');

$objLoginTitlePhrase = Phrase::getPhrase('login_title', PACKAGE_CORE, 0, $PublicPhrasePhraseInputTypeID);
$strLoginTitle = $objLoginTitlePhrase ? $objLoginTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Login or Register an Account';

$_ARCHON->PublicInterface->Title = $strLoginTitle;
$_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title);

require_once("header.inc.php");

$objSelectOnePhrase = Phrase::getPhrase('register_selectone', PACKAGE_CORE, 0, $PublicPhrasePhraseInputTypeID);
$strSelectOne = $objSelectOnePhrase ? $objSelectOnePhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';

$objLoginPhrase = Phrase::getPhrase('login_login', PACKAGE_CORE, 0, $PublicPhrasePhraseInputTypeID);
$strLogin = $objLoginPhrase ? $objLoginPhrase->getPhraseValue(ENCODE_HTML) : 'Login';
$objPasswordPhrase = Phrase::getPhrase('login_password', PACKAGE_CORE, 0, $PublicPhrasePhraseInputTypeID);
$strPassword = $objPasswordPhrase ? $objPasswordPhrase->getPhraseValue(ENCODE_HTML) : 'Password';
$objRememberMePhrase = Phrase::getPhrase('login_rememberme', PACKAGE_CORE, 0, $PublicPhrasePhraseInputTypeID);
$strRememberMe = $objRememberMePhrase ? $objRememberMePhrase->getPhraseValue(ENCODE_HTML) : 'Remember Me';

?>
<h1 id="titleheader"><?php echo(strip_tags($_ARCHON->PublicInterface->Title)); ?></h1>
<center>
<input type="button" value="Register an Account" onClick="location.href='?p=core/register&amp;go=<?php echo($go); ?>';">
<br />
<br />
OR
<br />
<br />
</center>
<form action="index.php" accept-charset="UTF-8" method="post">
<div class='researchformbox bground'>
<input type="hidden" name="p" value="<?php echo($_REQUEST['p']); ?>" />
  <div class="userformpair">
    <div class="userformlabel"><label for="ArchonLoginField"><?php echo($strLogin); ?>:</label></div>
    <div class="userforminput"><input type="text" id="ArchonLoginField" name="ArchonLogin" value="<?php echo($_REQUEST['login']); ?>" maxlength="50" /></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel"><label for="ArchonPasswordField"><?php echo($strPassword); ?>:</label></div>
    <div class="userforminput"><input type="password" id="ArchonPasswordField" name="ArchonPassword" /></div>
  </div>
  
  <div class="userformpair">
    <div class="userformlabel"><label for="RememberMeField"><?php echo($strRememberMe); ?>:</label></div>
    <div class="userforminput"><input type=checkbox name="RememberMe" value="1"></div>
  </div>
  <div id="userformsubmit">
    <input type="submit" value="<?php echo($strLogin); ?>" class="button" />
  </div>
<br/>
<p class="center"><a href="?p=core/privacy"><?php echo($strPrivacyNote); ?></a></p>
</div>
</form>
<?php
require_once("footer.inc.php");
?>