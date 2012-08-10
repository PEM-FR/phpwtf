define([
	"dojo/_base/declare",
	"widget/_Widget",
	"widget/element/ElementStore",
	"dijit/layout/TabContainer",
	"dijit/tree/dndSource",
	// @wtf_start loaded 2 times !
	"dijit/layout/TabContainer",
	// @wtf_stop
	"dijit/Menu",
	// @wtf_start loaded 2 times !
	"widget/element/ElementStore",
	// @wtf_stop
	// @wtf_start loaded 3 times !!!!
	"dijit/layout/TabContainer",
	// @wtf_stop
	// @wtf_start Do you REALLY need both?
	"dijit/layout/ContentPane",
	"dojox/layout/ContentPane",
	// @wtf_stop
	"dijit/form/RadioButton"
	], function(declare, _Widget, nls){
		// @wtf_start aren't you supposed to use "declare" directly? 
		return dojo.declare(
		// @wtf_stop
			"widget.SomeCrappyWidget", _Widget, {
				someFunc: function(){
					// @wtf_start you could give JavaScript some more luvin'
					// One var should be enough...
					var idSite = this.listSite.item.id;
					var url;
					var idEntite = null;
					var typeEntite;
					// @wtf_stop

				}
		});
	})
);