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

function setChatOpen(isOpen) {
    const widget = $('#chat-widget');
    const toggle = $('#chat-toggle');
    const close = $('#chat-close');

    widget.toggleClass('is-collapsed', !isOpen);
    toggle.attr('aria-expanded', isOpen ? 'true' : 'false');
    close.attr('aria-label', isOpen ? 'Dong chat' : 'Mo chat');
    localStorage.setItem('chatWidgetOpen', isOpen ? '1' : '0');

    if (isOpen) {
        $('.chat-body').scrollTop($('.chat-body')[0].scrollHeight);
        setTimeout(function () {
            $('#message').trigger('focus');
        }, 120);
    }
}

$(function () {
    setChatOpen(localStorage.getItem('chatWidgetOpen') !== '0');
});

$(document).on('click', '#chat-close', function () {
    const isCollapsed = $('#chat-widget').hasClass('is-collapsed');
    setChatOpen(isCollapsed);
});

$(document).on('click', '#chat-toggle', function () {
    const isCollapsed = $('#chat-widget').hasClass('is-collapsed');

    if (isCollapsed) {
        setChatOpen(true);
    }
});

$(document).on('keydown', '#message', function (e) {
    if (e.key === 'Enter') sendMessage();
});
