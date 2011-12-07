<?php
/**
 * Header file for all administrative output documents
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox, Paul Sorensen
 */
isset($_ARCHON) or die();

$_ARCHON->AdministrativeInterface->Header->NoControls = $_ARCHON->AdministrativeInterface->Header->NoControls || $_REQUEST['nocontrols'];

header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE html
   PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" >
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <title><?php echo($_ARCHON->ProductName); ?> <?php echo($_ARCHON->Version); ?> Administrative Interface</title>
      <link rel="stylesheet" href="adminthemes/<?php echo($_ARCHON->AdministrativeInterface->Theme); ?>/ui-theme/jquery-ui.css" type="text/css" media="screen" />
      <link rel="stylesheet" type="text/css" href="adminthemes/<?php echo($_ARCHON->AdministrativeInterface->Theme); ?>/style.css" />
      <link href="adminthemes/<?php echo($_ARCHON->AdministrativeInterface->Theme); ?>/dynatree/ui.dynatree.css" rel="stylesheet" type="text/css" />
      <link href="adminthemes/<?php echo($_ARCHON->AdministrativeInterface->Theme); ?>/dynatree/contextmenu/jquery.contextMenu.css" rel="stylesheet" type="text/css" />
      <link href="adminthemes/<?php echo($_ARCHON->AdministrativeInterface->Theme); ?>/jgrowl/jquery.jgrowl.css" rel="stylesheet" type="text/css" />
      
      <?php echo($_ARCHON->getJavascriptTags('jquery.min')); ?>
      <script type="text/javascript" src="packages/core/js/jquery.bgiframe.js"></script>
      <?php echo($_ARCHON->getJavascriptTags('jquery-ui.custom.min')); ?>


      <script type="text/javascript">
         /* <![CDATA[ */

<?php
      if($_ARCHON->AdministrativeInterface->Redirect)
      {
         echo("location.href='index.php?p={$_ARCHON->AdministrativeInterface->Redirect}';");
      }
?>
   imagePath = '<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>';
   request_p = '<?php echo($_REQUEST['p']); ?>';
   request_f = '<?php echo($_REQUEST['f']); ?>';
   descriptionID = <?php echo($_ARCHON->getPhraseTypeIDFromString('Description')); ?>;
   escapeXML = <?php echo(bool(CONFIG_ESCAPE_XML)); ?>;
   permissionsRead = <?php echo(bool($_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, READ))); ?>;
   permissionsAdd = <?php echo(bool($_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, ADD))); ?>;
   permissionsUpdate = <?php echo(bool($_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, UPDATE))); ?>;
   permissionsDelete = <?php echo(bool($_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, DELETE))); ?>;
   permissionsFullControl = <?php echo(bool($_ARCHON->Security->verifyPermissions($_ARCHON->Module->ID, FULL_CONTROL))); ?>;
   dialogCallback = null;
   submitCallback = null;
   advSelectID = null;
   dialog_p = null;
   dialog_f = null;
   /* ]]> */
      </script>



      <?php
      echo($_ARCHON->AdministrativeInterface->HeaderHTML);

      if(!$_ARCHON->AdministrativeInterface->Header->NoControls)
      {
         // Place special scripts here.
      }

      /**
       * Note: $_ARCHON->AdministrativeInterface->Header->[Onload and OnUnload] are depreciated. Use jQuery
       * event functions instead!
       */
      ?>
   </head>
   <body>
      <?php
      if(!$_ARCHON->AdministrativeInterface->Header->NoControls)
      {
         $objLogOutPhrase = Phrase::getPhrase('logout', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
         $strLogOut = $objLogOutPhrase ? $objLogOutPhrase->getPhraseValue(ENCODE_HTML) : 'Log Out';

         $packagetoggledclass = $_ARCHON->Security->Session->getRemoteVariable('pinpackages') ? ' packagetoggled' : '';

         $packagetogglesource = $_ARCHON->AdministrativeInterface->ImagePath;
         $packagetogglesource .= $_ARCHON->Security->Session->getRemoteVariable('pinpackages') ? '/packageclose.gif' : '/packageopen.gif';

         $packagepinsource = $_ARCHON->AdministrativeInterface->ImagePath;
         $packagepinsource .= $_ARCHON->Security->Session->getRemoteVariable('pinpackages') ? '/locked.gif' : '/unlocked.gif';
      ?>
         <div id="advancedhelp"><div id="advhelploadingscreen"></div><div id="advhelpcontent"></div></div>
         <div id="response" title="Admin Response"></div>
         <div id="background-wrap">
            <div id="page-wrap">
               <div id="inside">
                  <div id="header">
                     <div id="logobox"><a href="index.php?p=admin"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/logo.png" alt="Archon Logo" /></a></div>
                     <div id="infobox">
                        <div id="sessioninfo">
                           <span id="productinfo"><?php echo($_ARCHON->ProductName); ?> <?php
         echo($_ARCHON->Version);
         if($_ARCHON->Revision)
         {
            echo(" rev-" . $_ARCHON->Revision);
         }
      ?></span>
                        <span id="logininfo">
                           <?php
                           if($_ARCHON->Security->Session->User)
                           {
                           ?>
                              <strong>
                              <?php echo($_ARCHON->Security->Session->User->getString('DisplayName')); ?>
                           </strong>
                           |
                           <a href="index.php?f=logout"><?php echo($strLogOut); ?></a>
                           <?php
                           }
                           ?>

                        </span>
                     </div>                    
                  </div>
               </div>
               <div id="columns">
                  <div id="loadingscreen"></div>
                  <div id="packagelist">
                     <a id="packagepin" onclick="admin_ui_pinpackages();"><img src="<?php echo($packagepinsource); ?>" alt="pin packages" /></a>
                     <div id="packageaccordion">
                        <?php
                           $_ARCHON->loadModulePhrases();
                           $arrModules = $_ARCHON->getAllModules();

                           // Do core package first.
                        ?>
                           <div class="ui-accordion-group">
                              <h3 class="package-header <?php echo($_ARCHON->Packages[PACKAGE_CORE]->APRCode); ?>"><a><?php echo($_ARCHON->Packages[PACKAGE_CORE]->toString()); ?></a></h3>
                              <div class="module-list">
                                 <ul style="list-style-type:none">
                                 <?php
                                 foreach($arrModules as $objModule)
                                 {
                                    if($_ARCHON->Security->verifyPermissions($objModule->ID, READ) && $objModule->PackageID == PACKAGE_CORE)
                                    {
                                       $page = "admin/{$objModule->Package->APRCode}/{$objModule->Script}";
                                       $class = ($_REQUEST['p'] == $page) ? " class='active'" : '';
                                       echo("<li><a{$class} href='index.php?p=admin/{$objModule->Package->APRCode}/$objModule->Script'>{$objModule->toString()}</a></li>\n");
                                    }
                                 }
                                 $prevpackageid = 0;
                                 foreach($arrModules as $objModule)
                                 {
                                    // Skip modules we can't read or that are in the core.
                                    if($_ARCHON->Security->verifyPermissions($objModule->ID, READ) && $objModule->PackageID != PACKAGE_CORE)
                                    {
                                       if($objModule->PackageID != $prevpackageid)
                                       {
                                          // We have to close the last package. We know at least the core existed before.
                                 ?>
                                       </ul>
                                    </div>
                                 </div>
                        <?php
                                          $prevpackageid = $objModule->PackageID;
                        ?>
                                          <div class="ui-accordion-group">
                                             <h3 class="package-header <?php echo($objModule->Package->APRCode); ?>"><a><?php echo($objModule->Package->toString()); ?></a></h3>
                                             <div class="module-list">
                                                <ul style="list-style-type:none">
                                 <?php
                                       }
                                       $page = "admin/{$objModule->Package->APRCode}/{$objModule->Script}";
                                       $class = ($_REQUEST['p'] == $page) ? " class='active'" : '';
                                       echo("<li><a{$class} href='index.php?p={$page}'>{$objModule->toString()}</a></li>\n");
                                    }
                                 }
                                 ?>
                              </ul>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div id="main-wrapper">
                     <div id="main-content" class="<?php echo($packagetoggledclass); ?>">
                        <a id="packagetoggle" onclick="admin_ui_togglepackagelist();"><img src="<?php echo($packagetogglesource); ?>" alt="toggle package list" /></a>
                        <?php
                              }






