<?php
abstract class DigitalLibrary_MediaType
{
    /**
    * Loads MediaType from the database
    *
    * @return boolean
    */
    public function dbLoad()
    {
        global $_ARCHON;

        if(!$_ARCHON->loadObject($this, 'tblDigitalLibrary_MediaTypes'))
        {
            return false;
        }

        return true;
    }




	/**
    * Generates a formatted string of the MediaType object
    *
    * @todo Custom Formatting
    *
    * @param integer $MakeIntoLink[optional]
    * @return string
    */
    public function toString($MakeIntoLink = LINK_NONE)
    {
        global $_ARCHON;

        if(!$this->ID)
        {
            $_ARCHON->declareError("Could not convert MediaType to string: MediaType ID not defined.");
            return false;
        }

        if(!$this->MediaType)
        {
            $this->dbLoad();
        }

        if($MakeIntoLink == LINK_NONE)
        {
            $String .= $this->getString('MediaType');
        }
        else
        {
            if($_ARCHON->QueryStringURL)
            {
                $q = '&amp;q=' . $_ARCHON->QueryStringURL;
            }

            $String .= "<a href='?p=core/search&amp;mediatypeid={$this->ID}$q'>{$this->getString('MediaType')}</a>";
        }

        return $String;
    }

    /**
     * @var integer
     */
    public $ID = 0;

    /**
     * @var string
     */
    public $MediaType = NULL;

     public $ToStringFields = array('ID', 'MediaType');
}

$_ARCHON->mixClasses('MediaType', 'DigitalLibrary_MediaType');
?>