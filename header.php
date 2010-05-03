<?php 
$init_time = microtime(true);
include_once( 'init.php' );
auth_redirect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>SupportPress <?php if ( isset ( $title ) ) echo $title ; ?></title>

<link type="text/css" href="design.css" media="screen" rel="stylesheet" />
<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="js/jquery-fieldselection.js" type="text/javascript"></script>

<script src="js/fat.js" type="text/javascript"></script>
<script src="js/common.js" type="text/javascript"></script>
<?php /*
<script type="text/javascript">
jQuery.fn.shortkeys = jQuery.fn.keys = function (obj, settings) {		
	var el = this;
	this.settings = jQuery.extend({
			split: "+",
			moreKeys: {}			
		}, settings || {});	
	this.wackyKeys = { '.': 190, ',': 188, ';': 59,	'Space': 32	};	
	this.formElements  = new Array("input", "select", "textarea", "button");
	this.keys = new Array();	
	this.onFormElement = false;
	this.keysDown = new Array();
	this.init = function (obj) {
		for(x in this.wackyKeys) {
			this.wackyKeys[x.toUpperCase()] = this.wackyKeys[x];
		}
		for(x in obj) {
			this.keys.push(x.split(this.settings.split));
		}
		for(i in this.keys) {
			var quickArr = new Array();
			for(j in this.keys[i]) {
				quickArr.push(this.convertToNumbers(this.keys[i][j].toUpperCase()));
			}
			quickArr.sort();
			this.keys[i] = quickArr;
		}
	};	
	this.convertToNumbers = function (inp) {
		if (this.wackyKeys[inp] != undefined) {
			return this.wackyKeys[inp];
		}
		return inp.toUpperCase().charCodeAt(0);
	};	
	this.keyAdd = function(keyCode) {
		this.keysDown.push(keyCode);
		this.keysDown.sort();
	};
	this.keyRemove = function (keyCode) {
		for(i in this.keysDown) {
			if(this.keysDown[i] == keyCode) {
				this.keysDown.splice(i,1);
			}
		};	
		this.keysDown.sort();	
	};		
	this.keyTest = function (i) {
		if (this.keys[i].length != this.keysDown.length) return false;
		for(j in this.keys[i]) {
			if(this.keys[i][j] != this.keysDown[j]) {
				return false;
			}
		}	
		return true;
	};
	this.keyRemoveAll = function () {
		this.keysDown = new Array();	
	};
	this.focused = function (bool) {
		this.onFormElement = bool;
	}	
	$(document).keydown(function(e) {
		el.keyAdd(e.keyCode);
		var i = 0;
		for(x in obj) {
			if(el.keyTest(i) && !el.onFormElement) {
				obj[x]();
				return false;
				break;	
			}			
			i++;
		};	
	});	
	$(document).keyup(function (e) {
		el.keyRemove(e.keyCode);
	});	
	for(x in this.formElements) {
		$(this.formElements[x]).focus( function () {
			el.focused(true);
		});
		$(this.formElements[x]).blur( function () {
			el.focused(false);
		});
	}	
	$(document).focus( function () {
		el.keyRemoveAll();
	});
	
	this.init(obj);
	jQuery.extend(this.wackyKeys, this.settings.moreKeys);

	return this;
}

$(document).shortkeys({
'R': function() {

if(navigator.userAgent.indexOf('Safari') >= 0){Q=getSelection();}else{Q=document.selection?document.selection.createRange().text:document.getSelection();}
if ( !Q )
	return 'r';
parent = $('.lastclicked').val();
email = $('#' + parent + ' .email').html();

$.post( 'ajax-quote.php', { email: email, text: Q }, function(data) { 
	 $('#' + parent + ' .widetext').val( data );
	 $('#' + parent + ' .inlinereplyform').show();
	 $('#' + parent + ' .widetext')[0].focus();
} );

}
});

</script>
*/ ?>
<?php if ( isset( $js ) ) echo $js; ?>
<?php do_action('sp_head'); ?>
</head>

<body>

<h1><a href='./'>SupportPress</a></h1>

<div class="rightcol">
<?php do_action('menu-above'); ?>
<ul class="menu">
<li><a href="./?status=closed">Closed</a></li>
<li><a href="./?status=tickle">Tickle</a></li>
<li><a href="./?status=all">All Tickets</a></li>
<li><a href="thread-new.php">New Thread</a></li>
<li><a href="predefined-edit.php">Predefined Reply</a></li>
</ul>
<?php do_action('menu-below'); ?>
</div> <!-- rightcol -->

<div class="wrap">

<form id="searchit" method="get" action="./">
<input type="text" name="q" value="search" class="tiptext" />
<input type="text" name="sender" value="sender" class="tiptext" />
<select name="status" id="status">
  <option value="open" selected="selected">Open</option>
  <option value="">Any</option>
  <option value="closed">Closed</option>
  <option value="tickle">Ticklish</option>
</select>
<input type="submit" name="todo" value="Search &raquo;" />
</form>

