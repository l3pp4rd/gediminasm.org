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
        numAdded = numAdded ? offset + numAdded : 0;
        $('div#comments').data('offset', numAdded);
    }, 'json');
};

blog.createComment = function(idx, comment) {
    var entry = $('<div class="row comment" id="'+(idx)+'">').append(
        $('<div class="comment-title">').append(
            $('<span class="number">').append(comment.created),
            $('<span class="subject">').append(comment.subject),
            $('<span class="author">').append(comment.author),
            $('<span class="comment-reply">').append(
                $('<a href="#" class="reply">').append('reply')
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

blog.onArticleViewReady = function() {
    blog.loadComments(0, parseInt($('h1').attr('id')));
    $('a#show-more-comments').click(function () {
        var offset = parseInt($('div#comments').data('offset'));
        if (offset === 0) {
            $('a#show-more-comments').remove();
        } else {
            blog.loadComments(
                $('div#comments').data('offset'),
                parseInt($('h1').attr('id'))
            );
        }
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
    
    $('div.content code').attr('name', 'code').addClass('php');
    //dp.SyntaxHighlighter.all();
    //dp.SyntaxHighlighter.ClipboardSwf = '/highlighter/Scripts/clipboard.swf';
    //dp.SyntaxHighlighter.HighlightAll('code');
};
