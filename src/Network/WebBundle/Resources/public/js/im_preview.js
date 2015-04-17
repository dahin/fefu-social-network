function xhr(action, message) {
    message = message || {};
    return new Promise(function (resolve, reject) {
        var oReq = new XMLHttpRequest();
        oReq.open('POST', '/' + action);
        oReq.responseType = 'text';

        oReq.onreadystatechange = function () {
            if (oReq.readyState === 4) {
                switch (oReq.status) {
                case 200:
                    resolve(JSON.parse(oReq.response));
                break;

                case 500:
                    // it's an internal server error so we're assuming for response text
                    // to be a html document describing the error
                    // TODO: ensure this only happens in dev mode
                    document.write(oReq.responseText);
                    reject();

                default:
                    reject(oReq.response);
                    break;
                }
            }
        };

        var strMessage = JSON.stringify(message);
        oReq.send(strMessage);
    });
}


$(document).ready(function() {
    $("#post-text").markItUp(markitup_sonataMarkdownSettings);
});

function AddEdit (postBody, postDiv, handle, post_id, msg, del) {
    var linkedit = $('<button class="blue_button" style="display:inline-block" type="button">edit</button>');
    $(linkedit).click(function(e) {
        //postBody.hide();
        linkedit.hide();
        var postEdit =  postDiv.find('#post-edit');
        $('#post-form').hide();
        var edit = postEdit.find('#edit');
        var send = $('<input type="button" class="blue_button" id="send" value="Send"/>');
        $(edit).markItUp(markitup_sonataMarkdownSettings);
        edit.val(msg);
        postEdit.append(send);
        postEdit.show();
        $(send).click(function (e) {
            xhr('post_edit', {
                post_id: post_id,
                text: $(edit).val()
            }).then(function (data) {
                if(data.error){
                    console.log(data.error);
                    return;
                }
                $(edit).val('');
                $(postEdit).hide();
                send.hide();
                linkedit.show();
                handle(data);
                $('#post-form').show();
            });
                e.preventDefault();
        });
    });
    if (del) {
        var linkdelete = $('<button class="blue_button" style="display:inline-block" type="button">X</button>');
        $(linkdelete).click(function(e) {
            xhr('post_delete', {
                post_id: post_id,
            }).then(function (data) {
                if(data.error){
                    console.log(data.error);
                    return;
                }
                postBody.hide();
                postDiv.hide();
                handle(data);
            });
        });
        postDiv.append(linkdelete);
    }
    var tab = postDiv.find('ul.tabs');
    $(tab).each(function() {
        $(this).find('li').each(function(i) {
            $(this).click(function() {
                $(this).addClass('active').siblings().removeClass('active');
                var p = $(this).parents('div.tabs_container');
                if (i == 1) {
                    var preview = p.find('#edit-text');
                    preview.html('');
                    var text = p.find('div.tab_container').find("#edit");
                    xhr('preview_posts', {
                        text: $(text).val()
                    }).then(function (data) {
                        if(data.error){
                            console.log(data.error);
                            return;
                        }
                        preview.html(data.text);
                    });
                }
                p.find('div.tab_container').hide();
                p.find('div.tab_container:eq(' + i + ')').show();
            });
        });
    });
    postDiv.append(linkedit);
}