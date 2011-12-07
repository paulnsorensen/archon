<?php
/**
 * Footer file for default theme
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();
?>

</div>
<div id="bottom">
    <br/>
    <hr id="footerhr" />
    <div id="userbox" class="smround">
<?php
if($_ARCHON->Security->isAuthenticated())
{
    $logoutURI = preg_replace('/(&|\\?)f=([\\w])*/', '', $_SERVER['REQUEST_URI']);
    $Logout = (encoding_strpos($logoutURI, '?') !== false) ? '&amp;f=logout' : '?f=logout';
?>
        <div id="userinfo">
            You are logged in as <?php echo($_ARCHON->Security->Session->User->toString()); ?>.<br/>
<?php
	if($_ARCHON->Security->userHasAdministrativeAccess())
	{
	    echo("<a href='?p=admin' rel='external'>Admin</a>&nbsp;");
	}
    else
    {
        echo ("<a href='?p=core/account'>My Account</a>");

    }
?>
            <a href='<?php echo(encode($logoutURI, ENCODE_HTML) . $Logout); ?>'>Log Out</a>
        </div>
<?php
}
elseif($_ARCHON->config->ForceHTTPS)
{
   echo("<a href='index.php?p=core/login&amp;go='>Log In</a>");
}
else
{
?>

<div id="userlogincontrols">
  <a id="loginlink" href="index.php?p=admin/core/login&amp;go=" onclick="if($('#userlogin').is(':visible')) {this.innerHTML = 'Log In';} else {this.innerHTML = 'Hide';} $('#userlogin').slideToggle('normal'); return false;">Log In</a>
  <a href="?p=core/register">Register</a>
</div>
<div id="userlogin" style="display:none">&nbsp;
    <form action="<?php echo(encode($_SERVER['REQUEST_URI'], ENCODE_HTML)); ?>" accept-charset="UTF-8" method="post">
    <div class='loginpair'>
    	<div class='loginlabel'><label for="ArchonLoginField">Login:</label></div>
      	<div class='logininput'><input id="ArchonLoginField" type="text" name="ArchonLogin" size="20" tabindex="400" /></div>
    </div>
    <div class='loginpair'>
      <div class='loginlabel'><label for="ArchonPasswordField">Password:</label></div>
      <div class='logininput'><input id="ArchonPasswordField" type="password" name="ArchonPassword" size="20" tabindex="500" /></div>
    </div>
      <div id='loginsubmit'>
	      <input type="submit" value="Log in" class="button" tabindex="700" />&nbsp;&nbsp;<label for="RememberMeField"><input id="RememberMeField" type="checkbox" name="RememberMe" value="1" tabindex="600" />Remember me?</label>
	  </div>
    </form>
</div>
<?php
}
?>
  </div>
<?php

    echo("<div id='contactcontainer'>");

    if($_ARCHON->Repository->URL)
    {
        echo("<div id='repositorylink'><a href='{$_ARCHON->Repository->getString('URL')}'>{$_ARCHON->Repository->getString('Name')}</a></div>\n");
    }

    if($_ARCHON->Repository->Email && !$_ARCHON->Security->userHasAdministrativeAccess())
    {
        echo("<div id='emaillink'>Contact Us: <a href='?p=core/contact&amp;f=email&amp;referer=" . urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "'>Email Form</a></div>\n");
    }

    echo("</div>");

?>
</div>
