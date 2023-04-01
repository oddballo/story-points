(function(){
    function router(request, response){
        errors = ""
        if ( ! response.success ){
            errors += "<p>The request failed</p>";
        }
        if ( response.errors.length > 0 ){
            errors += "<p>There were one or more errors.";
            errors += "<ul>";
            response.errors.forEach(function(element){
                errors += "<li>"+element+"</li>";
            });
            errors += "</ul></p>";
        }
        $('#errors').html(errors);
        
        if ( response.success ){
            switch(request.action){
                case "createRoom":
                    refresh("dataLobby");
                    break; 
                case "destroyRoom":
                    window.location.href = "./";
                    break;
                case "voteRoom":
                case "clearRoom":
                case "showRoom":
                    refresh("dataRoom");
                    break;
                case "nameUser":
                    refresh("dataUser");
                    break;
                case "dataLobby":
                    dataLobby(response);
                    break;
                case "dataRoom":
                    dataRoom(response);
                    break;
                case "dataUser":
                    dataUser(response);
                    break;
            }
        } 
    }

    function dataUser(response){
        let html = "<p>User: "+response.data.name+"</p>";
        $('#dataUser').html(html);
    }

    function dataRoom(response){
        if(!response.data.exists){ 
            window.location.href = "./";
            return;
        }
        let html = "";
        if(response.data.votes.length == 0){
            html = "<p>No votes</p>";
        }else{
            html = "<ul>";
            response.data.votes.forEach(function(element){
                html += "<li>"+element.name+" - "+element.vote+"</li>";
            });
            html += "</ul>";
        }
        $('#dataRoom').html(html);
    }

    function dataLobby(response){
        let html = "";
        if(response.data.rooms.length == 0){
            html = "<p>No rooms</p>";
        }else{
            html = "<ul>";
            response.data.rooms.forEach(function(element){
                html += "<li><a href=\"room.html?room="+element+"\">"+element+"</a></li>";
            });
            html += "</ul>";
        }
        $('#dataLobby').html(html);
    }

    function request(data){
        let url = "api.php";
        if( room != false ){
            url = "api.php?room="+room;
        } 

        $.post({
            url: url, 
            data: data,
            success: function(response){
                router(data, response); 
            }
        });
    }

    function refresh(action){
        data = {"action": action};
        request(data);
    }

    var room = false;
    let searchParams = new URLSearchParams(window.location.search)
    if(searchParams.has('room')){
        room = searchParams.get('room');
    }
    var buttonpressed;
    $('button#refresh').click(function(){
        load();
    });
    $('form input[type="submit"]').click(function() {
        buttonpressed = $(this);
    })
    $("form").submit(function(e){
        e.preventDefault();

        let inputs = $(this).find(':input');
        
        let data = {};
        inputs.each(function() {
            if(this.name){
                data[this.name] = $(this).val();
            }
        });
        // Hack to get the submitted button value when
        // there is more than one submit button
        if(buttonpressed && (buttonpressed.val() || buttonpressed.val() === 0) && buttonpressed.attr('name')){
            data[buttonpressed.attr('name')] = buttonpressed.val();
        }
     
        request(data);
    });

    function load(){
        if ( $('#dataLobby').length ){
            refresh("dataLobby");
        }
        if ( $('#dataRoom').length ){
            refresh("dataRoom");
        }
    }
    function loadOnce(){
        if ( $('#dataUser').length ){
            refresh("dataUser");
        }
    }
    //function timeout(){
    //    setTimeout(function(){
    //        load();
    //        timeout();    
    //    }, 5000);
    //}

    async function subscribe() {
        url = '/longpoll/subscribe?random='+Math.random()+'&topic=';
        if ( !room ){
            url += "lobby";
        }else{
            url += encodeURIComponent("room:"+room);
        }
        let response = await fetch(url);
        if (response.status == 502) {
            await subscribe();
        } else if (response.status != 200) {
            console.log(response.statusText);
            await new Promise(resolve => setTimeout(resolve, 1000));
            await subscribe();
        } else {
            let message = await response.text();
            if(message == "nudge"){
                load();   
            }
            await subscribe();
        }
    }
    
    load();
    loadOnce();
    //timeout();
    subscribe();

})();
