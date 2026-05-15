function appendChat(content, side, cls) {
    const align = side === 'user' ? 'text-end' : '';
    $('#chat-box').append(`<div class="${align} mb-2"><span class="badge ${cls}">${$('<div>').text(content).html()}</span></div>`);
    $('.chat-body').scrollTop($('.chat-body')[0].scrollHeight);
}

function sendMessage() {
    const input = $('#message');
    const message = input.val().trim();
    if (!message) return;

    appendChat(message, 'user', 'text-bg-primary');
    input.val('');
    $('#chat-box').append('<div id="chat-loading" class="mb-2"><span class="badge text-bg-warning">Dang tra loi...</span></div>');

    $.ajax({
        url: `${window.APP_BASE || ''}/chatbot/chatbot.php`,
        method: 'POST',
        data: {message},
        success: function (data) {
            $('#chat-loading').remove();
            appendChat(data, 'bot', 'text-bg-secondary');
        },
        error: function () {
            $('#chat-loading').remove();
            appendChat('Chatbot dang ban, thu lai sau nhe.', 'bot', 'text-bg-danger');
        }
    });
}

$(document).on('keydown', '#message', function (e) {
    if (e.key === 'Enter') sendMessage();
});
