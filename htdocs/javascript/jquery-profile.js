(function(){
  var log = [];
  var handle = jQuery.event.handle, ready = jQuery.ready;
  var internal = false;
  var eventStack = [];
  var curEvent = log[0] = { event: "inline", methods: [], duration: 0 };

  if ( !jQuery.readyList )
    jQuery.readyList = [];

  jQuery.readyList.unshift(function(){
    if ( curEvent && curEvent.event == "inline" && curEvent.methods.length == 0 ) {
      log.shift();
    }

    if ( curEvent )
      eventStack.push( curEvent );

    var e = curEvent = log[log.length] = { event: "ready", target: formatElem(document), methods: [] };
    var start = (new Date).getTime();
    jQuery(document).bind("ready", function(){ var comment="jquery-profile.js#21";
      e.duration = (new Date).getTime() - start;
      curEvent = eventStack.pop();
    });
  });

  jQuery.event.handle = function(event){
    var pos = log.length;

    if ( curEvent )
      eventStack.push( curEvent );

    var e = curEvent = log[pos] = { event: event.type, target: formatElem(event.target), methods: [] };
    var start = (new Date).getTime();
    var ret = handle.apply( this, arguments );
    e.duration = (new Date).getTime() - start;
    curEvent = eventStack.pop();

    if ( e.methods.length == 0 && e.duration <= 1 ) {
      log.splice( pos, 1 );
    }

    return ret;
  };

  for ( var method in jQuery.fn ) (function(method){
    if ( method == "init" ) return;

    var old = jQuery.fn[method];

    jQuery.fn[method] = function(){
      if ( !internal && curEvent ) {
        internal = true;
        var m = curEvent.methods[curEvent.methods.length] = { name: method, inLength: this.length, args: arguments };
        var start = (new Date).getTime();
        var ret = old.apply( this, arguments );
        m.duration = (new Date).getTime() - start;
        if (FunctionMonitor && FunctionMonitor.currentFunction && FunctionMonitor.currentFunction != null)
          m.fn = FunctionMonitor.currentFunction;
		
        if ( curEvent.event == "inline" )
          curEvent.duration += m.duration;

        if ( ret && ret.jquery )
          m.outLength = ret.length;
        internal = false;
		return ret;
      } else {
        return old.apply( this, arguments );
      }
    };
  })(method);

  var init = jQuery.fn.init;

  jQuery.fn.init = function(){
    var args = Array.prototype.slice.call( arguments );
    if ( typeof args[1] == "undefined" )
      args.pop();

    if ( !internal && curEvent ) {
      internal = true;
      var m = curEvent.methods[curEvent.methods.length] = { name: "jQuery", args: args };
      var start = (new Date).getTime();
      var ret = init.apply( this, arguments );
      m.duration = (new Date).getTime() - start;
	  //var test = "function () {\n    if (!internal && curEvent) {";
	  //if (ret.selector && ret.selector.substr(0,test.length) != test)
   	    m.selector = ret.selector;
      m.context = ret.context;
      if (FunctionMonitor && FunctionMonitor.currentFunction && FunctionMonitor.currentFunction != null)
        m.fn = FunctionMonitor.currentFunction;
      if ( curEvent.event == "inline" )
        curEvent.duration += m.duration;
      m.outLength = ret.length;
      internal = false;
      return ret;
    } else {
      return init.apply( this, args );
    }
  };

  jQuery.fn.init.prototype = init.prototype;

  jQuery.getProfile = function(){
    return log;
  };

  jQuery.displayProfile = function(){
  	var doLogging = (FunctionMonitor && FunctionMonitor.logUrl && JSON && JSON.stringify);
	var methodNumber = 0;
    var str = "<div style='position: relative; text-align:left;background:#FFF;color:#000;font-size:10px;width:700px;height:700px;overflow:auto;padding:8px;margin:10px;'>";
	str += "<div style='position: absolute; top: 10px; right: 10px;'><img src='javascript/taktsoft_logo.jpg' alt='Taktsoft GmbH & Co KG' title='Taktsoft GmbH & Co KG' /><h1 style='font-size: 19px;'>Taktsoft Javascript-Profiler 0.1</h1></div>";

    for ( var i = 0; i < log.length; i++ ) {
      var total = log[i].duration;
      str += "<big><b>Event: " + log[i].event + " (" + log[i].duration + "ms)</b></big><br/>" + 
        "<table><tr><th>%</th><th>(ms)</th><th>Function</th><th>Method</th><th>in</th><th>out</th></tr>";

      var methods = log[i].methods;
      for ( var m = 0; m < methods.length; m++ ) {
        var method = methods[m];
        str += "<tr><td>" + ((method.duration / total) * 100).toFixed(1) + "%</td><td>" +
          method.duration + "</td><td>" + (method.fn ? method.fn : "(unknown)") + "</td>" +
		  "<td>" + (method.name == "jQuery" ? "" : "&nbsp;&nbsp;.") + method.name + "(" + formatArgs(method.args) + ")</td>" +
          "<td>" + (method.inLength || "") + "</td><td>" + (method.outLength || "") + "</td></tr>";
		
		// Logging?
		if (doLogging) {
		  if (method.name == "jQuery") methodNumber++;
		  var data = {
		  	event: log[i].event,
				duration: method.duration,
				//eventDuration: total,
				functionName: method.fn,
				methodNumber: methodNumber,
				name: method.name,
				args: formatArgs(method.args),
				selector: method.selector,
				inLength: method.inLength,
				outLength: method.outLength
		  };

		  if (method.context) {
		  	var node = method.context;
		  	data.context = "";
			  if (node != null) {
			  	if (node.nodeName) {
						data.context = data.context + node.nodeName.toLowerCase();
					} else if (node.localName) {
						data.context = data.context + node.localName.toLowerCase();
					} else if (node.tagName) {
						data.context = data.context + node.tagName.toLowerCase();
					} else {
						data.context = data.context + "[node]";
					}
					
					if(node.nodeType == 1) {
			      if (node.getAttribute("id")) {
		           data.context = data.context + "#" + node.getAttribute("id");
		        } 
		        if (node.getAttribute("class")) {
		        	var split_classes = node.getAttribute("class").split(" ");
		        	var key;
		        	for(key in split_classes) {
		        		data.context = data.context + "." + split_classes[key];	
		        	}
		        } 
		      }
			  } else {
			  	data.context = data.context + "[null]";
			  }
			  
	  	  data.context = data.context + " ";
		  }
		  $.post(FunctionMonitor.logUrl, {json: JSON.stringify(data), type: "jquery"}, function(data) { console.log(JSON.stringify(data)) });
		}
      }

      str += "</table>";
    }
	
	if (FunctionMonitor)
		str += "<pre>" + FunctionMonitor.getAllMetrics() + "</pre>";

    jQuery("body").append( str + "</div>" );
  };

  function formatElem(elem) {
    var str = "";

	if ( typeof elem != "undefined")
    if ( elem.tagName ) {
      str = "&lt;" + elem.tagName.toLowerCase();

      if ( elem.id )
        str += "#" + elem.id;

      if ( elem.className )
        str += "." + elem.className.replace(/ /g, ".");

      str += "&gt;";
    } else {
      str = elem.nodeName;
    }

    return str;
  }

  function formatArgs(args) {
    var str = [];

    for ( var i = 0; i < args.length; i++ ) {
      var item = args[i];

      if ( item && item.constructor == Array ) {
        str.push( "Array(" + item.length + ")" );
      } else if ( item && item.jquery ) {
        str.push( "jQuery(" + item.length + ")" );
      } else if ( item && item.nodeName ) {
	str.push( formatElem( item ) );
      } else if ( item && typeof item == "function" ) {
	    str.push( item.toString() );
      } else if ( item && typeof item == "object" ) {
	  	if (JSON)
				str.push(JSON.stringify(item))
			else
        str.push( "{object}" );
      } else if ( typeof item == "string" ) {
        str.push( '"' + item.replace(/&/g, "&amp;").replace(/</g, "&lt;") + '"' );
      } else {
        str.push( item + "" );
      }
    }

    return str.join(", ");
  }
})();
