(function($) {
  
   $.widget("ui.advselect", {
      options:{
         minLength: 0,
         delay: 300,
         values:[
         {
            id: 0,
            value: ''
         }
         ],
         url: 'index.php',
         params: {},
         dataType: 'jsonp',
         limit: 50,
         searchOptions:[],
         quickAdd: null,
         clearButton: false,
         multiple: false,
         useMetadata: true,
         editPhrase: 'Click to edit...'
      },
      _create: function() {
         var self = this;
         var input = this.element.hide();
         if(self.options.useMetadata){
            for(i in self.options){
               if(i != 'useMetadata'){
                  if(self.element.metadata()[i]){
                     if(i == 'params'){
                        self.options.params = $.extend({}, self.element.metadata().params);
                     }else if(i == 'quickAdd'){
                        self.options.quickAdd = function() {
                           eval(self.element.metadata().quickAdd);
                        };
                     }else if(i == 'searchOptions'){
                        self.options.searchOptions = self.element.metadata().searchOptions.slice(0);
                     }else{
                        self.options.i = self.element.metadata()[i];
                     }
                  }
               }
            }
         }


         if(this.element.attr('multiple')){
            this.multiple = true;
         }else{
            this.multiple = false;
         }
         this.open = false;

        
         this.done_button = $('<button type="button" />')
         .text('Done')
         .addClass('adminformbutton')
         .css('margin', '12px 2px 2px');

         

         this.list = $('<ul></ul>')
         .addClass('advselect')
         .click(function(){
            self._open();
         })
        
         $.each(this.element.children('option'), function(){
            var v = $(this).attr('value');
            if(v && v > 0){
               var text = $(this).html();
               if(!text){
                  text = v;
               }
               $('<li>'+text+'</li>')
               .attr('value',v)
               .appendTo(self.list);
            }                 
         });

         this.empty_li = $('<li>'+this.options.editPhrase+'</li>')
         .css('color', '#aaa');

         if(this.list.children().length == 0)
         {
            this.empty_li.appendTo(self.list);
         }

         var wrapper = $("<div />")
         .addClass('advselect_editable ui-helper-clearfix')
         .css('float', 'left')
         .insertAfter(input);
         this.element_div = $("<div />")
         .css('margin-bottom', '18px')
         .appendTo(wrapper)
         .hide();
         this.wrapper = wrapper;


         this._initSource();


         var filter = $("<input>")
         .css('min-width', 300)
         .css('vertical-align', 'bottom')
         .appendTo(self.element_div)
         .autocomplete({
            source: this.source,
            delay: 250,
            select: function(event, ui) {
               self._select(event, ui);
               return false;
            },
            minLength: 0
         })
         .removeClass("ui-corner-all")
         .addClass("ui-widget ui-widget-content ui-corner-left ui-advselect")
         .bind('blur', function() {
            self._trigger('blur')
         });

         this.filter = filter;

         this.indicator = $("<div/>")
         .insertAfter(self.filter)
         .addClass('advselect_loading');

         filter.bind('autocompletesearch', function(event, ui){
            self.indicator.addClass('active');
         });

         filter.bind('focus',function(){
            self.indicator.addClass('focus');
         });
         
         filter.bind('blur', function(){
            self.indicator.removeClass('focus');
         });

         var browseToggle = $("<a>&nbsp;</a>")
         .insertAfter(self.indicator)
         .attr('title', 'Browse')
         .button({
            icons: {
               primary: "ui-icon-triangle-1-s"
            },
            text: false
         }).removeClass("ui-corner-all")
         .addClass("ui-button-icon")
         .css("margin-left", "-1px")
         .css("margin-right", "0px")
         .css("padding", 0)
         .css("height", "24px")
         .click(function() {
            // close if already visible
            if (filter.autocomplete("widget").is(":visible")) {
               filter.autocomplete("close");
               return;
            }
            // pass empty string as value to search for, displaying all results
            self.indicator.addClass('active');
            filter.autocomplete("search", "");
            filter.focus();
         });

         var lastButton = browseToggle;
         this.searchOptInputs = [];


         if(this.options.searchOptions && this.options.searchOptions.length > 0){


            var searchOpts = $("<div />")
            .insertBefore(filter)
            .addClass('ui-searchoptions')
            .css('padding', '4px 8px 16px')
            .css('font-size', '10px')
            .hide();
         

            var optMaxWidth = 0;

            for (i in this.options.searchOptions) {
               var opt = $('<input type="hidden" />')
               .appendTo(searchOpts)
               .searchoption($.extend({
                  change: function() {
                     if(searchOpts.is(':visible')){
                        filter.autocomplete("search", "");
                        filter.focus();
                     }
                  }
               }, this.options.searchOptions[i]));

               this.searchOptInputs.push(opt);

               if(opt.outerWidth() > optMaxWidth){
                  optMaxWidth = opt.outerWidth();
               }
            }

            if(optMaxWidth){
               searchOpts.css('width',optMaxWidth+20);
            }

            var searchToggle = $("<a>&nbsp;</a>")
            .insertAfter(browseToggle)
            .attr('title', 'Search Options')
            .button({
               icons: {
                  primary: "ui-icon-search"
               },
               text: false
            }).removeClass("ui-corner-all")
            .addClass("ui-button-icon")
            .css("margin-left", "-1px")
            .css("margin-right", "0px")
            .css("padding", 0)
            .css("height", "24px")
            .click(function() {
               searchOpts.toggle('slide', {}, 300, function(){
                  if(searchOpts.is(':hidden')){
                     //clear search opts
                     searchOpts.find('input').each(function(){
                        $(this).searchoption("reset");
                     });
                  }
               });
            });
            
            lastButton = searchToggle;

         }

        
         
         if(this.options.quickAdd){
            var quickAddButton = $("<a>&nbsp;</a>")
            .insertAfter(lastButton)
            .attr('title', 'Quick Add')
            .button({
               icons: {
                  primary: "ui-icon-plus"
               },
               text: false
            }).removeClass("ui-corner-all")
            .addClass("ui-corner-right ui-button-icon")
            .css("margin-left", "-1px")
            .css("padding", 0)
            .css("height", "24px")
            .click(function() {
               if($.isFunction(self.options.quickAdd)){
                  self._trigger('quickAdd');
               }
            });

            lastButton = quickAddButton;
         }else{
            lastButton.addClass('ui-corner-right');
         }

         this.list.insertAfter(self.element_div);
      },
      _select: function(event, ui) {
         var self = this;

         if(ui.item.id && ui.item.id > 0){
            if(this.element.children('option[value="'+ui.item.id+'"]').length == 0){

               if(!self.multiple) {
                  self.element.empty();
               }
               $('<option>'+ui.item.value+'</option>')
               .attr('value', ui.item.id)
               .attr('selected', 'selected')
               .appendTo(self.element);
               
               self.element.children('option[value="0"]').remove();

            
               if(ui.item.value){
                  if(!self.multiple){
                     self.list.empty();
                  }
                  var li =  $('<li>'+ui.item.value+'</li>')
                  .attr('value',ui.item.id)
                  .appendTo(self.list);
                  $('<span class="remove">X</span>')
                  .appendTo(li)
                  .click(function(e){
                     self._clear($(e.target).closest('li').attr('value'));
                  });
               }
               self.element.trigger('change');
            }
         }
         self._reset();
      },
      _clear: function(x) {
         this.list.children('li[value="'+x+'"]').remove();
         this.element.children('option[value="'+x+'"]').remove();
         
         if(this.element.children().length == 0){
            $('<option>')
            .attr('value', 0)
            .attr('selected', 'selected')
            .appendTo(this.element);
         }
         
         
         this.element.trigger('change');
      },
      _open: function() {
         var self = this;
         if(this.open){
            return false;
         }

         this.element.focus();
         this.wrapper.addClass('active');

         $(":ui-advselect").not(this.element).each(function(){
            var $this = $(this);
            if($this.advselect("isOpen")){
               $this.advselect("close");
            }
         });

         this.open = true;

         this.empty_li.remove();

         this.list.addClass('open');

         this.element_div.show();
         $.each(this.list.children('li'), function(){
            $('<span class="remove">X</span>')
            .appendTo(this);
         });
         this.list.children('li').children('span.remove').click(function(e){
            self._clear($(e.target).closest('li').attr('value'));
         });
         self.done_button.insertAfter(self.list)
         .click(function(){
            self._close();
         });

      },
      _close: function() {
         var self = this;

         if(!this.open){
            return false;
         }

         this.wrapper.removeClass('active');

         this.open = false;

         this.list.removeClass('open');


         if(this.list.children().length == 0)
         {
            this.empty_li.appendTo(self.list);
         }

         self.done_button.remove();
         self.element_div.hide();
         this.list.children('li').children('span.remove').remove();
      },
      _initSource: function() {
         var self = this;

         self.source = function(request, response){
            var params = {
               q: request.term,
               limit: self.options.limit
            };

            self.params = $.extend(params, self.options.params);
           
            for (i in self.searchOptInputs){
               params[$(self.searchOptInputs[i]).attr('name')] = $(self.searchOptInputs[i]).val();
            }

            $.ajax({
               url: self.options.url,
               dataType: self.options.dataType,
               data: self.params,
               success: function(data) {
                  response($.map(data.results, function(item) {
                     return {
                        label: item.string,
                        value: item.string,
                        id: item.id
                     }
                  }))
                  self.indicator.removeClass('active');
               }
            })
         }
      },     
      _reset: function(){
         this.filter.val('');
      },
      change: function(){
         this.element.trigger('change');
      },
      isOpen: function(){
         return this.open;
      },
      close: function(){
         this._close();
      },
      add: function(id, text){
         var self = this;

         if(id && id > 0){
            if(this.element.children('option[value="'+id+'"]').length == 0){

               if(!self.multiple) {
                  self.element.empty();
               }
               $('<option>'+text+'</option>')
               .attr('value', id)
               .attr('selected', 'selected')
               .appendTo(self.element);

               self.element.children('option[value="0"]').remove();

               if(text){
                  if(!self.multiple){
                     self.list.empty();
                  }
                  var li =  $('<li>'+text+'</li>')
                  .attr('value',id)
                  .appendTo(self.list);
                  $('<span class="remove">X</span>')
                  .appendTo(li)
                  .click(function(e){
                     self._clear($(e.target).closest('li').attr('value'));
                  });
               }
               self.element.trigger('change');
               this._reset();
            }
         }
      },
      clear: function(){
         this.list.children().remove();
         this.element.children().remove();         
    
         $('<option>')
         .attr('value', 0)
         .attr('selected', 'selected')
         .appendTo(this.element);
             
         this.empty_li.appendTo(self.list);
                  
         this.element.trigger('change');
      }
   });


   

   $.widget("ui.searchoption", {
      options:{
         label: '',
         source: null,
         name: null,
         hierarchical: false,
         noselection: {
            id: 0,
            text: ' '
         }
      },
      _create: function() {
         var self = this;
         var wrapper = $("<div />").addClass( "ui-searchoption ui-widget" )
         .css('margin', '0 0 8px');

         var input = this.element
         .attr('name', this.options.name)
         .addClass('ui-searchoption-value')
         .wrap(wrapper);


         if(!this.options.hierarchical){

            this.select = $('<select></select>')
            .attr('name', this.options.name)
            .css('display', 'block')
            .insertAfter(input)
            .change(function(){
               input.val($(this).val());
               self._trigger('change');
            });

            this._initSource();
         }else{
            input.hierarchicalselect(this.options);
         }

         $("<label>"+this.options.label+"</label>").insertBefore(input)
         .css('display', 'block');
      },
      _initSource: function() {
         var self = this;
         var url;
         if ( $.isArray(this.options.source) ) {
            this._populateSelect(this.options.source);
         } else if ( typeof this.options.source === "string" ) {
            url = this.options.source;
            $.ajax({
               url: url,
               dataType: 'jsonp',
               success: function(data) {
                  self._response($.map(data.results, function(item) {
                     return {
                        id: item.id,
                        text: item.text
                     }
                  }));
               }
            })

         } else {
            this.source = this.options.source;
         }
      },
      _response: function(content) {
         if ( content.length ) {
            content = this._normalize(content);
            this._populateSelect(content);
         }
      },
      _populateSelect: function(data){
         $('<option>')
         .val(this.options.noselection.id)
         .html(this.options.noselection.text)
         .attr('selected', 'selected')
         .appendTo(this.select);

         for (j in data) {
            $('<option value="'+data[j].id+'">'+data[j].text+'</option>')
            .appendTo(this.select);
         }
      },
      _normalize: function( items ) {
         // assume all items have the right format when the first item is complete
         if ( items.length && items[0].id && items[0].text ) {
            return items;
         }
         return $.map( items, function(item) {
            if ( typeof item === "string" ) {
               return {
                  id: item,
                  text: item
               };
            }
            return $.extend({
               id: item.id || item.value,
               text: item.value || item.text
            }, item );
         });
      },
      reset: function() {
         this.element.next('select').val(this.options.noselection.id);
         this.element.next('select').change();

      }
   });



   $.widget("ui.selecttree", {
      options:{
         url: null,
         params: {},
         value:{
            id: 0,
            text: ''
         },
         canceltext: "Cancel",
         applytext: "Apply"
      },
      _create: function() {
         var self = this;
         var wrapper = $("<div />").addClass( "ui-selecttree ui-widget" );
         if(this.element.val() && !this.options.value.id){
            this.options.value.id = this.element.val();
         }

         var input = this.element.val(this.options.value.id)
         .wrap(wrapper);
         var display = $("<div />")
         .addClass('ui-selecttree-display')
         .insertAfter(input);

         this.dialogButton = $("<a>&nbsp;</a>")
         .insertAfter(display)
         .button({
            icons: {
               primary: "ui-icon-pencil"
            },
            text: false
         })
         .removeClass('ui-corner-all')
         .addClass('ui-corner-right ui-button-icon')
         .position({
            my: "left center",
            at: "right center",
            of: display,
            offset: "-1 0"
         }).css("top", "")
         .css("padding", 0)
         .css("height", '24px')
         .click(function() {
            if($(this).button('option', 'disabled') !== true){
               // close if already visible
               if (tree.dialog('isOpen')) {
                  tree.dialog('close');
               }else{
                  tree.dialog('open');
               }
            }
         });


         this.display = display;

         this._initValue();


         var tree = $("<div />").hide()
         .insertAfter(display);
         this.treeDialog = tree;


         var pending = $("<div />")
         .text(this.options.value.text)
         .addClass('ui-corner-all ui-widget-content ui-selecttree-pending')
         .hide()
         .appendTo(tree);

         this.pendingDisplay = pending;

         

         this._initTree();          
         
      },
      _initTree: function(){
         var self = this;

         this.treeview = $("<div/>")
         .insertAfter(this.pendingDisplay)
         .css('overflow', 'auto')
         .css('max-height', '450px');

         var p = this.options.params;

         this.treeview.dynatree({
            checkbox:true,
            selectMode:1,
            initAjax: {
               url:this.options.url,
               dataType: "jsonp",
               data: this.options.params


            },
            onLazyRead: function(dtnode){
               var params = $.extend({
                  key: dtnode.data.key
               },p);
               dtnode.appendAjax({
                  url:this.options.url,
                  dataType: "jsonp",
                  data: params

               });
            },
            onSelect: function(select, dtnode){
               if(select){

                  self.pendingDisplay.attr('data-id',dtnode.data.key);


                  var names = [];
                  var node = dtnode;
                  while(node){
                     names.push(node.data.title);
                     if(node.parent && node.parent.data.key != '_1'){
                        node = node.parent;
                     }else{
                        node = null;
                     }
                  }
                  names.reverse();
                  var text = names.join(' » ');
                  self.pendingDisplay.text(text);
                  self.treeDialog.dialog('option', 'title', text);
               }else{
                  self.pendingDisplay.attr('data-id', '');
                  self.pendingDisplay.text('');
                  self.treeDialog.dialog('option', 'title', '');
               }
            }
         });




         var btns = {};
         btns[this.options.applytext] = function() {
            self._setValue(self.pendingDisplay.attr('data-id'), self.pendingDisplay.text());
            self.treeDialog.dialog('close');
         };
         btns[this.options.canceltext] = function() {
            self.treeDialog.dialog('close');
         };
         this.treeDialog.dialog({
            modal:true,
            autoOpen: false,
            width: 580,
            minHeight: 400,
            title: this.options.value.text,
            buttons: btns
         });

  

      },
      _refreshTree: function(){
         this._setValue(0, '');
         this.pendingDisplay.text('');
         this.treeDialog.dialog('option', 'title', '');
         var p = this.options.params;
         
         this.treeview.dynatree('option', 'initAjax',{
            url:this.options.url,
            dataType: "jsonp",
            data: p
         });

         this.treeview.dynatree('option', 'onLazyRead', function(dtnode){
            var params = $.extend({
               key: dtnode.data.key
            },p);
            dtnode.appendAjax({
               url:this.options.url,
               dataType: "jsonp",
               data: params

            });
         });
         var tree = this.treeview.dynatree('getTree');
         tree.reload();
      },
      _setOption: function( key ) {
         $.Widget.prototype._setOption.apply( this, arguments );
         if ( key === "value" ) {
            this._initValue();
         }
      },
      _initValue: function(){
         this.element.val(this.options.value.id);
         var t = (this.options.value.text) ? this.options.value.text : '&nbsp;';
         this.display.html(t);
      },
      _setValue: function(id, text) {
         this.element.val(id);
         this.options.value.id = id;
         this.options.value.text = text;
         var t = (this.options.value.text) ? this.options.value.text : '&nbsp;';
         this.display.html(t);
      },
      enable: function(){
         this.display.removeClass('ui-state-disabled');
         this.dialogButton.button('enable');
      },
      disable: function(){
         this.display.addClass('ui-state-disabled');
         this.dialogButton.button('disable');
      },
      refresh: function (){
         this._refreshTree();
      }
   });


   $.widget("ui.hierarchicalselect", {
      options:{
         url: null,
         params: null,
         data: null,
         value: null,
         idname: 'parentid',
         noselection: {
            id: 0,
            text: '(Select One)'
         }
      },
      _create: function() {
         var self = this;
         var input = this.element;
         this.selects = [];
         var wrapper = $('<div />')
         .addClass('hierarchicalselect-wrapper')
         .bind('change', function(e) {
            if($(e.target).hasClass('hierarchicalselect')){
               var hIndex = parseInt($(e.target).attr("hierarchy-index"));
               var value = $(e.target).val();
               if(value != self.options.noselection.id){
                  input.val(value);
                  self._trigger('change');
                  self._getData(value, hIndex);
               }else{
                  self._destroySelect(hIndex+1);

                  if(hIndex != 0){
                     input.val(self.selects[hIndex-1].val());
                     self._trigger('change');
                  }else{
                     input.val(value)
                     self._trigger('change');
                  }
               }
            }
         });

         input.wrap(wrapper);
         

         this._createSelect(0)
         .insertAfter(input);
         this.data = this.options.data;
         this.response = function() {
            return self._response.apply( self, arguments );
         };

        
         if(this.data == null){
            this._getData(0, -1);
         }
      },
      _createSelect: function(hIndex) {
         this.selects.push($('<select>').addClass('hierarchicalselect').attr("hierarchy-index", hIndex));
         return this.selects[hIndex];
      },
      _destroySelect: function(hIndex) {
         var i = $(this.selects).length;
         while (i > hIndex){
            i--;
            this.selects[i].hide();
            this.selects[i].empty();
         }
      },
      _response: function( content, hIndex ) {
         if ( content.length ) {
            content = this._normalize( content );
            var i = hIndex+1;
            if(!this.selects[i]){
               var newSelect = $('<select>').addClass('hierarchicalselect').attr("hierarchy-index", i);
               this.selects.push(newSelect);
               newSelect.insertAfter(this.selects[hIndex]);
            }
            this._populateSelect( content, hIndex+1);
             
            // remove stale selects below this new one
            this._destroySelect(hIndex+2);
         }
      },
      _normalize: function( items ) {
         // assume all items have the right format when the first item is complete
         if ( items.length && items[0].id && items[0].text ) {
            return items;
         }
         return $.map( items, function(item) {
            if ( typeof item === "string" ) {
               return {
                  id: item,
                  text: item
               };
            }
            return $.extend({
               id: item.id || item.value,
               text: item.value || item.text
            }, item );
         });
      },   
      _getData: function(id, hIndex){
         var self = this;
         var params = this.options.params;
         params[this.options.idname] = id;
         $.ajax({
            url: this.options.url,
            dataType: 'jsonp',
            data: params,
            success: function(data) {
               self._response($.map(data.results, function(item) {
                  return {
                     id: item.id,
                     text: item.text
                  }
               }), hIndex);
            }
         })
      },
      _populateSelect: function(data, hIndex)
      {
         if(!hIndex)
         {
            hIndex = 0;
         }
         var select = this.selects[hIndex];

         select.empty();
         select.show();
         $('<option>')
         .val(this.options.noselection.id)
         .html(this.options.noselection.text)
         .attr('selected', 'selected')
         .appendTo(select);

         for (i in data){
            $('<option>')
            .val(data[i].id)
            .html(data[i].text)
            .appendTo(select);
         }
      }
   });


}(jQuery));


