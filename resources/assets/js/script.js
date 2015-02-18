// @codekit-prepend "../../../public/bower_components/mustache.js/mustache.js"
// @codekit-prepend "../../../public/bower_components/bootstrap-sass-official/assets/javascripts/bootstrap/dropdown.js"

(function($, window, undefined) {

    var PointsApp = function() {
        this.element = $('#check-points');
        this.element.on('click', $.proxy(this.checkPoints, this));
    };

    PointsApp.prototype.disableButton = function() {
        this.element.val('Checking...');
        this.element.attr('disabled', 'disabled');
        this.element.toggleClass('working');
    };

    PointsApp.prototype.enableButton = function() {
        this.element.val('Check Points');
        this.element.attr('disabled', false);
        this.element.toggleClass('working');
    };

    PointsApp.prototype.renderPoints = function(data) {
        var template = $('#point-table-render').first().html(),
            output = Mustache.render(template, {
                handle: data.handle,
                minutesOnline: data.minutes_online,
                points: data.points
            }),
            resultsTable = $('.points-results-table');

        if (resultsTable.length === 0)
        {
            $('#points-panel').append(output);
            return;
        }
        else
        {
            resultsTable.replaceWith(output);
        }
    };

    PointsApp.prototype.ajaxDone = function(data) {
        this.renderPoints(data);
        this.enableButton();
    };

    PointsApp.prototype.ajaxFail = function() {
        this.enableButton();
        alert('Unable to find handle.');
    };

    PointsApp.prototype.checkPoints = function(e) {
        var elem = $(e.target),
            handle = elem.parent().find('#handle').val();

        if (elem.hasClass('working')) {
            return;
        }

        this.disableButton();

        $.ajax({
            url         : '/api/points',
            dataType    : 'json',
            type        : 'GET',
            data        : { handle: handle }
        })
        .done($.proxy(this.ajaxDone, this))
        .fail($.proxy(this.ajaxFail, this));

        e.preventDefault(elem);
    };

    //new PointsApp();

})(jQuery, window, undefined);