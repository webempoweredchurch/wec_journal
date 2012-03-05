// Window Display Object
//    handles
var JournalWindow = Class.create({

	initialize: function(wrapperElement) {
		this.content = $(wrapperElement);
		this.setupEventListeners();
//		if (this.content.hasClassName('draggable')) {
			this._makeDraggable();
//		}
		if (this.content.hasClassName('alwaysVisible')) {
			this.alwaysVisible = true;
		}
		this.isDragging = false;
		var newElement   = new Element('input', {'class': 'newAction',   'id': 'newButton',   'value':'new', 'type':'button'});
		var closeElement = new Element('input', {'class': 'closeAction', 'id': 'closeButton', 'value':' ', 'type':'button'});
		var plusElement  = new Element('input', {'class': 'plusAction',  'id': 'plusButton',  'value':'', 'type':'button'});
		var minusElement = new Element('input', {'class': 'minusAction', 'id': 'minusButton', 'value':'', 'type':'button'});
		this.windowElement = $('journalWindow');

		if (newElement)		$('journalTopicField').insert({'after': newElement});
		if (plusElement)	this.windowElement.insert({'top': plusElement});
		if (minusElement)	this.windowElement.insert({'top': minusElement});
		if (closeElement)	this.windowElement.insert({'top': closeElement});

//		this.updateContent(content);

		this.createFormObservers();
		this.show();
	},

	updateContent: function(content) {
			// Get rid of any edit controls that already exist.
		editButtons = $('editControls');
		if (editButtons) {
			editButtons.remove();
		}

		$('journalWindowContent').update(content.stripScripts());

//		this.editPanel.createFormObservers();
	},

	show: function() {
			// grow in lightbox window
		//this.windowElement.grow({direction: 'center', duration: 0.5});
		this.windowElement.show();
//		this.setMaxContentSize();
	},

	setMaxContentSize: function() {
		windowHeight = document.viewport.getHeight();
		lightboxHeight = this.windowElement.getHeight();
		contentHeight = $('journalWindowContent').getHeight();
		maxLightboxHeight = windowHeight - 100;
		maxContentHeight = maxLightboxHeight - (lightboxHeight - contentHeight);

			// @todo	Max width is currently half the browser window.  Need to tweak this.
		windowWidth = document.viewport.getWidth();
		lightboxWidth = this.windowElement.getWidth();
		contentWidth = $('journalWindowContent').getWidth();
		maxLightboxWidth = windowWidth / 2;
		maxContentWidth = maxLightboxWidth - (lightboxWidth - contentWidth);

		$('journalWindowContent').setStyle({
			maxHeight: maxContentHeight + 'px',
			maxWidth: maxContentWidth + 'px'
		});
	},

		// @todo	Not currently working (and not called anywhere) due to timing issues.
	resizeWindow: function() {
			// determine lightbox size and location
		currentWindowHeight = document.viewport.getHeight();
		currentWindowWidth  = document.viewport.getWidth();
		currentLightboxHeight = this.windowElement.getHeight();
		currentLightboxWidth  = this.windowElement.getWidth();
		currentContentHeight = $('journalWindowContent').getHeight();
		currentContentWidth  = $('journalWindowContent').getWidth();
		maxLightboxHeight = currentWindowHeight - 100;
		maxLightboxWidth = currentWindowWidth - 100;

			// If the lightbox is too tall for the browser window, scale it down
		if (currentLightboxHeight > maxLightboxHeight) {
			newHeight = maxLightboxHeight - (currentLightboxHeight - currentContentHeight);
				// If we're making the content area smaller, we need to account for scrollbars too.
			newWidth = currentLightboxWidth + 20;

			$('journalWindowContent').setStyle({
				height: newHeight + 'px',
				width: newWidth + 'px'
			});
		}

			// If the lightbox is too wide for the browser window, scale it down.
		if (currentLightboxWidth > maxLightboxWidth) {
			newWidth = maxLightboxWidth - (currentLightboxWidth - currentContentWidth);
			newHeight = currentLightboxHeight + 20;
			$('journalWindowContent').setStyle({
				width: newWidth + 'px'
			});
		}

			// Center the lightbox on the page.
		this.windowElement.setStyle({
			position: 'fixed',
			top: ((currentWindowHeight / 2) - (currentLightboxHeight / 2)) + 'px',
			left: ((currentWindowWidth / 2) - (currentLightboxWidth / 2)) + 'px'
		});
	},

	removeWindow: function() {
		// remove buttons
		if ($('newButton')) 	{	$('newButton').remove(); }
		if ($('plusButton')) 	{	$('plusButton').remove(); }
		if ($('minusButton')) 	{	$('minusButton').remove(); }
		if ($('closeButton')) 	{	$('closeButton').remove(); }
				
	},
	
	closeWindow: function(forceClose) {
		if (!forceClose) {
			// check to see if need to save, and if so put up an alert. 
			// Bases decision on size of message content.
			// @todo possibly add an event listener on RTE or textarea and if change, then force save.
			prevSize = $('wecjournal_size').getValue();
			if (RTEarea != 0) 
				messageField = RTEarea['tx_wecjournal[message]']['editor'].getHTML();
			else
				messageField = $('journalWindow').select('textarea[name="tx_wecjournal[message]"]')[0].serialize();		
			messageField = messageField.replace(/&quot;/g,'"');
			messageField = messageField.replace(/<\/?[^>]+(>|$)/g, "");
			curSize = messageField.length;

			var saveFirst = false;
			if (prevSize != curSize) {
				saveFirst = confirm("You have unsaved journal entry(s). Do you want to save first? (p"+prevSize+'/c'+curSize+')');
			}
			if (saveFirst) {
				this.saveAndClose();
				return;
			}
		}
		
		// close the window
		this.windowElement.shrink();
		
		// set to hide (so saves in cookie)
		$('journalWindow').setAttribute('display', 'none');
		saveJournalCookie();
		
		// now actually remove the journal window
		hideJournal();
	},

	_makeDraggable: function() {
			// Make the window draggable
		new Draggable(this.content, {
			revert: false,
			scroll: window,
				// @todo	The z-index is set to 0 so that an inline style isn't added when an invalid drop occurs.
			zindex: 0,
			delay: 100,

			reverteffect: function (element, top_offset, left_offset) {
				new Effect.Move(element, { x: -left_offset, y: -top_offset, duration: 0,
					queue: {scope:'_draggable', position:'end' },
					afterFinish: function() {
					}
				});
			},
			onStart: function(draggableElement, event) {
				this.isDragging = true;
			},

			onEnd: function(draggableElement, event) {
				this.isDragging = false;
				saveJournalCookie();
			},

			onDrag: function(draggableElement, event) {
				if(event) {
					Position.prepare();
					var point = [Event.pointerX(event), Event.pointerY(event)];
				}
			}
		});
	},

	getFormParameters: function() {
			// Extract values from hidden form fields
		this._extraElements = new Array();
		this.content.select('form')[0].select('input').each(( function(formElement) {
			switch(formElement.readAttribute('name')) {
				case 'tx_wecjournal[cmd]':
					// do nothing
					break;
				case 'tx_wecjournal[record]':
					this.recordID = formElement.getValue();
					break;
				case 'tx_wecjournal[topic]':
					this.topic = formElement.getValue();
					break;
				case 'tx_wecjournal[pid]':
					this.pid = formElement.getValue();
					break;
				case 'tx_wecjournal[userid]':
					this.userID = formElement.getValue();
					break;
				default:
					this._extraElements.push(formElement);
					break;
			}
		}).bind(this));
		this.params = Form.serializeElements(this._extraElements);
	},

	createFormObservers: function() {
//		this.content.select('form').each((function(element) {
//			element.removeAttribute('onsubmit');
//			element.observe('submit', function(event) { Event.stop(event); });
//		}).bind(this));

			// Buttons at the bottom of the edit window
//		this.content.select('#editControls button').each((function(button) {
//			button.observe('click', this._handleButtonClick.bindAsEventListener(this));
//		}).bind(this));

			// Close button in the top right corner of the edit window
		if ($('newButton'))		$('newButton').observe('click', this._handleButtonClick.bindAsEventListener(this));
		if ($('closeButton'))	$('closeButton').observe('click', this._handleButtonClick.bindAsEventListener(this));
		if ($('plusButton'))	$('plusButton' ).observe('click', this._handleButtonClick.bindAsEventListener(this));
		if ($('minusButton'))	$('minusButton').observe('click', this._handleButtonClick.bindAsEventListener(this));

			// Attach dropdown
		this.content.select('select').each((function(button) {
			button.removeAttribute('onchange');
			button.observe('change', this._handleSelectChange.bindAsEventListener(this));
		}).bind(this));

	},

	_handleButtonClick: function(event) {
		if (this.isDragging) {
			Event.stop(event);
			return false;
		}
			
		eventElement = $(Event.element(event));
		if (eventElement.hasClassName('editBtn') ||
		    eventElement.hasClassName('actionBtn') ||
		    (eventElement.identify() == 'newButton') ||
			(eventElement.identify() == 'closeButton') ||
			(eventElement.identify() == 'plusButton') ||
			(eventElement.identify() == 'minusButton')
		   ) {
			element = eventElement;
		} else {
			element = eventElement.up('.actionBtn, .editBtn');
		}

		if(element) {
			if (element.hasClassName('saveAction')) {
				this.save();
			} else if (element.hasClassName('saveCloseAction') || (element.identify() == 'saveCloseButton')) {
				this.saveAndClose();
			} else if (element.hasClassName('closeAction') || (element.identify() == 'closeButton')) {
				this.close();
			} else if (element.hasClassName('plusAction') || (element.identify() == 'plusButton')) {
				this.resize(1);
			} else if (element.hasClassName('minusAction') || (element.identify() == 'minusButton')) {
				this.resize(-1);
			} else if (element.hasClassName('newAction') || (element.identify() == 'newButton')) {
				this.newEntry();
			} else if (element.hasClassName('printAction') || (element.identify() == 'printButton')) {
				this.print();
			}
		}

		Event.stop(event);
		return false;
	},
	
	_handleSelectChange: function(event) {
		eventElement = $(Event.element(event));

		topicVal = '';
		subtopicVal = '';
		if (eventElement.hasClassName('chooseTopic')) {
			topicVal = eventElement.options[eventElement.selectedIndex].value;
			$('journalWindow').select('input[name="tx_wecjournal[topic]"]')[0].value = topicVal;
		}
		else if (eventElement.hasClassName('chooseSubtopic')) {
			subtopicVal = eventElement.options[eventElement.selectedIndex].value;
			$('journalWindow').select('input[name="tx_wecjournal[subtopic]"]')[0].value = subtopicVal;
		}

		// send out new ajax call to get new entry
		this.load(topicVal,subtopicVal);

		Event.stop(event);
		return false;
	},

	setupEventListeners: function() {
			// Set up event handlers for the hover menu buttons
		$('journalWindow').select('.editBtn').each((function(button) {
			button.observe('click', this._handleButtonClick.bindAsEventListener(this));
		}).bind(this));

	},

	save: function() {
		action = new SaveAction(this);
		action.trigger();
	},

	close: function() {
		action = new CloseAction(this);
		action.trigger();
	},

	saveAndClose: function() {
		action = new SaveAndCloseAction(this);
		action.trigger();
	},
	
	print: function() {
		// call external function
		printJournal();
	},

	load: function(val1,val2) {
		action = new LoadAction(this);
		action.params1 = val1; // topic
		action.params2 = val2; // subtopic
		action.trigger();
	},

	resize: function(val1) {
		// Will go through following values. If minus and at smallest, stop. If plus and at biggest, stop.
		//	1) 280x280, 2) 300x340, 3) 300x440, 4) 340x500, 5) 360x600
		
		// get current width and height & determine which size on
		wd = $('journalWindow').getStyle('width');
		ht = $('journalWindow').getStyle('height');
		curSize = 2;
		wd = parseInt(wd);
		if (wd == 280) curSize = 1;
		else if (wd == 300) curSize = 2;
		else if (wd == 320) curSize = 3;
		else if (wd == 360) curSize = 4;
		else if (wd == 380) curSize = 5;
		
		curSize += val1;
		// if out of bounds, do nothing
		if ((curSize < 1) || (curSize > 5)) 
			return;
			
		setJournalSize(curSize);
		// save the resize info
		saveJournalCookie();
	},
	
	newEntry: function() {
		// @todo check if need to save or not
		// clear topic...
		$('journalTopicField').value = '';
		
		// clear entry
		if (RTEarea['tx_wecjournal[message]']["editor"])
			RTEarea['tx_wecjournal[message]']["editor"].setHTML('');
		
		// clear record value
		$('journalContent').select('input[name="tx_wecjournal[record]"]')[0].writeAttribute("value", 0);
	}
	
});

	// Object for notification popups. Creating a new instances triggers the popup.
var AJAXNotification = Class.create({
	initialize: function(content) {
			// Changing addClassName call to work around IE8 problems.
		this.notificationElement = new Element(
			'div',
			{'style': 'display: none;'}
		).addClassName('notificationMsg').update(content);

		body = $(document.getElementsByTagName('body')[0]);
		body.insert(this.notificationElement);
		this.notificationElement.appear({ duration: 0.75 });
	},

	hide: function() {
		this.notificationElement.fade({ duration: 0.35});
	}
});

// Define parent class for each action
//		This class handles sending and receiving an AJAX call
//		and processing the result
var WindowAction = Class.create({
	initialize: function(parent) {
		this.parent = parent;
		this.cmd = this._getCmd();
	},

	trigger: function(additionalParams) {
			// handle timeouts
		//this.setupTimer();
		if (actionRunning) {
			var waitNotification = new AJAXNotification(this._getAlreadyProcessingMsg());
			return;
		}
		actionRunning = true;

		this.parent.getFormParameters();
		this.recordID = this.parent.recordID;
		this.userID = this.parent.userID;

		var notification = new AJAXNotification(this._getNotificationMessage());
		paramRequest = 'eID=wec_journal' + '&tx_wecjournal[cmd]=' + this.cmd + '&tx_wecjournal[record]=' + this.recordID + '&tx_wecjournal[userid]=' + this.userID + '&pid=' + this.parent.pid;

		if (this.parent.params != undefined && this.parent.params != 0) {
			paramRequest += '&' + this.parent.params;
		}
		if (additionalParams != undefined) {
			paramRequest += '&' + additionalParams;
		}
			// now do the AJAX request
		new Ajax.Request(
			'index.php', {
				method: 'post',
				parameters: paramRequest,
				requestHeaders: { Accept: 'application/json' },
				onComplete: function(xhr) {
					actionRunning = false;
					notification.hide();
					if (waitNotification) {
						waitNotification.hide();
					}
					this._handleResponse(xhr);
				}.bind(this),
				onError: function(xhr) {
					actionRunning = false;
					notification.hide();
					alert('AJAX error: ' + xhr.responseText);
				}.bind(this)
		});
	},

	_handleResponse: function(xhr) {
		if (xhr.responseText.isJSON()) {
			var json = xhr.responseText.evalJSON(true);
				// @todo	Figure out how to handle errors.
			if (json.error) {
				alert(json.error);
			} else {
				if (json.content) {
					content = json.content;
					json.content = content.stripScripts();
				}

				if (json.newContent) {
					newContent = json.newContent;
					json.newContent = newContent.stripScripts();
				}

				id = this.parent.content.identify();
				this._process(json);
				this.parent.content = $(id);

//				if (json.content) {
//					JSHandler.evaluate(content);
//				}
//				if (json.newContent) {
//					JSHandler.evaluate(newContent);
//				}
			}
		}

	},

	_process: function() {
		// Implemented by concrete classes
	},

	_getCmd: function() {
		// Implemented by concrete classes
	},

	_getNotificationMessage: function() {
		return "We shouldn't ever see this message.";
	},

	_getAlreadyProcessingMsg: function() {
		return 'Already processing an action, please wait.';
	},

	setupTimer: function() {
			// Register global responders that will occur on all AJAX requests
		new Ajax.Responders.register({
			onCreate: function(request) {
			request['timeoutId'] = window.setTimeout(
				function() {
						// If we have hit the timeout and the AJAX request is active, abort it and let the user know
					if (this.callInProgress(request.transport)) 	{
						request.transport.abort();
						this.showFailureMessage();
							// Run the onFailure method if we set one up when creating the AJAX object
						if (request.options['onFailure']) {
							request.options['onFailure'](request.transport, request.json);
						}
					}
				},
				5000 // Five seconds
			);
			},
			onComplete: function(request) {
					// Clear the timeout, the request completed ok
				window.clearTimeout(request['timeoutId']);
			}
		});
	},
	callInProgress: function(xmlhttp) {
		var inProgress;

		switch (xmlhttp.readyState) {
			case 1:
			case 2:
			case 3:
				inProgress = true;
				break;
			// Case 4 and 0
			default:
				inProgress = false;
				break;
		}

		return inProgress;
	},
	showFailureMessage: function() {
		alert('Network problems -- please try again shortly.');
	}
});

function saveFormFields() {
	if (RTEarea != 0) {
		journalMsg = RTEarea['tx_wecjournal[message]']['editor'].getHTML();
		journalMsg = journalMsg.replace(/&quot;/g,'"');
		messageSize =journalMsg;
		formFields = 'tx_wecjournal[message]='+encodeURIComponent(journalMsg);
	}
	else {
		formFields = $('journalWindow').select('textarea[name="tx_wecjournal[message]"]')[0].serialize();
		messageSize = formField;
	}
	messageSize = messageSize.replace(/<\/?[^>]+(>|$)/g, "");	
	$('wecjournal_size').value = messageSize.length;
	
	if ($('journalWindow').select('input[name="tx_wecjournal[topic]"]')[0].value.length)
		formFields += '&' + $('journalWindow').select('input[name="tx_wecjournal[topic]"]')[0].serialize();
	if ($('journalWindow').select('input[name="tx_wecjournal[subtopic]"]')[0].value.length)
		formFields += '&' + $('journalWindow').select('input[name="tx_wecjournal[subtopic]"]')[0].serialize();
		
	return formFields;
}

var SaveAction = Class.create(WindowAction, {
	trigger: function($super) {
		formFields = saveFormFields();
		$super(formFields);
	},

	_process: function(json) {
		if (json.record) {
			$('journalWindow').select('input[name="tx_wecjournal[record]"]')[0].value = json.record;
		}
		if (json.topic) {
			setJournalTopic(json.topic,json.oldtopic);
		}		
	},

	_getNotificationMessage: function() {
		return 'Saving content.';
	},

	_getCmd: function() {
			// @todo	Temporary hack to return edit form again on save().
		return 'save';
	}
});
var CloseAction = Class.create(WindowAction, {
	trigger: function($super) {
		// @todo check if something to save...ask if want to save first
		
		if (journalWindow) {
			journalWindow.closeWindow(false);
			actionRunning = false;
		}

		formParams = '';
		// do not need to call AJAX to close...just close it.
		
//		$super(formParams);
	},

	_process: function(json) {
//		if (json.id) {
//			ep = editPanels.get(json.id);
//			ep.replaceContent(json.content);
//			scanForEditPanels();
//		} else {
//			this.parent.replaceContent(json.content);
//			this.parent.setupEventListeners();
//		}

//		if (json.newUID) {
//				// Insert the HTML and register the new edit panel.
//			this.parent.content.insert({'after': json.newContent});
//			nextEditPanel = this.parent.content.next('div.allWrapper');
//			editPanels.set(nextEditPanel.identify(), new EditPanel(nextEditPanel));
//		}


	},

	_getNotificationMessage: function() {
		return "Closing journal form";
	},

	_getCmd: function() {
		return 'close';
	}
});

var SaveAndCloseAction = Class.create(WindowAction, {
	trigger: function($super) {
		formFields = saveFormFields();
		$super(formFields);
		
		if (journalWindow) {
			journalWindow.closeWindow(true);
			actionRunning = false;
		}
	},

	_process: function(json) {
		if (json.record) {
			$('journalWindow').select('input[name="tx_wecjournal[record]"]')[0].value = json.record;
		}
		if (json.topic) {
			setJournalTopic(json.topic,json.oldtopic);
		}

//		if (json.id) {
//			ep = editPanels.get(json.id);
//			ep.replaceContent(json.content);
//			scanForEditPanels();
//		} else {
//			this.parent.replaceContent(json.content);
//			this.parent.setupEventListeners();
//		}

//		if (json.newUID) {
//				// Insert the HTML and register the new edit panel.
//			this.parent.content.insert({'after': json.newContent});
//			nextEditPanel = this.parent.content.next('div.allWrapper');
//			editPanels.set(nextEditPanel.identify(), new EditPanel(nextEditPanel));
//		}
	},

	_getNotificationMessage: function() {
		return 'Saving content.';
	},

	_getCmd: function() {
		return 'saveAndClose';
	}
});

var NewEntryAction = Class.create(WindowAction, {
	trigger: function($super) {
		if (RTEarea != 0)
			messageField = 'tx_wecjournal[message]='+RTEarea['tx_wecjournal[message]']['editor'].getHTML();
		else
			messageField = $('journalWindow').select('textarea[name="tx_wecjournal[message]"]')[0].serialize();

		topicField = $('journalWindow').select('input[name="tx_wecjournal[topic]"]')[0].serialize();
		subtopicField = $('journalWindow').select('input[name="tx_wecjournal[subtopic]"]')[0].serialize();
		$super(messageField+'&'+topicField);
	},

	_process: function(json) {
		if (json.record) {
			$('journalWindow').select('input[name="tx_wecjournal[record]"]')[0].value = json.record;
		}
		if (json.topic) {
			setJournalTopic(json.topic,json.oldtopic)
		}
	},

	_getNotificationMessage: function() {
		return 'New Entry';
	},

	_getCmd: function() {
		return 'newEntry';
	}
});

var LoadAction = Class.create(WindowAction, {
	trigger: function($super) {
		selectParams = 'tx_wecjournal[topic]=' + this.params1;
		selectParams += '&tx_wecjournal[subtopic]=' + this.params2;

		$super(selectParams);
	},

	_process: function(json) {
		if (json.record) {
			// set topic
			// set subtopic

			// set new content
			theContent = json.content;
		}
		// a new entry...
		else {
			theContent = ' ';
		}
		// update content in RTE or textarea
		if (RTEarea != 0) {
			RTEarea['tx_wecjournal[message]']['editor'].setHTML(theContent);
		}
		else {
			 $('journalWindow').select('textarea[name="tx_wecjournal[message]"]')[0].value = theContent;
		}

		// update record value
		$('journalWindow').select('input[name="tx_wecjournal[record]"]')[0].value = json.record;

	},

	_getNotificationMessage: function() {
		return 'Loading content.';
	},

	_getCmd: function() {
		return 'load';
	}
});

var journalWindow = 0;
var actionRunning = false;
var journalWindowSize = 2;

function setJournalCookie(c_name,value,expiredays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
function getJournalCookie(c_name) {
	if (document.cookie.length>0) {
	  c_start=document.cookie.indexOf(c_name + "=");
	  if (c_start!=-1) {
	    c_start=c_start + c_name.length+1;
	    c_end=document.cookie.indexOf(";",c_start);
	    if (c_end==-1) c_end=document.cookie.length;
	    	return unescape(document.cookie.substring(c_start,c_end));
	  }
	}
	return "";
}

// Save the x,y,ht,wd, and visibility
function saveJournalCookie() {
	var cookieInfo = '';

	var coords = $('journalWindow').viewportOffset();
	cookieInfo =  'x='    + coords[0] + '|y='  + coords[1];
	cookieInfo += '|wd='  + $('journalWindow').getStyle('width') + '|ht=' + $('journalWindow').getStyle('height');
	cookieInfo += '|sz='  + journalWindowSize;
	cookieInfo += '|vis=' + $('journalWindow').getStyle('display');

	setJournalCookie('tx_wecjournal', cookieInfo, 32);
}

function showJournal() {
	if ($('journalButton'))
		$('journalButton').hide();

	// setup window and attach events
	if (!journalWindow)
		journalWindow = new JournalWindow($('journalWindow'));
	else 
		$('journalWindow').show();
}

function hideJournal() {
	if ($('journalWindow'))
		$('journalWindow').hide();
		
	if ($('journalButton'))
		$('journalButton').show();
}

// called from onclick
function doShowJournal() {
	showJournal();
	saveJournalCookie();
}

// set the size. value is 1 - 5 (small to large)
function setJournalSize(curSize) {
	winWd = winHt = 0;
	curSize = parseInt(curSize);
	switch (curSize) {
		case 1: winWd = 280; winHt = 300; break; 
		case 2: winWd = 300; winHt = 350; break; 
		case 3: winWd = 320; winHt = 420; break; 
		case 4: winWd = 360; winHt = 500; break; 
		case 5: winWd = 380; winHt = 600; break; 
	}
	if (winWd > 0) {
		$('journalWindow').setStyle({ width: winWd + 'px'});		
		$('journalWindow').select('.journalContent')[0].setStyle({ height: (winHt - 102) + 'px'});
		var rte = $('journalForm').select('.htmlarea')[0];
		if (rte) {
			rte.setStyle({ width: winWd + 'px'});
			$('journalWindow').select('.editorIframe')[0].setStyle({ height: (winHt - 162) + 'px'});
			$('editorWraptx_wecjournal[message]').setStyle({ height: (winHt - 160) + 'px', width: winWd + 'px'});
		}
		journalWindowSize = curSize;
	}
}

escapeHTML = function(s) {
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#039;').replace(/"/, '&quot;');
};

function setJournalTopic(topic, oldtopic) {
	// rename oldtopic to topic
	if (oldtopic) {
		oldtopic = escapeHTML(oldtopic);
		var prevTopic = 0;
		$('chooseTopic').select('option').each(( function(optElement) {
			val = optElement.readAttribute('value');
			val = escapeHTML(val);
			if (val == oldtopic) {
				optElement.writeAttribute('value', topic);
				prevTopic = val;
				optElement.update(topic);
			}
		}));
	}
	// add new topic and make it selected
	else {
		newTopic   = new Element('option', {'selected':'selected'}).update(topic);
		$('chooseTopic').insert({'top': newTopic});
	}
	
}

// Hide the journal and show the journal button
Event.observe(window, 'load', function() {
	if ($('journalWindow') == null)
		return;
		
	// If cookie exists, then setup journal
	//--------------------------------------------------------
	if (journalCookie = getJournalCookie('tx_wecjournal')) {
		// break up & parse each section
		journalCookie = journalCookie.split("|");
//alert('get journalCookie='+journalCookie)
		for (i = 0; i < journalCookie.length; i++) {
			if ((p = journalCookie[i].indexOf('x=',0)) != -1) {
				xVal = journalCookie[i].substr(2);
				if (xVal != -1) {
					$('journalWindow').setAttribute('left', xVal);
					$('journalWindow').setAttribute('right', 'auto');
				}
			}
			else if (journalCookie[i].indexOf('y=',0) != -1) {
				yVal = journalCookie[i].substr(2);
				if (yVal != -1)
					$('journalWindow').setAttribute('top', yVal);
			}
			else if (journalCookie[i].indexOf('sz=',0) != -1) {
				szVal = journalCookie[i].substr(3);
				setJournalSize(szVal);
			}
			else if ((p = journalCookie[i].indexOf('vis=',0)) != -1) {
				dispVal = journalCookie[i].substr(4);
				$('journalWindow').setAttribute('display', dispVal);
			}
		}
		// show journal if supposed to (otherwise, keep it hidden)
		if ($('journalWindow').getStyle('display') != 'none') {
			showJournal();
		}
	}
	// default state is for journal to be hidden
	else {
		hideJournal();
	}
	
	// set topic in topicField if set in hidden field
	var thisTopic = 0;
	$('journalWindow').select('form')[0].select('input').each(( function(formElement) {
		if (formElement.readAttribute('name') == "tx_wecjournal[curtopic]") {
			thisTopic = formElement.getValue();
		}
	}).bind(this));
	
	if (thisTopic) {
		$('journalTopicField').value = thisTopic;
	}
	
});