var mousePosition;
var offset = [0, 0];
var selected_obj = null;
var isDown = false;
var canvas_old = {};
var edges = {}
var last_selected_edge = {}
var last_clicked_time = 0
var selected_graph = null

function add_draw_listener(id, g, readonly) {
    var elem = jQuery("#" + id)
    var cv = jQuery("#" + g + "_draw").offset()
    // Eichung
    elem.css({
        position: "absolute",
        left: "0px",
        top: "0px"
    })
    var eo = elem.offset()

    elem.css({
        left: cv.left - eo.left + "px",
        top: cv.top - eo.top + "px"
    })

    elem = document.getElementById(id)

    if (readonly) {
        return
    }

    elem.addEventListener('mousedown', function(e) {
        isDown = true;
        selected_obj = elem;
        selected_graph = g
        offset = [
            elem.offsetLeft - e.clientX,
            elem.offsetTop - e.clientY
        ];
    }, true);

    jQuery("#" + id).click(function(e) { //on add input button click
        e.preventDefault();

        var d = new Date();
        var time = d.getTime();
        if (time - last_clicked_time > 3000) {
            last_selected_edge[g] = null
        }
        last_clicked_time = time

        if (id == last_selected_edge[g]) {
            return;
        } else if (last_selected_edge[g] != null) {
            var tuple
            tuple = [id, last_selected_edge[g]]

            if (tuple in edges[g] || [last_selected_edge[g], id] in edges[g]) {
                delete edges[g][tuple]
                delete edges[g][
                    [last_selected_edge[g], id]
                ]
            } else {
                edges[g][tuple] = true
            }
            redraw_one_graph(g)
            last_selected_edge[g] = null
        } else {
            last_selected_edge[g] = id
        }
    })
}

function find_edge_id(name) {
    var d = name.split(":")
    if (dcl_global_readonly) {
        return jQuery('span[class="value entity_name"]').filter(function(index) {
            var parent_id = "h" + jQuery(this).attr('id').split("_")[0]
            var parentType = document.getElementById(parent_id).textContent;
            parentType = parentType.replace("[-]", "")
            return jQuery(this).text() === d[1] && parentType == d[0]
        }).attr("id")
    } else {
        return jQuery('input[value="' + d[1] + '"]').filter(function(i) {
            var parent_id = "h" + jQuery(this).attr('id').split("_")[0]
            var parentType = document.getElementById(parent_id).textContent;
            parentType = parentType.replace("[-]", "")
            return parentType == d[0]
        }).attr("id");
    }
}

function load_graph() {
    if (dcl_graphData == null) {
        return
    }
    for (var g in dcl_connecting_graphs) {
        var data = dcl_graphData[g]

        var c = jQuery("#" + g + "_draw")
        var rect = c.offset()

        if (data == null) {
            continue
        }
        var positions = data["pos"]
        var sedges = data["edges"]
        canvas_old[g] = rect
        if (!dcl_global_readonly) {
            for (var name in positions) {
                var id = find_edge_id(name) // todo find a better way of doing this
                var elem = jQuery("#c" + g + id)
                // calibrate coordinate dcl_system
                elem.css({
                    position: "absolute",
                    left: "0px",
                    top: "0px"
                })
                var eo = elem.offset()

                if (elem.length) {
                    var poss = positions[name]
                    elem.css({
                        left: poss[0] + rect.left - eo.left + "px",
                        top: poss[1] + rect.top - eo.top + "px"
                    })
                }
            }
        }
        for (e in sedges) {
            var id1 = find_edge_id(sedges[e][0])
            var id2 = find_edge_id(sedges[e][1])
            edges[g][
                ["c" + g + id1, "c" + g + id2]
            ] = ""
        }
    }
}

function resetGraphs() {
    selected_graph = null
    selected_obj = null
    jQuery("#dcl_graph_fields").html("")
    for (var g in dcl_connecting_graphs) {
        var o = dcl_connecting_graphs[g]
        jQuery("#dcl_graph_fields").append('<div id="g' + g + '"><h2>' + g + '</h2><div id="' + g + '_border"><canvas id="' + g + '_draw" width="' + o["width"] + '" height="' + o["height"] + '" style="width:' + o["width"] + 'px;height:' + o["height"] + 'px"> </canvas></div><form id="' + g + '_connections" class="connections"></form></div>')
        edges[g] = {}
        last_selected_edge[g] = null
    }
}

// the link is used, when read-only
function link_reference(txt, parentType) {
    return parentType + "-" + txt
}

function add_element(id, elem, txt) {
    elem.data('oldVal', txt);
    // find parent
    var parent_id = "h" + id.split("_")[0]
    var parentType = document.getElementById(parent_id).textContent;
    parentType = parentType.replace("[-]", "")
    elem.data('parentType', parentType);
    var grapa = document.getElementById(id.split("_")[0]).parentNode.id.split("_")[0]
    var grapaTyp = ""
    if (grapa != "data") {
        grapaTyp = document.getElementById("h" + grapa).textContent;
    }

    // add it to all graphs
    for (var g in dcl_connecting_graphs) {
        var wrap = jQuery('#' + g + '_connections')
        //console.log(parentType in dcl_connecting_graphs[g]["source"])
        //console.log(dcl_connecting_graphs[g])
        if (!(dcl_connecting_graphs[g]["source"].indexOf(parentType) != -1 || dcl_connecting_graphs[g]["target"].indexOf(parentType) != -1)) {
            continue
        }

        var name = "c" + g + id
        //console.log(name + " " + wrap)
        // create dummy nodes
        var readonly = (dcl_global_readonly || ("readonly" in dcl_connecting_graphs[g] && dcl_connecting_graphs[g]["readonly"]))
        var link = "#"
        if (readonly) {
            link = link_reference(txt, parentType)
        }
        jQuery(wrap).append('<span id="' + name + '" class="' + parentType + " grapa_" + grapaTyp + '">' + parentType + ":" + txt + '</span>')
        //add_draw_listener(name, g, readonly)
        add_draw_listener(name, g, readonly)
    }

    if (!dcl_global_readonly) {
        // Look for changes in the value
        elem.bind("propertychange change click keyup input paste", function(event) {
            // If value has changed...
            if (elem.data('oldVal') != elem.val()) {
                // Updated stored value
                //console.log(name)
                elem.data('oldVal', elem.val());
                jQuery("#h" + id).text(elem.val())

                for (var g in dcl_connecting_graphs) {
                    var name = "c" + g + elem.attr('id')
                    jQuery("#" + name).text(elem.data('parentType') + ":" + elem.val())
                }
            }
        });
    }
}

function update_names() {
    if (dcl_connecting_graphs == null || dcl_connecting_graphs.length == 0 || !jQuery("#dcl_graph_fields").length || !dcl_draw_graph) {
        return;
    }
    // move items
    var used = {}
    for (var g in dcl_connecting_graphs) {
        var wrap = jQuery('#' + g + '_connections')
        var rect = jQuery("#" + g + "_draw").offset()
        if (!(g in canvas_old)) {
            canvas_old[g] = rect
        }
        var delta = [rect.left - canvas_old[g].left, rect.top - canvas_old[g].top]
        canvas_old[g] = rect

        jQuery('.entity_name').each(function() {
            var elem = jQuery(this);
            var name = "c" + g + elem.attr('id')

            used[name] = true
            // Save current value of element
            if ("oldVal" in elem.data()) {
                var t = document.getElementById(name);
                if (t) {
                    t.style.left = Math.round(parseInt(t.style.left) + delta[0]) + "px";
                    t.style.top = Math.round(parseInt(t.style.top) + delta[1]) + "px";
                }
            }
        });
    }

    // remove unused items
    for (var g in dcl_connecting_graphs) {
        var parent = document.getElementById(g + "_connections")
        if (parent == null) {
            continue
        }
        var childs = parent.childNodes
        //console.log(used)
        for (var i = 0; i < childs.length; i++) {
            var id = childs[i].getAttribute("id")
            if (!(id in used)) {
                //console.log("removing: " + id)
                parent.removeChild(childs[i])
            }
        }
    }

    // add new elements
    jQuery('.entity_name').each(function() {
        var elem = jQuery(this);
        var txt
        if (dcl_global_readonly) {
            txt = elem.text()
        } else {
            txt = elem.val()
        }
        var id = elem.attr('id')
        jQuery("#h" + id).text(txt)

        if (dcl_global_readonly) {
            txt = elem.text()
        }
        // Save current value of element
        if (!("oldVal" in elem.data())) {
            add_element(id, elem, txt)
        }
    });
}

function check_unnecessary_graphs() {
    if (dcl_global_readonly) {
        for (var g in dcl_connecting_graphs) {
            if (Object.keys(edges[g]).length < 1) {
                jQuery("#g" + g).hide()
            } else {
                jQuery("#g" + g).show()
            }
        }
    }
}


function store_graph() {
    var map = {}
    for (var g in dcl_connecting_graphs) {
        var c = jQuery("#" + g + "_draw")
        var rect = c.offset()
        var positions = {}

        jQuery('.entity_name').each(function() {
            var elem = jQuery(this)
            var name = "#c" + g + elem.attr('id')
            var t = jQuery(name)
            if (t.length) {
                var pos = t.offset()
                var name = elem.data('parentType') + ":" + elem.val()
                positions[name] = [pos.left - rect.left, pos.top - rect.top]
            }
        });

        var g_edges = []
        for (e in edges[g]) {
            e = e.split(",")
            if (document.getElementById(e[0]) == null) {
                continue
            }
            // purge the type of the node
            try {
                var e1 = document.getElementById(e[0]).textContent;
                var e2 = document.getElementById(e[1]).textContent;
                g_edges.push([e1, e2])
            } catch (error) {
                console.log("Sandboxed the graph error for:");
                console.log(e[1]);
                console.error(error);
            }
        }
        map[g] = {
            "pos": positions,
            "edges": g_edges
        }
    }
    return map
}

function redraw_one_graph(g) {
    return
    //console.log(edges)
    var c = jQuery("#" + g + "_draw")
    var ctx = c[0].getContext('2d')
    var rect = c.offset()
    var scale = 1

    if (dcl_global_readonly && dcl_graphData) {
        var max = [0, 0]

        var data = dcl_graphData[g]
        var positions = data["pos"]
        for (var name in positions) {
            var pos = positions[name].slice()
            var id = find_edge_id(name) // todo find a better way of doing this
            var elem = jQuery("#c" + g + id)
            pos[0] = pos[0] + elem.outerWidth()
            pos[1] = pos[1] + elem.outerHeight()
            if (pos[0] > max[0]) {
                max[0] = pos[0]
            }
            if (pos[1] > max[1]) {
                max[1] = pos[1]
            }
        }
        max = [max[0], max[1]]

        jQuery("#" + g + "_draw").css({
            width: max[0],
            height: max[1]
        })
        ctx = jQuery("#" + g + "_draw")[0].getContext('2d');


        var box = jQuery("#" + g + '_border')
        if (box.width() < max[0]) {
            scale = box.width() / max[0]
        }
        if (box.height() < max[1]) {
            var tmp = box.height() / max[1]
            if (tmp < scale) {
                scale = tmp
            }
        }
        max[1] = max[1] * scale
        max[0] = max[0] * scale
        ctx.canvas.height = max[1]
        ctx.canvas.width = max[0]
        c.css({
            width: max[0],
            height: max[1]
        })

        // move all objects to the proper position
        for (var name in positions) {
            var id = find_edge_id(name) // todo find a better way of doing this
            var elem = jQuery("#c" + g + id)
            // calibrate coordinate dcl_system
            elem.css({
                position: "absolute",
                left: "0px",
                top: "0px"
            })
            var eo = elem.offset()

            if (elem.length) {
                var poss = positions[name]
                elem.css({
                    left: poss[0] * scale + (rect.left - eo.left) + "px",
                    top: poss[1] * scale + (rect.top - eo.top) + "px"
                })
            }
        }
    }

    ctx.clearRect(0, 0, c.width(), c.height())
    ctx.scale(1, 1)

    for (e in edges[g]) {
        e = e.split(",");
        if (jQuery("#" + e[0]).length > 0) {
            var e1o = jQuery("#" + e[0]);
            var e2o = jQuery("#" + e[1]);
            if (!e1o.length || !e2o.length) {
                delete edges[g][e]
                continue
            }
            var e1 = e1o.offset()
            var e2 = e2o.offset()
            ctx.beginPath();
            ctx.moveTo((e1.left - rect.left + e1o.width() / 2), (e1.top - rect.top + e1o.height() / 2));
            ctx.lineTo((e2.left - rect.left + e2o.width() / 2), (e2.top - rect.top + e2o.height() / 2));
            ctx.stroke();
        }
    }
}

function redraw_graph() {
    for (var g in dcl_connecting_graphs) {
        redraw_one_graph(g)
    }
}

jQuery(document).ready(function() {
    if (!dcl_draw_graph) {
        return
    }
    jQuery(window).resize(function() {
        redraw_graph()
    });

    //if(dcl_global_readonly){
    //  setInterval(function() {
    //    redraw_graph()
    //  }, 1000);
    //}

    document.addEventListener('mouseup', function() {
        isDown = false;
        selected_obj = null;
    }, true);

    document.addEventListener('mousemove', function(event) {
        event.preventDefault();
        if (isDown && selected_obj != null) {
            var cv = document.getElementById(selected_graph + "_draw").getBoundingClientRect()

            mousePosition = {
                x: event.clientX,
                y: event.clientY
            };
            // check if mouse position is outside the canvas
            if (mousePosition.x < cv.left || mousePosition.x > cv.width + cv.left) {
                if (mousePosition.x < cv.left) {
                    selected_obj.style.left = cv.left
                } else {
                    selected_obj.style.left = cv.width + cv.left
                }
                return
            }
            if (mousePosition.y < cv.top || mousePosition.y > cv.height + cv.top) {
                if (mousePosition.y < cv.top) {
                    selected_obj.style.top = cv.top
                } else {
                    selected_obj.style.top = cv.height + cv.top
                }
                return
            }

            selected_obj.style.left = (mousePosition.x + offset[0]) + 'px';
            selected_obj.style.top = (mousePosition.y + offset[1]) + 'px';
            redraw_one_graph(selected_graph)
        }
    }, true);
});