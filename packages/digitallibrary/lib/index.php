<?php
/**
 * Digital Library Package
 *
 * @package Archon
 * @subpackage API
 * @author Chris Rishel
 */

class DigitalContent extends ArchonObject
{

}
class File extends ArchonObject
{

}
class FileContents extends ArchonObject
{

}
class FileType extends ArchonObject
{

}
class MediaType extends ArchonObject
{

}

$_ARCHON->registerInclude('DigitalContent', 'digitalcontent.inc.php');
$_ARCHON->registerInclude('File', 'file.inc.php');
$_ARCHON->registerInclude('FileType', 'filetype.inc.php');
$_ARCHON->registerInclude('MediaType', 'mediatype.inc.php');

$_ARCHON->registerInclude('AdminField', 'core/adminfield.inc.php');
$_ARCHON->registerInclude('AdminRow', 'core/adminrow.inc.php');
$_ARCHON->registerInclude('Archon', 'core/archon.inc.php');
//$_ARCHON->registerInclude('User', 'core/user.inc.php');

if(defined('PACKAGE_CREATORS'))
{
    $_ARCHON->registerInclude('Creator', 'creators/creator.inc.php');
}

if(defined('PACKAGE_COLLECTIONS'))
{
    $_ARCHON->registerInclude('Collection', 'collections/collection.inc.php');
    $_ARCHON->registerInclude('CollectionContent', 'collections/collectioncontent.inc.php');
}

define('DIGITALLIBRARY_ACCESSLEVEL_NONE', 0, false);
define('DIGITALLIBRARY_ACCESSLEVEL_PREVIEWONLY', 1, false);
define('DIGITALLIBRARY_ACCESSLEVEL_FULL', 2, false);

define('DIGITALLIBRARY_FILE_FULL', 0, false);
define('DIGITALLIBRARY_FILE_PREVIEWLONG', 1, false);
define('DIGITALLIBRARY_FILE_PREVIEWSHORT', 2, false);

define('SEARCH_DEFAULTACCESSLEVEL_NONE', nextbitmask('SEARCH'), false);
define('SEARCH_DEFAULTACCESSLEVEL_PREVIEWONLY', nextbitmask('SEARCH'), false);
define('SEARCH_DEFAULTACCESSLEVEL_FULL', nextbitmask('SEARCH'), false);
define('SEARCH_DEFAULTACCESSLEVEL_ANY', SEARCH_DEFAULTACCESSLEVEL_NONE | SEARCH_DEFAULTACCESSLEVEL_PREVIEWONLY | SEARCH_DEFAULTACCESSLEVEL_FULL, false);

define('SEARCH_NOTBROWSABLE', nextbitmask('SEARCH'), false);

// This may become more robust later, but for now it will just duplicate SEARCH_DEFAULTACCESSLEVEL_ANY
define('SEARCH_DIGITALCONTENT', SEARCH_DEFAULTACCESSLEVEL_ANY | SEARCH_NOTBROWSABLE, false);

define('SEARCH_FILES_UNLINKED', nextbitmask('SEARCH'), false);
define('SEARCH_FILES_LINKED', nextbitmask('SEARCH'), false);
define('SEARCH_FILES_ALL', SEARCH_FILES_UNLINKED | SEARCH_FILES_LINKED, false);
?>
