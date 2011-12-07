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
$_ARCHON->AdministrativeInterface->ImagePath = 'adminthemes/default/images';
$_ARCHON->AdministrativeInterface->Theme = 'default';

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
      <link rel="stylesheet" type="text/css" href="adminthemes/default/style.css" />
      <link rel="stylesheet" type="text/css" href="adminthemes/default/installer.css" />


      <script type="text/javascript" src="packages/core/js/jquery.min.js"></script>
      <script type="text/javascript" src="packages/core/js/jquery.form.js"></script>
      <script type="text/javascript" src="packages/core/js/jquery.cookie.js"></script>
      <script type="text/javascript" src="packages/core/js/jquery-ui.custom.min.js"></script>


      <!--[if lt IE 7]>
            <script type="text/javascript" src="<?php echo($_ARCHON->AdministrativeInterface->ThemeJavascriptPath); ?>/jquery.pngfix.js"></script>
            <script type="text/javascript" src="<?php echo($_ARCHON->AdministrativeInterface->ThemeJavascriptPath); ?>/jquery.bgiframe.js"></script>
            <script type="text/javascript">
                $(function(){
                    $('img[@src$=png]').pngfix();
                });
            </script>
      <![endif]-->
      <script type="text/javascript">
         /* <![CDATA[ */
         var failureCount = 0;
         function updateMessageBox(){
            $.ajax({
               url: 'index.php?p=<?php echo($_REQUEST['p']); ?>&f=dbprogress',
               dataType: 'json',
               cache: false,
               global: false,
               success: function(data){

                  if(data.state == 'ERROR'){
                     $('#loader').fadeOut(600);
                     $('#banner').removeClass('info');
                     $('#banner').addClass('warning');
                     $('#banner').text(data.message);
                  }else if (data.state == 'DONE'){
                     $('#loader').fadeOut(600);
                     $('#messagebox').fadeOut(600);
                     $('#banner').removeClass('info');
                     $('#banner').addClass('success');
                     $('#banner').html($('#successmessage').html());
                     $('#installercontrols input').removeAttr('disabled');
                     $('#installercontrols input').removeClass('disabled');
                  }else{
                     $('#messagebox .message').text(data.message);

                     //                  if($('#messagebox p:last').text() != data.message){
                     //                     $('#messagebox').append('<p>'+ data.message + '</p>');
                     //                     $("#messagebox").animate({ scrollTop: $("#messagebox").attr("scrollHeight") }, 2000);
                     //                  }
                     setTimeout('updateMessageBox();', 2000);
                  }
               },
               error: function() {
                  if(failureCount < 6){
                     updateMessageBox();
                     failureCount++;
                  }else{
                     $('#loader').fadeOut(600);
                     $('#banner').text('Connection lost! An error may have occured. Click \'Next\' to proceed anyway.');
                     $('#installercontrols input').removeAttr('disabled');
                     $('#installercontrols input').removeClass('disabled');
                  }
               }
         });
   }

   /* ]]> */

      </script>
      <?php // echo($_ARCHON->getJavascriptTags('archon')); ?>
      <?php //echo($_ARCHON->getJavascriptTags('admin')); ?>


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

         ?>
      <div id="advancedhelp" title=""></div>
      <div id="response" title="Admin Response"></div>
      <div id="background-wrap">
         <div id="page-wrap">
            <div id="inside">
               <div id="header">
                  <div id="logobox"><a href="index.php?p=admin"><img src="<?php echo($_ARCHON->AdministrativeInterface->ImagePath); ?>/logo.png" alt="Archon Logo" /></a></div>
                  <div id="infobox">
                     <div id="sessioninfo">
                        <span id="productinfo"><?php echo($_ARCHON->ProductName); ?> <?php echo($_ARCHON->Version); if($_ARCHON->Revision){echo(" rev-".$_ARCHON->Revision);} ?></span>
                        <span id="logininfo">
                              <?php
                              if($_ARCHON->Security->Session->User)
                              {
                                 ?>
                                 <?php echo("$strLoggedInAs: " . $_ARCHON->Security->Session->User->getString('DisplayName')); ?>
                           <a href="index.php?f=logout"><?php echo($strLogOut); ?></a>
                                 <?php
                              }
                              ?>

                        </span>
                     </div>
                     <div id="messageboxfake">

                     </div>
                  </div>
               </div>
               <div id="columns">

                  <div id="main-wrapper">
                     <div id="main-content" class="<?php echo($packagetoggledclass); ?>">

                           <?php
                        }