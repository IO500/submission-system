var dcl_loadCompleteFunctions = []
var dcl_system = null

var dcl_prevent_edit_name = true
var dcl_draw_graph = true
var dcl_header = null
var dcl_reset_data = null

String.prototype.endsWith = function(suffix) {
    return this.indexOf(suffix, this.length - suffix.length) !== -1;
};

function walkAvailableFields(schema, parents, callback) {
    for (var s in schema) {
        var sub = schema[s]
        if (sub instanceof Object && "dtype" in sub) {
            callback(sub, s, parents)
            continue
        }
        if (s == "SCHEMES" || s == "SCHEMES_multi") {
            for (var op in sub) {
                var scheme_name = sub[op]
                var scheme_data = dcl_schemes[scheme_name]
                parents.push(scheme_name)
                walkAvailableFields(scheme_data, parents, callback)
                parents.pop()
            }
        } else {
            parents.push(s)
            walkAvailableFields(sub, parents, callback)
            parents.pop()
        }
    }
}

function edit_individual() {
    dcl_button_list = []
    dcl_calc_button_list = []
    dcl_buttons = 0

    var outwrap = jQuery("#data_fields_wrap"); //Fields wrapper
    dcl_scope_list = globals["scope"]
    jQuery(outwrap).html()
    str = recursive_create(dcl_repository, "global", dcl_system, true)
    jQuery(outwrap).append(str).fadeIn(500)
    // now add dcl_buttons
    for (i in dcl_button_list) {
        attach_add_btn(i)
    }
    jQuery("#1_remove").hide()

    component_added()
}

function loadData(data) {
    if (!data.hasOwnProperty('DATA')) {
        alert("Load failed");
        return;
    }
    dcl_reset_data = data;
    dcl_button_list = []
    dcl_buttons = 0
    dcl_load_button_list = []
    dcl_calc_button_list = []
    var str
    dcl_system = dcl_header["SYSTEM"]
    dcl_units = dcl_header["UNITS"]
    prepareUnits()
    dcl_repository = data["DATA"]; //JSON.parse
    dcl_graphData = data["GRAPH"]

    if (dcl_repository == null) {
        jQuery("#dcl_list").hide()
        return;
    }
    dcl_connecting_graphs = dcl_header["CONNECT"]
    dcl_schemes = dcl_header["SCHEMES"]
    globals = dcl_header["GLOBALS"]
    var scope = globals["scope"]

    provide_hooks()

    if (document.getElementById("data_fields_wrap") != null) {
        edit_individual();
    }
    for (f in dcl_loadCompleteFunctions) { //makes graphs work somehow???
        dcl_loadCompleteFunctions[f]()
    }
}

function provide_hooks() {
    if (!dcl_global_readonly) {
        jQuery("#dcl_wrap").html('<div id="status"></div>' +
            '<form id="dcl_data">' +
            '<div id="data_fields_wrap"></div>' +
            //'<p id=dcl_edtbuttons>' +
            //  '<button class="submitButton" type="button" onclick="loadData()">Load JSON</button>' +
            //  '<button class="submitButton" type="button" onclick="submitDCLChanges()">Save JSON</button>' +
            //  '<button class="submitButton" type="button" onclick="loadData(dcl_reset_data)">Reset</button>' +
            //'</p>' +
            '</form>' +
            '<div id="dcl_graph_fields"></div>'
        )
    } else {
        jQuery("#dcl_wrap").html(
            '<form id="dcl_data">' +
            '<div id="data_fields_wrap"></div>' +
            '</form>' +
            '<div id="dcl_graph_fields"></div>')
    }
}

function dcl_startup() {
    if (dcl_draw_graph) {
        dcl_loadCompleteFunctions = [resetGraphs, update_names, load_graph, redraw_graph, check_unnecessary_graphs]
    } else {
        dcl_loadCompleteFunctions = [update_names]
    }
    errfunc = function(jqXHR, textStatus, errorThrown) {
        jQuery("#dcl_wrap").html("error " + textStatus + "\nServer:" + jqXHR.responseText)
    }

    if (!dcl_site.startsWith("https")) {
        dcl_site = dcl_site; ///../lib/plugins/newcdcl/scripts/
    }

    var promises = [
        jQuery.ajax({
            url: dcl_schema, ///../lib/plugins/newcdcl/scripts/
            dataType: 'json',
            success: function(data) {
                dcl_header = data;
            },
            error: errfunc,
        }),
        jQuery.ajax({
            url: dcl_site,
            dataType: 'json',
            success: function(data) {
                dcl_reset_data = data
            },
            error: errfunc,
        })
    ];
    Promise.all(promises).then(function() {
        if (dcl_reset_data && dcl_header)
            loadData(dcl_reset_data);

        tippy('input', {
            trigger: 'mouseenter focus click',
            content(reference) {
                const title = reference.getAttribute('title')
                reference.removeAttribute('title')
                return title
            }
        });
    })
}