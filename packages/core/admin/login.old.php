<?php
/**
 * Login document for the administrative interface
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

$AdminPhrasePhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Administrative Phrase');

$objLoginSuccessfulPhrase = Phrase::getPhrase('login_loginsuccessful', PACKAGE_CORE, MODULE_NONE, $AdminPhrasePhraseTypeID);
$strLoginSuccessful = $objLoginSuccessfulPhrase ? $objLoginSuccessfulPhrase->getPhraseValue(ENCODE_HTML) : 'Login Successful';

$objContinuePhrase = Phrase::getPhrase('continue', PACKAGE_CORE, MODULE_NONE, $AdminPhrasePhraseTypeID);
$strContinue = $objContinuePhrase ? $objContinuePhrase->getPhraseValue(ENCODE_HTML) : 'Continue';

$objLoginPhrase = Phrase::getPhrase('login_login', PACKAGE_CORE, MODULE_NONE, $AdminPhrasePhraseTypeID);
$strLogin = $objLoginPhrase ? $objLoginPhrase->getPhraseValue(ENCODE_HTML) : 'Login';
$objPasswordPhrase = Phrase::getPhrase('login_password', PACKAGE_CORE, MODULE_NONE, $AdminPhrasePhraseTypeID);
$strPassword = $objPasswordPhrase ? $objPasswordPhrase->getPhraseValue(ENCODE_HTML) : 'Password';
$objRememberMePhrase = Phrase::getPhrase('login_rememberme', PACKAGE_CORE, MODULE_NONE, $AdminPhrasePhraseTypeID);
$strRememberMe = $objRememberMePhrase ? $objRememberMePhrase->getPhraseValue(ENCODE_HTML) : 'Remember me?';

$objLoginButtonPhrase = Phrase::getPhrase('login_loginbutton', PACKAGE_CORE, MODULE_NONE, $AdminPhrasePhraseTypeID);
$strLoginButton = $objLoginButtonPhrase ? $objLoginButtonPhrase->getPhraseValue(ENCODE_HTML) : 'Log in';

if($_ARCHON->Security->isAuthenticated())
{
    $go = $_REQUEST['go'] ? $_REQUEST['go'] : '?p=' . $_REQUEST['p'];
    $go = str_replace('f=logout', '', $go);
    
    if(!$_ARCHON->Security->userHasAdministrativeAccess())
    {
        $go = encoding_substr_count($go, 'admin') ? '?p=' : $go;
    }
    
    if(encoding_substr_count($go, 'admin/core/login'))
    {
        $go = '?p=';
    }

    $_ARCHON->AdministrativeInterface->Header->OnLoad = "setTimeout('location.href=\'" . encode($go, ENCODE_JAVASCRIPT) . "\';', 2000);";
    
    $objWelcomePhrase = Phrase::getPhrase('login_welcome', PACKAGE_CORE, MODULE_NONE, $AdminPhrasePhraseTypeID);
    $strWelcome = $objWelcomePhrase ? $objWelcomePhrase->getPhraseValue(ENCODE_HTML) : 'Welcome, $1!';
    $strWelcome = str_replace('$1', encode($_ARCHON->Security->Session->User->toString(), ENCODE_HTML), $strWelcome);

    include_once("header.inc.php");
?>
<center>
<br>
<table width="200" border="0" cellpadding="0" cellspacing="0">
  <tr class="header">
    <td class="header">
      <?php echo($strLoginSuccessful); ?>
    </td>
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr>
    <td>
      <center>
      <?php echo($strWelcome); ?>
      </center>
    </td>
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr class="button">
    <td>
      <center>
      <input type=button value="<?php echo($strContinue); ?>" class="button" onClick="location.href='?go=<?php echo(urlencode($go)); ?>';">
      </center>
    </td>
  </tr>
</table>
</center>
</html>
<?php
}
else
{
    if($_ARCHON->Security->Disabled)
    {
        return;
    }

    $sourcefile = getenv("SCRIPT_NAME");

    $_ARCHON->AdministrativeInterface->Header->OnLoad = "if(top.frames.length != 0) { parent.location = '?security_action=logout&go=" . urlencode($_SERVER['REQUEST_URI']) . "'; } document.forms.login.ArchonLogin.focus();";

    include_once("header.inc.php");

    if(!$_REQUEST['go'] && $_REQUEST['p'] != 'admin/core/login')
    {
        $go = $_SERVER['REQUEST_URI'];
    }
    else if($_REQUEST['go'])
    {
        $go = $_REQUEST['go'];
    }
    else
    {
        $go = '?p=';
    }
?>
<center>
<?php echo(CONFIG_CORE_LOGIN_BANNER); ?>
<br><br>
<b><?php echo($_ARCHON->Error); ?></b>
<form method="POST" action="index.php" name="login" accept-charset="UTF-8">
<input type=hidden name="p" value="admin/core/login">
<input type=hidden name="go" value="<?php echo(encode($go, ENCODE_HTML)); ?>">
<table>
  <tr>
    <td>
      <?php echo($strLogin); ?>:
    </td>
    <td>
      <input type="text" name="ArchonLogin" size=15>
    </td>
  </tr>
  <tr>
    <td>
      <?php echo($strPassword); ?>:
    </td>
    <td>
      <input type="password" name="ArchonPassword" size=15 class="password">
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>
      <input type=checkbox name="RememberMe" value="1"> <?php echo($strRememberMe); ?>
    </td>
  </tr>
  <tr>
    <td class="center" colspan="2">
      <input type="submit" value="<?php echo($strLoginButton); ?>" class="button">
    </td>
  </tr>
</table>
</form>
<br>
<?php echo("{$_ARCHON->getString('ProductName')} {$_ARCHON->Packages['core']->getString('DBVersion')}"); ?>
</center>
</body>
</html>
<?php
}
?>