<?php
/**
 * Classification Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Chris Rishel
 */
isset($_ARCHON) or die();

classification_ui_initialize();

// Determine what to do based upon user input
function classification_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      classification_ui_main();
   }
   elseif($_REQUEST['f'] == "search")
   {
      classification_ui_search();
   }
   elseif($_REQUEST['f'] == "hierarchicalselect")
   {
      classification_ui_hierarchicalselect();
   }
   elseif($_REQUEST['f'] == "tree")
   {
      classification_ui_tree();
   }
   elseif($_REQUEST['f'] == 'dialog_transfer')
   {
      classification_ui_dialog_transfer();
   }
   else
   {
      classification_ui_exec();
   }
}

function classification_ui_dialog_transfer()
{
   global $_ARCHON;


   $_ARCHON->AdministrativeInterface->setClass('Classification');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/collections/classification', 'transfer');
   $div = "<div id='transfer-items'><ul style='list-style-type:none;margin-left:6px'>";
   foreach($_ARCHON->AdministrativeInterface->IDs as $ID)
   {
      $obj = New Classification($ID);
      $div .= "<li>" . $obj->toString() . "</li>";
   }
   $div .= "</ul></div>";
   $dialogSection->insertRow('selecteditems')->insertHTML($div);
   $_ARCHON->AdministrativeInterface->Object->ID = $_ARCHON->AdministrativeInterface->IDs[0];

   $dialogSection->insertRow('newpath')->insertHTML("<input id='transfer-classificationid' type='hidden' value='0' name='ClassificationID' /><div id='transfer-classifications' style='max-height:400px;overflow:auto'></div>");
      
   $_ARCHON->AdministrativeInterface->outputInterface();
}

function classification_ui_tree()
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
      $arrNodes = $_ARCHON->traverseClassification($_REQUEST['currentkey']);

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
            $arrObjects[] = $_ARCHON->getChildClassifications($obj->ID);


            // add a ghost node
            if($obj == end($arrNodes))
            {
               $objGhost = New Classification(0);
               $objGhost->ParentID = $_REQUEST['pid'];

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

      $arrObjects = $_ARCHON->getChildClassifications($pid);
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

function classification_ui_hierarchicalselect()
{
   global $_ARCHON;

   $pid = ($_REQUEST['parentid'] && is_numeric($_REQUEST['parentid'])) ? $_REQUEST['parentid'] : 0;

   $arrObjects = $_ARCHON->getChildClassifications($pid);

   $callback = ($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

   header('Content-type: application/json; charset=UTF-8');

   $arrResults = array();
   foreach($arrObjects as $ID => $obj)
   {
      $arrResults[] = '{"id":"' . $ID . '","text":' . json_encode(caplength(call_user_func_array(array($obj, 'toString'), array()), CONFIG_CORE_RELATED_OPTION_MAX_LENGTH)) . '}';
   }

   if($callback)
   {
      echo($callback . "(");
   }
   echo("{\"results\":[" . implode(",", $arrResults) . "]}");
   if($callback)
   {
      echo(");");
   }

   die();
}

// ***********************************************
// * classification_ui_main()                      *
// ***********************************************
//
//   - Purpose: Creates the primary user interface
//              for the Classification Manager.
//
//   - Incoming Variables:
//
//      - $in_ID (Integer) (Optional):
//           ID from tblCollections_Classification which
//           indicates the current classification.
//
// ***********************************************
function classification_ui_main()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('Classification');

   $_ARCHON->AdministrativeInterface->setNameField('ClassificationIdentifier');

   $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

   $objClassification = $_ARCHON->AdministrativeInterface->Object;

   $pid = ($_REQUEST['parentid']) ? $_REQUEST['parentid'] : 0;
   $parentID = ($objClassification->ID) ? $objClassification->ID : $pid;



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
   $objTransferPhrase = Phrase::getPhrase('transfer', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strTransfer = $objTransferPhrase ? $objTransferPhrase->getPhraseValue(ENCODE_HTML) : 'Transfer';
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
               p: "admin/collections/classification",
               f: "tree",
               key: 0,
               currentKey: <?php echo($objClassification->ID ? $objClassification->ID : 0); ?>,
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
                  p: "admin/collections/classification",
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



      function launch_transfer(){
         var tree = $("#tree").dynatree("getTree");
         var nodes = tree.getSelectedNodes(true);
         var values = [];
         for (i in nodes){
            values.push(nodes[i].data.key);
         }
         admin_ui_dialogcallback(function(){var tree = $("#tree").dynatree("getTree"); tree.reload(); $('#tree_menu:visible').hide('blind');});
         admin_ui_opendialog("collections","classification", "transfer", {"IDs[]": values});
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
            <button class="adminformbutton" type="button" onclick="launch_transfer();"><?php echo($strTransfer); ?></button></div>
      </div>
   </div>
   <div class="tree_arrow_up" id="tree_bar"></div>
   <span style="color:#aaa;font-size:.85em;"><?php echo($strRightClickTree); ?></span>

   <?php
   $script = ob_get_clean();
   if($_ARCHON->AdministrativeInterface->Object->ID != 0 || $_ARCHON->AdministrativeInterface->Object->ParentID != 0)
   {
      $generalSection->insertRow('classificationhierarchy')->insertHTML($script);
   }



   $generalSection->insertRow('classificationidentifier')->insertTextField('ClassificationIdentifier', 5, 50);
   $generalSection->insertRow('title')->insertTextField('Title', 50, 255)->required();
   $generalSection->insertRow('description')->insertTextArea('Description');

//   $generalSection->insertRow('currentcreator')->insertSelect('CreatorID', 'getAllCreators', array(true));
   $generalSection->insertRow('currentcreator')->insertAdvancedSelect('CreatorID', array(
       'Class' => 'Creator',
       'Multiple' => false,
       'toStringArguments' => array(true),
       'params' => array(
           'p' => 'admin/creators/creators',
           'f' => 'search',
           'searchtype' => 'json'
       ),
       'quickAdd' => "advSelectID =\\\"CreatorsRelatedCreatorIDs\\\"; admin_ui_opendialog(\\\"creators\\\", \\\"creators\\\");"
   ));

   $generalSection->insertHiddenField('ParentID');


   $_ARCHON->AdministrativeInterface->setCarryOverFields(array('ParentID'));


   $_ARCHON->AdministrativeInterface->insertSearchOption(
           array('ParentID'), array('traverseClassification'), 'classificationid', array('getChildClassifications'), array('Classification')
   );

   if($_ARCHON->AdministrativeInterface->Object->ID)
   {

      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=collections/classifications&id={$_ARCHON->AdministrativeInterface->Object->ID}');
                                    $(this).attr('target', '_blank');", 'publicview', false);
   }
   else
   {
      $_ARCHON->AdministrativeInterface->insertHeaderControl(
              "$(this).attr('href', '?p=collections/classifications');
                                    $(this).attr('target', '_blank');", 'publicview', false);
   }
   $_ARCHON->AdministrativeInterface->outputInterface();
}

function classification_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchClassifications', array('parentid' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function classification_ui_exec()
{
   global $_ARCHON;

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');


   // variables for redirect
   $parentID = 0;
   $count = 0;
   $sameParent = true;


   if($_REQUEST['f'] == 'store')
   {
      foreach($arrIDs as &$ID)
      {
         $objClassification = New Classification($_REQUEST);

         $objClassification->ID = $ID;
         $objClassification->ClassificationIdentifier = $objClassification->ClassificationIdentifier ? $objClassification->ClassificationIdentifier : 0;
         $objClassification->CreatorID = $objClassification->CreatorID ? $objClassification->CreatorID : 0;

         $objClassification->dbStore();
         $ID = $objClassification->ID;
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      $count = 0;
      foreach($arrIDs as $ID)
      {
         $objClassification = New Classification($ID);
         $objClassification->dbLoad();
         $sameParent = $sameParent && ($parentID == $objClassification->ParentID);
         $parentID = $objClassification->ParentID;
         $objClassification->dbDelete();
         $count++;
      }
   }
   elseif($_REQUEST['f'] == 'transfer')
   {
      foreach($arrIDs as &$ID)
      {
         $objClassification = New Classification($ID);
         $objClassification->dbLoad();

         $objClassification->ParentID = $_REQUEST['classificationid'];

         $objClassification->dbStore();
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
      $msg = 'Classification Database Updated Successfully.';
   }

   if($_REQUEST['f'] == 'delete')
   {
      $location = ($count > 1 && !$sameParent) ? "index.php?p=admin/collections/classification" : "index.php?p=admin/collections/classification&id={$parentID}";
   }
   else
   {
      $location = NULL;
   }



   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error, false, $location);
}