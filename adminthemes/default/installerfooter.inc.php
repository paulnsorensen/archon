<?php
/**
 * Footer file for all Administrative scripts.
 *
 * @package Archon
 * @author Kyle Fox, Paul Sorensen
 */

isset($_ARCHON) or die();

if(!$_ARCHON->AdministrativeInterface->Header->NoControls)
{

    $strCopyright = 'Copyright &copy;$1 <a href="http://www.uiuc.edu/">The University of Illinois at Urbana-Champaign</a>';
    $strCopyright = str_replace('$1', $_ARCHON->CopyrightYear, $strCopyright);
?>
          </div>
        </div>
      </div>
      <div id="footer">
<?php
    echo($_ARCHON->AdministrativeInterface->FooterHTML);
?>
        <p>
          Powered by <a href='<?php echo($_ARCHON->ArchonURL); ?>'>Archon</a> Version <?php echo($_ARCHON->Version); ?><br/>
          <?php echo($strCopyright); ?>
        </p>
        <p>
          <br />
          Page Generated in: <?php echo(round(microtime(true) - $_ARCHON->StartTime, 3)); ?> seconds (<?php echo(round(100 * $_ARCHON->db->dbTime / (microtime(true) - $_ARCHON->StartTime))); ?>% SQL in <?php echo($_ARCHON->db->QueryCount); ?> queries)
        </p>
      </div>
    </div>
  </div>
  </div>
<?php
}
?>
</body>
</html>