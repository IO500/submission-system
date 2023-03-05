var dcl_graph_height = 0
var dcl_graph_width = 0

String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

jQuery(window).on("orientationchange", function() {
    showGraphs(true)
});

jQuery(window).resize(function() {
    showGraphs(true)
});

function showGraphs(checkSize = false) {
    var old_graph_width = dcl_graph_width
    dcl_graph_height = jQuery(window).height()
    dcl_graph_width = jQuery(window).width()
    if (dcl_graph_height > 600) {
        dcl_graph_height = 600
    }
    if (dcl_graph_width > 600) {
        dcl_graph_width = 600
        if (old_graph_width == 600 && checkSize) {
            return
        }
    }
    if (dcl_graph_height > dcl_graph_width) {
        dcl_graph_height = dcl_graph_width
    }
}