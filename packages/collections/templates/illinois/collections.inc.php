<?php
/**
 * Output file for browsing by collection
 *
 * @package Archon
 * @author Chris Rishel
 */

isset($_ARCHON) or die();

 
//    

    
  
    $arrCollectionCount = $_ARCHON->countCollections(true, true, $_SESSION['Archon_RepositoryID']);
    
     echo("<h1 id='titleheader'>" . strip_tags($_ARCHON->PublicInterface->Title) . "</h1>\n");

    
?>

<div class='center'><span class='bold'><?php echo($strBrowseHoldingsBeginning); ?>:</span><br/><br/>
<div class='bground beginningwith'>
<?php
    if(!empty($arrCollectionCount['#']))
    {
        echo("<a href='?p={$_REQUEST['p']}&amp;char=" . urlencode('#'). "'>-#-</a>" . INDENT);
    }
    else
    {
        echo("-#-" . INDENT);
    }

    for($i = 65; $i < 91; $i++)
    {
        $char = chr($i);

        if(!empty($arrCollectionCount[encoding_strtolower($char)]))
        {
            echo("<a href='?p={$_REQUEST['p']}&amp;char=$char'>-$char-</a>" . INDENT);
        }
        else
        {
            echo("-$char-" . INDENT);
        }

        if($char == 'M')
        {
            echo("<br/><br/>\n");
        }
    }
?>
</div>
</div>
