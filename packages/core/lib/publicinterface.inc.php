<?php
abstract class Core_PublicInterface
{
    /**
     * Adds entry to navigation array for use in a "breadcrumbs" style display in the public interface
     *
     * @param string $Title
     * @param string $URL
     * @param boolean $AddToFront
     * @return boolean
     */
    public function addNavigation($Title, $URL = NULL, $AddToFront = false)
    {
        if(!$Title)
        {
            return false;
        }

        $objNavigation->Title = $Title;
        $objNavigation->URL = $URL;

        if($AddToFront)
        {
            array_unshift($this->Navigation, $objNavigation);
        }
        else
        {
            array_push($this->Navigation, $objNavigation);
        }

        return true;
    }




    /**
     * Returns HTML for "breadcrumbs" style navigation display
     *
     * @return string
     */
    public function createNavigation()
    {
        global $_ARCHON;

        if(!empty($this->Navigation))
        {
            $Count = 0;

            foreach($this->Navigation as $objNavigation)
            {
                $Count++;

                $String .= ($objNavigation->URL && count($this->Navigation) > $Count) ? "<a href='$objNavigation->URL'>".$objNavigation->Title."</a>" : $objNavigation->Title;

                if(count($this->Navigation) > $Count)
                {
                    $String .= $_ARCHON->PublicInterface->Delimiter;
                }
            }
        }

        return $String;
    }



    public function outputGoogleAnalyticsCode()
    {
       global $_ARCHON;

       if($_ARCHON->config->GACode)
       {
       ?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try{
var pageTracker = _gat._getTracker("<?php echo($_ARCHON->config->GACode); ?>");
pageTracker._trackPageview();
} catch(err) {}
</script>
       <?php
       }
    }



    /**
     * Initializes PublicInterface
     *
     * @param string $Theme
     * @param string $TemplateSet
     */
    public function initialize($Theme, $TemplateSet)
    {
        global $_ARCHON;

        if(preg_match('/[\\/\\\\]/u', $Theme) || !file_exists('themes/' . $Theme))
        {
            $Theme = CONFIG_CORE_DEFAULT_THEME;
        }

        $this->Theme = $Theme;

        $this->ImagePath = "themes/$Theme/images";
        if(is_dir("themes/$Theme/js"))
        {
            $this->ThemeJavascriptPath = "themes/$Theme/js";
        }

        if(file_exists('themes/' . $this->Theme . '/init.inc.php'))
        {
            $cwd = getcwd();

            chdir('themes/' . $this->Theme);

            require_once('init.inc.php');

            chdir($cwd);
        }

        $this->TemplateSet = $TemplateSet;
        $this->Templates = $_ARCHON->loadTemplates($this->TemplateSet);
    }

    /**
     * Indicates if toString and getString functions should escape values before returning their string
     *
     * @var boolean
     */
    public $EscapeXML = CONFIG_ESCAPE_XML;

    public $Delimiter = ' -> ';

    public $DisableTheme = false;

    public $ImagePath = NULL;

    public $TemplateSet = NULL;

    public $Title = NULL;

    public $Theme = NULL;

    public $Navigation = array();

    public $Templates = array();

    public $PublicSearchFunctions = array();
}

$_ARCHON->mixClasses('PublicInterface', 'Core_PublicInterface');
?>