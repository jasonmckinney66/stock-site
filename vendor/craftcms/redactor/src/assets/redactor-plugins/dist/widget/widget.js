Redactor.add("plugin","widget",{translations:{en:{widget:"Widget","widget-html-code":"Widget HTML Code"}},modals:{widget:'<form action="">                     <div class="form-item">                         <label for="modal-widget-input">## widget-html-code ##</label>                         <textarea id="modal-widget-input" name="widget" style="height: 200px;"></textarea>                     </div>                 </form>'},init:function(t){this.app=t,this.lang=t.lang,this.opts=t.opts,this.toolbar=t.toolbar,this.component=t.component,this.insertion=t.insertion,this.inspector=t.inspector,this.selection=t.selection},onmodal:{widget:{opened:function(t,e){if(e.getField("widget").focus(),this.$currentItem){var i=decodeURI(this.$currentItem.attr("data-widget-code"));e.getField("widget").val(i)}},insert:function(t,e){var i=e.getData();this._insert(i)}}},oncontextbar:function(t,e){var i=this.inspector.parse(t.target);if(!i.isFigcaption()&&i.isComponentType("widget")){var n=i.getComponent(),o={edit:{title:this.lang.get("edit"),api:"plugin.widget.open",args:n},remove:{title:this.lang.get("delete"),api:"plugin.widget.remove",args:n}};e.set(t,n,o,"bottom")}},onbutton:{widget:{observe:function(t){this._observeButton(t)}}},start:function(){var t={title:this.lang.get("widget"),api:"plugin.widget.open",observe:"widget"};this.toolbar.addButton("widget",t).setIcon('<i class="re-icon-widget"></i>')},open:function(){this.$currentItem=this._getCurrent();var t={title:this.lang.get("widget"),width:"600px",name:"widget",handle:"insert",commands:{insert:{title:this.$currentItem?this.lang.get("save"):this.lang.get("insert")},cancel:{title:this.lang.get("cancel")}}};this.app.api("module.modal.build",t)},remove:function(t){this.component.remove(t)},_getCurrent:function(){var t=this.selection.getCurrent(),e=this.inspector.parse(t);if(e.isComponentType("widget"))return this.component.build(e.getComponent())},_insert:function(t){if(this.app.api("module.modal.close"),""!==t.widget.trim()){var e=this._isHtmlString(t.widget)?t.widget:document.createTextNode(t.widget),i=this.component.create("widget",e);i.attr("data-widget-code",encodeURI(t.widget.trim())),this.insertion.insertHtml(i)}},_isHtmlString:function(t){return!("string"==typeof t&&!/^\s*<(\w+|!)[^>]*>/.test(t))},_observeButton:function(t){var e=this.selection.getCurrent();this.inspector.parse(e).isComponentType("table")?t.disable():t.enable()}});
//# sourceMappingURL=widget.js.map