class FirebaseClass {
    constructor(config) {
        this.config = config;
        this.init();
    }

    init() {
        firebase.initializeApp(config);
        this.messages_ref = 'messages';
        let firestore = firebase.firestore();
        const settings = {timestampsInSnapshots: true};
        firestore.settings(settings);
        this.Firestore = firestore;
    }

    static getConversationId() {
        if(RECEIVER_ID < FIREBASE_UID) return RECEIVER_ID + FIREBASE_UID;
        else return FIREBASE_UID + RECEIVER_ID;
    }

    addMessage(receiver_uid, message) {
        this.Firestore.collection(FirebaseClass.getConversationId()).add({
            'receiver_id': receiver_uid,
            'receiver_name': RECEIVER_NAME,
            'sender_id': FIREBASE_UID,
            'sender_name': FIREBASE_NAME,
            'content': message,
            'created': Date.now(),
        })
    }

    static prepareData(data) {
        return {'id': data.id, 'data': data.data()};
    }

    drawMessage(res) {
        let receiverSource = document.getElementById("receiver-template").innerHTML;
        let templateReceiver = Handlebars.compile(receiverSource);

        let senderSource = document.getElementById("sender-template").innerHTML;
        let templateSender = Handlebars.compile(senderSource);

        let html = !res ? 'We have no messages yet.' : '';

        if(res) {
            let context = {content: res.data.content, time: moment(res.data.created).fromNow()};
            if (res.data.sender_id === FIREBASE_UID) html = templateSender(context);
            else html = templateReceiver(context);
        }


        $('.msg_history').append(html);
        let objDiv = document.getElementById("messagesHistory");
        objDiv.scrollTop = objDiv.scrollHeight;
    }

    messagesHistory() {
        if(this.unsubscribe) {
            this.unsubscribe();
        }

        this.unsubscribe = this.Firestore.collection(FirebaseClass.getConversationId())
            .orderBy('created', 'asc')
            .onSnapshot((snapshot) => {
                snapshot.docChanges()
                    .forEach((change) => {
                        if (change.type === 'added') {
                            this.drawMessage(FirebaseClass.prepareData(change.doc));
                        }
                    });
            });
    }
}


let config = {
    apiKey: "AIzaSyAOoIIWOuyGTxPjDuuPXuPvL4DFmuh_TTw",
    authDomain: "chat-dd165.firebaseapp.com",
    databaseURL: "https://chat-dd165.firebaseio.com",
    projectId: "chat-dd165",
    storageBucket: "chat-dd165.appspot.com",
    messagingSenderId: "43144060714"
};

let ObjFirebase = new FirebaseClass(config);
RECEIVER_ID = null;
RECEIVER_NAME = null;

function getMessages(firebase_id, name, event) {
    if ($(event).hasClass('active')) return false;
    $('.msg_history').empty();
    RECEIVER_ID = firebase_id;
    RECEIVER_NAME = name;

    $(`.chat_list`).removeClass('active');
    $(`.chat_list[data-id=${firebase_id}]`).addClass('active');

    ObjFirebase.messagesHistory(RECEIVER_ID);
}

function writeUserData(event) {
    event.preventDefault();
    let message = $('#message').val();
    if (message && RECEIVER_ID) {
        ObjFirebase.addMessage(RECEIVER_ID, message);
        $('#message').val('');
    }
}