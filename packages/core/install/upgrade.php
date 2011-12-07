<?php
if(!file_exists('packages/core/index.php'))
{
   die('The Archon Core could not be found in packages/core/');
}

require_once('common.inc.php');


$totalsteps = 5;
if(preg_match('/^upgrade([\d]+)$/', $_REQUEST['f'], $arrMatch) && $arrMatch[1] <= $totalsteps)
{
   $currentstep = $arrMatch[1];
}

$currentstep = $currentstep ? $currentstep : 1;

$cwd = getcwd();
chdir('packages/core/lib/');
require_once('index.php');
require_once('archoninstaller.inc.php');
chdir($cwd);

ArchonInstaller::checkForMDB2();

require_once('MDB2.php');

require_once('config.inc.php');
//include_once("db/db.inc.php");
require_once('start.inc.php');

if($_REQUEST['f'] == 'dbprogress')
{
   ArchonInstaller::printDBProgress();
   die();
}


if($currentstep > 2)
{

   $_ARCHON->initialize();

   $DBVersion = $_ARCHON->Packages[PACKAGE_CORE]->DBVersion;
}
else
{

   include('packages/core/index.php');
   $_ARCHON->Version = $Version;

   //TODO: Clean up this section
   $query = "SELECT DBVersion FROM tblCore_Packages WHERE APRCode = 'core'";
   $result = $_ARCHON->mdb2->query($query);
   if (PEAR::isError($result))
   {
      trigger_error($result->getMessage(), E_USER_ERROR);
   }
   $row1 = $result->fetchRow();
   $result->free();

   if(!$row1)
   {
      $query = "SELECT Value FROM tblArchon_Configuration WHERE Directive = 'Database Structure Version'";
      $result2 = $_ARCHON->mdb2->query($query);
      if (PEAR::isError($result2))
      {
         trigger_error($result2->getMessage(), E_USER_ERROR);
      }
      $row = $result2->fetchRow();
      $result2->free;
      if(!$row)
      {
         header("Location: index.php?p=install");
         die();
      }
      else
      {
         $row = $result->fetchRow();
         $DBVersion = $row['Value'];
      }
   }
   else
   {
      $DBVersion = $row1['DBVersion'];
   }

   if(version_compare($DBVersion, $_ARCHON->Version) != -1)
   {
      die("The Archon database structure has already been updated to the current version.");
   }

   // make sure the version is at least 2.22
   if(version_compare($DBVersion, 2.22) < 0)
   {
      die("Archon must be at version 2.22 or higher before upgrading to 3.0!");
   }
   elseif(version_compare($DBVersion, 2.23) <= 0)
   {
      $cwd = getcwd();
      chdir('packages/core/install/upgrade/2.xlib');
      require_once('index.php');
      chdir($cwd);

      $_ARCHON->Security = New Security2x();
   }
   else
   {
      $_ARCHON->Security = New Security();
   }
}

if(!$_ARCHON->Security->verifyPermissions(0, FULL_CONTROL))
{
   core_upgrade_login();
}
else if($_REQUEST['f'] == 'backup')
{
   core_upgrade_backup();
}
else if($currentstep)
{
   call_user_func("core_upgrade_upgrade{$currentstep}");
}
else
{
   $currentstep = 1;

   core_upgrade_upgrade1();
}




function core_upgrade_upgrade1()
{
   global $_ARCHON, $currentstep, $totalsteps, $DBVersion;
   output_upgrade_header();
   ?>
<p style="width: 65%; margin: 30px auto 10px;">You are now ready to begin the upgrade of Archon from version <?php echo($DBVersion); ?> to <?php echo($_ARCHON->Version); ?>.
    	      Please note that between some versions this process may take a while. Please set aside around 15 minutes for the upgrade to run.</p>

<strong><span style='color:red'>IMPORTANT: You should back up your database before continuing.</span></strong>

<div class="center warning" style="padding: 3px; width: 65%; margin: 60px auto">
   <strong> NOTICE: ARCHON no longer provides a database backup utility.</strong>
   <p>Please use the proper utility for your database or contact your system administrator to ensure you have a proper backup before continuing. Please note that the next step <em>will</em> alter your database.</p>
</div>


   <?php

   output_upgrade_footer();
}




function core_upgrade_upgrade2()
{
   global $_ARCHON, $currentstep, $totalsteps, $DBVersion;

   require_once("packages/core/lib/archoninstaller.inc.php");

   if($_REQUEST['exec']==true)
   {
      ignore_user_abort(true);

      header("Connection: close\r\n");
      header("Content-Encoding: none\r\n");
      ob_start();

      echo('submitted');
      $length = ob_get_length();
      header("Content-Length: ".$length);
      ob_end_flush();
      ob_flush();
      flush();

      $arrUpgradeDirs = ArchonInstaller::getUpgradeDirs('packages/core/install/upgrade');

      ArchonInstaller::upgradeDB('packages/core/install/upgrade/', $arrUpgradeDirs, 'Core');
      ArchonInstaller::updateDBProgressTable('DONE', '');
      die();
   }

   ArchonInstaller::dropDBProgressTable();
   ArchonInstaller::createDBProgressTable();


   ob_start();

   output_upgrade_header();

   ?>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $.ajax({
         url: 'index.php?p=upgrade&f=upgrade2&exec=true',
         global: false
      });
      updateMessageBox();
   });

   /* ]]> */
</script>
<p id="banner" class="info"><strong>Initializing Upgrade...</strong>
</p>
<div id="loader" class="center">
   <img src="adminthemes/default/images/bar-loader.gif" alt="loading" />
</div>
<div id="messagebox">
   Current step: <span class="message">Initializing...</span>
</div>
<p id="successmessage" class="hidden"> <strong>Upgrade Complete!</strong> Please click next to continue upgrading your packages.</p>

   <?php
   output_upgrade_footer(true);

   ob_end_flush();
   ob_flush();
   flush();

}




function core_upgrade_upgrade3()
{
   global $_ARCHON, $currentstep, $totalsteps, $DBVersion;
   require_once("packages/core/lib/archoninstaller.inc.php");


   if($_REQUEST['exec']==true)
   {
      ignore_user_abort(true);

      header("Connection: close\r\n");
      header("Content-Encoding: none\r\n");
      ob_start();

      echo('submitted');
      $length = ob_get_length();
      header("Content-Length: ".$length);
      ob_end_flush();
      ob_flush();
      flush();

      $arrAllPackages = $_ARCHON->getAllPackages(false);
      $arrTopologicalAllPackages = $_ARCHON->TopologicalPackageKeys;

      foreach($arrAllPackages as $objPackage)
      {
         if(!$_ARCHON->Packages[$objPackage->ID])
         {
            require_once("packages/{$objPackage->APRCode}/index.php");
            $arrAllPackages[$objPackage->ID]->Version = $_ARCHON->Packages[$objPackage->ID]->Version;
            $arrAllPackages[$objPackage->ID]->DBVersion = $_ARCHON->Packages[$objPackage->ID]->DBVersion;
         }
         $arrTopologicalAllPackages[$objPackage->APRCode] =& $arrAllPackages[$objPackage->ID];
      }

      //slice the core package off the front
      array_shift($arrTopologicalAllPackages);


      foreach($arrTopologicalAllPackages as $objPackage)
      {
         $TryNext = false;
         if(!empty($_ARCHON->Packages[$objPackage->APRCode]->DependsUpon))
         {
            foreach($_ARCHON->Packages[$objPackage->APRCode]->DependsUpon as $DependsUponAPRCode => $Version)
            {
               if(!$objPackage->Enabled || version_compare($Version, $objPackage->DBVersion) == -1)
               {
                  $TryNext = true;
                  break;
               }
            }
         }
         if($TryNext)
         {
            $IssueUpgrading = true;
            continue;
         }
         if(version_compare($objPackage->DBVersion, $objPackage->Version) != -1)
         {
            if(!file_exists("packages/{$objPackage->APRCode}/install/upgrade.php"))
            {
               $IssueUpgrading = true;
               continue;
            }
            require_once("packages/{$objPackage->APRCode}/install/upgrade.php");
         }

      }

      if ($IssueUpgrading)
      {
         ArchonInstaller::updateDBProgressTable('ERROR', "Archon has finished upgrading your packages, but please note that there may have been some issues. Please visit the Package Manager after this upgrade has completed to verify the status of your upgrades.<br />");
      }
      else
      {
         ArchonInstaller::updateDBProgressTable('DONE', '');
      }
      die();
   }

   ArchonInstaller::updateDBProgressTable('', '');

   ob_start();

   output_upgrade_header();


   ?>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $.ajax({
         url: 'index.php?p=upgrade&f=upgrade3&exec=true',
         global: false
      });
      updateMessageBox();
   });

   /* ]]> */
</script>
<p id="banner" class="info"><strong>Initializing Upgrade...</strong>
</p>
<div id="loader" class="center">
   <img src="adminthemes/default/images/bar-loader.gif" alt="loading" />
</div>
<div id="messagebox">
   Current step: <span class="message">Initializing...</span>
</div>
<p id="successmessage" class="hidden"> <strong>Upgrade Complete!</strong> Please click next to continue upgrading your phrases.</p>


   <?php
   output_upgrade_footer(true);

   ob_end_flush();
   ob_flush();
   flush();

}


function core_upgrade_upgrade4()
{
   global $_ARCHON, $currentstep, $totalsteps, $DBVersion;

   output_upgrade_header();

   require_once("packages/core/lib/archoninstaller.inc.php");

   $arrPhraseLanguages = ArchonInstaller::getPhraseLanguagesArray();

   ?>
<script type="text/javascript">
   <!--
   $(function() {
      if($('#DefaultLanguageID option[value=0]').attr('selected')) {
         $('input[name="nextbutton"]').attr('disabled', 'disabled');
      }

      $('#DefaultLanguageID').change(function(e) {
         if($(e.target).children('[value=0]').attr('selected')){
            $('input[name="nextbutton"]').attr('disabled', 'disabled');
         }else{
            $('input[name="nextbutton"]').removeAttr('disabled');
         }
      });

      $('#languages :checkbox').click(function (e) {
         var lang = $(e.target).val();
         if($(e.target).attr('checked')){
            $('#DefaultLanguageID option').each(function (){
               if($(this).attr('value') == lang) {
                  $(this).removeAttr('disabled');
               }
            });
         }else{
            $('#DefaultLanguageID option').each(function (){
               if($(this).attr('value') == lang) {
                  $(this).attr('disabled', 'disabled');
                  if($(this).attr('selected')) {
                     $(this).removeAttr('selected');
                     $('#DefaultLanguageID option[value=0]').attr('selected', 'selected');
                     $('#DefaultLanguageID').change();
                  }
               }
            });

            if($('#languages :checkbox:checked').length == 0) {
               $('input[name="nextbutton"]').attr('disabled', 'disabled');
            }else {
               $('input[name="nextbutton"]').removeAttr('disabled');
            }
         }
      });
   });
   -->
</script>


<div class="center">

   <p>The Archon Administrative Interface has support for multiple languages.
      Please select the languages you wish it to support below.</p>
   <p>You may also select the default language to be used in the Administrative Interface.</p>

   <table id="languages" style="width:45%; margin: 20px auto; text-align: left;">
      <tr>
         <th>Language</th>
         <th>Install</th>
      </tr>
         <?php


         foreach($arrPhraseLanguages['languages'] as $objLanguage)
         {
            $checked = ($objLanguage->ID == CONFIG_CORE_DEFAULT_LANGUAGE || $objLanguage->LanguageShort == 'eng' || array_search($objLanguage->ID, $arrPhraseLanguages['installed']) !== false) ? 'checked' : '';
            ?>
      <tr>
         <td><?php echo($objLanguage->toString()) ?></td>
         <td><input type="checkbox" name="languageIDs[]" value="<?php echo($objLanguage->ID); ?>" checked="<?php echo($checked); ?>" /></td>
      </tr>
            <?php
         }
         ?>
      <tr>
         <td colspan="2"><hr style="border: none; background: #ddd;" /></td>
      </tr>
      <tr>
         <td><strong>Default Language:</strong></td>
         <td>
            <select id ="DefaultLanguageID" name="DefaultLanguageID">
               <option value="0">(Select One)</option>
                  <?php
                  foreach($arrPhraseLanguages['languages'] as $objLanguage)
                  {
                     $selected = ($objLanguage->ID == CONFIG_CORE_DEFAULT_LANGUAGE) ? ' selected' : '';

                     $disabled = (array_search($objLanguage->ID, $arrPhraseLanguages['installed']) === false) ? ' disabled="disabled"' : '';

                     echo('<option value="'.$objLanguage->ID.'"'.$selected.$disabled.'>'.$objLanguage->LanguageLong.'</option>');
                  }
                  ?>
            </select>
         </td>
      </tr>
   </table>
</div>
   <?php
   output_upgrade_footer();
}




function core_upgrade_upgrade5()
{
   global $_ARCHON, $currentstep, $totalsteps;
   require_once("packages/core/lib/archoninstaller.inc.php");

   if($_REQUEST['exec']==true)
   {
      ignore_user_abort(true);

      header("Connection: close\r\n");
      header("Content-Encoding: none\r\n");
      ob_start();

      echo('submitted');
      $length = ob_get_length();
      header("Content-Length: ".$length);
      ob_end_flush();
      ob_flush();
      flush();

      $in_DefaultLanguageID = $_REQUEST['defaultlanguageid'] ? $_REQUEST['defaultlanguageid'] : 0;

      if($in_DefaultLanguageID && is_natural($in_DefaultLanguageID))
      {
         $CorePackageID = PACKAGE_CORE;
         $query = "UPDATE tblCore_Configuration SET Value = '$in_DefaultLanguageID' WHERE PackageID = '$CorePackageID' AND Directive = 'Default Language';";
         $affected = $_ARCHON->mdb2->exec($query);
         ArchonInstaller::handleError($affected, $query);
      }

      foreach($_REQUEST['languageids'] as $languageID)
      {
         $objLanguage = New Language($languageID);
         $objLanguage->dbLoad();
         $strRequest = 'language_'.$objLanguage->LanguageShort;
         $_REQUEST[$strRequest]=true;
      }


      $SecurityDisabled = $_ARCHON->Security->Disabled;
      $_ARCHON->Security->Disabled = true;

      $_REQUEST['f'] = 'import-phrasexml';
      $_REQUEST['allpackages'] = true;

      include('packages/core/db/import-phrasexml.inc.php');

      $_ARCHON->Security->Disabled = $SecurityDisabled;


      ArchonInstaller::updateDBProgressTable('DONE', '');
      sleep(15);
      ArchonInstaller::dropDBProgressTable();
      die();
   }


   ArchonInstaller::updateDBProgressTable('', '');



   ob_start();

   output_upgrade_header();

   ?>
<script type="text/javascript">
   /* <![CDATA[ */
   $(function () {
      $.ajax({
         url: 'index.php?p=upgrade&f=upgrade5&exec=true',
         data: {
            defaultlanguageid: '<?php echo($_REQUEST['defaultlanguageid']); ?>',
            'languageids[]': <?php echo(js_array($_POST['languageIDs'])); ?>
         },
         global: false
      });

      updateMessageBox();
   });

   /* ]]> */
</script>
<p id="banner" class="info"><strong>Initializing Upgrade...</strong>
</p>
<div id="loader" class="center">
   <img src="adminthemes/default/images/bar-loader.gif" alt="loading" />
</div>
<div id="messagebox">
   <p style="font-weight:normal">The Archon Upgrader is now loading phrases into the database to prepare for
      multilingual support in the administrative interface. Please be patient as this
      may take a while.</p>
</div>
<p id="successmessage" class="hidden"> <strong>Upgrade Complete!</strong> Please click finish to go to the administrative interface. Additional packages may be available for installation.</p>


   <?php
   output_upgrade_footer(true);

   ob_end_flush();
   ob_flush();
   flush();
}







function core_upgrade_login()
{
   global $_ARCHON, $currentstep, $totalsteps, $DBVersion;

   output_upgrade_header(false);

   ?>

<div class="center">

   <p style="margin: 30px auto 10px;">Please log in as an Administrator with full permissions to upgrade Archon.</p>


   <table style="width:55%; background: #fcfcfc; margin: 30px auto; border: 1px solid #f5f5f5;">
      <tr>
         <td>
            Login:
         </td>
         <td>
            <input type="text" name="ArchonLogin" size=15>
         </td>
      </tr>
      <tr>
         <td>
            Password:
         </td>
         <td>
            <input type="password" name="ArchonPassword" size=15 class="password">
         </td>
      </tr>
   </table>
</div>

   <?php
   output_upgrade_footer();
}




function output_upgrade_header($increment_step = true)
{
   global $_ARCHON, $currentstep, $totalsteps, $DBVersion;

   include("adminthemes/default/installerheader.inc.php");
   if($increment_step)
   {
      $step_value = $currentstep + 1;
   }
   else
   {
      $step_value = $currentstep;
   }
   ?>

<div class="center"><form id="upgradeform" name="upgrade" method="post" action="index.php" accept-charset="UTF-8">
      <input type="hidden" name="p" value="upgrade" />
      <input type="hidden" name="f" value="upgrade<?php echo($step_value); ?>" />
      <input type="hidden" name="upgrader" value="true" />
      <div style="font-size:1.1em; font-weight:bolder; padding:0 5px 10px; margin-bottom: 10px; border-bottom: 1px solid #eee"><?php echo($_ARCHON->ProductName); ?> <?php echo($_ARCHON->Version); ?> Upgrader (Step <?php echo($currentstep); ?> of <?php echo($totalsteps); ?>)</div>
      <div id="installerpage">
            <?php


         }

         function output_upgrade_footer($disabled = false)
         {
            global $_ARCHON, $currentstep, $totalsteps;
            $disabled = $disabled ? 'disabled' : '';
            ?>
      </div>
      <div id="installercontrols">
            <?php
            if($currentstep > 1)
            {
               ?>
         <input type=button class="adminformbutton <?php echo($disabled); ?>" style="float:left" name="prevbutton" value="Previous" onclick="location.href='?p=upgrade&amp;f=upgrade<?php echo($currentstep - 1); ?>';" <?php echo($disabled); ?>/>
               <?php
            }

            if($currentstep == $totalsteps)
            {
               ?>
         <input type=button class="adminformbutton <?php echo($disabled); ?>" style="float:right" name="finishbutton" value="Finish" onclick="location.href='?p=admin/core/packages';" <?php echo($disabled); ?>/>
               <?php
            }
            else
            {
               ?>
         <input type=submit class="adminformbutton <?php echo($disabled); ?>" style="float:right" name="nextbutton" value="Next" onclick="$(this).addClass('disabled'); $(this).attr('disabled','disabled'); $('#upgradeform').submit(); return false;" <?php echo($disabled); ?>/>
               <?php
            }
            ?>

      </div>

   </form>
</div>

   <?php
   include('adminthemes/default/installerfooter.inc.php');

}


?>
