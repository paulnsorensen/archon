<?php
/**
 * Subject Manager
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
   subjects_ui_main();
}
elseif($_REQUEST['f'] == "search")
{
   subjects_ui_search();
}
elseif($_REQUEST['f'] == "dialog")
{
   subjects_ui_dialog();
}
elseif($_REQUEST['f'] == "tree")
{
   subjects_ui_tree();
}
else
{
   subjects_ui_exec();
}

function subjects_ui_tree()
{
   global $_ARCHON;

   $add_ghost_node = false;

   if($_REQUEST['currentkey'] == 0 && $_REQUEST['pid'] && is_numeric($_REQUEST['pid']))
   {
      // add a ghost node to represent a new item
      $add_ghost_node = true;
      $_REQUEST['currentkey'] = $_REQUEST['pid'];
   }

   $pid = ($_REQUEST['key'] && is_numeric($_REQUEST['key'])) ? $_REQUEST['key'] : 0;

   if($pid == 0 && $_REQUEST['currentkey'] && is_numeric($_REQUEST['currentkey']))
   {
      // build a path of nodes to the root from the current key
      $arrNodes = $_ARCHON->traverseSubject($_REQUEST['currentkey']);

      $arrObjects = array();
      $arrResults = array();

      // we only want one root node
      $arrObjects[] = array($arrNodes[0]->ID => $arrNodes[0]);

      // traverse the path of nodes, adding all content at that level to the array
      foreach($arrNodes as $obj)
      {
         // ignore the last node since we only need its siblings,
         // unless a ghost node needs to be added, in which case the current key
         // is actually the ghost's parent
         if($obj != end($arrNodes) || $add_ghost_node)
         {
            $arrObjects[] = $_ARCHON->getChildSubjects($obj->ID);


            // add a ghost node
            if($obj == end($arrNodes))
            {
               $objGhost = New Subject(0);
               $objGhost->ParentID = $_REQUEST['pid'];
//               $objGhost->ContainsContent = false;

               $lastArray = end($arrObjects);
               if(empty($lastArray))
               {
                  array_pop($arrObjects);
                  $arrObjects[] = array($objGhost);
               }
               else
               {

                  $lastArray[0] = $objGhost;
                  array_pop($arrObjects);
                  $arrObjects[] = $lastArray;
               }

               //return currentkey back to 0
               $_REQUEST['currentkey'] = 0;
            }
         }
      }



      $i = !$add_ghost_node ? count($arrNodes) : count($arrNodes) + 1;
      $deepest_i = $i - 1;
      $found_leaf = false;

      // iterate through array of nodes in bottom up order
      $arrObjects = array_reverse($arrObjects);
      foreach($arrObjects as $array)
      {
         $i--;
         foreach($array as $ID => $obj)
         {
            if(!$found_leaf && $ID == $_REQUEST['currentkey'])
            {
               if($ID != 0)
               {
                  $arrResults[$i][] = '{"key":"' . $ID . '","title":' . json_encode(call_user_func_array(array($obj, 'toString'), array())) . ',"focus": true,"activate": true,"isLazy":' . bool($obj->hasChildren()) . '}';
               }
               else
               {
                  $arrResults[$i][] = '{"key":"' . $ID . '","title":"[New Item]","focus": true,"activate": true,"isLazy":' . bool($obj->hasChildren()) . '}';
               }
               $found_leaf = true;
            }
            elseif($ID == $arrNodes[$i]->ID)
            {
               $arrResults[$i][] = '{"key":"' . $ID . '","title":' . json_encode(call_user_func_array(array($obj, 'toString'), array())) . ',"expand":true,"hideCheckbox":true,"children":[' . implode(",", $arrResults[$i + 1]) . ']}';
               unset($arrResults[$i + 1]);
            }
            else
            {

               $arrResults[$i][] = '{"key":"' . $ID . '","title":' . json_encode(call_user_func_array(array($obj, 'toString'), array())) . ',"hideCheckbox":' . bool($i != $deepest_i) . ',"isLazy":' . bool($obj->hasChildren()) . '}';
            }
         }
      }
      $arrResults = $arrResults[0];
   }
   else
   {

      $arrObjects = $_ARCHON->getChildSubjects($pid);
      $arrResults = array();
      foreach($arrObjects as $ID => $obj)
      {
         $arrResults[] = '{"key":"' . $ID . '","title":' . json_encode(call_user_func_array(array($obj, 'toString'), array())) . ',"isLazy":' . bool($obj->hasChildren()) . '}';
      }
   }

   $callback = ($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

   header('Content-type: application/json; charset=UTF-8');

   if($callback)
   {
      echo($callback . "(");
   }

   echo("[" . implode(",", $arrResults) . "]");

   if($callback)
   {
      echo(");");
   }

   die();
}

// subjects_ui_main()
//   - purpose: Creates the primary user interface
//              for the Subject Manager.
function subjects_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Subject');
   $_ARCHON->AdministrativeInterface->setNameField('Subject');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $objSubject = $_ARCHON->AdministrativeInterface->Object;

   $pid = ($_REQUEST['parentid']) ? $_REQUEST['parentid'] : 0;
   $parentID = ($objSubject->ID) ? $objSubject->ID : $pid;
   $objDeleteMessagePhrase = Phrase::getPhrase('deletemessage_children', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strDeleteMessage = $objDeleteMessagePhrase ? $objDeleteMessagePhrase->getPhraseValue(ENCODE_HTML) : 'Are you sure you want to delete this record AND all of its children (if applicable)?';
   $objDeletePhrase = Phrase::getPhrase('delete', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strDelete = $objDeletePhrase ? $objDeletePhrase->getPhraseValue(ENCODE_HTML) : 'Delete';
   $objAddSiblingPhrase = Phrase::getPhrase('addsibling', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strAddSibling = $objAddSiblingPhrase ? $objAddSiblingPhrase->getPhraseValue(ENCODE_HTML) : 'Add Sibling';
   $objAddChildPhrase = Phrase::getPhrase('addchild', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strAddChild = $objAddChildPhrase ? $objAddChildPhrase->getPhraseValue(ENCODE_HTML) : 'Add Child';
   $objEditPhrase = Phrase::getPhrase('edit', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strEdit = $objEditPhrase ? $objEditPhrase->getPhraseValue(ENCODE_HTML) : 'Edit';
   $objRightClickTreePhrase = Phrase::getPhrase('rightclicktree', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strRightClickTree = $objRightClickTreePhrase ? $objRightClickTreePhrase->getPhraseValue(ENCODE_HTML) : 'Right click on tree nodes to activate context menu';

   ob_start();
   ?>


   <script type="text/javascript">
      /* <![CDATA[ */

      $(function(){
         $("#tree").dynatree({
            checkbox: true,
            autoFocus: false,
            onDblClick: function(node, event){
               location.href = '?p=' + request_p + '&id=' + node.data.key;
            },
            onClick: function(node, event) {
               // Eat mouse events, while a menu is open
               if( $(".contextMenu:visible").length > 0 ){
                  return false;
               }
            },
            onKeydown: function(node, event) {
               // Eat keyboard events, when a menu is open
               if( $(".contextMenu:visible").length > 0 )
                  return false;

               switch( event.which ) {

                  // Open context menu on [Space] key (simulate right click)
                  case 32: // [Space]
                     $(node.span).trigger("mousedown", {
                        preventDefault: true,
                        button: 2
                     })
                     .trigger("mouseup", {
                        preventDefault: true,
                        pageX: node.span.offsetLeft,
                        pageY: node.span.offsetTop,
                        button: 2
                     });
                     return false;

                  // Handle Ctrl-C, -X and -V
               case 67:
                  if( event.ctrlKey ) { // Ctrl-C
                     copyPaste("copy", node);
                     return false;
                  }
                  break;
               case 86:
                  if( event.ctrlKey ) { // Ctrl-V
                     copyPaste("paste", node);
                     return false;
                  }
                  break;
               case 88:
                  if( event.ctrlKey ) { // Ctrl-X
                     copyPaste("cut", node);
                     return false;
                  }
                  break;
            }
         },
         initAjax: {
            url: "index.php",
            dataType: "jsonp",
            data: {
               p: "admin/subjects/subjects",
               f: "tree",
               key: 0,
               currentKey: <?php echo($objSubject->ID ? $objSubject->ID : 0); ?>,
               pid: <?php echo($parentID); ?>
            },
            success: function(){
               bindContextMenu();
            }
         },
         onLazyRead: function(dtnode){
            dtnode.appendAjax({
               url: "index.php",
               dataType: "jsonp",
               data: {
                  p: "admin/subjects/subjects",
                  f: "tree",
                  key: dtnode.data.key
               },
               success: function(){
                  bindContextMenu();
               }
            });
         },
         onSelect: function(select, dtnode){
            if(select){
               $('#tree_menu:hidden').show('blind');
                                                               
               if(($("#tree").dynatree("getSelectedNodes").length > 1)){
                  $('#tree_menu .singular:visible').hide('slide');
               }
            }else{
               if($("#tree").dynatree("getSelectedNodes").length == 0){
                  $('#tree_menu').hide('blind');
               }else if(($("#tree").dynatree("getSelectedNodes").length == 1)){               
                  $('#tree_menu .singular:hidden').show('slide');
               }
            }
         }
      });


      $('#tree_bar').click(function(){
         $('#tree_wrap').toggle('blind');
         if($('#tree_bar').hasClass('tree_arrow_up')){
            $('#tree_bar').removeClass('tree_arrow_up');
            $('#tree_bar').addClass('tree_arrow_down');
         }else{
            $('#tree_bar').removeClass('tree_arrow_down');
            $('#tree_bar').addClass('tree_arrow_up');
         }
      });

      admin_ui_submitcallback(function(){
         if($('#IDs').val() != 0){
            var tree = $("#tree").dynatree("getTree");
            tree.reload();
            $('#tree_menu:visible').hide('blind');
         }
      });
   });

   function bindContextMenu() {
      // Add context menu to all nodes:
      $("span.dynatree-node")
      .destroyContextMenu()
      .contextMenu({menu: "myMenu"}, function(action, el, pos) {
         // The event was bound to the <span> tag, but the node object
         // is stored in the parent <li> tag
         var node = el.parents("[dtnode]").attr("dtnode");
         switch( action ) {
            case "delete":
               var tree = $("#tree").dynatree("getTree");
               admin_ui_confirm('<?php echo($strDeleteMessage); ?>', function() {
                  admin_ui_delete(node.data.key, (node.data.key != $('#IDs').val()));
                  tree.reload();
                  $('#tree_menu:visible').hide('blind');
                  return false
               });
               break;
            case "addchild":
               location.href = '?p=' + request_p + '&parentid=' + node.data.key + '&selectedtab=1';
               break;
            case "addsibling":
               if(node.parent.data.key != '_1'){
                  location.href = '?p=' + request_p + '&parentid=' + node.parent.data.key + '&selectedtab=1';
               }else{
                  location.href = '?p=' + request_p + '&id=0&selectedtab=1';
               }
               break;
            case "edit":
               location.href = '?p=' + request_p + '&id=' + node.data.key;
               break;
            default:
               alert('Invalid Action');
            }
         });
      };


      function add_child(){
         var tree = $("#tree").dynatree("getTree");
         var nodes = tree.getSelectedNodes(true);
         if(nodes.length != 1){
            return false;
         }
                        
         location.href = '?p=' + request_p + '&parentid=' + nodes[0].data.key + '&selectedtab=1';
      }
                           
      function add_sibling(){
         var tree = $("#tree").dynatree("getTree");
         var nodes = tree.getSelectedNodes(true);
         if(nodes.length != 1){
            return false;
         }
                        
         if(nodes[0].parent.data.key != '_1'){
            location.href = '?p=' + request_p + '&parentid=' + nodes[0].parent.data.key + '&selectedtab=1';
         }else{
            location.href = '?p=' + request_p + '&id=0&selectedtab=1';
         }
      }

      function delete_nodes(){
         var tree = $("#tree").dynatree("getTree");
         var nodes = tree.getSelectedNodes(true);
         var values = [];

         var suppressRedirect = true;
         for (i in nodes){
            values.push(nodes[i].data.key);
            if(nodes[i].data.key == $('#IDs').val()){
               suppressRedirect = false;
            }
         }
         admin_ui_confirm('<?php echo($strDeleteMessage); ?>', function() {admin_ui_delete(values, suppressRedirect); tree.reload(); $('#tree_menu:visible').hide('blind'); return false});
      }

      /* ]]> */
   </script>
   <ul id="myMenu" class="contextMenu">
      <li class="edit"><a href="#edit"><?php echo($strEdit); ?></a></li>
      <li class="copy separator"><a href="#addchild"><?php echo($strAddChild); ?></a></li>
      <li class="copy"><a href="#addsibling"><?php echo($strAddSibling); ?></a></li>
      <li class="delete separator"><a href="#delete"><?php echo($strDelete); ?></a></li>
   </ul>
   <div id="tree_wrap" class="clearfix">
      <div id="tree"></div>
      <div id="tree_menu" class="hidden clearfix">
         <div class="singular" style="float:left">
            <button class="adminformbutton" type="button" onclick="add_child();"><?php echo($strAddChild); ?></button>
            <button class="adminformbutton" type="button" onclick="add_sibling();"><?php echo($strAddSibling); ?></button>
         </div>
         <div class="multiple" style="float:left">
            <button class="adminformbutton" type="button" onclick="delete_nodes();"><?php echo($strDelete); ?></button>
         </div>
      </div>
   </div>
   <div class="tree_arrow_up" id="tree_bar"></div>
   <span style="color:#aaa;font-size:.85em;"><?php echo($strRightClickTree); ?></span>

   <?php
   $script = ob_get_clean();
   if($_ARCHON->AdministrativeInterface->Object->ID != 0 || $_ARCHON->AdministrativeInterface->Object->ParentID != 0)
   {
      $generalSection->insertRow('subjecthierarchy')->insertHTML($script);
   }


   $generalSection->insertRow('subject')->insertTextField('Subject', 30, 100)->required();
   $generalSection->insertRow('subjecttypeid')->insertSelect('SubjectTypeID', 'getAllSubjectTypes');

   $parentID = ($_ARCHON->AdministrativeInterface->Object->ParentID) ? $_ARCHON->AdministrativeInterface->Object->ParentID : $_REQUEST['parentid'];
   if(!$parentID)
   {
      $generalSection->insertRow('subjectsourceid')->insertSelect('SubjectSourceID', 'getAllSubjectSources');
   }

   $generalSection->insertRow('identifier')->insertTextField('Identifier', 10, 50);

   //@TODO: show root subjectsource if exists
//    else
//    {
//        $arrSubjects = $_ARCHON->traverseSubject($parentID);
//        $rootSubject = current($arrSubjects);
//        $generalSection->insertRow('subjectsourceid')->insertInformation($rootSubject);//->SubjectSource->toString());
//    }

   $generalSection->insertRow('description')->insertTextArea('Description');


//   $generalSection->insertRow('subjecthierarchy')->insertHierarchicalBrowseInterface(
//           'SubjectHierarchy',
//           'traverseSubject',
//           'searchSubjects',
//           array(
//           'parentid' => $parentID,
//           'subjecttypeid' => 0
//           )
//   );

   if($_ARCHON->AdministrativeInterface->Object->ID)
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=core/search&subjectid={$_ARCHON->AdministrativeInterface->Object->ID}');
                                    $(this).attr('target', '_blank');", 'publicview', false);
   }
   else
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=subjects/subjects');
                                    $(this).attr('target', '_blank');", 'publicview', false);
   }
   $_ARCHON->AdministrativeInterface->insertSearchOption('SubjectTypeID', 'getAllSubjectTypes', 'subjecttypeid');

   $_ARCHON->AdministrativeInterface->setCarryOverFields(array('ParentID'));


   $_ARCHON->AdministrativeInterface->outputInterface();
}

function subjects_ui_dialog()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Subject');
   $_ARCHON->AdministrativeInterface->setNameField('Subject');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   //$_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/subjects/subjects', 'store');


   //$dialogSection->insertRow('parentsubject')->insertInformation('ParentSubject', '[root]');

   $dialogSection->insertRow('subject')->insertTextField('Subject', 30, 100)->required();

   $dialogSection->insertRow('subjecttypeid')->insertSelect('SubjectTypeID', 'getAllSubjectTypes');
   $dialogSection->insertRow('subjectsourceid')->insertSelect('SubjectSourceID', 'getAllSubjectSources');

   $_ARCHON->AdministrativeInterface->outputInterface();
}

// subjects_ui_list()
//   - purpose: Generates the listbox in a separate
//              frame for the main subjects UI.
function subjects_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchSubjects', array('parentid' => NULL, 'subjecttypeid' => 0, 'showchildren' => true, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0), array(), array(LINK_NONE, true));
}

function subjects_ui_exec()
{
   global $_ARCHON;

   //@set_time_limit(0);
   // variables for redirect
   $parentID = 0;
   $count = 0;
   $sameParent = true;

   $name = NULL;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objSubject = New Subject($_REQUEST);
         $objSubject->ID = $ID;
         $objSubject->dbStore();
         $ID = $objSubject->ID;
         $name = $objSubject->getString('Subject');
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      $count = 0;
      foreach($arrIDs as $ID)
      {
         $objSubject = New Subject($ID);
         $objSubject->dbLoad();
         $sameParent = $sameParent && ($parentID == $objSubject->ParentID);
         $parentID = $objSubject->ParentID;
         $objSubject->dbDelete();
         $count++;
      }
   }
   else
   {
      $_ARCHON->declareError("Unknown Command: {$_REQUEST['f']}");
   }

   if($_ARCHON->Error)
   {
      $msg = $_ARCHON->Error;
   }
   else
   {
      $msg = 'Subject Database Updated Successfully.';
   }

   if($_REQUEST['f'] == 'delete')
   {
      $location = ($count > 1 && !$sameParent) ? "index.php?p=admin/subjects/subjects" : "index.php?p=admin/subjects/subjects&id={$parentID}";
   }
   else
   {
      $location = NULL;
   }

   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error, false, $location, NULL, $name);
}
