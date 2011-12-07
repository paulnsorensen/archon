<?php
/**
 * Register a user account form
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

if(!$_ARCHON->Security->isAuthenticated())
{
    header('Location: index.php?p=');
}

editprofile_initialize();

function editprofile_initialize()
{
	if(!$_REQUEST['f'])
	{
	    editprofile_form();
	}
	else
	{
	    editprofile_exec();
	}
}


function editprofile_form()
{
    global $_ARCHON;

    

    $objMyAccountPhrase = Phrase::getPhrase('myaccount_title', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strMyAccountTitle = $objMyAccountPhrase ? $objMyAccountPhrase->getPhraseValue(ENCODE_HTML) : 'My Account';
    $objEditProfileTitlePhrase = Phrase::getPhrase('editprofile_title', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strEditProfileTitle = $objEditProfileTitlePhrase ? $objEditProfileTitlePhrase->getPhraseValue(ENCODE_HTML) : 'Edit My Profile';

    $_ARCHON->PublicInterface->Title = $strEditProfileTitle;
    $_ARCHON->PublicInterface->addNavigation($strMyAccountTitle, "?p=core/account");
    $_ARCHON->PublicInterface->addNavigation($_ARCHON->PublicInterface->Title, "?p={$_REQUEST['p']}");

    require_once("header.inc.php");
    
    $arrLanguages = $_ARCHON->getAllLanguages();
    $arrCountries = $_ARCHON->getAllCountries();
    
    $UserCountryID = $_REQUEST['countryid'] ? $_REQUEST['countryid'] : $_ARCHON->Security->Session->User->CountryID;
    
    $objSelectOnePhrase = Phrase::getPhrase('selectone', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strSelectOne = $objSelectOnePhrase ? $objSelectOnePhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';
    $objRequiredPhrase = Phrase::getPhrase('requirednotice', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strRequired = $objRequiredPhrase ? $objRequiredPhrase->getPhraseValue(ENCODE_NONE) : 'Fields marked with an asterisk (<span style="color:red">*</span>) are required.';
    $objYesPhrase = Phrase::getPhrase('yes', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strYes = $objYesPhrase ? $objYesPhrase->getPhraseValue(ENCODE_NONE) : 'Yes';
    $objNoPhrase = Phrase::getPhrase('no', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strNo = $objNoPhrase ? $objNoPhrase->getPhraseValue(ENCODE_NONE) : 'No';
    $objSubmitPhrase = Phrase::getPhrase('submit', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strSubmit = $objSubmitPhrase ? $objSubmitPhrase->getPhraseValue(ENCODE_HTML) : 'Submit';
    
    $objEmailPhrase = Phrase::getPhrase('editprofile_email', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strEmail = $objEmailPhrase ? $objEmailPhrase->getPhraseValue(ENCODE_HTML) : 'E-mail';
    $objFirstNamePhrase = Phrase::getPhrase('editprofile_firstname', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strFirstName = $objFirstNamePhrase ? $objFirstNamePhrase->getPhraseValue(ENCODE_HTML) : 'First Name';
    $objLastNamePhrase = Phrase::getPhrase('editprofile_lastname', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strLastName = $objLastNamePhrase ? $objLastNamePhrase->getPhraseValue(ENCODE_HTML) : 'Last Name';
    $objDisplayNamePhrase = Phrase::getPhrase('editprofile_displayname', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strDisplayName = $objDisplayNamePhrase ? $objDisplayNamePhrase->getPhraseValue(ENCODE_HTML) : 'Display Name';
    $objLanguagePhrase = Phrase::getPhrase('editprofile_language', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strLanguage = $objLanguagePhrase ? $objLanguagePhrase->getPhraseValue(ENCODE_HTML) : 'Language';
    $objCountryPhrase = Phrase::getPhrase('editprofile_country', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strCountry = $objCountryPhrase ? $objCountryPhrase->getPhraseValue(ENCODE_HTML) : 'Country';
    $objPasswordPhrase = Phrase::getPhrase('editprofile_password', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strPassword = $objPasswordPhrase ? $objPasswordPhrase->getPhraseValue(ENCODE_HTML) : 'Password';
    $objConfirmPasswordPhrase = Phrase::getPhrase('editprofile_confirmpassword', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strConfirmPassword = $objConfirmPasswordPhrase ? $objConfirmPasswordPhrase->getPhraseValue(ENCODE_HTML) : 'Confirm Password';
    $objPrivacyNotePhrase = Phrase::getPhrase('editprofile_privacynote', PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
    $strPrivacyNote = $objPrivacyNotePhrase ? $objPrivacyNotePhrase->getPhraseValue(ENCODE_HTML) : 'Privacy Note';
?>
<h1 id="titleheader"><?php echo(strip_tags($_ARCHON->PublicInterface->Title)); ?></h1>
<form action="index.php" accept-charset="UTF-8" method="post">
<div class='userformbox bground'>
<input type="hidden" name="p" value="<?php echo($_REQUEST['p']); ?>" />
<input type="hidden" name="f" value="store" />
<input type="hidden" name="id" value="<?php echo($_ARCHON->Security->Session->User->ID); ?>" />
  <p class="center">
    <span class="bold"><?php echo($_ARCHON->PublicInterface->Title); ?></span><br/>
    <?php echo($strRequired); ?>
  </p>
  <div class="userformpair">
    <div class="userformlabel"><label for="EmailField"><?php echo($strEmail); ?>:</label></div>
    <div class="userforminput"><input type="text" id="EmailField" name="Email" value="<?php echo($_ARCHON->Security->Session->User->Email); ?>" maxlength="50" /> <span style="color:red">*</span></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel"><label for="FirstNameField"><?php echo($strFirstName); ?>:</label></div>
    <div class="userforminput"><input type="text" id="FirstNameField" name="FirstName" value="<?php echo($_ARCHON->Security->Session->User->FirstName); ?>" maxlength="50" /> <span style="color:red">*</span></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel"><label for="LastNameField"><?php echo($strLastName); ?>:</label></div>
    <div class="userforminput"><input type="text" id="LastNameField" name="LastName" value="<?php echo($_ARCHON->Security->Session->User->LastName); ?>" maxlength="50" /> <span style="color:red">*</span></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel"><label for="DisplayNameField"><?php echo($strDisplayName); ?>:</label></div>
    <div class="userforminput"><input type="text" id="DisplayNameField" name="DisplayName" value="<?php echo($_ARCHON->Security->Session->User->DisplayName); ?>" maxlength="100" /> <span style="color:red">*</span></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel"><label for="PasswordField"><?php echo($strPassword); ?>:</label></div>
    <div class="userforminput"><input type="password" id="PasswordField" name="Password" /></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel"><label for="ConfirmPasswordField"><?php echo($strConfirmPassword); ?>:</label></div>
    <div class="userforminput"><input type="password" id="ConfirmPasswordField" name="ConfirmPassword" /></div>
  </div>
  <div class="userformpair">
    <div class="userformlabel"><label for="LanguangeIDField"><?php echo($strLanguage); ?>:</label></div>
    <div class="userforminput">
      <select id="LanguangeIDField" name="LanguageID">
        <option value="0"><?php echo($strSelectOne); ?></option>
<?php
	if(!empty($arrLanguages))
	{
	    foreach($arrLanguages as $objLanguage)
	    {
	        $selected = ($_ARCHON->Security->Session->User->LanguageID == $objLanguage->ID) ? ' selected' : '';
	
	        echo("        <option value=\"$objLanguage->ID\"$selected>" . $objLanguage->toString() . "</option>");
	    }
	}
?>
      </select>
    </div>
  </div>
  <div class="userformpair">
    <div class="userformlabel"><label for="CountryIDField"><?php echo($strCountry); ?>:</label></div>
    <div class="userforminput">
      <select id="CountryIDField" name="CountryID">
        <option value="0"><?php echo($strSelectOne); ?></option>
<?php
    if(!empty($arrCountries))
    {
        foreach($arrCountries as $objCountry)
        {
            $selected = ($UserCountryID == $objCountry->ID) ? ' selected' : '';
    
            echo("        <option value=\"$objCountry->ID\"$selected>" . $objCountry->toString() . "</option>");
        }
    }
?>
      </select><span style="color:red">*</span>
    </div>
  </div>
<?php
    
    $prevUserProfileFieldCategoryID = 0;
    
    $_ARCHON->Security->Session->User->dbLoadUserProfileFields();
        
    foreach($_ARCHON->Security->Session->User->UserProfileFields as $Key => $objUserProfileField)
    {
    	if(is_natural($Key) && $objUserProfileField->UserEditable && (empty($objUserProfileField->Countries) || isset($objUserProfileField->Countries[$UserCountryID])))
    	{
    	    if($prevUserProfileFieldCategoryID != $objUserProfileField->UserProfileFieldCategoryID)
            {
?>
  <div class="userformpair">
    <div class="userformlabel"><label for="UserProfileFieldCategory<?php echo($objUserProfileField->UserProfileFieldCategoryID); ?>"><b><?php echo($objUserProfileField->UserProfileFieldCategory->toString()); ?></b></label></div>
    <div class="userforminput">&nbsp;</div>
  </div>
<?php
                $prevUserProfileFieldCategoryID = $objUserProfileField->UserProfileFieldCategoryID;
            }
            
		$objUserProfileFieldPhrase = Phrase::getPhrase('editprofile_' . strtolower($objUserProfileField->UserProfileField), PACKAGE_CORE, 0, PHRASETYPE_PUBLIC);
	        $strUserProfileField = $objUserProfileFieldPhrase ? $objUserProfileFieldPhrase->getPhraseValue(ENCODE_HTML) : $objUserProfileField->UserProfileField;
	        
	        $required = $objUserProfileField->Required || (isset($objUserProfileField->Countries[$UserCountryID]) && $objUserProfileField->Countries[$UserCountryID]->Required) ? '<span style="color:red">*</span>' : '';
	        $value = isset($_REQUEST['userprofilefields'][$objUserProfileField->ID]['value']) ? $_REQUEST['userprofilefields'][$objUserProfileField->ID]['value'] : $_ARCHON->Security->Session->User->UserProfileFields[$objUserProfileField->ID]->Value;
	
	        if($objUserProfileField->InputType == 'radio')
	        {
	        	if($value)
			    {
			        $checkedYes = ' checked';
			        $checkedNo = '';
			    }
			    else
			    {
			        $checkedYes = '';
			        $checkedNo = ' checked';
			    }
?>
  <div class="userformpair">
    <div class="userformlabel"><label for="<?php echo($objUserProfileField->UserProfileField); ?>Field"><?php echo($strUserProfileField); ?>:</label></div>
    <div class="userforminput"><input type="radio" id="<?php echo($objUserProfileField->UserProfileField); ?>Yes" name="UserProfileFields[<?php echo($objUserProfileField->ID); ?>][Value]" value="1"<?php echo($checkedYes);?> /><?php echo($strYes); ?><input type="radio" id="<?php echo($objUserProfileField->UserProfileField); ?>No" name="UserProfileFields[<?php echo($objUserProfileField->ID); ?>][Value]" value="0"<?php echo($checkedNo);?>  /><?php echo($strNo); ?></div>
  </div>
<?php
	        }
	        elseif($objUserProfileField->InputType == 'select')
	        {
	            $arrSelectChoices = call_user_func(array($_ARCHON, $objUserProfileField->ListDataSource));
?>
  <div class="userformpair">
    <div class="userformlabel"><label for="<?php echo($objUserProfileField->UserProfileField); ?>Field"><?php echo($strUserProfileField); ?>:</label></div>
    <div class="userforminput">
      <select id="<?php echo($objUserProfileField->UserProfileField); ?>Field" name="UserProfileFields[<?php echo($objUserProfileField->ID); ?>][Value]">
        <option value="0"><?php echo($strSelectOne); ?></option>
<?php
	            if(!empty($arrSelectChoices))
	            {
	                foreach($arrSelectChoices as $obj)
	                {
	                    if(!property_exists($obj, 'CountryID') || !isset($obj->CountryID) || $obj->CountryID == $UserCountryID)
                        {
                            $selected = ($value == $obj->ID) ? ' selected' : '';

                            echo("        <option value=\"$obj->ID\"$selected>" . $obj->toString() . "</option>");
                        }
	                }
	            }
?>
      </select> <?php echo($required); ?>
    </div>
  </div>
<?php
	        }
	        elseif($objUserProfileField->InputType == 'textarea')
	        {
?>
  <div class="userformpair">
    <div class="userformlabel"><label for="<?php echo($objUserProfileField->UserProfileField); ?>Field"><?php echo($strUserProfileField); ?>:</label></div>
    <div class="userforminput"><textarea id="<?php echo($objUserProfileField->UserProfileField); ?>Field" name="UserProfileFields[<?php echo($objUserProfileField->ID); ?>][Value]" rows="<?php echo($objUserProfileField->Size); ?>" cols="50"><?php echo($value); ?></textarea> <?php echo($required); ?></div>
  </div>
<?php
	        }
	   		elseif($objUserProfileField->InputType == 'textfield')
	   		{
?>
  <div class="userformpair">
    <div class="userformlabel"><label for="<?php echo($objUserProfileField->UserProfileField); ?>Field"><?php echo($strUserProfileField); ?>:</label></div>
    <div class="userforminput"><input type="text" id="<?php echo($objUserProfileField->UserProfileField); ?>Field" name="UserProfileFields[<?php echo($objUserProfileField->ID); ?>][Value]" value="<?php echo($value); ?>" size="<?php echo($objUserProfileField->Size); ?>" maxlength="<?php echo($objUserProfileField->MaxLength); ?>" /> <?php echo($required); ?></div>
  </div>
<?php
   		    }
            elseif($objUserProfileField->InputType == 'timestamp')
            {
                if($value && is_natural($value))
                {
                    $value = date(CONFIG_CORE_DATE_FORMAT, $value);
                }
?>
  <div class="userformpair">
    <div class="userformlabel"><label for="<?php echo($objUserProfileField->UserProfileField); ?>Field"><?php echo($strUserProfileField); ?>:</label></div>
    <div class="userforminput"><input type="text" id="<?php echo($objUserProfileField->UserProfileField); ?>Field" name="UserProfileFields[<?php echo($objUserProfileField->ID); ?>][Value]" value="<?php echo($value); ?>" size="<?php echo($objUserProfileField->Size); ?>" maxlength="<?php echo($objUserProfileField->MaxLength); ?>" /> <?php echo($required); ?></div>
  </div>
<?php
            }
    	}
    }
?>
  <div id="userformsubmit">
    <input type="submit" value="<?php echo($strSubmit); ?>" class="button" />
  </div>
<br/>
<p class="center"><a href="?p=core/privacy"><?php echo($strPrivacyNote); ?></a></p>
</div>
</form>
<?php
    require_once("footer.inc.php");
}




function editprofile_exec()
{
    global $_ARCHON;

    $objUser = New User($_REQUEST);
    
    $objTmpUser = New User($_ARCHON->Security->Session->User->ID);
    
    if(!$objTmpUser->dbLoad())
    {
    	$_ARCHON->declareError("Could not store User: There was already an error.");
    	$_REQUEST['f'] = '';
    }
    else
    {
    	$objUser->Login = $objTmpUser->Login;
    	$objUser->RegisterTime = $objTmpUser->RegisterTime;
    	$objUser->Pending = $objTmpUser->Pending;
    	$objUser->PendingHash = $objTmpUser->PendingHash;
//    	$objUser->RepositoryID = $objTmpUser->RepositoryID;
    	$objUser->RepositoryLimit = $objTmpUser->RepositoryLimit;
    	$objUser->Locked = $objTmpUser->Locked;

        //Make sure user stays as a public user
        $objUser->IsAdminUser = 0;

	    if($_REQUEST['f'] == 'store')
	    {
	    	$arrUserProfileFields = $_ARCHON->getAllUserProfileFields();
	    	
	        if(!empty($arrUserProfileFields))
	        {
	        	foreach($arrUserProfileFields as $objUserProfileField)
	        	{
	        		if(!$_ARCHON->Security->userHasAdministrativeAccess() && !$_REQUEST['userprofilefields'][$objUserProfileField->ID]['value'] && ($objUserProfileField->Required || (isset($objUserProfileField->Countries[$objUser->CountryID]) && $objUserProfileField->Countries[$objUser->CountryID]->Required)))
	        		{
	        			$_ARCHON->declareError("Could not store User: Required field $objUserProfileField->UserProfileField is empty.");
	        			$_REQUEST['f'] = '';
	        		}
	        	
			        if($_REQUEST['userprofilefields'][$objUserProfileField->ID]['value'] && $objUserProfileField->InputType == 'timestamp' && !is_natural($_REQUEST['userprofilefields'][$objUserProfileField->ID]['value']))
			        {
			            if(($timeValue = strtotime($_REQUEST['userprofilefields'][$objUserProfileField->ID]['value'])) === false)
			            {
			                $_ARCHON->declareError("Could not store User: strtotime() unable to parse value '{$_REQUEST['userprofilefields'][$objUserProfileField->ID]['value']}'.");
			            }
			            else
			            {
                            $_REQUEST['userprofilefields'][$objUserProfileField->ID]['value'] = $timeValue;
			            }
			        }
	        	}
	        }
	        
	        if($_REQUEST['password'] != $_REQUEST['confirmpassword'])
	        {
	            $_ARCHON->declareError("Could not store User: Passwords do not match.");
	            $_REQUEST['f'] = '';
	        }
	        elseif(!$_ARCHON->Error && $objUser->dbStore())
	        {
	            foreach($_REQUEST['userprofilefields'] as $UserProfileFieldID => $arr)
	            {
	            	if(isset($arrUserProfileFields[$UserProfileFieldID]) && $arrUserProfileFields[$UserProfileFieldID]->UserEditable)
	            	{
                        $objUser->dbSetUserProfileField($UserProfileFieldID, $arr['value']);
	            	}
	            }
	
	            $msg = "Profile updated successfully.";
	
	            if(!$_ARCHON->Error)
	            {
                    $location = '?p=core/account';
	            }
	            else
	            {
	            	$location = '?p=core/editprofile';
	            }
	        }
	        else
	        {
	            $_REQUEST['f'] = '';
	        }
	    }
	    else
	    {
	        $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
	        $_REQUEST['f'] = '';
	    }
    }

    if($_ARCHON->Error)
    {
       $msg = $_ARCHON->clearError();
    }

    if($location)
    {
        $_ARCHON->sendMessageAndRedirect($msg, $location);
    }
    else
    {
        $_ARCHON->PublicInterface->Header->Message = $msg;
        editprofile_initialize();
    }
}
