var dcl_repository = [] // Array that will contain all sites
var dcl_schemes = []
var dcl_button_list = []
var dcl_calc_button_list = []
var dcl_load_button_list = []
var dcl_units = []
var dcl_buttons = 0
var dcl_status_msgs = []
var dcl_connecting_graphs = []
var dcl_global_readonly = true
var dcl_scope_list = "site"
var dcl_graphData = null
var dcl_host = window.location.protocol + "//" + window.location.host + '//cdcl';
var dcl_unitValueMap = {}
//var dcl_upload_page = "http://localhost/io-500-hub/submissions/add";

function prepareUnits() {
    for (var type in dcl_units) {
        for (var n in dcl_units[type]) {
            var unit = dcl_units[type][n]
            dcl_unitValueMap[unit[0]] = [unit[1], type]
        }
    }
}

function getGraphData() {
    return dcl_graphData
}

function createType_read_only(label, map, name, value, unit_selected) {
    var str2 = ""
    if (label == "name") {
        str2 = 'style="display:none;"'
    }
    var str = '<div class="value" ' + str2 + '><span class="label">' + label + '</span><span id="' + name + '" class="value'

    if (label == "name") {
        str = str + ' entity_name'
    }
    var unit = map["unit"]
    if (unit) {
        value = value + " " + unit_selected
    }

    return str + '">' + value + "</span></div>"
}

function dcl_choice_change(name) {
    var tgt = jQuery("#" + name + "_other");
    if (!tgt.length) return;
    var elem = jQuery("#" + name);
    if (elem.val() != "other") {
        tgt.prop("disabled", true);
        tgt.val("");
        tgt.css({
            "background-color": ""
        });
    } else {
        tgt.prop("disabled", false);
        tgt.css("background-color", "white");
    }
}

function createType(label, map, name, value, unit_selected) {
    var type = map["dtype"]
    var autocomplete = map["load"] // support autocomplete

    if (("enabled" in map && !map["enabled"]) || dcl_global_readonly) {
        if (value == "") {
            return ""
        }
        return createType_read_only(label, map, name, value, unit_selected);
    }
    var default_unit = map["default-unit"]

    var str = '<label for="' + name + '">' + label + "</label>"

    var attr = 'id="' + name + '"'

    if (autocomplete) {
        // str = str + '<span class="autocomplete">'
        attr = attr + ' class="autocomplete"'
    }
    if (label == "name") {
        if (dcl_prevent_edit_name) {
            if (name == "1_name") {
                attr = attr + ' disabled="disabled"'
            }
        }
        attr = attr + ' class="entity_name"'
    }
    if ("required" in map) {
        attr = attr + " required"
    }
    if ("maxlength" in map) {
        attr = attr + " maxlength=" + map["maxlength"]
    }
    if ("desc" in map) {
        //str = str + '<div class="help">' + map["desc"] +'</div>'
        if ("pattern" in map) {
            attr = attr + ' title="' + map["desc"] + '\nPattern:' + map["pattern"] + '"'
        } else {
            attr = attr + ' title="' + map["desc"] + '"'
        }
    } else {
        if ("pattern" in map) {
            attr = attr + ' title="Pattern: ' + map["pattern"] + '"'
        }
    }
    if ("pattern" in map) {
        //str = str + '<div class="help">' + map["desc"] +'</div>'
        attr = attr + ' pattern="^' + map["pattern"] + '$"'
    }
    if (type == "number" || type == "integer") {
        attr = attr + ' min="0"'
    }


    if (type == "options") {
        str = str + "<table>";
        value = value.split(";");
        for (v in map["choice"]) {
            var option = map["choice"][v]
            var lname = name + "_opt" + v;
            var sel = ""
            if (value instanceof Array && value.includes(option)) {
                sel = ' checked'
            }
            str = str + '<tr><td><input type="checkbox" id="' + lname + '" value="' + option + '"' + sel + '></td><td><label for="' + lname + '">' + option + '</label></td></tr>';
        }
        str = str + "</table>";
    } else if (type == "choice") {
        var other = false;
        var found_selection = false;
        var disabled = true;
        var other_text = "";
        for (v in map["choice"]) {
            var option = map["choice"][v]
            if (option == "other") {
                other = true;
            }
            if (option == value) {
                found_selection = true;
            }
        }
        if (value != "" && !found_selection && other) {
            other_text = ' value="' + value + '"';
            value = "other";
            disabled = false;
        }

        str = str + "<select " + attr + ' onchange=\'dcl_choice_change("' + name + '")\'><option value=""></option>';
        for (v in map["choice"]) {
            var option = map["choice"][v]
            var sel = ""
            if (option == value) {
                sel = ' selected="selected"'
            }
            str = str + "<option" + sel + ">" + option + "</option>"
        }
        str = str + "</select>";
        if (other) {
            str = str + "<input type='text' " + 'id="' + name + '_other" ' + (disabled ? "disabled" : "") + other_text + "></input>"
        }
    } else if (type == "number") {
        str = str + '<input type="number" value="' + value + '"' + attr + ' step="any">'
    } else if (type == "integer") {
        str = str + '<input type="number" value="' + value + '"' + attr + ' step="1">'
    } else {
        str = str + '<input type="' + type + '" value="' + value + '"' + attr + '>'
    }

    var unit = map["unit"]
    var hasUnit = ''
    if (unit) {
        hasUnit = ' value-side'
        // support unit selector
        available = dcl_units[unit]
        str = str + '<select id="' + name + '_unit">' // <option></option>
        for (v in available) {
            var metric = available[v][0]
            if (unit_selected == metric || (unit_selected == null && default_unit == metric)) {
                str = str + '<option selected="selected">' + metric + "</option>"
            } else {
                str = str + "<option>" + metric + "</option>"
            }
        }
        str = str + "</select>"
    }
    var aggregate = map["aggregate"]
    if (aggregate) {
        str = str + '<a href="#" id="' + name + '_calc" class="dcl_recalc">[recalc]</a>'
        dcl_calc_button_list.push([name, aggregate])
    }
    //if(autocomplete){ // </span>
    //    str = str + '<a href="#" id="' + name + '_store" class="dcl_store">[upload model]</a>'
    //    dcl_load_button_list.push([name, autocomplete])
    //}
    if (autocomplete) { // </span>
        //str = str + '<a href="#" id="' + name + '_store" class="dcl_store">[upload model]</a>'
        dcl_load_button_list.push([name, autocomplete])
    }
    return '<div class="value' + hasUnit + '">' + str + '</div>' //'<span class="error" id="'+ name + '_err"></span>
}

function create_component(data, s, n, parent, schema, schema_btn_id = -1, forceshow = false, tooltip = "") {
    dcl_buttons = dcl_buttons + 1
    var pos = dcl_buttons
    var hidden = ""
    var cls = "entity"
    if (schema_btn_id > 0) {
        cls = "schema"
    }
    if (parent != "global" && cls == "entity" && forceshow == false) {
        //hidden='style="display:none;"'
    }
    var tls = ""
    tls = tls + '<a href="#" id="' + pos + '_rollup" class="rollup fa">&#xf078</a>'
    if (!dcl_global_readonly) {
        tls = tls + '<a href="#" id="' + pos + '_remove" class="fa"> &nbsp; &#xf00d;</a>'
    }
    if (tooltip) {
        tooltip = ' title="' + tooltip + '"';
    }
    dcl_button_list[dcl_buttons] = [s, n, parent, schema, schema_btn_id]
    cls = cls + " " + s.replaceAll(" ", "")
    str = "<div class='" + cls + "' id='" + pos + "'><div class='head' id='head" + pos + "'><span id='h" + pos + "' " + tooltip + ">" + s + '</span><span id="h' + pos + '_name" class="name"></span><span class="navigation">' + tls + "</span></div><div class='body' id='" + pos + "_b'" + hidden + "><hr>"
    //str = str + '<input type="hidden" name="type" value="'+ pos + "_" + s + '"/>'
    str = str + recursive_create(data, pos, schema, false) // data[s]
    str = str + "</div></div>"
    return str
}

function create_component_header(s, n, parent, schema) {
    dcl_buttons = dcl_buttons + 1
    dcl_button_list[dcl_buttons] = [s, n, parent, schema, -1]
    str = '<a href="#" class="' + dcl_buttons + '_add">+ ' + s + '</a> '
    return str
}

function create_schema_header(s, n, parent, schema, hidden = false, multi = false, tooltip = "") {
    dcl_buttons = dcl_buttons + 1
    dcl_button_list[dcl_buttons] = [s, n, parent, schema, dcl_buttons, hidden, multi, tooltip]
    if (tooltip != "") {
        tooltip = " title=\"" + tooltip + "\"";
    }
    str = ' <a href="#" class="' + dcl_buttons + '_add"' + tooltip + '>' + s + '</a> '
    return str
}

function recursive_create(data, name, schema, top_level) {
    var str = ""
    var re = new RegExp(" ", 'g')
    var n
    var s
    var attr_str = ""
    var append_str = ""
    var schema_str = ""
    var scheme_value_str = ""
    for (var s in schema) {
        if (s == "SCHEMES" || s == "SCHEMES_multi") {
            // append all dcl_schemes!
            for (n in schema[s]) {
                var scheme_name = schema[s][n]
                // check if childs contains any of this scheme
                var used_scheme = false
                var schema_caption = scheme_name;
                var tooltip = "";
                if (scheme_name.indexOf(":") != -1) {
                    var tmp = scheme_name.split(":")
                    scheme_name = tmp[1];
                    schema_caption = tmp[0]; // + " (type:" + scheme_name + ")";
                    if (tmp.length > 2) {
                        tooltip = tmp[2];
                    }
                }
                var scheme_data = dcl_schemes[scheme_name]

                if (data != null) {
                    for (i in data.childs) {
                        c = data.childs[i]
                        if (c.type == schema_caption) {
                            // found schema

                            var button_id = dcl_buttons + 1;
                            if (!used_scheme) {
                                schema_str = schema_str + create_schema_header(schema_caption, n, name, scheme_data, s == "SCHEMES", s == "SCHEMES_multi", tooltip)
                            }
                            used_scheme = true
                            scheme_value_str = scheme_value_str + create_component(c, schema_caption, n, name, scheme_data, button_id, tooltip)
                        }
                    }
                }
                if (!used_scheme) {
                    schema_str = schema_str + create_schema_header(schema_caption, n, name, scheme_data, false, s == "SCHEMES_multi", tooltip)
                }
            }
            continue
        }
        n = name + "_" + s // + data["name"]
        n = n.replace(re, '')
        if (schema[s] instanceof Object && "dtype" in schema[s]) {
            // this is the final object
            value = ""
            unit = null
            if (data != null && "att" in data) {
                value = data["att"][s]
                if ("undefined" === typeof value) {
                    value = ""
                } else if (value instanceof Array) {
                    unit = value[1]
                    value = value[0]
                } else if ("unit" in schema[s] && ("" + value).split(" ").length == 2) {
                    value = ("" + value).split(" ")
                    unit = value[1]
                    value = value[0]
                }
            }
            attr_str = attr_str + createType(s, schema[s], n, value, unit)
            continue
        } else if (schema[s] instanceof Object) {
            if (top_level) {
                str = str + create_component(data, s, n, name, schema[s])
            } else if (data != null) {
                // check all matching entities according to the schema
                for (c in data.childs) {
                    if (data.childs[c]["type"] == s) {
                        var child = data.childs[c]
                        str = str + create_component(child, s, n, name, schema[s])
                    }
                }
            }
            append_str = append_str + create_component_header(s, n, name, schema[s])
        }
        //console.log(data[s]);
    }
    if (top_level) {
        return str
    } else if (dcl_global_readonly) {
        return attr_str + scheme_value_str + str
    } else {
        return attr_str + '<div class="schema_list">' + schema_str + "</div>" + '<div class="component_list">' + append_str + "</div>" + scheme_value_str + str
    }
}

function find_calc_childs(parent, terms, depth, result) {
    if (depth == terms.length - 1) {
        if (terms[depth] in parent["att"]) {
            var val = parent["att"][terms[depth]]

            if (val instanceof Array) {
                result.push(parseFloat(val[0]) * dcl_unitValueMap[val[1]][0])
            } else {
                result.push(parseFloat(val))
            }
        } else {
            result.push(NaN)
        }
        return
    }
    for (var i in parent["childs"]) {
        var c = parent["childs"][i]
        if (c["type"] && c["type"] == terms[depth]) {
            find_calc_childs(c, terms, depth + 1, result)
        }
    }
}

function find_suitable_unit(unit, val) {
    var baseunit = dcl_unitValueMap[unit][1]
    var a = dcl_units[baseunit]
    for (var i in a) {
        var u = a[i]
        if (val < u[1]) {
            // take the unit one before
            if (i == 0) {
                return u
            }
            if (val / a[i - 1][1] < 2) {
                if (i < 2) {
                    return a[0]
                } else {
                    return a[i - 2]
                }
            } else {
                return a[i - 1]
            }
        }
    }
    return a[a.length - 1]
}

function add_calc_button(name, aggregates) {
    jQuery("#" + name + "_calc").unbind()
    jQuery("#" + name + "_calc").click(function(e) { //on add input button click
        e.preventDefault()
        var data = traverse_elements_to_obj(document.getElementById(name.split("_")[0], 0), 0);

        var sum = 0
        for (var a in aggregates) {
            var eq = aggregates[a]
            var terms = eq.split("*") // only * supported
            var all_values = []
            for (var n in terms) {
                var term = terms[n].trim().split(".")
                var values = []
                find_calc_childs(data, term, 0, values)
                all_values.push(values)
            }
            for (var c in all_values[0]) {
                var tmp = 1
                for (var n in terms) {
                    tmp = tmp * all_values[n][c]
                }
                if (!isNaN(tmp)) {
                    sum = sum + tmp
                }
            }
        }
        // based on unit
        var unit = jQuery("#" + name + "_unit")
        if (unit.length) {
            var unit_scale = find_suitable_unit(unit.val(), sum)
            unit.val(unit_scale[0])
            sum = sum / unit_scale[1]
            sum = sum.toFixed(2)
        }

        jQuery("#" + name).val(sum)
    });
}

function hasClass(txt, cls) {
    return (' ' + txt + ' ').indexOf(' ' + cls + ' ') > -1;
}

function insert_data(el, data) {
    for (var i = 0; i < el.children.length; i++) {
        var c = el.children[i]
        if (c.nodeName == "DIV") {
            if (c.className == "body") {
                for (var p = 0; p < c.children.length; p++) {
                    var cc = c.children[p]

                    if (cc.nodeName == "DIV") {
                        if (hasClass(cc.className, "entity") || hasClass(cc.className, "schema")) {
                            // find matching child in the data
                            var type = cc.firstChild.firstChild.innerText
                            for (var f in data.childs) {
                                if ("type" in data.childs[f] && data.childs[f]["type"] == type) {
                                    insert_data(cc, data.childs[f]);
                                }
                            }
                        } else if (hasClass(cc.className, "value")) {
                            var label = ""
                            for (var q = 0; q < cc.children.length; q++) {
                                var ccc = cc.children[q]
                                if (ccc.nodeName == "LABEL") {
                                    label = ccc.firstChild.nodeValue
                                } else if (ccc.nodeName == "INPUT" || ccc.nodeName == "SELECT") {
                                    var id = ccc.id
                                    if (label in data["att"]) {
                                        var val = data["att"][label]
                                        if (val instanceof Array) {
                                            if (id.endsWith("_unit")) {
                                                val = val[1]
                                            } else {
                                                val = val[0]
                                            }
                                        }
                                        if (id.endsWith("_other")) {
                                            // deal with selectboxes supporting "other"
                                            var id_select = id.substr(0, id.length - 6)
                                            var res = jQuery("#" + id_select)
                                            res.val(val)
                                            if (res.val() == val) {
                                                continue;
                                            }
                                            jQuery("#" + id_select).val("other")
                                        }
                                        jQuery("#" + id).val(val)
                                        jQuery("#" + id).css({
                                            background: "#e6ffc6"
                                        })
                                    } else {
                                        jQuery("#" + id).css({
                                            background: "inherit"
                                        })
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

function add_store_button(name, load) {
    var type = name.split("_")[1]
    jQuery("#" + name + "_store").unbind()

    jQuery("#" + name + "_store").click(function(e) { //on add input button click
        e.preventDefault()
        var model = jQuery("#" + name).val().trim()
        if (model.length < 5) {
            alert("Error, the model name is too short for upload: \"" + model + "\"")
            return;
        }
        var result = confirm("Are you sure you want to upload this model?");
        if (!result) {
            return;
        }
        var p = document.getElementById(name.split("_")[0], 0)
        var data = traverse_elements_to_obj(p, 0);
        payload = {
            "field": type,
            "value": model,
            "type": load,
            "data": data
        }

        jQuery.ajax({
            type: "POST",
            url: dcl_host + "/ajax-model.php",
            data: JSON.stringify(payload),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function(data) {},
            failure: function(errMsg) {
                addError("Error " + errMsg)
                printError()
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Server Error: \"" + errorThrown + "\"")
            }
        });
    });
}

function component_added() {
    for (var c in dcl_calc_button_list) {
        var name = dcl_calc_button_list[c][0]
        var aggregates = dcl_calc_button_list[c][1]
        add_calc_button(name, aggregates)
    }
    for (var c in dcl_load_button_list) {
        var name = dcl_load_button_list[c][0]
        var load = dcl_load_button_list[c][1]
        add_store_button(name, load)
        //add_autocomplete(name, load)
    }
}

function add_autocomplete(name, load) {
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dcl_host + "/ajax-model.php?type=" + load,
        success: function(data) {
            var arr = data['complete'] || [];
            autocomplete(name, arr);
            var elem = jQuery("#" + name);
            elem.focusout(function() {
                dcl_autocomplete_load(elem, load)
            });
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log("Server Error: \"" + errorThrown + "\"")
        }
    });
}

function dcl_autocomplete_load(elem, type) {
    var model = elem.val();
    if (model.length < 5) return;
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dcl_host + "/ajax-model.php?type=" + type + "&model=" + model,
        success: function(data) {
            if ("childs" in data) {
                var p = document.getElementById(elem.attr('id').split("_")[0], 0)
                insert_data(p, data)
            }
        },
        failure: function(errMsg) {
            addError("Error " + errMsg)
            printError()
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert("Server Error: \"" + errorThrown + "\"")
        }
    });
}

function attach_add_btn(i) {
    var s = dcl_button_list[i][0]
    var n = dcl_button_list[i][1]
    var parent = dcl_button_list[i][2]
    var schema = dcl_button_list[i][3]
    var pos_add_btn = dcl_button_list[i][4]
    var hidden = dcl_button_list[i][5]
    var multi = dcl_button_list[i][6]
    var tooltip = dcl_button_list[i][7]

    var wrap = jQuery("#" + parent + "_b"); //Fields wrapper

    if (hidden) {
        jQuery("." + i + "_add").hide(0)
    }

    jQuery("." + i + "_add").click(function(e) { //on add input button click
        e.preventDefault();
        var start = dcl_button_list.length
        var str = create_component(null, s, n, parent, schema, pos_add_btn, forceshow = true, tooltip = tooltip)
        jQuery(wrap).append(str);
        component_added()

        for (var i = start; i < dcl_button_list.length; i++) {
            attach_add_btn(i)
        }
        if (pos_add_btn > 0 && !multi) {
            jQuery(this).hide(0)
        }
        update_names()
    });
    jQuery("#" + i + "_remove").click(function(e) { //user click on remove text
        //console.log(pos_add_btn)
        e.preventDefault();
        var parent_id = jQuery(this).attr('id').split("_")[0]
        jQuery("#" + parent_id).remove();
        delete dcl_button_list[i]
        if (pos_add_btn > 0) {
            jQuery("." + pos_add_btn + "_add").show(0)
        }
        update_names()
    })
    jQuery("#head" + i).click(function(e) {
        e.preventDefault();
        jQuery("#" + i + "_b").toggle(0)
        update_names()
    })
}

var toView;

function verify_input(e) {
    p = e.pattern
    //var err_field = document.getElementById(e.getAttribute("id") + '_err')
    var req = e.getAttribute("required")
    if (p == "") {
        if (req != null && e.value == "") {
            e.style.background = "#ffded7"
            //err_field.innerHTML = ""
            addError("Invalid input")
            if (toView == false) {
                e.scrollIntoView();
                toView = true;
            }
        } else {
            e.style.background = "#e6ffc6"
        }
    } else {
        if (e.value == "" && req == null) {
            e.style.background = "#e6ffc6"
            return
        }
        var rx = new RegExp(p);
        if (e.value.match(rx)) {
            e.style.background = "#e6ffc6"
            //err_field.innerHTML = ""
        } else {
            // mark the field red
            e.style.background = "#ffded7"
            addError("Invalid input")
            if (toView == false) {
                e.scrollIntoView();
                toView = true;
            }
            //err_field.innerHTML = "Expected regex: " + p
        }
    }
}

function traverse_elements_to_obj(el, depth) {
    var children = []
    var label = ""
    var head = ""
    var value = ""
    var attributes = {}

    toView = false

    //console.log(el.nodeName + ":" + el.className)
    for (var i = 0; i < el.children.length; i++) {
        var c = el.children[i]
        if (c.nodeName == "DIV" || depth < 1) {
            if (c.className == "head") {
                head = c.firstChild.textContent
            } else if (hasClass(c.className, "entity") || hasClass(c.className, "schema")) {
                children.push(traverse_elements_to_obj(c, depth + 1));
            } else if (c.className == "body") {
                for (var p = 0; p < c.children.length; p++) {
                    var cc = c.children[p]
                    if (cc.nodeName == "DIV") {
                        if (hasClass(cc.className, "entity") || hasClass(cc.className, "schema")) {
                            children.push(traverse_elements_to_obj(cc, depth + 1));
                        } else if (hasClass(cc.className, "value")) {
                            for (var q = 0; q < cc.children.length; q++) {
                                var ccc = cc.children[q]
                                if (ccc.nodeName == "LABEL") {
                                    label = ccc.firstChild.nodeValue
                                    value = -1
                                } else if (ccc.nodeName == "INPUT") {
                                    if (ccc.value != "") {
                                        attributes[label] = ccc.value
                                    }
                                    verify_input(ccc)
                                    value = ccc.value
                                } else if (ccc.nodeName == "SELECT") {
                                    ccc.style.background = "#e6ffc6"
                                    if (value == -1) {
                                        var req = ccc.getAttribute("required")
                                        attributes[label] = ccc.value
                                        if (req != null && ccc.value == "") {
                                            ccc.style.background = "#ffded7"
                                            addError("Invalid input")
                                        }
                                    } else if (value == "") {
                                        // skip this one
                                    } else {
                                        attributes[label] = [value, ccc.value]
                                    }
                                } else if (ccc.nodeName == "TABLE") { // must be an option field
                                    var t = ccc.firstChild; // table.body
                                    var tmp_array = [];
                                    for (var o = 0; o < t.children.length; o++) {
                                        // tr elements
                                        var to = t.children[o].firstChild.firstChild;
                                        if (to.checked) {
                                            tmp_array.push(to.value)
                                        }
                                    }
                                    if (tmp_array.length) {
                                        attributes[label] = tmp_array.join(";")
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if (head == "") {
        if (children.length == 1) {
            return children[0]
        }
        return children
    }
    return {
        "type": head,
        "att": attributes,
        "childs": children
    }
}

function resetStatus() {
    dcl_status_msgs = []
    var status = document.getElementById("status")
    status.style.color = "darkgreen"
}


function addError(msg) {
    dcl_status_msgs.push(msg)
}

function printError() {
    var status = document.getElementById("status")
    status.style.color = "#d63b1e"
    str = ""

    if (dcl_status_msgs.length > 1) {
        str = dcl_status_msgs.length + " Errors"
    } else {
        for (var m in dcl_status_msgs) {
            str = str + '<div class="error_msg">' + dcl_status_msgs[m] + '</div>'
        }
    }

    status.innerHTML = str

    alert("Please, verify and correct all inputs in red!")
}

function submitDCLChanges() {
    var payload = getJSON()
    if (!payload) return;

    /*
    if (typeof dcl_upload_page !== 'undefined') {
        jQuery.ajax({
            type: "POST",
            url: dcl_upload_page,
            data: JSON.stringify(payload),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function(data) {

            },
            failure: function(errMsg) {
                addError("Error " + errMsg)
                printError()
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Server Error: \"" + errorThrown + "\"")
            }
        });
        
        return
    }

    var data = "data:application/json;charset=utf-8," + encodeURIComponent(JSON.stringify(payload));
    var element = document.createElement('a');

    element.setAttribute('href', data);
    element.setAttribute('download', "site.json");

    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);

    if (dcl_graphData != null) {
        redraw_graph()
    }

    return dcl_status_msgs.length == 0;
    */

    document.getElementById('json').value = JSON.stringify(payload);

    return true;
}

function loadDCLJSONFile() {
    fileInput = document.createElement("input")
    fileInput.type = 'file'
    fileInput.accept = '.json'
    fileInput.onchange = function(e) {
        var file = e.target.files[0];
        if (!file) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            var contents = JSON.parse(e.target.result);
            loadData(contents);
        };
        reader.readAsText(file);
    }
    fileInput.click()
}

function getJSON() {
    dcl_graphData = getGraphData()
    resetStatus()

    var data = traverse_elements_to_obj(document.getElementById("data_fields_wrap", 0), 0);

    if (dcl_status_msgs.length > 0) {
        printError()

        return false
    }

    dcl_graphData = store_graph()

    var json = {
        "DATA": data,
        "GRAPH": dcl_graphData
    }

    if (dcl_graphData != null && dcl_draw_graph) {
        redraw_graph()
    }

    reset = json

    return json;
}

function exportData() {
    var payload = getJSON();
    if (!payload) return;
    dcl_global_readonly = true; //switch to readonly mode so the correct dom is copied over
    loadData(payload); //redraw page

    //copy page
    var doccopy = document.cloneNode(true);
    var promises = []
    var wrap = doccopy.getElementById("dcl_wrap")
    wrap.nextElementSibling.innerHTML = "";


    //grab stylesheet
    Array.from(doccopy.getElementsByTagName("link")).forEach(
        function(l) {
            if (l.rel == "stylesheet") {
                promises.push(fetch(l.href).then(data => data.text()).then(
                    function(data) {
                        var src = doccopy.createElement("style");
                        src.innerHTML = data;
                        doccopy.head.append(src);
                        doccopy.head.append(doccopy.createTextNode("\n\t"));
                        l.outerHTML = "";
                    }
                ));
            }
        }
    )
    //remove js links
    Array.from(doccopy.getElementsByTagName("script")).forEach(
        function(l) {
            l.outerHTML = "";
        }
    )
    //add jquery
    var jq = doccopy.createElement("script");
    jq.src = "https://code.jquery.com/jquery-3.1.1.slim.min.js"
    jq.integrity = "sha256-/SIrNqv8h6QGKDuNoLGA4iret+kyesCkHGzVUUV0shc="
    jq.setAttribute("crossorigin", "anonymous")
    doccopy.head.append(doccopy.createTextNode("\t"));
    doccopy.head.append(jq);
    doccopy.head.append(doccopy.createTextNode("\n\t"));
    //add javascript
    var files = ["/../lib/plugins/newcdcl/scripts/dcl-readonly.min.js", ]
    files.forEach(function(path) {
        promises.push(fetch(path).then(data => data.text()).then(
            function(data) {
                var src = doccopy.createElement("script");
                src.type = "text/javascript";
                src.innerHTML = data;
                doccopy.head.append(src);
                doccopy.head.append(doccopy.createTextNode("\n\t"));
            }
        ))
    });

    Promise.all(promises).then(function() {
        var js = doccopy.createElement("script");
        js.type = "text/javascript";
        js.innerHTML =
            "loadData(" + JSON.stringify(dcl_graphData) + "," + JSON.stringify(dcl_header["CONNECT"]) + "," + dcl_buttons + ");" //reduced script has a different load func
        wrap.appendChild(js);
        doccopy.getElementById("dokuwiki__header").remove();
        doccopy.getElementById("dokuwiki__aside").remove();
        doccopy.getElementById("dokuwiki__footer").remove();
        doccopy.body.innerHTML = wrap.outerHTML;
        var ver = doccopy.createElement("p");
        ver.align = "left"
        ver.style = "float: left;";
        ver.innerHTML = "CDCL V1.0";
        var t = doccopy.createElement("a");
        t.href = "https://www.vi4io.org";
        t.innerHTML = "Powered by VI4IO";
        t.align = "center"
        t.style = "float: center;"
        doccopy.body.appendChild(ver);
        doccopy.body.appendChild(t)


        var html = "<!DOCTYPE html>\n" + doccopy.documentElement.innerHTML;
        var start = html.substring(0, html.indexOf("<meta name=\"keywords\"") + 48)
        end = html.substring(html.indexOf("<meta name=\"viewport\""))
        html = start + end
        createDownload(html);
    });
    //return page to normal
    dcl_global_readonly = false;
    loadData(payload);
}

function createDownload(str) {
    var b64 = btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
        function toSolidBytes(match, p1) {
            return String.fromCharCode('0x' + p1);
        }));
    var data = "data:text/html;base64," + b64;
    var element = document.createElement('a');
    element.setAttribute('href', data);
    element.setAttribute('download', "site.html");
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}

/* https://www.w3schools.com/howto/howto_js_autocomplete.asp */
function autocomplete(inp, arr) {
    var id = inp;
    inp = document.getElementById(inp);
    /*the autocomplete function takes two arguments,
    the text field element and an array of possible autocompleted values:*/
    var currentFocus;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function(e) {
        var a, b, i, val = this.value;
        /*close any already open lists of autocompleted values*/
        closeAllLists();
        if (!val) {
            return false;
        }
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        /*append the DIV element as a child of the autocomplete container:*/
        this.parentNode.appendChild(a);
        /*for each item in the array...*/
        for (i = 0; i < arr.length; i++) {
            /*check if the item starts with the same letters as the text field value:*/
            if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                /*create a DIV element for each matching element:*/
                b = document.createElement("DIV");
                /*make the matching letters bold:*/
                b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                b.innerHTML += arr[i].substr(val.length);
                /*insert a input field that will hold the current array item's value:*/
                b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                /*execute a function when someone clicks on the item value (DIV element):*/
                b.addEventListener("click", function(e) {
                    /*insert the value for the autocomplete text field:*/
                    inp.value = this.getElementsByTagName("input")[0].value;
                    /*close the list of autocompleted values,
                    (or any other open lists of autocompleted values:*/
                    inp.focus();
                    closeAllLists();
                    /* loose focus */
                    var el = document.querySelector(':focus');
                    if (el) el.blur();
                });
                a.appendChild(b);
            }
        }
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
            /*If the arrow DOWN key is pressed,
            increase the currentFocus variable:*/
            currentFocus++;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 38) { //up
            /*If the arrow UP key is pressed,
            decrease the currentFocus variable:*/
            currentFocus--;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 13) {
            /*If the ENTER key is pressed, prevent the form from being submitted,*/
            e.preventDefault();
            if (currentFocus > -1) {
                /*and simulate a click on the "active" item:*/
                if (x) x[currentFocus].click();
            }
        }
    });

    function addActive(x) {
        /*a function to classify an item as "active":*/
        if (!x) return false;
        /*start by removing the "active" class on all items:*/
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        /*add class "autocomplete-active":*/
        x[currentFocus].classList.add("autocomplete-active");
    }

    function removeActive(x) {
        /*a function to remove the "active" class from all autocomplete items:*/
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }

    function closeAllLists(elmnt) {
        /*close all autocomplete lists in the document,
        except the one passed as an argument:*/
        var x = document.getElementsByClassName("autocomplete-items");
        for (var i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function(e) {
        closeAllLists(e.target);
    });
}