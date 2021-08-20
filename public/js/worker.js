//--------------------------------------------------------------------
// This file is executed only once (when the Sharedworker is created).
//--------------------------------------------------------------------

// This array will contain objects in the format {connectionID: <MessagePort>, id: <int>}
// Such objects are the Tabs the user has opened using links/urls from the same domain.
// If another browser is opened with the same domain, another variable (array) will be created for that browser
// and will not include the user and the opened tabs of the first browser. This happens because the thread
// is not shared between different browsers.
var Tabs = [];
var counter = 0;
var wsConnected = false;

// The connection to the WebSocket Server.
var socket = new WebSocket("wss://ssa:443/wss/");

// Called when the WebSocket Server accepts the connection.
socket.onopen = function(e) {
   //
};

// Event handler fired when the WebSocket Server sends a message to this client.
socket.onmessage = function(e) {
   var message = JSON.parse(e.data);
   for (var i=0; i < Tabs.length; i++) {
      Tabs[i].connectionID.postMessage(message);
   }
};

socket.onclose = function(event) {
   if (event.wasClean) {
      console.log('Websocket closed normally');
   }
   else {
      console.log('Websocket closed unexpectedly.');
   }
};

socket.onerror = function(error) {
   console.log('Error on client websocket: ', error.data);
};



// This event is fired every time a browser tab connects to this Sharedworker.
onconnect = function(e) {
   var conn = e.ports[0];
   counter++;
   Tabs.push({ connectionID: conn, id: counter });
   // Send the connection ID to the latest connection.
   conn.postMessage({tabID: counter, type: 'CONNECTION'});
   //console.log('Total tabs: ' + Tabs.length);
   

   // Event handler fired when a tab/window sends a message to this Sharedworker.
   conn.onmessage = function(e) {
      var connection = e.data.connectionID;
      switch (e.data.action) {
         case "connect":
            if (!wsConnected) {
               // Add the connected user name to the websocket server.
               setTimeout(() => {
                  socket.send(JSON.stringify({action: 'connect', username: e.data.username}));
               }, 850);
               wsConnected = true;
            }
            else {
               // User is already connected to WebSocket Server. Retrieve number of unread notifications.
               socket.send(JSON.stringify({action: 'getTotalUnreadMessages', username: e.data.username}));
            }
            break;
         
         case "close":
            var elem = Tabs.find(element => element.connectionID == connection);

            if (elem !== undefined || elem !== null) {
               var index = Tabs.indexOf(elem);
               Tabs.splice(index, 1);
            }
            //console.log('Remaining tabs: ' + Tabs.length);
            if (Tabs.length == 0) {
               // User doesn't have any tabs opened. Disconnect user from WebSocket Server.
               socket.send(JSON.stringify({ action: 'disconnect', username: e.data.username }));
            }
            break;

         case "list":
            socket.send(JSON.stringify({action: 'list', to: currentUser}));
            break;
         
         case "help":
            socket.send(JSON.stringify({action: 'help'}));
      } // switch
   } // port.onmessage

   conn.start();
} // onconnect
