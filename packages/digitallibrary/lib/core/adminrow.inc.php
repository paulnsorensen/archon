<?php
abstract class DigitalLibrary_AdminRow
{
    public function insertUploadField($FieldName, $Events = array())
    {
        global $_ARCHON;

        $objAdminField = $this->insertField($FieldName, 'uploadfield', $Events);

        return $objAdminField;
    }
}

$_ARCHON->mixClasses('AdminRow', 'DigitalLibrary_AdminRow');
?>