
var ck_config =
{
   toolbar:
   [
   ['Cut','Copy','Paste','PasteText','PasteFromWord'],
   ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
   ['Bold','Italic','Underline','-','Subscript','Superscript'],
   ['Link','Unlink'],
   ['SpecialChar'],
   ['Source']
   ],
   height: 120,
   width: '75%',
   keystrokes:
   [
   [ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],
   [ CKEDITOR.CTRL + 89 /*Y*/, 'redo' ],
   [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/, 'redo' ],


   [ CKEDITOR.CTRL + 66 /*B*/, 'bold' ],
   [ CKEDITOR.CTRL + 73 /*I*/, 'italic' ],
   [ CKEDITOR.CTRL + 85 /*U*/, 'underline' ],

   [ CKEDITOR.ALT + 109 /*-*/, 'toolbarCollapse' ]
   ],   
   resize_minWidth: 575,
   resize_maxWidth: 750,
   enterMode: CKEDITOR.ENTER_P,
   entities: false,
   entities_latin: false,
   undoStackSize: 10,
   tabIndex: 1,
   forceSimpleAmpersand: true,
   htmlEncodeOutput: false

}
var ck_namefield_config =
{
   toolbar:
   [
   ['Bold','Italic','Underline'],
   ['SpecialChar']
   ],
   height: 40,
   width: '60%',
   resize_enabled: false,
   removePlugins: 'elementspath',
   keystrokes:
   [
   [ 13, 'blur'],
   [ CKEDITOR.SHIFT + 13, 'blur' ],
   [ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],
   [ CKEDITOR.CTRL + 89 /*Y*/, 'redo' ],
   [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/, 'redo' ],


   [ CKEDITOR.CTRL + 66 /*B*/, 'bold' ],
   [ CKEDITOR.CTRL + 73 /*I*/, 'italic' ],
   [ CKEDITOR.CTRL + 85 /*U*/, 'underline' ],

   [ CKEDITOR.ALT + 109 /*-*/, 'toolbarCollapse' ]
   ],
   enterMode: CKEDITOR.ENTER_P,
   entities: false,
   entities_latin: false,
   undoStackSize: 10,
   tabIndex: 1,
   forceSimpleAmpersand: true,
   htmlEncodeOutput: false
}



$(function () {
   ck_archon_bind_editor('.editable');
});

function ck_archon_bind_editor(selector){
   $(selector).bind({
      click: function(e){
         var id;
         if($(e.target).is('div')){
            id = $(e.target).attr('id')
         }else{
            id = $(e.target).closest('div').attr('id');
         }
         ck_archon_create(id);
      },
      focus: function(e){
         $(e.target).bind(($.browser.opera ? "keypress" : "keydown") + ".filter", function (e){
            if(e.keyCode == 13){
               var id;
               if($(e.target).is('div')){
                  id = $(e.target).attr('id')
               }else{
                  id = $(e.target).closest('div').attr('id');
               }
               ck_archon_create(id);
            }
         });
      },
      blur: function(e){
         $(e.target).unbind(($.browser.opera ? "keypress" : "keydown") + ".filter");
      }
   });
}

function ck_archon_create(str_id)
{
   // close any open editors
   $('.ckbutton').remove();

   for (i in CKEDITOR.instances) {
      ck_archon_destroy(i);
   }

   var textarea = $('#'+ str_id + 'Input');
   var button = $('<button type="button" />')
   .text('Done')
   .addClass('adminformbutton')
   .addClass('ckbutton')
   .css('margin', '12px 2px 2px');
   var editor;
   if($('#'+str_id).hasClass('namefield')){
      editor = CKEDITOR.replace( str_id, ck_namefield_config);
   }else{
      editor = CKEDITOR.replace( str_id, ck_config);
   }
   button.insertAfter('#'+str_id+'Field')
   .click(function() {
      ck_archon_destroy(str_id);
      button.button('destroy');
      button.remove();
   });
   var html = textarea.val();
   textarea.focus();
   
   editor.on('instanceReady', function() {

      editor.setData(admin_ui_decodebbcode(html));
      editor.focus();
   });
}

function ck_archon_destroy(str_id)
{
   editor = CKEDITOR.instances[str_id];
   if(editor){
      ck_archon_updateelement(str_id);
      editor.destroy();
   }
}


function ck_archon_updateall()
{
   for (i in CKEDITOR.instances) {
      ck_archon_updateelement(i);
   }
}


function ck_archon_updateelement(str_id)
{
   editor = CKEDITOR.instances[str_id];
   if(editor){
      editor.updateElement();

      if(escapeXML){
         if($('#'+str_id).hasClass('namefield')){
            $('#'+str_id+'Input').val(admin_ui_encodebbcode(editor.getData(), true));
            var str = $('#'+str_id).html();
            str = str.replace(/<p>/gi, '');
            str = str.replace(/<\/p>/gi, '');
            $('#'+str_id).html(str);
         }else{
            $('#'+str_id+'Input').val(admin_ui_encodebbcode(editor.getData()));
         }
      }else{
         $('#'+str_id+'Input').val(editor.getData());
      }

      $('#'+str_id+'Input').change();
   }
}
