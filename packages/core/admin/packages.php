<?php
/**
 * Package manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

// Determine what to do based upon user input
if(!$_REQUEST['f'])
{
   packages_ui_main();
}
else
{
   packages_ui_exec();
}







// packages_ui_main()
//   - purpose: Creates the primary user interface
//              for the packages Manager.
function packages_ui_main()
{
   global $_ARCHON;

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general')->disable();
   $browseSection = $_ARCHON->AdministrativeInterface->getSection('browse')->disable();

   $arrPackages = array();
   foreach($_ARCHON->Packages as $key => $objPackage)
   {
      if(!is_natural($key))
      {
         $arrPackages[$objPackage->ID] = clone $objPackage;
         $arrPackages[$key] = $arrPackages[$objPackage->ID];
      }
   }

   $arrAllPackages = $_ARCHON->getAllPackages(false);

   foreach($arrAllPackages as $objPackage)
   {
      if($objPackage->Enabled)
      {
         $arrEnabledPackages[$objPackage->ID] = $objPackage;
      }

      $arrAPRCodeIDMap[$objPackage->APRCode] = $objPackage->ID;
   }

   $DescriptionPhraseTypeID = $_ARCHON->getPhraseTypeIDFromString('Description');

   $objUninstallPhrase = Phrase::getPhrase('uninstall', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strUninstall = $objUninstallPhrase ? $objUninstallPhrase->getPhraseValue(ENCODE_HTML) : 'Uninstall';
   $objUpgradePhrase = Phrase::getPhrase('upgrade', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strUpgrade = $objUpgradePhrase ? $objUpgradePhrase->getPhraseValue(ENCODE_HTML) : 'Upgrade';
   $objInstallPhrase = Phrase::getPhrase('install', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strInstall = $objInstallPhrase ? $objInstallPhrase->getPhraseValue(ENCODE_HTML) : 'Install';

   $objAreYouSureUninstallPhrase = Phrase::getPhrase('areyousureuninstall', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strAreYouSureUninstall = $objAreYouSureUninstallPhrase ? $objAreYouSureUninstallPhrase->getPhraseValue(ENCODE_JAVASCRIPTTHENHTML) : 'This will irreversibly delete ALL DATA associated with the $1 package!  Are you sure you want to do this? (You should backup your data in the database manager first.)';
   $objAreYouSureUpgradePhrase = Phrase::getPhrase('areyousureupgrade', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strAreYouSureUpgrade = $objAreYouSureUpgradePhrase ? $objAreYouSureUpgradePhrase->getPhraseValue(ENCODE_JAVASCRIPTTHENHTML) : 'This will update the database to use a newer version of the $1 package.  Are you sure you want to do this? (You should backup your data in the database manager first.)';
   $objAreYouSureInstallPhrase = Phrase::getPhrase('areyousureinstall', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strAreYouSureInstall = $objAreYouSureInstallPhrase ? $objAreYouSureInstallPhrase->getPhraseValue(ENCODE_JAVASCRIPTTHENHTML) : 'Are you sure you want to install the $1 package? (You should backup your data in the database manager first.)';

   $objUpdatePhrase = Phrase::getPhrase('update', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strUpdate = $objUpdatePhrase ? $objUpdatePhrase->getPhraseValue(ENCODE_HTML) : 'Update';
   $objPleaseWaitPhrase = Phrase::getPhrase('pleasewait', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strPleaseWait = $objPleaseWaitPhrase ? $objPleaseWaitPhrase->getPhraseValue(ENCODE_JAVASCRIPT) : 'Please wait...';

   $objPackageNamePhrase = Phrase::getPhrase('packagename', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strPackageName = $objPackageNamePhrase ? $objPackageNamePhrase->getPhraseValue(ENCODE_HTML) : 'Package Name';
   $objVersionPhrase = Phrase::getPhrase('version', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strVersion = $objVersionPhrase ? $objVersionPhrase->getPhraseValue(ENCODE_HTML) : 'Version';
   $objEnabledPhrase = Phrase::getPhrase('enabled', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strEnabled = $objEnabledPhrase ? $objEnabledPhrase->getPhraseValue(ENCODE_HTML) : 'Enabled';
   $objDependsUponPhrase = Phrase::getPhrase('dependsupon', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strDependsUpon = $objDependsUponPhrase ? $objDependsUponPhrase->getPhraseValue(ENCODE_HTML) : 'Depends Upon';
   $objDependedUponByPhrase = Phrase::getPhrase('dependeduponby', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strDependedUponBy = $objDependedUponByPhrase ? $objDependedUponByPhrase->getPhraseValue(ENCODE_HTML) : 'Depended Upon By';
   $objEnhancesPhrase = Phrase::getPhrase('enhances', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strEnhances = $objEnhancesPhrase ? $objEnhancesPhrase->getPhraseValue(ENCODE_HTML) : 'Enhances';
   $objEnhancedByPhrase = Phrase::getPhrase('enhancedby', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strEnhancedBy = $objEnhancedByPhrase ? $objEnhancedByPhrase->getPhraseValue(ENCODE_HTML) : 'EnhancedBy';

   $objYesPhrase = Phrase::getPhrase('yes', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
   $strYes = $objYesPhrase ? $objYesPhrase->getPhraseValue(ENCODE_HTML) : 'Yes';
   $objNoPhrase = Phrase::getPhrase('no', PACKAGE_CORE, MODULE_NONE, PHRASETYPE_ADMIN);
   $strNo = $objNoPhrase ? $objNoPhrase->getPhraseValue(ENCODE_HTML) : 'No';

   $objVisitPhrase = Phrase::getPhrase('visit', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strVisit = $objVisitPhrase ? $objVisitPhrase->getPhraseValue() : 'Visit <a href="$1">The Archon Project Website</a> to download additional packages.';
   $strVisit = str_replace('$1', 'http://www.archon.org/', $strVisit);


   $_ARCHON->AdministrativeInterface->disableQuickSearch();

   $installedsection = $_ARCHON->AdministrativeInterface->insertSection('installed', 'custom');
   ob_start();
   ?>
<script type="text/javascript">
   <!--
   function packages_ui_main_js_uninstall(aprcode)
   {
      $('#fInput').val('uninstall');
      $('#aprcodeInput').val(aprcode);
      $('#mainform').submit();
   }

   function packages_ui_main_js_install(aprcode)
   {
      $('#fInput').val('install');
      $('#aprcodeInput').val(aprcode);
      $('#mainform').submit();
   }

   function packages_ui_main_js_upgrade(aprcode)
   {
      $('#fInput').val('upgrade');
      $('#aprcodeInput').val(aprcode);
      $('#mainform').submit();
   }

   $(function() {
      $('#installedpackagestable :radio').change(function () {
         $('#fInput').val('updatepackages');
         $('#mainform').submit();
      });
   });
   -->
</script>
   <?php
   ?>
<input type="hidden" name="aprcode" id="aprcodeInput" value="" />
<table id="installedpackagestable">
   <tr>
      <th><?php echo($strPackageName); ?></th>
      <th><?php echo($strVersion); ?></th>
      <th><?php echo($strEnabled); ?></th>
      <th><?php echo($strDependsUpon); ?></th>
      <th><?php echo($strDependedUponBy); ?></th>
      <th><?php echo($strEnhances); ?></th>
      <th><?php echo($strEnhancedBy); ?></th>
      <th><?php echo($strUninstall); ?></th>
      <th><?php echo($strUpgrade); ?></th>
   </tr>
      <?php
      // $_ARCHON->Packages is missing the disabled packages. Their information needs filled in.
      foreach($arrAllPackages as $objPackage)
      {
         if(!$_ARCHON->Packages[$objPackage->ID])
         {
            include("packages/{$objPackage->APRCode}/index.php");
            $_ARCHON->Packages[$objPackage->ID] =& $_ARCHON->Packages[$objPackage->APRCode];
            $_ARCHON->Packages[$objPackage->ID]->APRCode = $objPackage->APRCode;
            $_ARCHON->Packages[$objPackage->ID]->Version = $Version;
         }
      }

      foreach($arrAllPackages as $objPackage)
      {
         $yeschecked = $objPackage->Enabled ? 'checked="checked"' : '';
         $nochecked = $objPackage->Enabled ? '' : 'checked="checked"';

         $DependsUpon = '';
         $DependedUponBy = '';
         $Enhances = '';
         $EnhancedBy = '';

         $DisableEnabledOption = '';
         $StyleUninstallButton = '';
         $StyleUpgradeButton = 'style="display: none;"';

         foreach($_ARCHON->Packages as $key => $dependentPackage)
         {
            if($dependentPackage->DependsUpon[$objPackage->APRCode])
            {
               $_ARCHON->Packages[$objPackage->ID]->DependedUponBy[$dependentPackage->APRCode] = true;
            }
         }
         foreach($_ARCHON->Packages as $key => $enhancedPackage)
         {
            if($enhancedPackage->Enhances[$objPackage->APRCode])
            {
               $_ARCHON->Packages[$objPackage->ID]->EnhancedBy[$enhancedPackage->APRCode] = true;
            }
         }

         if(!empty($_ARCHON->Packages[$objPackage->ID]->DependsUpon))
         {
            foreach($_ARCHON->Packages[$objPackage->ID]->DependsUpon as $APRCode => $Version)
            {
               $DependsUpon .= $arrAllPackages[$arrAPRCodeIDMap[$APRCode]]->toString() . ' ' . encode($Version, ENCODE_HTML) . '<br />';
            }
         }

         if(!empty($_ARCHON->Packages[$objPackage->ID]->DependedUponBy))
         {
            foreach($_ARCHON->Packages[$objPackage->ID]->DependedUponBy as $APRCode => $bool)
            {
               $DependedUponBy .= $arrAllPackages[$arrAPRCodeIDMap[$APRCode]]->toString() . '<br />';

               $StyleUninstallButton = 'style="display: none;"';

               if($objPackage->Enabled && $arrAllPackages[$arrAPRCodeIDMap[$APRCode]]->Enabled)
               {
                  $DisableEnabledOption = 'disabled="disabled"';
               }
            }
         }

         if(!empty($_ARCHON->Packages[$objPackage->ID]->Enhances))
         {
            foreach($_ARCHON->Packages[$objPackage->ID]->Enhances as $APRCode => $Version)
            {
               if($arrAllPackages[$arrAPRCodeIDMap[$APRCode]])
               {
                  $Enhances .= $arrAllPackages[$arrAPRCodeIDMap[$APRCode]]->toString() . ' ' . encode($Version, ENCODE_HTML) . '<br />';
               }
               else
               {
                  $Enhances .= $APRCode . ' ' . encode($Version, ENCODE_HTML) . '<br />';
               }
            }
         }

         if(!empty($_ARCHON->Packages[$objPackage->ID]->EnhancedBy))
         {
            foreach($_ARCHON->Packages[$objPackage->ID]->EnhancedBy as $APRCode => $bool)
            {
               if($arrEnabledPackages[$arrAPRCodeIDMap[$APRCode]])
               {
                  $EnhancedBy .= $arrEnabledPackages[$arrAPRCodeIDMap[$APRCode]]->toString() . '<br />';
               }
            }

         }

         if($objPackage->Enabled && $objPackage->APRCode == 'core')
         {
            $DisableEnabledOption = 'disabled="disabled"';
            $StyleUninstallButton = 'style="display: none;"';
         }
         elseif(!$objPackage->Enabled && !empty($_ARCHON->Packages[$objPackage->APRCode]->DependsUpon))
         {
            foreach($_ARCHON->Packages[$objPackage->APRCode]->DependsUpon as $APRCode => $Version)
            {
               if(!$_ARCHON->Packages[$APRCode] || version_compare($_ARCHON->Packages[$APRCode]->DBVersion, $Version) == -1)
               {
                  $DisableEnabledOption = 'disabled="disabled"';
               }
            }
         }

         if(!$DisableEnabledOption)
         {
            $DisableEnabledOption = "";
         }

         if(version_compare($objPackage->DBVersion, $_ARCHON->Packages[$objPackage->APRCode]->Version) == -1)
         {
            $StyleUninstallButton = 'style="display: none;"';

            $UpdateUpgradeStyle = true;
            foreach($_ARCHON->Packages[$objPackage->APRCode]->DependsUpon as $APRCode => $Version)
            {
               if(!$_ARCHON->Packages[$APRCode] || version_compare($_ARCHON->Packages[$APRCode]->DBVersion, $Version) == -1)
               {
                  $UpdateUpgradeStyle = false;
               }
            }

            if($UpdateUpgradeStyle)
            {
               $StyleUpgradeButton = '';
            }
         }

         $arrDisplayedPackages[$objPackage->APRCode] = true;

         $strUninstallWarning = str_replace('$1', $objPackage->toString(), $strAreYouSureUninstall);
         $strUpgradeWarning = str_replace('$1', $objPackage->toString(), $strAreYouSureUpgrade);
         ?>
   <tr>
      <td><?php echo($objPackage->toString()); ?></td>
      <td><?php echo($objPackage->getString('DBVersion')); ?></td>
      <td><input type='radio' name='Enabled[<?php echo($objPackage->ID); ?>]' value='1' <?php echo($DisableEnabledOption); ?> <?php echo($yeschecked); ?> /> <?php echo($strYes); ?><input type='radio' name='Enabled[<?php echo($objPackage->ID); ?>]' value='0' <?php echo($DisableEnabledOption); ?> <?php echo($nochecked); ?> /> <?php echo($strNo); ?></td>
      <td><?php echo($DependsUpon); ?></td>
      <td><?php echo($DependedUponBy); ?></td>
      <td><?php echo($Enhances); ?></td>
      <td><?php echo($EnhancedBy); ?></td>
      <td><input type='button' class='adminformbutton' <?php echo($StyleUninstallButton); ?> onclick="if (confirm('<?php echo($strUninstallWarning); ?>')) { packages_ui_main_js_uninstall('<?php echo($objPackage->APRCode); ?>'); }" value="<?php echo($strUninstall); ?>" /></td>
      <td><input type='button' class='adminformbutton' <?php echo($StyleUpgradeButton); ?> onclick="if (confirm('<?php echo($strUpgradeWarning); ?>')) { packages_ui_main_js_upgrade('<?php echo($objPackage->APRCode); ?>'); }" value="<?php echo($strUpgrade); ?>" /></td>
   </tr>
         <?php
      }
      ?>
</table>
   <?php
   $strInstalledTable = ob_get_clean();
   $installedsection->setCustomArguments($strInstalledTable);

   $additionalsection = $_ARCHON->AdministrativeInterface->insertSection('additional', 'custom');
   ob_start();
   ?>
<table id="additionalpackagestable">
   <tr>
      <th><?php echo($strPackageName); ?></th>
      <th><?php echo($strDependsUpon); ?></th>
      <th><?php echo($strEnhances); ?></th>
      <th><?php echo($strInstall); ?></th>
   </tr>
      <?php
      if($handle = opendir("packages/"))
      {
         while(false !== ($dir = readdir($handle)))
         {
            if(file_exists("packages/$dir/index.php") && $dir != ".." && $dir != '.')
            {
               if(!$arrDisplayedPackages[$dir])
               {
                  include("packages/$dir/index.php");

                  $DependsUpon = '';
                  $Enhances = '';

                  $StyleUninstallButton = '';

                  if(!empty($_ARCHON->Packages[$dir]->DependsUpon))
                  {
                     foreach($_ARCHON->Packages[$dir]->DependsUpon as $APRCode => $Version)
                     {
                        if($arrAllPackages[$arrAPRCodeIDMap[$APRCode]])
                        {
                           $DependsUpon .= $arrAllPackages[$arrAPRCodeIDMap[$APRCode]]->toString() . ' ' . encode($Version, ENCODE_HTML) . '<br />';
                        }
                        else
                        {
                           $DependsUpon .= $APRCode . ' ' . encode($Version, ENCODE_HTML) . '<br />';
                        }

                        if(!$_ARCHON->Packages[$APRCode]->Enabled || version_compare($_ARCHON->Packages[$APRCode]->DBVersion, $Version) == -1)
                        {
                           $StyleUninstallButton = 'style="display: none;"';
                        }
                     }
                  }

                  if(!empty($_ARCHON->Packages[$dir]->Enhances))
                  {
                     foreach($_ARCHON->Packages[$dir]->Enhances as $APRCode => $Version)
                     {
                        if($arrAllPackages[$arrAPRCodeIDMap[$APRCode]])
                        {
                           $Enhances .= $arrAllPackages[$arrAPRCodeIDMap[$APRCode]]->toString() . ' ' . encode($Version, ENCODE_HTML) . '<br />';
                        }
                        else
                        {
                           $Enhances .= $APRCode . ' ' . encode($Version, ENCODE_HTML) . '<br />';
                        }
                     }
                  }

                  $strInstallWarning = str_replace('$1', $dir, $strAreYouSureInstall);
                  ?>
   <tr>
      <td><?php echo($dir); ?></td>
      <td><?php echo($DependsUpon); ?></td>
      <td><?php echo($Enhances); ?></td>
      <td><input type='button' class='adminformbutton' <?php echo($StyleUninstallButton); ?> value="<?php echo($strInstall); ?>" onclick="if (confirm('<?php echo($strInstallWarning); ?>')) { packages_ui_main_js_install('<?php echo($dir); ?>'); }" /></td>
   </tr>
                  <?php
               }
            }
         }
      }

      $_ARCHON->Packages = $arrPackages;

      ?>
</table>
   <?php

   $strAdditionalPackagesTables = ob_get_clean();
   $additionalsection->setCustomArguments($strAdditionalPackagesTables);

   $_ARCHON->AdministrativeInterface->outputInterface();

}





function packages_ui_exec()
{
   global $_ARCHON;

   @set_time_limit(0);

   $objPackage = New Package();

   if($_REQUEST['f'] == 'updatepackages')
   {
      if(!empty($_REQUEST['enabled']))
      {
         foreach($_REQUEST['enabled'] as $PackageID => $Enabled)
         {
            $objPackage->ID = $PackageID;

            if($Enabled)
            {
               $objPackage->dbEnable();
            }
            else
            {
               $objPackage->dbDisable();
            }
         }
      }

      $location = "?p=admin/core/packages";
   }
   else if($_REQUEST['f'] == 'install')
   {
      $_REQUEST['aprcode'] = preg_replace('/[^\w]/u', '', $_REQUEST['aprcode']);

      $arrPackages = array();
      foreach($_ARCHON->Packages as $key => $objPackage)
      {
         if(!is_natural($key))
         {
            $arrPackages[$objPackage->ID] = clone $objPackage;
            $arrPackages[$key] = $arrPackages[$objPackage->ID];
         }
      }

      $arrAllPackages = $_ARCHON->getAllPackages(false);

      // $_ARCHON->Packages is missing the disabled packages. They're information needs filled in.
      foreach($arrAllPackages as $objPackage)
      {
         if(!$_ARCHON->Packages[$objPackage->ID])
         {
            include("packages/{$objPackage->APRCode}/index.php");
            $_ARCHON->Packages[$objPackage->ID] =& $_ARCHON->Packages[$objPackage->APRCode];
            $_ARCHON->Packages[$objPackage->ID]->APRCode = $objPackage->APRCode;
            $_ARCHON->Packages[$objPackage->ID]->Version = $Version;
            $_ARCHON->Packages[$objPackage->ID]->DBVersion = $objPackage->DBVersion;
         }
      }

      if(!$_ARCHON->Security->verifyPermissions(MODULE_PACKAGES, FULL_CONTROL))
      {
         $_ARCHON->declareError("Could not install package: Permission denied.");
      }
      else if($_ARCHON->Packages[$_REQUEST['aprcode']] && $_REQUEST['installstep'] < 2)
      {
         $_ARCHON->declareError("Could not install package: A Package with the same APRCode is already installed.");
      }
      else if(!file_exists("packages/{$_REQUEST['aprcode']}/install/install.php"))
      {
         $_ARCHON->declareError("Could not install package: Installation script not found.");
      }
      else
      {
         include("packages/{$_REQUEST['aprcode']}/index.php");

         if(!empty($_ARCHON->Packages[$_REQUEST['aprcode']]->DependsUpon))
         {
            foreach($_ARCHON->Packages[$_REQUEST['aprcode']]->DependsUpon as $APRCode => $Version)
            {
               if(!$_ARCHON->Packages[$APRCode])
               {
                  $_ARCHON->declareError("Could not install package: Package {$_REQUEST['aprcode']} depends upon package $APRCode which is not installed.");
                  break;
               }
               else if(!$_ARCHON->Packages[$APRCode]->Enabled)
               {
                  $_ARCHON->declareError("Could not install package: Package {$_REQUEST['aprcode']} depends upon package $APRCode which is not enabled.");
                  break;
               }
               else if(version_compare($_ARCHON->Packages[$APRCode]->DBVersion, $Version) == -1)
               {
                  $_ARCHON->declareError("Could not install package: Package {$_REQUEST['aprcode']} requires Package $APRCode version $Version or newer (installed version is {$_ARCHON->Packages[$APRCode]->DBVersion}).");
                  break;
               }
            }
         }
      }

      if(!$_ARCHON->Error)
      {
         $_ARCHON->Packages = $arrPackages;

         ob_start();

         include("packages/{$_REQUEST['aprcode']}/install/install.php");

         //            if(trim(ob_get_contents()))
         //            {
         //                ob_end_flush();
         //                die();
         //            }
         //            else
         //            {
         $msg = ob_end_clean();

         $arrAllLanguages = $_ARCHON->getAllLanguages(true);
         $languagesquery = '';
         foreach($arrAllLanguages as $ID => $junk)
         {
            $arrPhrases = $_ARCHON->searchPhrases('module', PACKAGE_CORE, MODULE_ABOUT, PHRASETYPE_ADMIN, $ID, 1);
            if(!empty($arrPhrases))
            {
               $objLanguage = New Language($ID);
               $objLanguage->dbLoad();
               $languagesquery .= "&language_{$objLanguage->LanguageShort}=true";
            }
         }


         $location = "?p=admin/core/database&f=import&importutility=core/phrasexml&aprcode={$_REQUEST['aprcode']}$languagesquery&go=" . urlencode("?p={$_REQUEST['p']}");
         //            }
      }

      $_ARCHON->Packages = $arrPackages;
   }
   else if($_REQUEST['f'] == 'uninstall')
   {
      $_REQUEST['aprcode'] = preg_replace('/[^\w]/u', '', $_REQUEST['aprcode']);

      $arrPackages = array();
      foreach($_ARCHON->Packages as $key => $objPackage)
      {
         if(!is_natural($key))
         {
            $arrPackages[$objPackage->ID] = clone $objPackage;
            $arrPackages[$key] = $arrPackages[$objPackage->ID];
         }
      }

      $arrAllPackages = $_ARCHON->getAllPackages(false);

      // $_ARCHON->Packages is missing the disabled packages. They're information needs filled in.
      foreach($arrAllPackages as $objPackage)
      {
         if(!$_ARCHON->Packages[$objPackage->ID])
         {
            include("packages/{$objPackage->APRCode}/index.php");
            $_ARCHON->Packages[$objPackage->ID] =& $_ARCHON->Packages[$objPackage->APRCode];
            $_ARCHON->Packages[$objPackage->ID]->APRCode = $objPackage->APRCode;
            $_ARCHON->Packages[$objPackage->ID]->Version = $Version;
            $_ARCHON->Packages[$objPackage->ID]->DBVersion = $objPackage->DBVersion;
         }
      }

      foreach($_ARCHON->Packages as $key => $dependentPackage)
      {
         if($dependentPackage->DependsUpon[$_REQUEST['aprcode']])
         {
            $PackageDependingUpon = $dependentPackage->APRCode;
         }
      }

      if(!$_ARCHON->Security->verifyPermissions(MODULE_PACKAGES, FULL_CONTROL))
      {
         $_ARCHON->declareError("Could not install package: Permission denied.");
      }
      else if(!$_ARCHON->Packages[$_REQUEST['aprcode']])
      {
         $_ARCHON->declareError("Could not uninstall package: Package {$_REQUEST['aprcode']} is not installed.");
      }
      else if($PackageDependingUpon)
      {
         $_ARCHON->declareError("Could not uninstall package: Package $PackageDependingUpon depends upon package {$_REQUEST['aprcode']}.");
      }
      else if(!file_exists("packages/{$_REQUEST['aprcode']}/install/uninstall.php"))
      {
         $_ARCHON->declareError("Could not upgrade package: Uninstall script not found.");
      }
      else
      {
         $_ARCHON->Packages = $arrPackages;

         $currentSecurity = $_ARCHON->Security->Disabled;
         $_ARCHON->Security->Disabled = true;

         ob_start();

         include("packages/{$_REQUEST['aprcode']}/install/uninstall.php");

         $_ARCHON->Security->Disabled = $currentSecurity;

         //            if(trim(ob_get_contents()))
         //            {
         //                ob_end_flush();
         //                die();
         //            }
         //            else
         //            {
         //                ob_end_clean();
         $msg = ob_get_clean();
         $location = "?p={$_REQUEST['p']}";
         //            }
      }

      $_ARCHON->Packages = $arrPackages;
   }
   else if($_REQUEST['f'] == 'upgrade')
   {
      $_REQUEST['aprcode'] = preg_replace('/[^\w]/u', '', $_REQUEST['aprcode']);

      $arrPackages = array();
      foreach($_ARCHON->Packages as $key => $objPackage)
      {
         if(!is_natural($key))
         {
            $arrPackages[$objPackage->ID] = clone $objPackage;
            $arrPackages[$key] = $arrPackages[$objPackage->ID];
         }
      }

      $arrAllPackages = $_ARCHON->getAllPackages(false);

      // $_ARCHON->Packages is missing the disabled packages. They're information needs filled in.
      foreach($arrAllPackages as $objPackage)
      {
         if(!$_ARCHON->Packages[$objPackage->ID])
         {
            include("packages/{$objPackage->APRCode}/index.php");
            $_ARCHON->Packages[$objPackage->ID] =& $_ARCHON->Packages[$objPackage->APRCode];
            $_ARCHON->Packages[$objPackage->ID]->APRCode = $objPackage->APRCode;
            $_ARCHON->Packages[$objPackage->ID]->Version = $Version;
            $_ARCHON->Packages[$objPackage->ID]->DBVersion = $objPackage->DBVersion;
         }
      }

      if(!$_ARCHON->Security->verifyPermissions(MODULE_PACKAGES, FULL_CONTROL))
      {
         $_ARCHON->declareError("Could not upgrade package: Permission denied.");
      }
      else if(!$_ARCHON->Packages[$_REQUEST['aprcode']])
      {
         $_ARCHON->declareError("Could not upgrade package: Package {$_REQUEST['aprcode']} is not installed.");
      }
      else if(version_compare($_ARCHON->Packages[$_REQUEST['aprcode']]->DBVersion, $_ARCHON->Packages[$_REQUEST['aprcode']]->Version) != -1)
      {
         $_ARCHON->declareError("Could not upgrade package: Database is up to date.");
      }
      else if(!file_exists("packages/{$_REQUEST['aprcode']}/install/upgrade.php"))
      {
         $_ARCHON->declareError("Could not upgrade package: Upgrade script not found.");
      }
      else
      {
         include("packages/{$_REQUEST['aprcode']}/index.php");

         if(!empty($_ARCHON->Packages[$_REQUEST['aprcode']]->DependsUpon))
         {
            foreach($_ARCHON->Packages[$_REQUEST['aprcode']]->DependsUpon as $APRCode => $Version)
            {
               if(!$_ARCHON->Packages[$APRCode])
               {
                  $_ARCHON->declareError("Could not upgrade package: Package {$_REQUEST['aprcode']} depends upon package $APRCode which is not installed.");
                  break;
               }
               else if(!$_ARCHON->Packages[$APRCode]->Enabled)
               {
                  $_ARCHON->declareError("Could not upgrade package: Package {$_REQUEST['aprcode']} depends upon package $APRCode which is not enabled.");
                  break;
               }
               else if(version_compare($_ARCHON->Packages[$APRCode]->DBVersion, $Version) == -1)
               {
                  $_ARCHON->declareError("Could not upgrade package: Package {$_REQUEST['aprcode']} requires Package $APRCode version $Version or newer (installed version is {$_ARCHON->Packages[$APRCode]->DBVersion}).");
                  break;
               }
            }
         }
      }

      if(!$_ARCHON->Error)
      {
         $_ARCHON->Packages = $arrPackages;

         ob_start();

         include("packages/{$_REQUEST['aprcode']}/install/upgrade.php");

         //            if(trim(ob_get_contents()))
         //            {
         //                ob_end_flush();
         //                die();
         //            }
         //            else
         //            {
         ob_end_clean();

         $arrAllLanguages = $_ARCHON->getAllLanguages(true);
         $languagesquery = '';
         foreach($arrAllLanguages as $ID => $junk)
         {
            $arrPhrases = $_ARCHON->searchPhrases('module', PACKAGE_CORE, MODULE_ABOUT, PHRASETYPE_ADMIN, $ID, 1);
            if(!empty($arrPhrases))
            {
               $objLanguage = New Language($ID);
               $objLanguage->dbLoad();
               $languagesquery .= "&language_{$objLanguage->LanguageShort}=true";
            }
         }


         $location = "?p=admin/core/database&f=import&importutility=core/phrasexml&aprcode={$_REQUEST['aprcode']}$languagesquery&go=" . urlencode("?p={$_REQUEST['p']}");

         //            }
      }

      $_ARCHON->Packages = $arrPackages;
   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
      //$location = "window.top.frames['main'].location='?p={$_REQUEST['p']}&f=';";
   }

   if($_ARCHON->Error)
   {
      $msg = $_ARCHON->Error;
   }
   else
   {
      $msg = "Package Database Updated Successfully.";
   }

   $_ARCHON->sendMessageAndRedirect($msg, $location);
}
