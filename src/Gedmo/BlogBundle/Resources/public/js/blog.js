if (blog === undefined) {
    var blog = {};
}

blog.loadComments = function(offset, articleId) {
    $.getJSON('/article/'+articleId+'/comments/'+offset, function(data) {
        var numAdded = 0;
        $.each(data, function (i, comment) {
            var entry = blog.createComment(i+offset, comment);
            $('div#comments').append(entry);
            numAdded++;
        });
        $('div#comments').data('offset', offset + numAdded);
        if (offset + numAdded === $('div#comments').data('count')) {
            $('a#show-more-comments').remove();
        }
    }, 'json');
};

blog.createComment = function(idx, comment) {
    var entry = $('<div class="row comment" id="'+(idx+1)+'">').append(
        $('<div class="comment-title">').append(
            $('<span class="number">').append(comment.created),
            $('<span class="subject">').append(comment.subject),
            $('<span class="author">').append(comment.author),
            $('<span class="comment-reply">').append(
                $('<a href="#" class="reply">').append('[reply]')
            )
        ),
        $('<div class="separator">'),
        $('<div class="comment-body">').append(comment.content)
    );
    return entry;
};

/**
 * against = {
 *   comment: {
 *     subject: {
 *       'Field cannot be empty': /.+/
 *     },
 *     content: {
 *       'Field cannot be empty': /.+/
 *     }
 *   }
 * }
 */
blog.validate = function(form, against, onError, onValidated) {
    var hadErrors = false;
    $(form + ' :input').not(':button, :submit, :reset, :hidden').each(function (idx, input) {
        var name = $(input).attr('name');
        name = name.split('[');
        for (var i = 0; i < name.length; i++) {
            name[i] = name[i].replace(/\]$/, '');
        }
        var patterns = against, key, errors = [];
        while (key = name.shift()) {
            if (undefined !== patterns[key]) {
                patterns = patterns[key];
            } else {
                patterns = {};
                break;
            }
        }
        for (msg in patterns) {
            if (patterns.hasOwnProperty(msg)) {
                if (!$(input).val().match(patterns[msg])) {
                    errors.push(msg);
                }
            }
        }
        if (errors.length) {
            hadErrors = true;
            onError(input, errors);
        }
    });
    hadErrors || onValidated();
};

blog.submitComment = function () {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/article/comment',
        data: $('#comment-form :input'),
        success: function(comment) {
            if (comment) {
                var entry = blog.createComment(0, comment);
                $('#comments-header').show();
                $('div#comments').prepend(entry);
                $('#comment-form div.input :input')
                    .not(':button, :submit, :reset, :hidden')
                    .val('')
                    .removeAttr('checked')
                    .removeAttr('selected')
                ;
            }
        }
    });
};

blog.submitMessage = function () {
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '/contact/send',
        data: $('#contact-form :input'),
        success: function(msg) {
            var flash = $('div.alert-message');
            if (msg.error !== undefined) {
                flash
                    .removeClass('success')
                    .addClass('error')
                    .find('p')
                    .html('Failed to send an email: '+msg.error)
                ;
            } else {
                flash
                    .addClass('success')
                    .removeClass('error')
                    .find('p')
                    .html('Email was sent from: ' + msg.email)
                ;
                $('#contact-form div.input :input')
                    .not(':button, :submit, :reset, :hidden')
                    .val('')
                    .removeAttr('checked')
                    .removeAttr('selected')
                ;
            }
            flash.show();
        }
    });
};

blog.onContactReady = function() {
    $('#contact-form button[type=submit]').click(function () {
        var validation = {
            message: {
                sender: {
                    'Field cannot be empty': /.+/
                },
                email: {
                    'Email must be entered': /.+/,
                    'Email is invalid': /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/
                },
                content: {
                    'Message cannot be empty': /.+/
                }
            }
        };
        var onError = function(input, errors) {
            $('<span class="help-inline">')
                .append(errors.shift())
                .insertAfter($(input));
            ;
            $(input).parent().parent().addClass('error');
        };
        // clear all errors
        $('#contact-form div.error').each(function () {
            $(this)
                .removeClass('error')
                .find('span.help-inline')
                .remove()
            ;
        });
        blog.validate('#contact-form', validation, onError, blog.submitMessage);
        return false;
    });
};

blog.onArticleViewReady = function() {
    var count = parseInt($('div#comment-count').text());
    $('div#comments').data('count', count);
    var load = function() {
        blog.loadComments(
            $('div#comments').data('offset'),
            parseInt($('h1').attr('id'))
        );
    };
    if (isNaN(count) || count == 0) {
        $('a#show-more-comments').remove();
        $('#comments-header').hide();
    } else {
        $('div#comments').data('offset', 0);
        load();
    }
    
    $('a#show-more-comments').click(function () {
        load();
        return false;
    });
    $('#comment-form button[type=submit]').click(function () {
        var validation = {
            comment: {
                subject: {
                    'Field cannot be empty': /.+/
                },
                content: {
                    'Body cannot be empty': /.+/
                }
            }
        };
        var onError = function(input, errors) {
            $('<span class="help-inline">')
                .append(errors.shift())
                .insertAfter($(input));
            ;
            $(input).parent().parent().addClass('error');
        };
        // clear all errors
        $('#comment-form div.error').each(function () {
            $(this)
                .removeClass('error')
                .find('span.help-inline')
                .remove()
            ;
        });
        blog.validate('#comment-form', validation, onError, blog.submitComment);
        return false;
    });
    $('a.reply').live('click', function () {
        var subject = 'Re: ' + $(this).parent().parent().children().filter('span.subject').html();
        $('#comment-subject').attr('value', subject);
        $('#post-comment').attr('tabindex', -1).focus();
        return false;
    });
};
