<?php
/**
 * Collection Content Manager
 *
 *
 * @package Archon
 * @subpackage AdminUI
 * @author Kyle Fox, Paul Sorensen
 */
isset($_ARCHON) or die();

ob_implicit_flush();

collectioncontent_ui_initialize();

// Determine what to do based upon user input
function collectioncontent_ui_initialize()
{
   if(!$_REQUEST['f'])
   {
      collectioncontent_ui_main();
   }
   elseif($_REQUEST['f'] == 'search')
   {
      collectioncontent_ui_search();
   }
   elseif($_REQUEST['f'] == 'tree')
   {
      collectioncontent_ui_tree();
   }
   elseif($_REQUEST['f'] == 'getnextsortorder')
   {
      collectioncontent_ui_getnextsortorder();
   }
   elseif($_REQUEST['f'] == 'getsiblingcontent')
   {
      collectioncontent_ui_getsiblingcontent();
   }
   elseif($_REQUEST['f'] == 'dialog_renumber')
   {
      collectioncontent_ui_dialog_renumber();
   }
   elseif($_REQUEST['f'] == 'dialog_transfer')
   {
      collectioncontent_ui_dialog_transfer();
   }
   else
   {
      collectioncontent_ui_exec();
   }
}

function collectioncontent_ui_getsiblingcontent()
{
   global $_ARCHON;

   $id = $_REQUEST['id'];

   if(!$id || !is_numeric($id))
   {
      $id = NULL;
   }

   $arrObjects = $_ARCHON->getCollectionContentLevel($_REQUEST['collectionid'], $_REQUEST['parentid'], $id);
   $arrResults = array();
   foreach($arrObjects as $ID => $obj)
   {
      $arrResults[] = '{"id":"' . $ID . '","text":' . json_encode(call_user_func_array(array($obj, 'toString'), array())) . ',"sortorder":"' . $obj->SortOrder . '"}';
   }


   $callback = ($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

   header('Content-type: application/json; charset=UTF-8');

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

function collectioncontent_ui_dialog_transfer()
{
   global $_ARCHON;


   $_ARCHON->AdministrativeInterface->setClass('CollectionContent');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/collections/collectioncontent', 'transfer');
   $div = "<div id='transfer-items'><ul style='list-style-type:none;margin-left:6px'>";
   foreach($_ARCHON->AdministrativeInterface->IDs as $ID)
   {
      $obj = New CollectionContent($ID);
      $div .= "<li>" . $obj->toString() . "</li>";
   }
   $div .= "</ul></div>";
   $dialogSection->insertRow('selecteditems')->insertHTML($div);

   $_ARCHON->AdministrativeInterface->Object->ID = $_ARCHON->AdministrativeInterface->IDs[0];

   $objNoSelectionPhrase = Phrase::getPhrase('selectone', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
   $strNoSelection = $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';

   $objRepositoryPhrase = Phrase::getPhrase('repository', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strRepository = $objRepositoryPhrase ? $objRepositoryPhrase->getPhraseValue(ENCODE_HTML) : 'Repository';

   $objClassificationPhrase = Phrase::getPhrase('classification', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
   $strClassification = $objClassificationPhrase ? $objClassificationPhrase->getPhraseValue(ENCODE_HTML) : 'Classification';

   $dialogSection->insertRow('collection')->insertAdvancedSelect('CollectionID', array(
       'Class' => 'Collection',
       'Multiple' => false,
       'toStringArguments' => array(),
       'params' => array(
           'p' => 'admin/collections/collections',
           'f' => 'search',
           'searchtype' => 'json',
       ),
       'quickAdd' => "advSelectID =\\\"CollectionIDInput\\\"; admin_ui_opendialog(\\\"collections\\\", \\\"collections\\\");",
       'searchOptions' => array(
           array(
               'label' => $strRepository,
               'name' => 'RepositoryID',
               'source' => 'index.php?p=admin/core/repositories&f=list'
           ),
           array(
               'label' => $strClassification,
               'name' => 'ClassificationID',
               'hierarchical' => true,
               'url' => 'index.php',
               'params' => '{p: "admin/collections/classification", f: "hierarchicalselect"}',
               'noselection' => '{id: 0, text: "' . $strNoSelection . '"}'
           )
       )
   ));


   $dialogSection->insertRow('newpath')->insertHTML("<input id='transfer-contentid' type='hidden' value='0' name='CollectionContentID' /><div id='transfer-content' style='max-height:400px;overflow:auto'></div>");

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function collectioncontent_ui_dialog_renumber()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->setClass('CollectionContent');

   $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
   $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
   $dialogSection->setDialogArguments('form', NULL, 'admin/collections/collectioncontent', 'renumbercontent');
   $dialogSection->insertRow('amount')->insertTextField('ShiftAmount', 10, 10);
   $dialogSection->insertRow('direction')->insertRadioButtons('ShiftDirection', array(UP => 'up', DOWN => 'down'));
   $dialogSection->insertRow('shiftsortorder')->insertCheckBox('ShiftSortOrder');

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function collectioncontent_ui_tree()
{
   global $_ARCHON;

   $cid = $_REQUEST['collectionid'];

   if(!$cid || !is_numeric($cid))
   {
      $callback = ($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

      header('Content-type: application/json; charset=UTF-8');

      if($callback)
      {
         echo($callback . "(");
      }

      echo('{"error":"CollectionID not defined"}');

      if($callback)
      {
         echo(");");
      }

      die();
   }

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
      $arrNodes = $_ARCHON->traverseCollectionContent($_REQUEST['currentkey']);



      $arrObjects = array();
      $arrResults = array();

      // get all root nodes
      $arrObjects[] = $_ARCHON->getChildCollectionContent(0, $cid);

      // traverse the path of nodes, adding all content at that level to the array
      foreach($arrNodes as $obj)
      {
         // ignore the last node since we only need its siblings,
         // unless a ghost node needs to be added, in which case the current key
         // is actually the ghost's parent
         if($obj != end($arrNodes) || $add_ghost_node)
         {
            $arrObjects[] = $_ARCHON->getChildCollectionContent($obj->ID, $cid);


            // add a ghost node
            if($obj == end($arrNodes))
            {
               $objGhost = New CollectionContent(0);
               $objGhost->ParentID = $_REQUEST['pid'];
               $objGhost->ContainsContent = false;

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
                  $arrResults[$i][] = '{"key":"' . $ID . '","title":' . json_encode(call_user_func_array(array($obj, 'toString'), array())) . ',"focus": true,"activate": true,"isLazy":' . bool($obj->ContainsContent) . '}';
               }
               else
               {
                  $arrResults[$i][] = '{"key":"' . $ID . '","title":"[New Item]","focus": true,"activate": true,"isLazy":' . bool($obj->ContainsContent) . '}';
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

               $arrResults[$i][] = '{"key":"' . $ID . '","title":' . json_encode(call_user_func_array(array($obj, 'toString'), array())) . ',"hideCheckbox":' . bool($i != $deepest_i) . ',"isLazy":' . bool($obj->ContainsContent) . '}';
            }
         }
      }
      $arrResults = $arrResults[0];
   }
   else
   {

      $arrObjects = $_ARCHON->getChildCollectionContent($pid, $cid);
      $arrResults = array();
      foreach($arrObjects as $ID => $obj)
      {
         $arrResults[] = '{"key":"' . $ID . '","title":' . json_encode(call_user_func_array(array($obj, 'toString'), array())) . ',"isLazy":' . bool($obj->ContainsContent) . '}';
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

function collectioncontent_ui_getnextsortorder()
{
   global $_ARCHON;

   echo(json_encode($_ARCHON->getNextContentSortOrder($_REQUEST['collectionid'], $_REQUEST['parentid'])));
}

// collectioncontent_ui_main()
//   - purpose: Creates the primary user interface
//              for the Collections Manager.
function collectioncontent_ui_main()
{
   global $_ARCHON;

   $arrPhysOnlyLCIDs = array();
   foreach($_ARCHON->getAllLevelContainers() as $ID => $objLevelContainer)
   {
      if($objLevelContainer->PhysicalContainer && !$objLevelContainer->IntellectualLevel)
      {
         $arrPhysOnlyLCIDs[] = $ID;
      }
   }

   $_ARCHON->AdministrativeInterface->setClass('CollectionContent');
   $_ARCHON->AdministrativeInterface->setNameField('Title');
   $_ARCHON->AdministrativeInterface->disableQuickSearch();

   $_ARCHON->AdministrativeInterface->getSection('browse')->disable();


   if($_REQUEST['parentid'] && !($_REQUEST['collectionid'] || $_ARCHON->AdministrativeInterface->Object->CollectionID))
   {
      $objParentContent = New CollectionContent($_REQUEST['parentid']);
      $objParentContent->dbLoad();
      $_ARCHON->AdministrativeInterface->Object->CollectionID = $objParentContent->CollectionID;
   }


   if($_ARCHON->AdministrativeInterface->Object->CollectionID || $_REQUEST['collectionid'])
   {
      $objContent = $_ARCHON->AdministrativeInterface->Object;
      $objContent->CollectionID = ($objContent->CollectionID) ? $objContent->CollectionID : $_REQUEST['collectionid'];

      $_ARCHON->AdministrativeInterface->insertHeaderControl("admin_ui_goto('admin/collections/collections',{collectionid: {$objContent->CollectionID}});", 'editcollection');

      $displayRootContent = $_REQUEST['displayrootcontent'];
      unset($_REQUEST['displayrootcontent']);


      if($objContent->CollectionID && !$objContent->Collection)
      {
         $objContent->Collection = new Collection($objContent->CollectionID);
         $objContent->Collection->dbLoad();
      }

      if(!$displayRootContent)
      {
         $pid = ($_REQUEST['parentid']) ? $_REQUEST['parentid'] : 0;
         $pID = ($objContent->ParentID) ? $objContent->ParentID : $pid;
         $parentID = ($objContent->ID) ? $objContent->ID : $pid;
      }
      else
      {
         $parentID = 0;
      }

      $objChangeCollectionPhrase = Phrase::getPhrase('changecollection', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strChangeCollection = $objChangeCollectionPhrase ? $objChangeCollectionPhrase->getPhraseValue(ENCODE_HTML) : 'Change Collection';
      

      $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');

      $collectionslink = "<a style='float:left' href='index.php?p=" . $_REQUEST['p'] . "&amp;collectionid=" . $objContent->CollectionID . "&amp;classificationid=" . $objContent->Collection->ClassificationID . "&amp;id=0&amp;displayrootcontent=true'>" . bb_decode($objContent->Collection->toString()) . "</a>";
      $collectionslink .= "<a class=adminformbutton style='margin-left:20px; font-size:70%' href='index.php?p=". $_REQUEST['p'] ."'>".$strChangeCollection."</a>";
      $generalSection->insertRow('parentcollection')->insertHTML($collectionslink, 'Collection');
      $collectionIDField = $generalSection->insertHiddenField('CollectionID');
//        $collectionIDField->IDPrefix = "hidden-"; //this is just to make it valid


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
      $objRenumberPhrase = Phrase::getPhrase('renumber', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strRenumber = $objRenumberPhrase ? $objRenumberPhrase->getPhraseValue(ENCODE_HTML) : 'Renumber';
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
                  p: "admin/collections/collectioncontent",
                  f: "tree",
                  key: 0,
                  currentKey: <?php echo($objContent->ID ? $objContent->ID : 0); ?>,
                  collectionid: <?php echo($objContent->CollectionID); ?>,
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
                     p: "admin/collections/collectioncontent",
                     f: "tree",
                     key: dtnode.data.key,
                     collectionid: <?php echo($objContent->CollectionID); ?>
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
                  location.href = '?p=' + request_p + '&parentid=' + node.data.key;
                  break;
               case "addsibling":
                  if(node.parent.data.key != '_1'){
                     location.href = '?p=' + request_p + '&parentid=' + node.parent.data.key;
                  }else{
                     location.href = '?p=' + request_p + '&id=0&collectionid=<?php echo($objContent->CollectionID); ?>';
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
                                                                              
            location.href = '?p=' + request_p + '&parentid=' + nodes[0].data.key;
         }
                                                                                 
         function add_sibling(){
            var tree = $("#tree").dynatree("getTree");
            var nodes = tree.getSelectedNodes(true);
            if(nodes.length != 1){
               return false;
            }
                                                                              
            if(nodes[0].parent.data.key != '_1'){
               location.href = '?p=' + request_p + '&parentid=' + nodes[0].parent.data.key;
            }else{
               location.href = '?p=' + request_p + '&id=0&collectionid=<?php echo($objContent->CollectionID); ?>';
            }
         }

         function launch_renumber(){
            var tree = $("#tree").dynatree("getTree");
            var nodes = tree.getSelectedNodes(true);

            var values = [];
            for (i in nodes){
               values.push(nodes[i].data.key);
            }
            admin_ui_dialogcallback(function(){var tree = $("#tree").dynatree("getTree"); tree.reload(); $('#tree_menu:visible').hide('blind');});
            admin_ui_opendialog('collections', 'collectioncontent', 'renumber', {'IDs[]': values});
         }

         function launch_transfer(){
            var tree = $("#tree").dynatree("getTree");
            var nodes = tree.getSelectedNodes(true);
            var values = [];
            for (i in nodes){
               values.push(nodes[i].data.key);
            }
            admin_ui_dialogcallback(function(){var tree = $("#tree").dynatree("getTree"); tree.reload(); $('#tree_menu:visible').hide('blind');});
            admin_ui_opendialog("collections","collectioncontent", "transfer", {"IDs[]": values});
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
               <button class="adminformbutton" type="button" onclick="launch_renumber();"><?php echo($strRenumber); ?></button>
               <button class="adminformbutton" type="button" onclick="launch_transfer();"><?php echo($strTransfer); ?></button>
            </div>
         </div>
      </div>
      <div class="tree_arrow_up" id="tree_bar"></div>
      <span style="color:#aaa;font-size:.85em;"><?php echo($strRightClickTree); ?></span>

      <?php
      $script = ob_get_clean();
      $generalSection->insertRow('contenthierarchy')->insertHTML($script);


      if(!$displayRootContent)
      {

         $generalSection->insertRow('levelcontainerid')->insertSelect('LevelContainerID', 'getAllLevelContainers')->required();
         $generalSection->insertRow('levelcontaineridentifier')->insertTextField('LevelContainerIdentifier', 5, 10)->required();
         $generalSection->insertRow('title')->insertNameField('Title');
         $generalSection->getRow('title')->setEnableConditions('LevelContainerID', $arrPhysOnlyLCIDs, true, true);
         $generalSection->insertRow('privatetitle')->insertNameField('PrivateTitle');
         $generalSection->getRow('privatetitle')->setEnableConditions('LevelContainerID', $arrPhysOnlyLCIDs, true, true);
         $objContent->SortOrder = ($objContent->SortOrder) ? $objContent->SortOrder : $_ARCHON->getNextContentSortOrder($objContent->CollectionID, $pID);

         $generalSection->insertRow('sortorder')->insertTextField('SortOrder', 5, 11);
//         $generalSection->insertRow('enabled')->insertRadioButtons('Enabled');


         if($objContent->ID)
         {
            $objMoveEndPhrase = Phrase::getPhrase('moveend', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strSortOrderEnd = $objMoveEndPhrase ? $objMoveEndPhrase->getPhraseValue(ENCODE_HTML) : 'move to the end';

            $objMoveBeginningPhrase = Phrase::getPhrase('movebeginning', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strSortOrderBeginning = $objMoveBeginningPhrase ? $objMoveBeginningPhrase->getPhraseValue(ENCODE_HTML) : 'move to the beginning';

            $objMoveAfterPhrase = Phrase::getPhrase('moveafter', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strSortOrderAfter = $objMoveAfterPhrase ? $objMoveAfterPhrase->getPhraseValue(ENCODE_HTML) : 'move after';

            $objMoveBeforePhrase = Phrase::getPhrase('movebefore', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strSortOrderBefore = $objMoveBeforePhrase ? $objMoveBeforePhrase->getPhraseValue(ENCODE_HTML) : 'move before';
         }
         else
         {
            //TODO: Find a way to disable save/update buttons
//            $_ARCHON->AdministrativeInterface->CanDelete = false;
//            $_ARCHON->AdministrativeInterface->CanUpdate = false;

            $objInsertEndPhrase = Phrase::getPhrase('insertend', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strSortOrderEnd = $objInsertEndPhrase ? $objInsertEndPhrase->getPhraseValue(ENCODE_HTML) : 'insert at the end';

            $objInsertBeginningPhrase = Phrase::getPhrase('insertbeginning', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strSortOrderBeginning = $objInsertBeginningPhrase ? $objInsertBeginningPhrase->getPhraseValue(ENCODE_HTML) : 'insert at the beginning';

            $objInsertAfterPhrase = Phrase::getPhrase('insertafter', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strSortOrderAfter = $objInsertAfterPhrase ? $objInsertAfterPhrase->getPhraseValue(ENCODE_HTML) : 'insert after';

            $objInsertBeforePhrase = Phrase::getPhrase('insertbefore', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
            $strSortOrderBefore = $objInsertBeforePhrase ? $objInsertBeforePhrase->getPhraseValue(ENCODE_HTML) : 'insert before';
         }

         $siblingContent = $_ARCHON->getCollectionContentLevel($objContent->CollectionID, $pID, $objContent->ID);




         ob_start();
         ?>
         <a id="SortOrderToggle" href="#">&nbsp;</a>
         <div style="min-height:6px">
            <div id="SortOrderInterface" style="display:none; float:left; margin-top: 6px; padding: 6px; border-top: 1px solid #eee">

               <select id="SortOrderSelect">
                  <option value="0"><?php echo($strSortOrderEnd); ?></option>
                  <option value="1"><?php echo($strSortOrderBeginning); ?></option>
                  <option value="after"><?php echo($strSortOrderAfter); ?></option>
                  <option value="before"><?php echo($strSortOrderBefore); ?></option>
               </select>

               <select id="SiblingContent" style="display:none;">
                  <option value="0">(Select One)</option>
                  <?php
                  if(!empty($siblingContent))
                  {
                     foreach($siblingContent as $ID => $obj)
                     {
                        echo("<option value=\"" . $obj->SortOrder . "\">" . call_user_func_array(array($obj, 'toString'), array()) . "</option>");
                     }
                  }
                  ?>
               </select>
            </div>
         </div>
         <?php
         $customHTML = ob_get_clean();

         $generalSection->getRow('sortorder')->insertHTML($customHTML);

         $enabledRow = $generalSection->insertRow('enabled');
         $enabledRow->insertRadioButtons('Enabled');
         if($objContent->ParentID)
         {
            if(!$objContent->Parent || $objContent->Parent->ID != $objContent->ParentID)
            {
               $objContent->Parent = New CollectionContent($objContent->ParentID);
            }

            if(!$objContent->Parent->Enabled)
            {
               $objForceEnabledPhrase = Phrase::getPhrase('forceenabled', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
               $strForceEnabled = $objForceEnabledPhrase ? $objForceEnabledPhrase->getPhraseValue(ENCODE_HTML) : 'Force Application (Enables all disabled parents up to root level)';

               $enabledRow->insertCheckBox('ForceEnabled');
               $enabledRow->insertHTML("<label id='ForceEnabledLabel' for='ForceEnabledCheckboxInput'>{$strForceEnabled}</label>");
            }
         }
         $_ARCHON->AdministrativeInterface->addReloadRow($enabledRow);

         $generalSection->insertRow('date')->insertTextField('Date', 25, 75);
         $generalSection->getRow('date')->setEnableConditions('LevelContainerID', $arrPhysOnlyLCIDs, true, true);

         $sortOrder = ($objContent->SortOrder) ? $objContent->SortOrder : $_ARCHON->getNextContentSortOrder($objContent->CollectionID, $pID);
         $lcid = ($objContent->LevelContainerID) ? $objContent->LevelContainerID : 0;

         ob_start();
         ?>
         <script type='text/javascript'>
            /* <![CDATA[ */



            $(function(){
               $('#SortOrderToggle').button({
                  icons: {
                     primary: 'ui-icon-gear'
                  },
                  text: false
               })
               .click(function(){
                  if(!$(this).closest('.adminrow').hasClass('disabledrow')){
                     if($('#SortOrderInterface').is(':hidden')){
                        $('#SortOrderInterface').show('blind');
                     }else{
                        $('#SortOrderInterface').fadeOut(600);
                     }
                  }

                  return false;
               });

               if($('#EnabledInput').val() != 1){
                  $('#ForceEnabledCheckboxInput').attr('disabled', true);
                  $('#ForceEnabledField').addClass('disabledfield');
                  $('#ForceEnabledLabel').closest('.adminfieldwrapper').addClass('disabledfield');
               }

               $('#EnabledInput').change(function(){
                  if($('#EnabledInput').val() == 1){
                     $('#ForceEnabledCheckboxInput').removeAttr('disabled');
                     $('#ForceEnabledField').removeClass('disabledfield');
                     $('#ForceEnabledLabel').closest('.adminfieldwrapper').removeClass('disabledfield');
                  }else{
                     $('#ForceEnabledCheckboxInput').attr('disabled', true);
                     $('#ForceEnabledField').addClass('disabledfield');
                     $('#ForceEnabledLabel').closest('.adminfieldwrapper').addClass('disabledfield');
                  }
               });

            })


            var physOnlyLCIDs = <?php echo(js_array($arrPhysOnlyLCIDs, false)); ?>;
            var origSO = <?php echo($sortOrder); ?>;
            var origLCID = <?php echo($lcid); ?>;

            $('#LevelContainerIDInput').change(function () {
               var lcid = $('#LevelContainerIDInput').val();

               var disableUserFields = false;
               for(key in physOnlyLCIDs){
                  if(physOnlyLCIDs[key] == lcid){
                     disableUserFields = true;
                     break;
                  }
               }
               if(disableUserFields){
                  $('#moduletabs').tabs('option', 'disabled', [2,3,4]);
               }else{
                  $('#moduletabs').tabs('option', 'disabled', []);
               }
            });


            $('#SortOrderSelect').change(function() {
               var val = $(this).val();
               if(val == '0' || val == '1'){
                  if($('#SiblingContent').is(':visible')){
                     $('#SiblingContent').fadeOut(600);
                  }
                  $('#SortOrderInput').val(parseInt(val));
               }else{
                  if($('#SiblingContent').is(':hidden')){                    
                     $('#SiblingContent').show('fold');
                  }else{
                     // fire change event to handle the sortorder direction
                     $('#SiblingContent').trigger('change');
                  }
               }
            });

            $('#SiblingContent').change(function() {
               var sortorder = parseInt($(this).val());
               var position = $('#SortOrderSelect').val();
               if(origSO != 0 && origSO < sortorder){
                  sortorder--;
               }
               if(position == 'after'){
                  $('#SortOrderInput').val(sortorder + 1);
               }else if (position == 'before'){
                  $('#SortOrderInput').val(sortorder);
               }else{
                  // fire change event to handle the error
                  $('#SortOrderSelect').trigger('change');
               }
            });

            $('#SortOrderInput').focus(function(){
               if($('#SortOrderInterface').is(':visible')){
                  $('#SortOrderInterface').fadeOut(600);
               }

            });



            /* ]]> */
         </script>
         <?php
         $script = ob_get_clean();
         $generalSection->getRow('sortorder')->insertHTML($script);
         $generalSection->insertRow('description')->insertTextArea('Description', 5, 47);
         $generalSection->getRow('description')->setEnableConditions('LevelContainerID', $arrPhysOnlyLCIDs, true, true);

         $physOnly = (array_search($objContent->LevelContainerID, $arrPhysOnlyLCIDs) !== false);

         if(CONFIG_COLLECTIONS_ENABLE_USER_DEFINED_FIELDS)
         {
            $userfieldSection = $_ARCHON->AdministrativeInterface->insertSection('userfields', 'multiple');
            $userfieldSection->setMultipleArguments('UserField', 'UserFields', 'dbLoadUserFields');
            $userfieldSection->insertRow('userfields_title')->insertTextField('Title', 20, 50);
            $userfieldSection->insertRow('userfields_value')->insertTextArea('Value');
            $userfieldSection->insertRow('userfields_eadelementid')->insertSelect('EADElementID', 'getAllEADElements');
            if($physOnly)
            {
               $userfieldSection->disable(false);
            }
         }

         if(CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_CREATORS)
         {
            $creatorsSection = $_ARCHON->AdministrativeInterface->insertSection('creators');
            $creatorsSection->insertRow('creators')->insertAdvancedSelect('Creators', array(
                'Class' => 'Creator',
                'RelatedArrayName' => 'Creators',
                'RelatedArrayLoadFunction' => 'dbLoadCreators',
                'Multiple' => true,
                'toStringArguments' => array(),
                'params' => array(
                    'p' => 'admin/creators/creators',
                    'f' => 'search',
                    'searchtype' => 'json'
                ),
                'quickAdd' => "advSelectID =\\\"CreatorsRelatedCreatorIDs\\\"; admin_ui_opendialog(\\\"creators\\\", \\\"creators\\\");"
            ));
            if($physOnly)
            {
               $creatorsSection->disable(false);
            }
         }

         if(CONFIG_COLLECTIONS_ENABLE_CONTENT_LEVEL_SUBJECTS)
         {
            $subjectsSection = $_ARCHON->AdministrativeInterface->insertSection('subjects');
            $subjectTypes = $_ARCHON->getSubjectTypeJSONList();

            $subjectsSection->insertRow('subjects')->insertAdvancedSelect('Subjects', array(
                'Class' => 'Subject',
                'RelatedArrayName' => 'Subjects',
                'RelatedArrayLoadFunction' => 'dbLoadSubjects',
                'Multiple' => true,
                'toStringArguments' => array(LINK_NONE, true),
                'params' => array(
                    'p' => 'admin/subjects/subjects',
                    'f' => 'search',
                    'searchtype' => 'json',
                    'parentid' => '',
                    'subjecttypeid' => 0,
                    'showchildren' => true
                ),
                'quickAdd' => "advSelectID =\\\"SubjectsRelatedSubjectIDs\\\"; admin_ui_opendialog(\\\"subjects\\\", \\\"subjects\\\");",
                'searchOptions' => array(
                    array(
                        'label' => 'Subject Type',
                        'name' => 'SubjectTypeID',
                        'source' => $subjectTypes,
                        'noselection' => '{id: 0, text: "' . $strNoSelection . '"}'
                    )
                )
            ));
            if($physOnly)
            {
               $subjectsSection->disable(false);
            }
         }




         if(defined('PACKAGE_DIGITALLIBRARY'))
         {
            if($objContent->ID)
            {
               $objDigitalContentPhrase = Phrase::getPhrase('digitalcontent', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
               $strDigitalContent = $objDigitalContentPhrase ? $objDigitalContentPhrase->getPhraseValue(ENCODE_HTML) : 'digitalcontent';

               $objEditPhrase = Phrase::getPhrase('edit', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
               $strEdit = $objEditPhrase ? $objEditPhrase->getPhraseValue(ENCODE_HTML) : 'edit';

               $digitalContentSection = $_ARCHON->AdministrativeInterface->insertSection('digitalcontent', 'custom');
               $objContent->dbLoadDigitalContent();

               ob_start();

               if(!empty($objContent->DigitalContent))
               {
                  ?>
                  <script type="text/javascript">
                     /* <![CDATA[ */
                     $(function() {
                        $('.addbutton').button({
                           icons:{
                              primary:"ui-icon-plus"
                           },
                           text:false
                        });
                     });
                     /* ]]> */

                  </script>

                  <div style="margin:10px 0 0 20px" class="infotablewrapper"><table id="digitalcontenttable" class='infotable'>
                        <tr>
                           <th style='min-width: 90%;'><?php echo($strDigitalContent); ?></th>
                           <th style='text-align:center'><a class='addbutton' href='#' style='' onclick='admin_ui_dialogcallback(function() {admin_ui_reloadsection("digitalcontent", admin_ui_getboundelements(), function(){ $(".addbutton").button({icons:{primary:"ui-icon-plus"},text:false}); return false});}); admin_ui_opendialog("digitallibrary","digitallibrary", "add", "&amp;collectionid=<?php echo($objContent->CollectionID); ?>&amp;collectioncontentid=<?php echo($objContent->ID); ?>"); return false;'>&nbsp;</a></th>
                        </tr>
                        <?php
                        $count = 0;
                        foreach($objContent->DigitalContent as $objDigitalContent)
                        {

                           $strEditButton = "<a class='adminformbutton' href='index.php?p=admin/digitallibrary/digitallibrary&id={$objDigitalContent->ID}' rel='external'>" . $strEdit . "</a>";

                           $ListItem = "<td>" . $objDigitalContent->toString() . "</td><td style='text-align:center'>" . $strEditButton . "</td>";
                           if($count % 2 == 0)
                           {
                              echo("<tr class='evenrow'>{$ListItem}</tr>");
                           }
                           else
                           {
                              echo("<tr>{$ListItem}</tr>");
                           }
                           $count++;
                        }
                        ?>
                     </table></div>
                  <?php
               }
               else
               {
                  $objAddDigitalContentPhrase = Phrase::getPhrase('adddigitalcontent', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
                  $strAddDigitalContent = $objAddDigitalContentPhrase ? $objAddDigitalContentPhrase->getPhraseValue(ENCODE_HTML) : 'adddigitalcontent';
                  ?>
                  <div style="padding:20px 0 0 40px">
                     <a class='adminformbutton' href='#' onclick='admin_ui_dialogcallback(function() {admin_ui_reloadsection("digitalcontent", admin_ui_getboundelements(), function(){ $(".addbutton").button({icons:{primary:"ui-icon-plus"},text:false}); return false});}); admin_ui_opendialog("digitallibrary","digitallibrary", "add", "&amp;collectionid=<?php echo($objContent->CollectionID); ?>&amp;collectioncontentid=<?php echo($objContent->ID); ?>"); return false;'><?php echo($strAddDigitalContent); ?></a>
                  </div>
                  <?php
               }

               $digitalContentTable = ob_get_clean();

               $digitalContentSection->setCustomArguments($digitalContentTable);
            }
         }
      }
      else
      {
         $_ARCHON->AdministrativeInterface->CanUpdate = false;
      }
   }
   else
   {
      $objNoSelectionPhrase = Phrase::getPhrase('selectone', PACKAGE_CORE, 0, PHRASETYPE_ADMIN);
      $strNoSelection = $objNoSelectionPhrase ? $objNoSelectionPhrase->getPhraseValue(ENCODE_HTML) : '(Select One)';

      $objRepositoryPhrase = Phrase::getPhrase('repository', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strRepository = $objRepositoryPhrase ? $objRepositoryPhrase->getPhraseValue(ENCODE_HTML) : 'Repository';

      $objClassificationPhrase = Phrase::getPhrase('classification', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
      $strClassification = $objClassificationPhrase ? $objClassificationPhrase->getPhraseValue(ENCODE_HTML) : 'Classification';


      $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
      ob_start();
      ?>
      <input style="min-width:300px" name="collectionid" id="CollectionInput" />
      <script type="text/javascript">
         /* <![CDATA[ */
         $(function(){                                          
            $('#CollectionInput').autocomplete({
               source: function( request, response ) {
                  $.ajax({
                     url: "index.php",
                     dataType: "jsonp",
                     data: {
                        p: 'admin/collections/collections',
                        f: 'search',
                        searchtype: 'json',
                        q: request.term
                     },
                     success: function( data ) {
                        response( $.map( data.results, function( item ) {
                           return {
                              label: item.string,
                              value: item.string,
                              id: item.id
                           }
                        }));
                     }
                  });
               },
               minLength: 2,
               select: function( event, ui ) {                         
                  if(ui.item.id && ui.item.id != 0){
                     location.href = 'index.php?p='+request_p+'&collectionid=' + ui.item.id + '&displayrootcontent=true';
                  }
               }
            });
         });
         /* ]]> */
      </script>
      <?php
      $script = ob_get_clean();
      $generalSection->insertRow('selectparentcollection')->insertHTML($script);

      $_ARCHON->AdministrativeInterface->CanUpdate = false;
      $_ARCHON->AdministrativeInterface->CanAdd = false;
   }


   $_ARCHON->AdministrativeInterface->setCarryOverFields(array('CollectionID', 'ParentID'));

   $_ARCHON->AdministrativeInterface->outputInterface();
}

function collectioncontent_ui_search()
{
   global $_ARCHON;

   $_ARCHON->AdministrativeInterface->searchResults('searchCollectionContent', array('searchflags' => SEARCH_COLLECTIONCONTENT, 'collectionid' => 0, 'repositoryid' => 0, 'parentid' => 0, 'limit' => CONFIG_CORE_SEARCH_RESULTS_LIMIT, 'offset' => 0));
}

function collectioncontent_ui_exec()
{
   global $_ARCHON;

   @set_time_limit(0);

   // variables for redirect
   $collectionID = 0;
   $parentID = 0;
   $count = 0;
   $sameParent = true;

   $objCollectionContent = New CollectionContent($_REQUEST);

   $arrIDs = is_array($_REQUEST['ids']) ? $_REQUEST['ids'] : array('0');

   if($_REQUEST['f'] == 'store')
   {
      if($arrIDs == array('0'))
      {
         $LCID = $_REQUEST['levelcontaineridentifier'];
         $LCID = str_replace(' ', '', $LCID);

         $arrLevelContainerIdentifiers = array(0);

         if(preg_match('/([A-Z])-([B-Z])/', $LCID, $matches))
         {
            if($matches[1] < $matches[2])
            {
               $arrLevelContainerIdentifiers = range($matches[1], $matches[2]);
            }
         }
         elseif(preg_match('/([\d]+[,|\.|-][\d]+)+/', $LCID))
         {
            $arrLevelContainerIdentifiers = $_ARCHON->createFloatArrayFromString($LCID);
         }
      }

      $arrLevelContainerIdentifiers = (!empty($arrLevelContainerIdentifiers) && $arrLevelContainerIdentifiers != array(0)) ? $arrLevelContainerIdentifiers : array($_REQUEST['levelcontaineridentifier']);

      foreach($arrIDs as &$ID)
      {
         $count = 0;
         foreach($arrLevelContainerIdentifiers as $LCID)
         {
            if($count > 0)
            {
               $ID = 0;
            }
            $objCollectionContent = New CollectionContent($_REQUEST);
            $objCollectionContent->ID = $ID;
            $objCollectionContent->LevelContainerIdentifier = $LCID;
            $objCollectionContent->SortOrder += $count;

            if(is_array($_REQUEST['userfields']) && !empty($_REQUEST['userfields']))
            {
               foreach($_REQUEST['userfields'] as $UserFieldID => $array)
               {
                  $array['id'] = $UserFieldID;

                  $objUserField = New UserField($array);

                  if($array['_fdelete'])
                  {
                     if($array['id'])
                     {
                        $objUserField->dbDelete();
                     }
                  }
                  elseif($objUserField->Title)
                  {
                     $objCollectionContent->UserFields[] = $objUserField;
                  }
               }
            }

            $stored = $objCollectionContent->dbStore($_REQUEST['forceenabled']);
            $ID = $objCollectionContent->ID;

            if($stored)
            {
               if(is_array($_REQUEST['relatedsubjectids']))
               {
                  $objCollectionContent->dbUpdateRelatedSubjects($_REQUEST['relatedsubjectids']);
               }

               if(is_array($_REQUEST['relatedcreatorids']))
               {
                  $objCollectionContent->dbUpdateRelatedCreators($_REQUEST['relatedcreatorids']);
               }
            }

            $count++;
         }
      }
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      $count = 0;
      foreach($arrIDs as $ID)
      {
         $objCollectionContent = New CollectionContent($ID);
         $objCollectionContent->dbLoad();
         $collectionID = $objCollectionContent->CollectionID;
         $sameParent = $sameParent && ($parentID == $objCollectionContent->ParentID);
         $parentID = $objCollectionContent->ParentID;
         $objCollectionContent->dbDelete();
         $count++;
      }
   }
   elseif($_REQUEST['f'] == 'transfer')
   {
      foreach($arrIDs as &$ID)
      {
         $objCollectionContent = New CollectionContent($ID);
         $objCollectionContent->dbLoad();

         $objCollectionContent->CollectionID = $_REQUEST['collectionid'];
         $objCollectionContent->ParentID = $_REQUEST['collectioncontentid'];

         $objCollectionContent->dbStore();
      }
   }
   elseif($_REQUEST['f'] == 'renumbercontent')
   {

      $_ARCHON->shiftLevelContainerIdentifiers($arrIDs, $_REQUEST['shiftdirection'], $_REQUEST['shiftamount'], $_REQUEST['shiftsortorder']);

//      $location = "parent.location='?p={$_REQUEST['p']}&f=content&collectionid={$_REQUEST['collectionid']}&parentid={$_REQUEST['parentid']}';";
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
      $msg = 'CollectionContent Database Updated Successfully.';
   }

   if($_REQUEST['f'] == 'store')
   {
      $dispRootContent = ($objCollectionContent && $objCollectionContent->ParentID == 0) ? "&displayrootcontent=true" : "";
      $location = ($count > 1) ? "index.php?p=admin/collections/collectioncontent&collectionid={$objCollectionContent->CollectionID}&id={$objCollectionContent->ParentID}{$dispRootContent}" : NULL;
   }
   elseif($_REQUEST['f'] == 'delete')
   {
      $dispRootContent = ($parentID == 0) ? "&displayrootcontent=true" : "";
      $location = ($count > 1 && !$sameParent) ? "index.php?p=admin/collections/collectioncontent&collectionid={$collectionID}{$dispRootContent}" : "index.php?p=admin/collections/collectioncontent&collectionid={$collectionID}&id={$parentID}{$dispRootContent}";
   }


   $_ARCHON->AdministrativeInterface->sendResponse($msg, $arrIDs, $_ARCHON->Error, false, $location);
}
?>
