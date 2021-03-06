(function () {
    let contactItems = document.getElementsByClassName('contact-item');
    let contactChevrons = document.getElementsByClassName('contact-chevron');
    let listMessage = document.getElementById('list-message');
    let viewMessage = document.getElementById('view-message');
    Array.from(contactItems).forEach(function (contactItem) {
        contactItem.addEventListener('click', function () {
            Array.from(contactChevrons).forEach(function (contactChevron) {
                contactChevron.remove();
            })
            let contactId = contactItem.dataset.contactId;
            let contactEmail = contactItem.dataset.contactEmail;
            contactItem.innerHTML = '<span>' + contactEmail + '</span><i class="bi bi-caret-right-fill contact-chevron"></i>'
            let httpRequest = new XMLHttpRequest();
            let url = Routing.generate('contact_message_list', {'contactId': contactId});
            httpRequest.open("GET", url);
            httpRequest.send();
            httpRequest.onload = function () {
                if (httpRequest.status === 200) {
                    listMessage.innerHTML = httpRequest.response;
                    viewMessage.innerHTML = "";
                    listenMessageItems();
                }
            }
        });
    });

    function listenMessageItems() {
        let messageItems = document.getElementsByClassName('message-item');
        Array.from(messageItems).forEach(function (messageItem) {
            messageItem.addEventListener('click', function () {
                let messageChevrons = document.getElementsByClassName('message-chevron');
                Array.from(messageChevrons).forEach(function (messageChevron) {
                    messageChevron.remove();
                })
                let messageId = messageItem.dataset.messageId;
                let messageDate = messageItem.dataset.messageDate;
                let messageTime = messageItem.dataset.messageTime;
                let httpRequestView = new XMLHttpRequest();
                let urlView = Routing.generate('message_view', {'messageId': messageId});
                httpRequestView.open("GET", urlView);
                httpRequestView.send();
                httpRequestView.onload = function () {
                    if (httpRequestView.status === 200) {
                        viewMessage.innerHTML = httpRequestView.response;
                        let itemMessage = document.getElementById('item-message-' + messageId);
                        itemMessage.innerHTML = "<span>Message re??u le " + messageDate + " ?? : " + messageTime + "</span> <i class='bi bi-caret-right-fill message-chevron'></i>"
                        listenMessageContent();
                    }
                }
            });
        });
    }

    function listenMessageContent() {
        let btnPrecessedMessage = document.getElementById('btn-processed-message');
        if (btnPrecessedMessage) {
            btnPrecessedMessage.onclick = (element) => {
                let messageId = btnPrecessedMessage.dataset.messageId;
                let httpRequest = new XMLHttpRequest();
                let url = Routing.generate('message_processed', {'id': messageId});
                httpRequest.open("PUT", url);
                httpRequest.send();
                httpRequest.onload = function () {
                    if (httpRequest.status === 200) {
                        let itemMessage = document.getElementById('item-message-' + messageId);
                        itemMessage.classList.remove('bg-danger', 'border-danger')
                        itemMessage.classList.add('bg-success', 'border-success')
                        btnPrecessedMessage.remove()

                    }
                }
            }
        }

        let btnRemoveMessage = document.getElementById('btn-remove-message');
        btnRemoveMessage.onclick = (element) => {
            let confirmation = confirm('??tes vous s??r de vouloir supprimer le message');
            if (confirmation) {
                let messageId = btnRemoveMessage.dataset.messageId;
                let httpRequest = new XMLHttpRequest();
                let url = Routing.generate('message_remove', {'id': messageId});
                httpRequest.open("DELETE", url);
                httpRequest.send();
                httpRequest.onload = function () {
                    if (httpRequest.status === 200) {
                        let itemMessage = document.getElementById('item-message-' + messageId);
                        let contentMessage = document.getElementById('content-message-' + messageId);
                        itemMessage.remove()
                        contentMessage.remove()
                    }
                }
            }
        }
    }
})(
);