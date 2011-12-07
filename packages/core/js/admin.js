function admin_ui_previewlink(e)
{
   admin_ui_loadadvancedhelp($(e).attr('href'));
   
   return false;
}

function admin_ui_loadadvancedhelp(url)
{
   $('#advhelploadingscreen').show();

   $('#advancedhelp').dialog('open');

   $('#advhelpcontent').css('max-height', $(window).height() - 150);

   $('#advhelpcontent').load(url + " #abstract", function() {
      externalLinks();
      $('#advhelploadingscreen').fadeOut('slow');
   });

   return false;
}

function admin_ui_opendialog(pkg, object, dialogfn, extraParams)
{
   var admindialog = $('#dialogmodal');

   var params = 'p=admin/' + pkg + '/' + object + '&adminoverridesection=dialogform';
   dialog_p = 'admin/' + pkg + '/' + object;

   if(dialogfn) {
      params += '&f=dialog_' + dialogfn;
      dialog_f = 'dialog_' + dialogfn;
   }
   else {
      params += '&f=dialog';
      dialog_f = 'dialog';
   }

   if(extraParams){
      if(jQuery.isPlainObject(extraParams)){
         extraParams = '&' + jQuery.param(extraParams);
      }
      params += extraParams;
   }

   $('#dialogloadingscreen').show();

   admindialog.dialog('open');

   admindialog.load('index.php #dialogformsectionbody > *', params, function (){

      $('#dialogmodal .focusable').attr('tabindex','1');
      $('#dialogmodal .editable').focus(function (e){
         if(!$(e.target).parent().parent().hasClass('multiplefield')){
            var phrasename = $(e.target).closest('.adminrow').attr('id').replace(/row$/g, '');
            if($('#' + phrasename + 'helptext').text().length > 0)
            {
               admin_ui_changehelp($('#' + phrasename + 'helptext').text());
            }
         }
      });

      ck_archon_bind_editor('#dialogmodal .editable');
      $('.advancedselect').advselect();
      $('#dialogloadingscreen').fadeOut('slow');
      $('#dialogmodal :input:visible:first').focus();
      if(dialogfn == 'transfer'){
         setupTransferInterface(object);
      }
   });
   
   
   

}

function admin_ui_submitdialog()
{
   ck_archon_updateall();

   $('#dialogmodal .relatedselect>*').attr('selected','selected');


   var admindialog = $('#dialogmodal');
   $('#dialogform').ajaxForm({
      dataType: 'xml',
      success: function (responseXML) {
         //admin_ui_displayresponse(xml);

         var response = $('#dialogresponse');

         //Draw attention to malformed fields.
         if($('archonresponse', responseXML).attr('error') != 'false')
         {
            response.text($('message', responseXML).text());
            response.show();
            $('problemfield', responseXML).each(function (i) {
               // Focus only on first missed field.
               var callback;
               if(i == 0)
               {
                  callback = function () {
                     if($(this).attr('type') != 'hidden' && $(this).is(':visible')){
                        $(this).focus();
                     }
                  };
               }
               else
               {
                  callback = function () {};
               }
               //dialog id prefix
               var problemInput = $('#dialog-' + $(this).text() + 'Input');
               if(!problemInput.hasClass('editable')){
                  problemInput.effect('highlight', {}, 1500, callback);
               }
            });

         //return false;
         }
         else
         {         
            admindialog.dialog('close');
            $('.jGrowl-notification').trigger('jGrowl.close');
            if(dialogCallback){
               dialogCallback();
            }
            if(advSelectID){
               advancedSelectAdd(advSelectID, responseXML);
               advSelectID = null;
            }
            $('#successbox').text($('message', responseXML).text());
            $('#successbox').slideDown();
            setTimeout("$('#successbox').fadeOut('slow');",1400);
         }
      }
   });
   $('#dialogform').submit();
   
   $('#dialogform .relatedselect>*').removeAttr('selected');
}

function admin_ui_goto(p_var, params){
   page = 'index.php?p=' + p_var;
   if(params){
      page = page + '&' + $.param(params);
   }
   location.href = page;
}

function admin_ui_reloadfield(fieldname, params){
   var overridefield = fieldname;
   fieldname = fieldname.replace(/[\[\]]/gi, '');

   var parameters;
   if(fieldname.indexOf('dialog-') == -1){
      parameters = {
         p: request_p,         
         'IDs[]': $('#IDs').val(),
         adminoverridefield: overridefield
      };
   }else{
      parameters = {
         p: dialog_p,
         f: dialog_f,
         'IDs[]': $('#dialog-IDs').val(),
         adminoverridefield: overridefield
      };
   }

   if(params){
      $.extend(parameters, params);
   }

   if('f' in parameters && !parameters.f)
   {
      delete parameters.f;
   }

   var fieldparams = {};
   $('.reloadparam:input').each(function() {
      fieldparams[$(this).attr('name')] = $(this).val();    
   });

   $.extend(parameters, fieldparams);

   $('#'+fieldname+'Field').load('index.php #'+fieldname+'Field>*', parameters, function () {
      $('#'+fieldname+'Field').change();
      formchange('#'+fieldname+'Field .watchme');
      admin_ui_activatereloadededitors(fieldname+'Field');
   });
}

function admin_ui_reloadrow(rowname){
   $('#'+rowname+'row .fieldcell').load('index.php #'+rowname+'row > .fieldcell >*', {
      p: request_p,
      'IDs[]': $('#IDs').val(),
      adminoverriderow: rowname
   }, function() {
      admin_ui_activatereloadededitors(rowname+'row .fieldcell');
   });
}

function admin_ui_processxml(xml){
   if($('id', xml).length > 0 && $('#IDs>option').val() == '0' && $('suppressredirect', xml).text() == 'false')
   {
      location.href = 'index.php?p=' + request_p + '&id=' + $('id', xml).text();
   }
   else if($('location', xml).length > 0)
   {
      if($('location', xml).attr('target')){
         window.open($('location', xml).text(),$('location', xml).attr('target'));       
      }
      else{
         location.href = $('location', xml).text();
      }
   }
}

function admin_ui_updatenamefield(fieldname){
   if($('#'+fieldname+'Input').val())
   {
      $('#curobjectname').html(admin_ui_decodebbcode($('#'+fieldname+'Input').val()));
   }
}

function admin_ui_reloadsection(sectionname, boundElements, callback){
   $('#'+sectionname+'fragment').load('index.php #'+sectionname+'sectionbody>*', {
      p: request_p,
      'IDs[]': $('#IDs').val(),
      adminoverridesection: sectionname
   }, function() {
      admin_ui_activatereloadededitors(sectionname+'fragment');
      admin_ui_triggerboundelements('load', boundElements);
      $('#'+sectionname+'fragment .advancedselect').advselect();

      if(callback){
         jQuery.each(this, callback);
      }
   });
}

function admin_ui_getboundelements(){
   var boundElements = [];
   $('.bound').each(function() {
      boundElements.push($(this).attr("id"));
   });
   return boundElements;
}

function admin_ui_triggerboundelements(eventtype, boundElements){
   var i;
   for (i in boundElements){
      $('#'+boundElements[i]).trigger(eventtype);
   }
}

function admin_ui_dialogcallback(callback){
   dialogCallback = callback;
}

function admin_ui_submitcallback(callback){
   submitCallback = callback;
}

function admin_ui_changehelp(helpHTML, toggleHelp)
{
   if($('.fragment').hasClass('helptoggled') && $('#helpcontents').html() != admin_ui_decodebbcode(helpHTML))
   {
      $('#helpcontents').fadeOut('normal', function () {
         $(this).html(admin_ui_decodebbcode(helpHTML)).find('a').click(function() {

            return admin_ui_previewlink(this);
         });
      }).fadeIn();
   }
   else
   {
      $('#helpcontents').html(admin_ui_decodebbcode(helpHTML)).find('a').click(function() {
         return admin_ui_previewlink(this);
      });

      if(toggleHelp)
      {
         admin_ui_togglehelpbox();
      }
   }
}

function admin_ui_pinhelp(unpin)
{
   if($('#helpbox').length == 0)
   {
      return;
   }

   if($.cookie('Archon_pinhelp') || unpin)
   {
      $.cookie('Archon_pinhelp', null, {
         path: cookiePath
      });

      $('#helppin img').attr('src', imagePath + '/unlocked.gif');
   }
   else
   {
      $.cookie('Archon_pinhelp', 'pinned', {
         path: cookiePath
      });

      $('#helppin img').attr('src', imagePath + '/locked.gif');
   }
}

function admin_ui_pinpackages(unpin)
{
   if($('#packagelist').length == 0)
   {
      return;
   }

   var path = document.location.pathname;
   var dir = path.substring(0, path.lastIndexOf('/')) + '/';

   if($.cookie('Archon_pinpackages') || unpin)
   {
      $.cookie('Archon_pinpackages', null, {
         path: cookiePath
      });

      $('#packagepin img').attr('src', imagePath + '/unlocked.gif');
   }
   else
   {
      $.cookie('Archon_pinpackages', 'pinned', {
         path: cookiePath
      });

      $('#packagepin img').attr('src', imagePath + '/locked.gif');
   }
}


function admin_ui_displayresponse(xml)
{

   // Draw attention to malformed fields.
   if($('archonresponse', xml).attr('error') != 'false')
   {
      var response = $('#response');

      response.text($('message', xml).text());

      response.dialog('open');


      $('problemfield', xml).each(function (i) {
         // Focus only on first missed field.
         var callback;
         if(i == 0)
         {
            callback = function () {
               $(this).focus();
            };
         }
         else
         {
            callback = function () {};
         }
         var field = $('#' + $(this).text() + 'Input');
         if(field.attr('type') != 'hidden' && !field.hasClass('hidden') && field.css('display') != 'none'){
            field.effect('highlight', {}, 1500, callback);
         }
      });

      return false;
   }
   else
   {
      $('#successbox').text($('message', xml).text());
      $('#successbox').slideDown();
      setTimeout("$('#successbox').fadeOut(1000);",1400);
   }

   return true;
}

function admin_ui_delete(ids, suppressRedirect)
{
   if(permissionsDelete)
   {
      var IDs;

      if(!ids){
         // Need to use browse list value if deleting from browse section.
         if($('#moduletabs').tabs('option','selected') == $('#browsesectionbody .tabposition').text() && $('#browselistselect').val()){
            IDs = $('#browselistselect').val();
         }else{
            IDs = $('#IDs').val();
         }
      }else{
         IDs = ids;
      }

      $.ajax({
         url: 'index.php',
         dataType: 'xml',
         data:{
            p: request_p,
            f: 'delete',
            'IDs[]': IDs
         },
         success: function(xml){
            if(admin_ui_displayresponse(xml))
            {
               if(!suppressRedirect)
               {
                  if($('location', xml).length > 0)
                  {
                     if($('location', xml).attr('target')){
                        window.open($('location', xml).text(),$('location', xml).attr('target'));
                     }
                     else{
                        location.href = $('location', xml).text();
                     }
                  }else{
                     location.href = 'index.php?p=' + request_p;
                  }
                  return;
               }
            }
            if(typeof 'useBrowseFilter' == 'function'){
               useBrowseFilter();
            }
         }
      });
   }
}


function admin_ui_addnew(carryOverFields)
{
   $('#addcontrol').attr('href', 'index.php?p=' + request_p + '&selectedtab=1');
   jQuery.each(carryOverFields, function (i, val) {
      if($('#' + val + 'Input').val() && $('#' + val + 'Input').val() != 0)
      {
         $('#addcontrol').attr('href', $('#addcontrol').attr('href') + '&' + val + '=' + $('#' + val + 'Input').val());
      }
   });

   location.href = $('#addcontrol').attr('href');
}

function admin_ui_addnewchild(target, parentid, carryOverFields)
{
   $(target).attr('href', 'index.php?p=' + request_p + '&parentid=' + parentid + '&selectedtab=1');
   jQuery.each(carryOverFields, function (i, val) {
      if(val != 'ParentID' && $('#' + val + 'Input').val() && $('#' + val + 'Input').val() != 0)
      {
         $(target).attr('href', $(target).attr('href') + '&' + val + '=' + $('#' + val + 'Input').val());
      }
   });

   location.href = $(target).attr('href');
}





function admin_ui_togglehelpbox()
{
   var newMargin;
   var open;

   if($('.fragment').hasClass('helptoggled'))
   {
      $('#helptoggle').removeClass('toggled');
      $('#helpbox').css('height', 0);
      $('#helpbox').css('overflow', 'hidden');
      newMargin = $('#helpbox').css('margin-right');
      admin_ui_pinhelp(true);
      open = false;
   }
   else
   {
      $('#helptoggle').addClass('toggled');
      $('#helpbox').css('overflow', 'auto');
      $('#helpbox').css('height', $('#fragmentcontainer').innerHeight()-40);

      newMargin = $('#helpbox').outerWidth({
         margin: true
      }) + 'px';
      open = true;
   }

   /*$('.ui-tabs-panel').animate({marginRight: newMargin}, 'normal', function () {
        $(this).toggleClass('helptoggled');
    });*/

   $('.fragment').css('marginRight', newMargin);
   $('.fragment').toggleClass('helptoggled');

   return open;
}

function admin_ui_togglepackagelist(){
   var newMargin;
   var imgSrc;

   if($('#main-content').hasClass('packagetoggled')){
      newMargin = $('#packagelist').css('margin-left');
      imgSrc = imagePath + "/packageopen.gif";
      admin_ui_pinpackages(true);
   }else{
      newMargin = $('#packagelist').outerWidth({
         margin: true
      });
      imgSrc = imagePath + "/packageclose.gif";
   }

   $('#main-content').css('marginLeft', newMargin);
   $('#main-content').toggleClass('packagetoggled');
   $('#packagetoggle img').attr('src', imgSrc);
}


function admin_ui_markmultipledeletion(arrayname, id)
{
  
   if($('.multiplerow'+id).hasClass('deletetoggled'))
   {
      $('#' + arrayname + id + '_fDeleteInput').val('0');
      $('#' + arrayname + id +'row .multiplefield').fadeTo('normal', 1);
      $('#multipledelete'+id).html("Delete");
   }
   else
   {
      $('#' + arrayname + id + '_fDeleteInput').val('1');
      $('#' + arrayname + id +'row .multiplefield').fadeTo('normal', 0.25);
      $('#multipledelete'+id).html("Cancel");
   }
   $('.multiplerow'+id).toggleClass('deletetoggled');
}


function admin_ui_hierarchicalchange(wholefieldname, changedselect)
{
   var loadparams = {};

   var groupFieldName = $(changedselect).attr('name').replace(/(New$)|([\d]+$)/g, '').replace(/(New\]$)|([\d]+\]$)/g, ']');
   var idName = groupFieldName.replace(/\[|\]/g, '');

   if($(changedselect).val() == 0 && $('#' + idName + 'ParentFieldName').text().length > 0)
   {
      var parentFieldName = $('#' + idName + 'ParentFieldName').text();
      var parentFieldValue = $('#' + idName + 'ParentFieldValue').text();
      loadparams[parentFieldName] = parentFieldValue;
   }
   else
   {
      selfFieldName = $('#' + idName + 'SelfFieldName').text();
      loadparams[selfFieldName] = $(changedselect).val();
   }

   admin_ui_reloadfield(wholefieldname, loadparams);

   if(typeof useBrowseFilter == 'function')
   {
      useBrowseFilter();
   }
}




function admin_ui_togglerow(rowName, rowSwitch, clearFields)
{
   rowSwitch = !rowSwitch;
   $('#' + rowName + 'row').toggleClass('disabledrow', rowSwitch);
   $('#' + rowName + 'row :input').attr('disabled', rowSwitch);
   $('#' + rowName + 'row .editable').toggleClass('disabled', rowSwitch);

   if(rowSwitch && clearFields){
      $('#' + rowName + 'row :input').clearFields();
   }
} 



function admin_ui_encodebbcode(data, suppressNewLines)
{
   
   // remove divs
   data = data.replace( /<div[^>]*?>/gi, '');
   data = data.replace( /<\/div>/gi, '');

   data = data.replace( /\&amp;/gi, '&');

   data = data.replace( /<br \/>/gi, '');

   data = data.replace( /\n/gi, '');
   data = data.replace( /<p>/gi, '');

   if(suppressNewLines){
      data = data.replace( /<\/p>/gi, '');
   }else{
      data = data.replace( /<\/p>/gi, '\n');
   }
   data = admin_ui_trim(data);

   data = data.replace(/\&nbsp;/gi, ' ');

   // [b]
   data = data.replace( /<(?:b|strong)>(.+?)<\/(?:b|strong)>/gi, '[b]$1[/b]') ;

   // [i]
   data = data.replace( /<(?:i|em)>(.+?)<\/(?:i|em)>/gi, '[i]$1[/i]') ;

   // [u]
   data = data.replace( /<u>(.+?)<\/u>/gi, '[u]$1[/u]') ;

   // [sup]
   data = data.replace( /<sup>(.+?)<\/sup>/gi, '[sup]$1[/sup]') ;

   // [sub]
   data = data.replace( /<sub>(.+?)<\/sub>/gi, '[sub]$1[/sub]') ;

   // [url]
   data = data.replace( /<a .*?href=(["'])(.+?)\1.*?>[\s]*(.+?)[\s]*<\/a>/gi, '[url=$2]$3[/url]') ;

   data = data.replace( /\&lt;/gi, '<');
   data = data.replace( /\&gt;/gi, '>');


   return data ;
}


function admin_ui_truncatebbcode(data){
   data = data.replace(/(.+?)\[.*/, '$1');
   data = data.replace(/\s+$/,'');

   return data;
}

function admin_ui_trim(str) {
   return str.replace(/^\s+|\s+$/g,"");
}


function admin_ui_decodebbcode(data)
{
   data = data.replace(/[ ]{2}/gi, '&nbsp; ');

   data = data.replace( /</gi, '&lt;');
   data = data.replace( />/gi, '&gt;');

   // [b]
   data = data.replace( /\[b\](.+?)\[\/b]/gi, '<strong>$1</strong>' ) ;
   //data = data.replace( /\[b\][\s]*\[\/b]/gi, '' ) ;

   // [i]
   data = data.replace( /\[i\](.+?)\[\/i]/gi, '<em>$1</em>' ) ;

   // [u]
   data = data.replace( /\[u\](.+?)\[\/u\]/gi, '<u>$1</u>' ) ;

   // [sup]
   data = data.replace( /\[sup\](.+?)\[\/sup\]/gi, '<sup>$1</sup>' );

   // [sub]
   data = data.replace( /\[sub\](.+?)\[\/sub\]/gi, '<sub>$1</sub>' );

   // [url]
   data = data.replace( /\[url\](.+?)\[\/url]/gi, '<a href="$1">$1</a>' ) ;
   data = data.replace( /\[url\=([^\]]+)](.+?)\[\/url]/gi, '<a href="$1">$2</a>' ) ;

   if(data.indexOf('\n') != -1){
      data = '<p>' + data.replace( /\n/gi, '</p><p>') + '</p>';
   }

   return data;
}


function admin_ui_activatereloadededitors(selector){
   $('#'+selector+' .focusable').attr('tabindex','1');
   ck_archon_bind_editor('#'+selector+' .editable');
}



function admin_ui_submit(){
   $('#savecontrol').addClass('disabled');
   $('#savecontrol').addClass('submitting');
   ck_archon_updateall();
   $('.relatedselect>*').attr('selected','selected');
   $('#mainform').submit();
   $('.relatedselect>*').removeAttr('selected');
   if(submitCallback){
      submitCallback();
   }
}



function admin_ui_delegationbind(eventtype, selector, callback)
{
   $(document).bind(eventtype, function (e) {
      if($(e.target).is(selector))
      {
         return callback(e);
      }
   });
}

function admin_ui_confirm(text, callback){
   var response = $('#response');

   response.text(text);

   response.dialog('open');

   response.dialog({
      buttons:[

      {
         text: 'Ok',
         disabled: true,
         click:function(){
            $(this).dialog('close');
            if(callback){
               jQuery.each(this, callback);
            }
            $(this).dialog('option','buttons', {
               Ok: function(){
                  $(this).dialog('close');
               }
            });
         }
      },
      {
         text: 'Cancel',
         click:function(){
            $(this).dialog('close');
            $(this).dialog('option','buttons', {
               Ok: function(){
                  $(this).dialog('close');
               }
            });
         }
      }
      ]
   });

   setTimeout("enableOkButton()", 1500);
}

function enableOkButton() {
   $(":button:contains('Ok')").removeAttr("disabled").removeClass("ui-state-disabled");
}


function advancedSelectAdd(idname, responseXML){
   $('#'+idname).advselect('add', $('id', responseXML).text(), $('name',responseXML).text());
}


function setupTransferInterface(object){
   
   switch(object){
      case 'classification':
         var treediv = $('#transfer-classifications');         
         var input = $('#transfer-classificationid');

         var initData = {
            p: "admin/collections/classification",
            f: "tree",
            key: 0,
            currentKey: 0            
         };
         
         var lazyData = {
            p: "admin/collections/classification",
            f: "tree"
         };
         
         break;
         
      case 'collectioncontent':
         
         var treediv = $('#transfer-content');
         var input = $('#transfer-contentid');              
         
         var initData = {
            p: "admin/collections/collectioncontent",
            f: "tree",
            key: 0,
            currentKey: 0,
            collectionid: $('#dialog-CollectionIDInput').val()
                
         };
         
         var lazyData = {
            p: "admin/collections/collectioncontent",
            f: "tree",
            collectionid: $('#dialog-CollectionIDInput').val()
         };
         
         $('#dialog-CollectionIDInput').change(function(){      
            var p = {
               p: "admin/collections/collectioncontent",
               f: "tree",
               collectionid: $('#dialog-CollectionIDInput').val()
            }
      
            treediv.dynatree('option', 'initAjax',{
               url:"index.php",
               dataType: "jsonp",
               data: p
            });
            treediv.dynatree('option', 'onLazyRead', function(dtnode){
               var params = $.extend({
                  key: dtnode.data.key
               },p);
               dtnode.appendAjax({
                  url:this.options.url,
                  dataType: "jsonp",
                  data: params
               });
            });      
      
            var tree = treediv.dynatree("getTree");     
            tree.reload();
         });
         
         break;
         
      default:
         return;
   }
   
   
   if (treediv.length == 0){
      return;
   }
   

    
   treediv.dynatree({
      checkbox: true,
      autoFocus: false,
      selectMode: 1,
                           
      initAjax: {
         url: "index.php",
         dataType: "jsonp",
         data: initData
      },
      onLazyRead: function(dtnode){
         var params = $.extend({
            key: dtnode.data.key
         },lazyData);
         
         dtnode.appendAjax({
            url: "index.php",
            dataType: "jsonp",
            data: params       
         });
      },
      onSelect: function(select, dtnode){
         if(select){
            input.val(dtnode.data.key);
         }else{
            input.val(0);
         }         
      }
   });            
}



function admin_ui_init(){

   formChanges = [];

   $.metadata.setType("class");

   $('#scrolltop').button({
      icons:{
         primary: "ui-icon-circle-arrow-n"
      },
      text: false
   });

   $('.advancedselect').advselect();


   // One event for all help links.
   admin_ui_delegationbind('click', '.helplink', function (e) {
      var phrasename = $(e.target).metadata().phrasename;
      var packageid = $(e.target).metadata().packageid;
      var moduleid = $(e.target).metadata().moduleid;

      $.ajax({
         url: 'index.php',
         dataType: 'html',
         data: {
            p: 'admin/core/ajax',
            f: 'getphrase',
            phrasename: phrasename,
            phrasetypeid: descriptionID,
            moduleid: moduleid,
            packageid: packageid
         },
         success: function(data) {
            if(data){
               if(!$('#dialogmodal').dialog('isOpen')){
                  admin_ui_changehelp(data, true);
                  $('.helplink').removeClass('active');
                  $(e.target).addClass('active');
                  $('#editphrase').attr('target', '_blank');
                  $('#editphrase').attr('href', '?p=admin/core/phrases&f=predict&phrasename='+phrasename+'&phrasetypeid='+descriptionID+'&moduleid='+moduleid+'&packageid='+packageid
                     );
               //            }else{
               //               admin_ui_changehelp(data.error, true);
               }else{
                  $('#dialogmodal .helplink').removeClass('active');
                  $.jGrowl(data, {
                     life: 10000,
                     close: function(){
                        $(e.target).removeClass('active');
                     }
                  });
                  $(e.target).addClass('active');

               }
            }
         }
      });
   //      if($('#' + phrasename + 'helptext').text().length > 0)
   //      {
   //         admin_ui_changehelp($('#' + phrasename + 'helptext').text(), true);
   //      }
   });

   //   admin_ui_delegationbind('click', '.adminrow :input', function (e) {
   //      var phrasename = $(e.target).closest('.adminrow').attr('id').replace(/row$/g, '');
   //      if($('#' + phrasename + 'helptext').text().length > 0)
   //      {
   //         admin_ui_changehelp($('#' + phrasename + 'helptext').text());
   //      }
   //   });

   $('.adminrow :input').focus(function (e) {
      var phrasename = $(e.target).closest('.adminrow').attr('id').replace(/row$/g, '');
      if($('#' + phrasename + 'helptext').text().length > 0)
      {
         admin_ui_changehelp($('#' + phrasename + 'helptext').text());
      }
   });

   // One event for all checkboxes.
   admin_ui_delegationbind('change', '.fieldcheckbox', function (e) {
      var inputid = $(e.target).attr('id').replace(/CheckboxInput$/g, 'Input');
      $('#' + inputid).focus();
      $('#' + inputid).val($(e.target).is(':checked') ? '1' : '0');
      $('#' + inputid).change();
   });

   admin_ui_delegationbind('change', '.fieldradiobutton', function (e) {
      var hiddenelement = $(e.target).parent().siblings(':hidden');
      hiddenelement.focus();
      hiddenelement.val($(e.target).siblings().andSelf().filter(':checked').val());
      hiddenelement.change();
   });

   //   $('.radiobuttonset').buttonset();


  
   admin_ui_delegationbind('change', '.hierarchicalselectfield select', function (e) {
      var idname = $(e.target).closest('.adminfieldwrapper').attr('id').replace(/Field/i, '');
      $('#'+idname).focus();
      admin_ui_hierarchicalchange(idname, $(e.target));
   });



   //TODO: Move me to a better place and maybe use a better class
   admin_ui_delegationbind('click', '.fullcontrol', function (e) {
      $(e.target).parent().siblings().children(':checkbox').attr("checked", $(e.target).attr("checked"));
   });


   $('#moduletabs').bind('tabsshow', function (event, ui) {
      if($('#helptoggle').hasClass('toggled') && !$.cookie('Archon_pinhelp')){
         admin_ui_togglehelpbox();
      }
   });

   
   externalLinks();

   $('.focusable').attr('tabindex','1');

   var activeindex = 0;
   var itr = 0;
   $('.package-header').each(function () {
      var aprcode = request_p.replace( /admin\/(.+?)\/[a-z]*/gi, '$1');
      if($(this).hasClass(aprcode)){
         activeindex = itr;
      }
      itr++;
   });

   if(request_p != 'admin/core/home'){
      $('#packageaccordion').accordion({
         header: ".package-header",
         autoHeight: true,
         animated: "bounceslide",
         collapsible: true,
         active: activeindex
      });
   }else{
      $('#packageaccordion').accordion({
         header: ".package-header",
         autoHeight: true,
         animated: "bounceslide",
         collapsible: true,
         active: false
      });
   }
   

   $('#packageaccordion .ui-accordion-content li:nth-child(odd)').addClass('odd-accordion-content');


  
   $('#response').dialog({
      modal:true,
      autoOpen:false,
      overlay:{
         opacity: 0.4,
         background: "black"
      },
      title: 'Admin Response',
      draggable:false,
      resizable: false,
      beforeClose: function(){
         $(this).empty();
      },
      buttons:{
         Ok: function(){
            $(this).dialog('close');
         }
      }
   });
   


   $('#dialogmodal').dialog({
      modal:true,
      autoOpen: false,
      width: 780,
      minHeight: 400,
      position: ['center', 60],
      overlay:{
         opacity: 0.3,
         background: "black"
      },
      resizable: false,
      draggable: false,
      buttons: {
         'Save': function(){
            admin_ui_submitdialog();
         },
         'Cancel': function(){
            $(this).dialog('close');
            $('.jGrowl-notification').trigger('jGrowl.close');
         }
      }
   });


   $('#advancedhelp').dialog({
      modal:true,
      autoOpen: false,
      title: 'Advanced Help',
      position: ['center', 30],
      width: 780,
      minHeight: 400,
      overlay:{
         opacity: 0.5,
         background: "black"
      },
      resizable: false,
      draggable: false,
      buttons: {
         'Close': function(){
            $(this).dialog('close');
         }
      }
   });


   $('.ui-tabs-disabled').fadeTo('normal', 0.35);


   $('.editable').focus(function (e){
      if(!$(e.target).parent().parent().hasClass('multiplefield')){
         var phrasename = $(e.target).closest('.adminrow').attr('id').replace(/row$/g, '');
         if($('#' + phrasename + 'helptext').text().length > 0)
         {
            admin_ui_changehelp($('#' + phrasename + 'helptext').text());
         }
      }
      if($(e.target).hasClass('disabled')){
         return;
      }   
   });

   $('.editable a').attr('target', '_blank');

   if(!$('#quicksearchfield').attr('disabled')){
      $('#quicksearchfield').autocomplete({
         source: function(request, response) {
            $.ajax({
               url: 'index.php',
               dataType: 'jsonp',
               data: {
                  p: request_p,
                  f: 'search',
                  searchtype: 'json',
                  q: request.term
               },
               success: function(data) {
                  response($.map(data.results, function(item) {
                     return {
                        label: item.string,
                        value: item.string,
                        id: item.id
                     }
                  }))
               }
            })
         },
         minLength: 2,
         select: function(event, ui) {
            if(ui.item.id){
               location.href = 'index.php?p='+request_p+'&id=' + ui.item.id;
            }
         },
         close: function() {
            $('#quicksearchfield').val('');           
         }
      });
   }

   if(jQuery.browser.msie && jQuery.browser.version < 7.0)
   {
      $('#modulemain').bgiframe();
   }


   $('#CreatorsRelatedCreatorIDs').bind('change', function () {

      var arrOpts = $('#CreatorsRelatedCreatorIDs>option');
      var arrNotToRemove = [];
      var arrToAdd = [];

      //This should keep the (select one) value in the select
      arrNotToRemove[0] = true;

      $(arrOpts).each(function (i, opt) {
         var v = $(opt).val();
         var toAdd = true;

         $('select[name="PrimaryCreatorID"]>*').each(function (j, currOpt) {
            if(toAdd) { //if found, make looping faster
               if($(currOpt).val() == v){
                  arrNotToRemove[j] = true;
                  toAdd = false;
               }
            }
         });

         if(toAdd) {
            arrToAdd.push(i);
         }
      });

      var selectFirst = false;

      for (i=$('select[name="PrimaryCreatorID"]>*').length-1; i>=0; i--){
         if(!arrNotToRemove[i]) {
            if(!selectFirst && $('select[name="PrimaryCreatorID"]>*').eq(i).attr('selected')){
               selectFirst = true; //the selected primary creator has been removed, so select the first listed
            }
            $('select[name="PrimaryCreatorID"]>*').eq(i).remove();
         }
      }

      if($('select[name="PrimaryCreatorID"]>*').length == 1){ //only the (select one) option is there
         selectFirst = true;
      }

      $.each(arrToAdd, function(i, val) {
         $('select[name="PrimaryCreatorID"]').append($('#CreatorsRelatedCreatorIDs>*').eq(val).clone());
      });
      arrNotToRemove = [];
      arrToAdd = [];

      // The second node is actually the first listed creator, since the first one is the (select one)
      if(selectFirst){
         $('select[name="PrimaryCreatorID"]>*').eq(1).attr('selected','selected');
      }
   });


   if($('.fragment').hasClass('helptoggled'))
   {
      $('#helptoggle').addClass('toggled');
      $('#helpbox').css('overflow', 'auto');
      $('#helpbox').css('height', $('#fragmentcontainer').innerHeight()-40);
   }

   // set everything on load
   $('#mainform .watchme').each(function(){
      formfocus(this);
   });

   // change these to admin_delegationbind?
   $('#mainform').delegate('.watchme', 'focus', function(){
      formfocus(this);
   });

   $('#mainform').delegate('.watchme', 'change', function(){
      formchange(this);
   });


   var minHt = $('#packagelist').outerHeight();
   if(minHt < 600){
      minHt = 600;
   }
   $('#main-content').css('min-height', minHt);

   //This should be the last thing to run
   $('#loadingscreen').fadeOut('slow');
}

function formfocus(x){
   if(!$(x).closest('.adminfieldwrapper').data('orig_value_set')){
      $(x).closest('.adminfieldwrapper').data('orig_value_set', true);
      $(x).closest('.adminfieldwrapper').data('orig_value', $(x).val());
      $(x).closest('.adminfieldwrapper').data('dirty', false);
   }
}

function formchange(x){
   if($(x).closest('.adminfieldwrapper').data('orig_value_set')){
      if($(x).closest('.adminfieldwrapper').data('orig_value') != $(x).val()){
         
         if($(x).closest('.adminfieldwrapper').data('dirty') == false){
            formChanges.push($(x).attr('name'));
            $(x).closest('.adminfieldwrapper').data('dirty', true);
            $(x).closest('.fieldcell').siblings('.labelcell').addClass('unsaved');
            $(x).closest('.multiplefield').addClass('unsaved');
         }
      }else{

         //remove element from formChanges
         for(i in formChanges){
            if(formChanges[i] == $(x).attr('name')){
               formChanges.splice(i, 1);
               break;
            }
         }
         $(x).closest('.adminfieldwrapper').data('dirty', false);
         if(!($(x).closest('.adminfieldwrapper').siblings('.adminfieldwrapper').data('dirty'))){
            $(x).closest('.fieldcell').siblings('.labelcell').removeClass('unsaved');
            $(x).closest('.multiplefield').removeClass('unsaved');
         }
      }
   }
   else{
//console.log('you need to figure something out here, sir');
}
}

function admin_ui_updateformchangearray(){
   var i;
   while(i = formChanges.pop()){
      $('[name="'+i+'"]').closest('.adminfieldwrapper').data('orig_value_set', false);
      $('[name="'+i+'"]').closest('.adminfieldwrapper').data('dirty', false);
   }
   $('.labelcell').removeClass('unsaved');
   $('.multiplefield').removeClass('unsaved');
}


$(function () {

   admin_ui_init();
      
});

window.onbeforeunload = function(e) {
   if(formChanges.length){
      return 'You have unsaved changes!';
   }
};