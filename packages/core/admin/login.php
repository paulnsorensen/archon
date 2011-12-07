<?php
isset($_ARCHON) or die();

if ($_ARCHON->config->ForceHTTPS && !$_ARCHON->Security->Session->isSecureConnection())
{
   die('<html><body onLoad="location.href=\'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '\';"></body></html>');
}

$objContinuePhrase = Phrase::getPhrase('continue', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
$strContinue = $objContinuePhrase ? $objContinuePhrase->getPhraseValue(ENCODE_HTML) : 'Continue';

$objLoginPhrase = Phrase::getPhrase('login_login', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
$strLogin = $objLoginPhrase ? $objLoginPhrase->getPhraseValue(ENCODE_HTML) : 'Login';
$objPasswordPhrase = Phrase::getPhrase('login_password', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
$strPassword = $objPasswordPhrase ? $objPasswordPhrase->getPhraseValue(ENCODE_HTML) : 'Password';
$objRememberMePhrase = Phrase::getPhrase('login_rememberme', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
$strRememberMe = $objRememberMePhrase ? $objRememberMePhrase->getPhraseValue(ENCODE_HTML) : 'Remember me?';

$objLoginButtonPhrase = Phrase::getPhrase('login_loginbutton', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
$strLoginButton = $objLoginButtonPhrase ? $objLoginButtonPhrase->getPhraseValue(ENCODE_HTML) : 'Log in';

if ($_ARCHON->Security->isAuthenticated())
{

   $objWelcomePhrase = Phrase::getPhrase('login_welcome', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
   $strWelcome = $objWelcomePhrase ? $objWelcomePhrase->getPhraseValue(ENCODE_HTML) : 'Welcome, $1!';
   $strWelcome = str_replace('$1', encode($_ARCHON->Security->Session->User->toString(), ENCODE_HTML), $strWelcome);

   include_once("header.inc.php");
?>
   <div id="adminlogin" class="rounded-all">
      <div id="adminloginbanner"> <?php echo($strWelcome); ?></div>
      <div class="center"><input type=button value="<?php echo($strContinue); ?>" class="button" onclick="location.href='?p=<?php echo($go); ?>';" /></div>
   </div>





<?php
} else
{
   if ($_ARCHON->Security->Disabled)
   {
      return;
   }

   include_once("header.inc.php");

?>

   <div id="adminlogin" class="rounded-all">
      <div id="adminloginbanner"> <?php echo(CONFIG_CORE_LOGIN_BANNER); ?></div>
      <div id="adminloginerror"><?php echo($_ARCHON->Error); ?></div>
      <form method="post" action="index.php" name="login" accept-charset="UTF-8">
         <input type="hidden" name="p" value="<?php echo($_REQUEST['p']); ?>" />
         <input type="hidden" name="id" value="<?php echo($_REQUEST['id']); ?>" />

         <table>
            <tr>
               <td><label for="input-archonlogin"><?php echo($strLogin); ?></label></td>
               <td><input id ="input-archonlogin" type="text" name="ArchonLogin" size="18" /></td>
            </tr>
            <tr>
               <td><label for="input-archonpassword"><?php echo($strPassword); ?></label></td>
               <td><input id="input-archonpassword" type="password" name="ArchonPassword" size="18" class="password" /></td>
            </tr>
            <tr>
               <td></td><td><input type="submit" value="<?php echo($strLoginButton); ?>" class="button" />
                  <input id="input-rememberme" type="checkbox" name="RememberMe" value="1" /><label for="input-rememberme"> <?php echo($strRememberMe); ?></label></td>
            </tr>
         </table>
      </form>
   </div>
<?php
}
include_once('footer.inc.php');
?>
