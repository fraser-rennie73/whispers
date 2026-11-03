
var match,
	pl     = /\+/g,  // Regex for replacing addition symbol with a space
	search = /([^&=]+)=?([^&]*)/g,
	decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
	query  = window.location.search.substring(1);

urlParams = {};
while (match = search.exec(query))
   urlParams[decode(match[1])] = decode(match[2]);

// **********

function ready(callback){
    // in case the document is already rendered
    if (document.readyState!='loading') callback.call( this );
    // modern browsers
    else if (document.addEventListener) document.addEventListener('DOMContentLoaded', callback);
    // IE <= 8
    else document.attachEvent('onreadystatechange', function(){
        if (document.readyState=='complete') callback.call( this );
    });
}

// **********

var list = null;
var items = null;

ready( function() {
	
	list = document.getElementsByTagName("ul")[0];
	items = list.getElementsByTagName("li");

	Array.prototype.forEach.call( items, function( item, i ) {
		//item.dataset.articleid = i;
		item.draggable = true;
		item.children[0].draggable = false;

		item.addEventListener('drag', DraggingStart);
		item.addEventListener('dragover', DraggingOver);
		item.addEventListener('dragleave', DraggingLeave);
		item.addEventListener('drop', DraggingStop);
	});
	
});

// **********

function PostData( data, callback )
{
	console.log( data );
	
	fetch('Scripts/post-data.php', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify(data),
	})
	.then(response => response.json())
	.then(data => {
		console.log('Success:', data);
		if( !!callback ) callback.call( this, data );
	})
	.catch((error) => {
		console.error('Error:', error);
	});
}


// **********

var dragSource = null;
var dragDestination = null;

function DraggingStart( e )
{
	dragSource = e.target;

	while( dragSource != null && dragSource.tagName !== "LI" )
		dragSource = dragSource.parentElement;
	
	if( dragSource ) 
		dragSource.classList.add( "dragFrom" );
}
function DraggingOver( e )
{
	e.preventDefault();
	dragDestination = e.target;

	while( dragDestination != null && dragDestination.tagName !== "LI" )
		dragDestination = dragDestination.parentElement;

	if( dragDestination )
	{
		var pos = e.offsetY / dragDestination.offsetHeight;
		if( pos < 0.5 )
		{
			dragDestination.classList.remove( "dragToAfter" );
			dragDestination.classList.add( "dragToBefore" );
		}
		else
		{
			dragDestination.classList.remove( "dragToBefore" );
			dragDestination.classList.add( "dragToAfter" );
		}
	}
}
function DraggingLeave( e )
{
	e.preventDefault();
	if( dragDestination )
	{
		dragDestination.classList.remove( "dragToAfter" );
		dragDestination.classList.remove( "dragToBefore" );
	}
}
function DraggingStop( e )
{
	if( dragSource && dragDestination )
	{
		dragSource.classList.remove( "dragFrom" );
		dragDestination.classList.remove( "dragToAfter" );
		dragDestination.classList.remove( "dragToBefore" );

		var pos = e.offsetY / dragDestination.offsetHeight;
		if( pos >= 0.5 )
			dragDestination = dragDestination.nextSibling;
		
		if( dragSource !== dragDestination )
		{
			list.insertBefore( dragSource, dragDestination );
			
			var items = list.getElementsByTagName( "li" );
			var order = [];
			Array.prototype.forEach.call( items, function(item, i) { order.push( item.dataset.articleid ); });
			console.log( order );
			
			PostData({ action: 'SetOrder', categoryId: urlParams.cat, listOrder: order }, function(data){ console.log(data); });
		}
			
		dragSource = null;
		dragDestination = null;
	}
}

