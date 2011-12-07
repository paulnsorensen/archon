var cookiePath = document.location.pathname.substring(0, document.location.pathname.lastIndexOf('/')) + '/';


function incrementCount(id)
{
   if(!id)
   {
      return;
   }

   var obj = document.getElementById(id + 'Count');

   if(!obj)
   {
      return;
   }

   if(obj.innerHTML == '0')
   {
      var objTitle = document.getElementById(id + 'Title');
      objTitle.innerHTML = "<a href='#' onclick=\"toggleDisplay('" + id + "'); return false;\"><img id='" + id + "Image' src='" + imagePath + "/plus.gif' alt='expand/collapse' /> " + objTitle.innerHTML + "</a>";
   }

   obj.innerHTML = parseInt(obj.innerHTML) + 1;
}

function toggleDisplay(id)
{
   if(!id)
   {
      return;
   }

   var objResults = document.getElementById(id + 'Results');
   var objImg = document.getElementById(id + 'Image');

   if(!objResults)
   {
      return;
   }

   if(jQuery)
   {
      if(objImg)
      {
         objImg.src = $(objResults).is(':visible') ? imagePath + '/plus.gif' : imagePath + '/minus.gif';
      }

      $(objResults).slideToggle('fast');
   }
   else
   {
      objResults.style.display = ((objResults.style.display == '') ? 'none' : '');

      if(objImg)
      {
         objImg.src = (objResults.style.display == '') ? imagePath + '/minus.gif' : imagePath + '/plus.gif';
      }
   }


}

function externalLinks() {
   if(jQuery)
   {
      $("a[rel='external']").attr('target', '_blank');
   }
   else
   {
      if (!document.getElementsByTagName) {
         return;
      }
      var anchors = document.getElementsByTagName('a');
      for (var i=0; i<anchors.length; i++) {
         var anchor = anchors[i];
         if (anchor.getAttribute('href') &&
            anchor.getAttribute('rel') == 'external') {
            anchor.target = '_blank';
         }
      }
   }
}

function escapejQuerySelectorStrings(string)
{
   string = string.replace(/(:|\[|\]|\.)/g, '\\$1');

   return string;
}


function updateResearchCartLinks()
{
   $.ajax({
      url: 'index.php',
      data: {
         p: 'collections/research',
         f: 'jsoncart'
      },
      dataType: 'jsonp',
      success: function(data){
         for (i in data.results) {
            var e = data.results[i];
            var collection = $('#cid'+e.CollectionID);
            if(collection.length){
               collection.removeClass('research_add');
               collection.addClass('research_delete')
               var img = collection.children('img.cart');
               var src = img.attr('src');
               img.attr('src', src.replace('addto', 'removefrom'));
               for (j in e.ContentIDs){
                  c = e.ContentIDs[j];
                  var content = $('#ccid'+c);
                  if(content.length){
                     content.removeClass('research_add');
                     content.addClass('research_delete')
                     img = content.children('img.cart');
                     src = img.attr('src');
                     img.attr('src', src.replace('addto', 'removefrom'));
                  }
               }
            }
         }
      }
   });   
}


function triggerResearchCartEvent(anchor, params)
{
   var f_param;
   if($(anchor).hasClass('research_add')){
      f_param = 'add';
   }else if($(anchor).hasClass('research_delete')){
      f_param = 'delete';
   }else{
      return;
   }

   var parameters = $.extend({
      p: 'collections/research',
      f: f_param
   }, params);
   $.ajax({
      url: 'index.php',
      data: parameters,
      dataType:'jsonp',
      success: function(data){

         $('#cartcount').text(data.response.cartcount);

         var img = $(anchor).children('img.cart');
         var src = img.attr('src');
         $.jGrowl(data.response.message);
         if(parameters['f'] == 'add')
         {
            img.attr('src', src.replace('addto', 'removefrom'));            
            $(anchor).removeClass('research_add');
            $(anchor).addClass('research_delete');
            $('#viewcartlink:hidden').show();

         }else{
            img.attr('src', src.replace('removefrom', 'addto'));
            $(anchor).removeClass('research_delete');
            $(anchor).addClass('research_add');
            if(data.response.cartcount == 0 && $('#viewcartlink').hasClass('hidewhenempty')){
               $('#viewcartlink').hide();
            }
         }
      }
   });
}

function removeFromCart(params){
   var parameters = $.extend({
      p: 'collections/research',
      f: 'delete'
   }, params);
   $.ajax({
      url: 'index.php',
      data: parameters,
      dataType:'jsonp',
      success: function(){
         location.href = 'index.php?p=collections/research&f=cart';
      }
   });
}