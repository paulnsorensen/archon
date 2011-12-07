<?php
abstract class Core_UserProfileFieldCategory
{
    /**
    * Deletes UserProfileFieldCategory from the database
    *
    * @return boolean
    */
    public function dbDelete()
    {
        global $_ARCHON;
        
        $ID = $this->ID;

        if(!$_ARCHON->deleteObject($this, MODULE_USERPROFILEFIELDCATEGORIES, 'tblCore_UserProfileFieldCategories'))
        {
            return false;
        }
        
        return true;
    }
    
    
    
    
    
    /**
    * Loads UserProfileFieldCategory
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblCore_UserProfileFieldCategories'))
        {
            return false;
        }

        return true;
    }





	/**
    * Stores UserProfileFieldCategory to the database
    *
    * @return boolean
    */
    public function dbStore()
    {
    	global $_ARCHON;

        $checkquery = "SELECT ID FROM tblCore_UserProfileFieldCategories WHERE UserProfileFieldCategory = ? AND ID != ?";
        $checktypes = array('text', 'integer');
        $checkvars = array($this->UserProfileFieldCategory, $this->ID);
        $checkqueryerror = "A UserProfileFieldCategory with the same UserProfileFieldCategory already exists in the database";
        $problemfields = array('UserProfileFieldCategory');
        $requiredfields = array('UserProfileFieldCategory');
        
        if(!$_ARCHON->storeObject($this, MODULE_USERPROFILEFIELDCATEGORIES, 'tblCore_UserProfileFieldCategories', $checkquery, $checktypes, $checkvars, $checkqueryerror, $problemfields, $requiredfields))
        {
            return false;
        }
        
        return true;
    }
    
    
    
    
    /**
     * Outputs UserProfileFieldCategory as a string
     *
     * @return string
     */
    public function toString()
    {
        return $this->getString('UserProfileFieldCategory');
    }

    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $UserProfileFieldCategory = '';

    /**
     * @var integer
     */
    public $DisplayOrder = 1;
}

$_ARCHON->mixClasses('UserProfileFieldCategory', 'Core_UserProfileFieldCategory');
?>