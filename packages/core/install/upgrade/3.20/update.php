<?php

isset($_ARCHON) or die();

ArchonInstaller::updateDBProgressTable('', "Set ResearchFunctionality to ALL by default");

$query = "UPDATE tblCore_Repositories SET ResearchFunctionality = " . RESEARCH_ALL;

ArchonInstaller::execQuery($query);

?>
