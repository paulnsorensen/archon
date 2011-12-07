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
   <hr id="footerhr" />
   <div id="userbox">
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
            ?>
         <a href='<?php echo(encode($logoutURI, ENCODE_HTML) . $Logout); ?>'>Log Out</a>
      </div>
         <?php
      }
      else
      {
         ?>
      <!--<div id="userlogincontrols"><a href="index.php?p=admin/core/login&amp;go=" onclick="if($('xyz').visible()) {Effect.BlindUp('xyz',{duration: 0.8}); $(this).innerHTML = 'Log In';} else {Effect.BlindDown('xyz',{duration: 0.8}); $(this).innerHTML = 'Hide';} return false;">Log In</a>-->
      <div id="userlogincontrols"><a id="loginlink" href="index.php?p=admin/core/login&amp;go=" onclick="if($('#userlogin').is(':visible')) {this.innerHTML = 'Log In (Staff)';} else {this.innerHTML = 'Hide';} $('#userlogin').slideToggle('normal'); return false;">Log In (Staff)</a>
      </div>
      <div id="userlogin" class="mdround" style="display:none">&nbsp;
         <form action="<?php echo(encode($_SERVER['REQUEST_URI'], ENCODE_HTML)); ?>" accept-charset="UTF-8" method="post">
            <div class='loginpair'>
               <div class='loginlabel'><label for="ArchonLoginField">Login/E-mail:</label></div>
               <div class='logininput'><input id="ArchonLoginField" type="text" name="ArchonLogin" size="20" tabindex="900" /></div>
            </div>
            <div class='loginpair'>
               <div class='loginlabel'><label for="ArchonPasswordField">Password:</label></div>
               <div class='logininput'><input id="ArchonPasswordField" type="password" name="ArchonPassword" size="20" tabindex="1000" /></div>
            </div>
            <div id='loginsubmit'>
               <input type="submit" value="Log In" class="button" tabindex="1100" />&nbsp;&nbsp;<label for="RememberMeField"><input id="RememberMeField" type="checkbox" name="RememberMe" value="1" tabindex="1200" />Remember me</label>
            </div>
            <div id='registerlink'>
               <a href="?p=core/register" tabindex="800">Register an Account</a>

            </div>
         </form>
      </div>
         <?php
      }
      ?>
   </div>
   <?php
   if(defined('PACKAGE_COLLECTIONS'))
   {
      echo("<div id='contactcontainer'>");

      echo ("<div><a href='http://www.illinois.edu' ><img src='". $_ARCHON->PublicInterface->ImagePath ."/logo.gif' alt='Illinios I-Mark Logo'/></a> </div>");

      if($_ARCHON->Repository->URL)
      {
         echo("<div id='repositorylink'><a href='http://www.illinois.edu'>University of Illinois at Urbana-Champaign</a><br/><br/><a href='http://www.library.uiuc.edu/archives/'>University of Illinois Archives</a><br/><a href='{$_ARCHON->Repository->getString('URL')}'>{$_ARCHON->Repository->getString('Name')}</a></div>\n");
      }

      echo("<div id='emaillink'>Contact Us: <a href='http://library.uiuc.edu/archives/email-ahx.php'>Email Form</a></div>\n");

      echo("</div>");
   }

   ?>
</div>