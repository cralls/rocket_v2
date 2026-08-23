jQueryCb(function(){
    var $ = jQueryCb;

    //temp hack until the great refactoring
    var preloadRequireModulesNeededLater = function()
    {
        if(pulsestorm_commerbug_json['metadata']['version'].indexOf('2.0.') !== -1)
        {
            requirejs(['Magento_Ui/js/lib/ko/template/loader','Magento_Ui/js/lib/ko/template/renderer'], function(){
            });                    
            return;
        }
        
        requirejs(['Magento_Ui/js/lib/knockout/template/loader','Magento_Ui/js/lib/knockout/template/renderer'], function(){
        });                            
    };
    preloadRequireModulesNeededLater();
    
    var getRequireJsUrl = function(module, extension)
    {
        extension = extension ? extension : '';
        if(module.indexOf('!') !== -1)
        {
            module = module.split('!').pop();
        }
        return requirejs.toUrl(module) + extension;
    }

    var getKnockoutTemplateLoader = function()
    {
        var loader;
        try
        {
            loader = requirejs('Magento_Ui/js/lib/knockout/template/loader');                
        }
        catch(e)
        {
            //Magento 2.0.x
            loader = requirejs('Magento_Ui/js/lib/ko/template/loader');
            loader.formatPath = function (path) {
                var newPath = 'text!';
                var parts = path.split('/');
                newPath += parts.shift() + '/template/' + parts.join('/') + '.html';
                return newPath
            }                            
        }
        return loader;
    }
            
    var changeToTab = function(tab_id){
        var parts  = tab_id.split('-');
        if(parts[0] != 'tab') { return; }
        parts.shift(); 
        var tab_content_id = parts.join('-');
        $('#commercebug-tab-content').html(                
            $('#'+tab_content_id).html()
        );            
        $('#commercebug-tab-content .pulsestorm-data-table').DataTable({
            "aaSorting": []
        });
        setupEventsForTab(tab_id);
    };
    
    var saveLastTabClicked = function(tab_id){
        localStorage.pulsestorm_commercebug_lasttab = tab_id;
    };
    
    var getSelectedTabId    = function(){
        var id = jQueryCb('#commercebug-tabs .active').parent().attr('id').split('_').pop();
        return id;
    };
    
    var getAllTabIds = function(all_tabs){
        var all_ids = jQueryCb.map(all_tabs, function(item){
           return item.id;
        });
        return all_ids;    
    };
    
    var tabForward  = function(all_tabs){
        var all_ids      = getAllTabIds(all_tabs);
        var selected_tab = getSelectedTabId();
        var currentIndex = all_ids.indexOf(selected_tab);   
        if(currentIndex + 1 === all_ids.length)
        {
            currentIndex = -1;
        }
        var nextTab = all_ids[(currentIndex + 1)];
        switchToTab(nextTab);
    };

    var tabBackwards  = function(all_tabs){
        var all_ids      = getAllTabIds(all_tabs);
        var selected_tab = getSelectedTabId();
        var currentIndex = all_ids.indexOf(selected_tab);        
        if(currentIndex === 0)
        {
            currentIndex = all_ids.length;
        }
        var nextTab = all_ids[(currentIndex - 1)];
        switchToTab(nextTab);
    };
        
    var switchToTab = function(tab_id)
    {
        $('#tabs_tabs_tab_'+tab_id+' div').trigger('click');
        setupEventsForTab(tab_id);
    };
        
    var insertUiComponentTreeIntoContainer = function(componentName, container)
    {
        var logLocal = function(thing)
        {
            // console.log(thing);
        }
        logLocal(container);
        logLocal(componentName);

        reg = requirejs('uiRegistry');
        topUi = reg.get(componentName);
        var addNode = function(id, parent, text, nodes)
        {
            var treeNode = {"id":id,"parent":parent,"text":text};
            nodes.push(treeNode); 
            return nodes;
        }

        var getUniqueId = function()
        {
            //http://stackoverflow.com/a/2117523/4668
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
                return v.toString(16);
            });
        }
        
        var getKnockoutJsHref = function(module)
        {
            var loader = getKnockoutTemplateLoader();
            //sometimes module is a function?
            var path = loader.formatPath(module);
            var url  = getRequireJsUrl(path, '');
            return getViewHref(url);
        }
        
        var getRequireJsHref = function(module)
        {
            return getViewHref(
                getRequireJsUrl(module, '.js')
            );
        }
        
        var getViewHref = function(string)
        {        
            return '<a href="' + string  + '" target="_blank" class="ps-ui-component-resource-view">[view]</a>';               
        }
        
        var report = function(element, indent, nodes, inferredParent)
        {
            //column_controls (and others?) have elems that are not is true children?    
            parentName = inferredParent ? inferredParent : element.parentName;

            var indent = indent ? indent + '    ' : '    ';
    
            
            var elementNamespace = element['ns'];
            if(!elementNamespace)
            {
                elementNamespace = element.name.split('.').shift();                    
            }
            //customer vs. product_list.product_list
            if(!nodes && (elementNamespace !== element.name))  //product_list.product_list
            {
                nodes = [{
                    "id":elementNamespace,
                    "parent":'#',
                    "text":elementNamespace
                }];
        
            }
            else if(!nodes) //customer
            {
                nodes = [];
                parentName = '#';
            }
    

            var uniqueGeneratedIdForTree = element.name + getUniqueId();
            addNode(uniqueGeneratedIdForTree, parentName, 
                element.index, nodes);
                
            logLocal(indent + element.index + '::' + element.component + '('+element.name+')');
    

            if(element.component){
                addNode(getUniqueId() + element.name + '_template', uniqueGeneratedIdForTree,
                    '<strong>RequireJS Component:</strong> ' + 
                        element.component + ' ' + 
                        getRequireJsHref(element.component), nodes);
                logLocal(indent + '    - RequireJS: ' + element.component);
    
            }
    
            if(element.template){
                addNode(getUniqueId() + element.name + '_template', uniqueGeneratedIdForTree,
                    '<strong>Knockout.js Template:</strong> ' + 
                        element.template + ' ' + 
                        getKnockoutJsHref(element.template), nodes);
                logLocal(indent + '    - template: ' + element.template);
            }

            if(element.regions){
                var regions     = Object.keys(element.regions);
                var region_id   = getUniqueId() + element.name + '_regions';
                addNode(region_id, uniqueGeneratedIdForTree,
                    '<strong>Shadow Region Hierarchy:</strong> ' + regions.length + ' region(s)', nodes);               

                jQueryCb.each(regions, function(k, region){
                    var sub_region_id = getUniqueId() + element.name + '_regions_region_' + region;
                    addNode(sub_region_id, region_id, '<strong>Region Name: </strong>' + region, nodes);
                    
                    var elements = element.regions[region]();
                    jQueryCb.each(elements, function(k, element){
                        report(element, indent, nodes, sub_region_id)
                    });                    
                });                    
            }
    
            //add templates
            var possibleTemplates = jQueryCb.map(element, function(value, key){
                return key;
            });

            possibleTemplates = jQueryCb.grep(possibleTemplates, function(value, key){
                return value.match(/(tmpl|tpl)$/i);
            });            
            // var possibleTemplates = ['bodyTmpl','headerTmpl'];
            jQueryCb.each(possibleTemplates, function(key, value){
                if(element[value] && (typeof element[value] === 'string')){ 
                    addNode(getUniqueId() + element.name + '_bodytemplate', uniqueGeneratedIdForTree,
                        '<strong>Knockout.js ' + value+ ':</strong> '   + 
                            element[value] + ' ' +
                            getKnockoutJsHref(element[value]), nodes);
                    logLocal(indent + '    - ' + value + ': ' + element[value]);
                }
            });    
    
            if(!element.elems)
            {
                return;
            }
            var elems = element.elems();
            for(var i=0;i<elems.length;i++)
            {
                // element = elems[i];
                report(elems[i], indent,nodes, uniqueGeneratedIdForTree);
            }
            return nodes;
        }
    
        nodes = report(topUi);
        
        if(!jQueryCb(container).hasClass('jstree'))
        {
            jQueryCb(container)
                .on('click', '.jstree-anchor', function (e) {
                    jQueryCb(container).jstree(true).toggle_node(e.target);
                })
                .jstree(
                    { 'core' : {
                        'data' : nodes,
                        'dblclick_toggle' : false,       
                        "themes":{
                            "icons":false
                            }            
                        } 
                    }
                );
            jQueryCb(container).bind('ready.jstree', function(e){
                jQuery('.jstree-container-ul').delegate('a', 'click', function(e){
                    if(!jQuery(this).hasClass('ps-ui-component-resource-view')){return;}
                    window.open(
                        jQuery(this).attr('href')
                    );
                });        
            });    
        };
    }
            
    var setupEventsForTab = function(tab_id)
    {
        //console.log(tab_id);
        if(tab_id === 'tab-commercebug-ui')
        {

            jQueryCb('#commercebug-ui-table button').click(function(event){
                var scope = jQueryCb(event.target).closest('td').prev().html();
                var viewModelKey = scope . 
                    replace(/^.*?scope:.*?'([^']*?)'.*$/,'$1');
                var tr = (event.target).closest('tr');
                if(!jQueryCb(tr).next().hasClass('cb-ui-view-tree-container'))
                {
                    jQueryCb(tr).after('<tr class="cb-ui-view-tree-container"><td colspan="2"><div class="cb-ui-view-tree-div">'+viewModelKey+'</div></td></tr>');                    
                }    
                var container = jQueryCb(tr).next().find('div.cb-ui-view-tree-div').first();
                            
                insertUiComponentTreeIntoContainer(viewModelKey, container);
            });        
        }
        else if(tab_id === 'tab-commercebug-class-lookup')
        {
            jQueryCb('#ps-commercebug-class-lookup').submit(function(e){
                e.preventDefault();
                var identifier = jQueryCb(document.forms['ps-commercebug-class-lookup']['lookup']).val();                
                var url = identifier;
                if(url.indexOf('http') !== 0)
                {
                    var loader = getKnockoutTemplateLoader();
                    var path = loader.formatPath(identifier);
                    url  = getRequireJsUrl(path);
                }
                jQueryCb.get(url, function(result){
                    var getKnockoutTemplateRenderer = function()
                    {
                        var renderer;
                        try
                        {
                            renderer = requirejs('Magento_Ui/js/lib/knockout/template/renderer');
                        }
                        catch(e)
                        {
                            // renderer = false;
                            renderer = requirejs('Magento_Ui/js/lib/ko/template/renderer');            
                        }
            
                        return renderer;                        
                    }
                
                    var renderer = getKnockoutTemplateRenderer();
                    if(!renderer)
                    {
                        jQueryCb('#ps-commercebug-class-lookup-results').
                            css('display', 'block');
                        jQueryCb('#ps-commercebug-class-lookup-results').val(
                            'Knockout.js Lookup Requires Magento 2.1');                                            
                        return;
                    }
                    
                    var fragment = document.createDocumentFragment();
                    jQueryCb(fragment).append(result);
    
                    //fragment is passed by reference, modified
                    if(renderer['normalize'])
                    {
                        renderer.normalize(fragment);
                    }
                    var string = new XMLSerializer().serializeToString(fragment);
                    
                    jQueryCb('#ps-commercebug-class-lookup-results').
                        css('display', 'block');
                    jQueryCb('#ps-commercebug-class-lookup-results').val(
                        string);                        
                    // console.log(string);    
                });                    
            });        
        
        }
        else if(tab_id === 'tab-commercebug-layout')
        {
            //setup event handlers
            jQueryCb('.pulsestorm_textbox_but').click(function(event){
                event.preventDefault();
                var id = jQueryCb(this).attr('id');
                jQueryCb('#pulsestorm_textbox_container').css('display','block');
                var map = {
                    'pulsestorm_button_view_handle_layout':function(){
                        var label = 'Commerce Bug found ' + 
                            pulsestorm_commerbug_json['page_layout_xml'].length +
                            ' package/handle layout trees. A package/handle' + 
                            " tree contains **all** loaded and merged XML files. \n\n";
                        return label + pulsestorm_commerbug_json['page_layout_xml'];
                    },
                    'pulsestorm_button_view_request_layout':function(){
                        var label = 'Commerce Bug found ' + pulsestorm_commerbug_json['request_layout_xml'].length + 
                        ' page/request layout ' + 
                        'trees. Magento creates a page/request tree by looking' + 
                        'at the handles, and selecting nodes from the ' + 
                        'package/handle tree that match.' + "\n\n";
                        return label + pulsestorm_commerbug_json['request_layout_xml'];
                    },
                    'pulsestorm_button_view_structure_schedule':function(){
                        var label = '';
                        return label + pulsestorm_commerbug_json['scheduled_structure'].join("\n");
                    },
                    'pulsestorm_button_view_xmlfile':function(){
                        var label = 'Commerce Bug found ' + 
                        pulsestorm_commerbug_json['page_layout_xmlfile'].length + 
                        ' sets of layout' + 
                        ' handle XML files.  There are the files Magento loads to ' + 
                        " create the package/handle XML trees.";
                        var files = pulsestorm_commerbug_json['page_layout_xmlfile'];
                        files = jQuery.map(files, function(val){
                            return val.join("\n");
                        });
                        var sep = "\n+--------------------------------------------------+\n";
                        return label + sep + files.join(sep);
                    }
                }; 
                jQueryCb('#pulsestorm_textbox_container_textbox').val(map[id]());                
            });                    
        }    
    };

    var setupSkeleton = function(all_tabs)
    {
        //setup skeleton that will hold actual commercebug
        var string = '<div id="commercebug-container"><div id="commercebug-tabs"></div><div id="commercebug-tab-content" style="min-height: 200px; border: 1px solid #ddd; border-top: 0px;"></div></div>';
        jQueryCb('body').append(string);
        
        //setup HTML/DOM nodes that hold our actual content
        //(there were created via PHP rendering previously)
        jQueryCb('body').append('<div id="div_commercebug" style="display:none"></div>');
        
        jQueryCb('#div_commercebug').append('<h1>Temporary Commerce Bug HTML Source</h1>');
        var all_ids = jQueryCb.map(all_tabs, function(item){
           var id = item.id.split('-').slice(1).join('-');
           jQueryCb('#div_commercebug').append('<div id="'+id+'"><div></div></div>');
        });

    }    
                
    var setupKeyboardShortcuts = function(all_tabs){
        jQueryCb(document).bind('keyup',function(e){	
            var code = (e.keyCode ? e.keyCode : e.which);
            
            //bail if we're in certain tags.  Not ideal as it kills
            //tab navigation, but that's why we let them turn it off
            if( jQueryCb(e.target).is('input') || 
            jQueryCb(e.target).is('textarea') 	||
            jQueryCb(e.target).is('select') 	||
            jQueryCb(e.target).is('option')	)
            {
                return true;
            }
            
            if(code == 76)
            {
                tabForward(all_tabs);
            }
            else if (code == 72)
            {
                tabBackwards(all_tabs);
            }  
        });  
    };
    var setupJqueryHook = function(){
        $('#commercebug-tabs').w2tabs({
            name: 'tabs',
            active: 'tab1',
            tabs: all_tabs,
            onClick: function (event) {
                var tab_id = event.target;
                changeToTab(tab_id);
                saveLastTabClicked(tab_id);            
            }
        });    
    }
    
    var templateTableId = function(div_id)
    {
        return (div_id + '-table').replace('#','');
    }
    
    var templateDataTable = function(id,headers)
    {
        var table_id = templateTableId(id);
        var string = '<table border="1" id="'+table_id+'"  class="pulsestorm-data-table">';
        string += '<thead><tr>';
        jQueryCb.each(headers, function(key, header)
        {
            string += '<th>' + header + '</th>';
        });
        // string += '<tr><td>Class</td></tr>';
        string += '</tr></thead><tbody></tbody></table>';
        
        return string;
    };
    
    var templateDataTableRowWithIndex = function(index, contents)
    {
        var string = '<tr><td valign="top">' + index + '</td><td valign="top">' +
            contents + '</td></tr>';
        return string;            
    
    }

    var templateDataTableRowWithArray = function(cells)
    {
        var string = '<tr>';
        jQueryCb.each(cells, function(k,v){
            string += '<td valign="top">' + v + '</td>';
        });
        string += '</tr>';
        return string;            
    }

    var templateDataTableRow = function(contents)
    {
        var string = '<tr><td valign="top">' +
            contents + '</td></tr>';
        return string;            
    }

    
    var templateDataTableRow = function(contents)
    {
        var string = '<tr><td valign="top">' +
            contents + '</td></tr>';
        return string;            
    }
    
    var templatePhpClassAndFile = function(className, file)
    {
        var string  = '<pre class="pulsestorm_commercebug_phpclass">' + 
            className + '</pre>' + "\n";
        string      += '<pre class="pulsestorm_commercebug_file">' + 
            file + '</pre>';
        return string;
    }
    
    var setupRequestTab = function(id)        
    {
        var dataTable = templateDataTable(id,['Type','File/Class']);          
        var rows      = '';
        jQueryCb.each(pulsestorm_commerbug_json['controllers'], function(className, item){
            if(!item['class'])
            {
                return;
            }
            rows += '<tr>';
            rows += '<td valign="top">Interceptor</td>';
            rows += '<td>';
            rows += templatePhpClassAndFile(
                item['interceptor']['className'],item['interceptor']['file']);
            rows += '</td>';
            rows += '</tr>';            
            
            rows += '<tr>';
            rows += '<td valign="top">Controller</td>';
            rows += '<td>';
            rows += templatePhpClassAndFile(
                item['class']['className'],item['class']['file']);
            rows += '</td>';
            rows += '</tr>';                        
        });   
        if(!rows)
        {
            rows = '<tr><td>No controllers found. There are a few reasons this might happen. <ul style="margin-left:3em;margin-top:1em;"><li>This is a full page cache hit.  See System -&gt; Cache Managment in the backend to disable full page cache.</li><li>Your controller\'s _isAllowed method blocked controller dispatch due to insufficient ACL permissions.  </li></td><td></td></tr>'
        }           
        // var rows      = '<tr><td valign="top">This is a test</td></tr>';
        dataTable     = replaceDataTableStringBodyWithRows(dataTable, rows);
        dataTable     = dataTable.replace('<tbody></tbody>',
            '<tbody>'+rows+'</tbody>');
    
        var selector = id + ' div';
        jQueryCb(selector).append(dataTable); 
    };
    
    var setupSingleRowDataTableTabWithData = function(id, data, labelHead)
    {
        var selector  = id + ' div';
        var dataTable = templateDataTable(id,[labelHead]);  
        
        var rows      = '';
        jQueryCb.each(data, function(className, info){
            rows += templateDataTableRow(templatePhpClassAndFile(
                className, info.file
            ));
        });              
        // var rows      = '<tr><td valign="top">This is a test</td></tr>';
        dataTable     = dataTable.replace('<tbody></tbody>',
            '<tbody>'+rows+'</tbody>');
        jQueryCb(selector).append(dataTable);     
    }
    
    var setupCrudModelTab = function(id)
    {
        setupSingleRowDataTableTabWithData(
            id, pulsestorm_commerbug_json['models'], 'CRUD/AbstractModel'); 
    };

    var setupCollectionsTab = function(id)
    {
        setupSingleRowDataTableTabWithData(
            id, pulsestorm_commerbug_json['collections'], 'Collections');    
    };

    var replaceDataTableStringBodyWithRows = function(dataTable, rows)
    {
        dataTable     = dataTable.replace('<tbody></tbody>',
            '<tbody>'+rows+'</tbody>');        
        return dataTable;    
    };
    
    var templateBlockRowFromItem = function(item)
    {
        var string = '<tr><td><table width="100%"><thead></thead><tbody><tr><td style="width:400px"><strong>Name: </strong><$name$></td><td><pre class="pulsestorm_commercebug_phpclass"><$className$></pre></td></tr><tr><td colspan="2"><pre class="pulsestorm_commercebug_phpfile"><$template$></pre><pre class="pulsestorm_commercebug_phpfile"><$classFile$></pre></td></tr></tbody></table></td></tr>';
        if(!item.template)
        {
            item.template = '[no template]';
        }
        return string.replace('<$name$>',item.name) .
            replace('<$className$>',item.className) .
            replace('<$classFile$>',item.classFile) .
            replace('<$template$>',item.template);            
        return '<tr><td>wwtf mate</td></tr>';
    };
    
    var setupBlocksTab = function(id)
    {
        var dataTable = templateDataTable(id,['Block names, classes, and files']);    
        var rows      = '';
        jQueryCb.each(pulsestorm_commerbug_json['blocks'], function(key, value){
            rows += templateBlockRowFromItem(value);       
        });
        
        dataTable     = replaceDataTableStringBodyWithRows(dataTable, rows);
        var selector = id + ' div';
        jQueryCb(selector).append(dataTable);    
    };

    var templateGraphForm = function(data)
    {
        var warnings='';
        if(data.full_page_cache)
        {
            warnings = "<p>It looks like full page caching is on -- this tab won't have the information you're looking for.</p>";
        }
        var string = '<form target="_blank" method="post" action="https://graph.pulsestorm.net/dot">' +
        warnings + 
        '<div>' + 
            '<input name="token_as_commercebug" type="hidden" value="<$token$>">' + 
            '<button type="submit">Render Graph</button>' + 
            '<button onclick="jQueryCb(\'#pulsestorm_commercebug_graph_source_container\').toggle();return false;">Show Graph Source</button>' +         
            '<button class="pulsestorm_textbox_but" id="pulsestorm_button_view_handle_layout">View Package/Handle Layout</button>'  + 
            '<button class="pulsestorm_textbox_but" id="pulsestorm_button_view_xmlfile">View Loaded XML Files</button>'             +                                     
            '<button class="pulsestorm_textbox_but" id="pulsestorm_button_view_request_layout">View Page/Request Layout</button>'   + 
            '<button class="pulsestorm_textbox_but" id="pulsestorm_button_view_structure_schedule">View Structure Schedule</button>'+             
        '</div>' + 
        '<div style="display:none;" id="pulsestorm_commercebug_graph_source_container">' +
            '<textarea rows="10" cols="72" name="as_commercebug_dot_console" id="as_commercebug_dot_console"><$graph$></textarea>' +        
        '</div>' +
        '<div style="display:none;" id="pulsestorm_textbox_container">' +
            '<textarea rows="10" cols="72" name="pulsestorm_textbox_container_textbox" id="pulsestorm_textbox_container_textbox"></textarea>' +        
        '</div>';
        
        return string.replace('<$token$>',data.nonce) .
            replace('<$graph$>',data.graph);
    }
    
    var setupLayoutTab = function(id)
    {
        var form = templateGraphForm(pulsestorm_commerbug_json['layouts']);
        
        var dataTable = templateDataTable(id,['Index','Handles']);          
        var rows      = '';
        jQueryCb.each(pulsestorm_commerbug_json['handles'], function(index, handle){
            rows += templateDataTableRowWithIndex(index, handle);
        });              
        // var rows      = '<tr><td valign="top">This is a test</td></tr>';
        dataTable     = replaceDataTableStringBodyWithRows(dataTable, rows);
        dataTable     = dataTable.replace('<tbody></tbody>',
            '<tbody>'+rows+'</tbody>');
                
        var selector = id + ' div';
        jQueryCb(selector).append(form + dataTable);          
    };

    var wrap = function(subject, tag)
    {
        return ['<',tag,'>',subject,'</',tag,'>'].join('');
    };
    
    var wrapPre = function (subject)
    {
        return wrap(subject, 'pre');
    };
    
    var setupDataTableTabWithRowsCallback = function(id, data, labelHead, callback)
    {
        labelHead = Array.isArray(labelHead) ? labelHead : [labelHead];    
        var dataTable = templateDataTable(id,labelHead);   
        var aRows = jQueryCb(data).map(callback);  
        var rows = jQueryCb.makeArray(aRows).join('');

        dataTable     = replaceDataTableStringBodyWithRows(dataTable, rows);
        dataTable     = dataTable.replace('<tbody></tbody>',
            '<tbody>'+rows+'</tbody>');
    
        var selector = id + ' div';
        jQueryCb(selector).append(dataTable);         
    }
    
    var setupSingleRowDataTableTabWithDataWrapPre = function(id, data, labelHead)
    {
        var callback = function(className, file){
            return templateDataTableRow(wrapPre(file));
        }    
        setupDataTableTabWithRowsCallback(id, data, labelHead, callback);   
    };
    
    var setupUiComponentTab = function(id)
    {    
        var scopes = jQueryCb('*[data-bind*="scope:"]').each(function(k,v){
            return jQueryCb(v).attr('data-bind');
        }).map(function(k,v){
            return jQueryCb(v).attr('data-bind');
        });

        var callback = function(className, file){
            return templateDataTableRowWithArray([
                wrapPre(file), '<button class="cb-ui-view-tree" onclick="return false;">View Tree</button>'
            ]);
        }    
        setupDataTableTabWithRowsCallback(id, scopes, 
            ['Knockout Scopes in Use',''], callback);   

        //         var tabHtml = '<strong>Hello</strong>';
        //         var selector = id + ' div';        
        //         jQueryCb(selector).append(tabHtml);
    }
    
    var setupOtherFilesTab = function(id)
    {
        return setupSingleRowDataTableTabWithDataWrapPre(
            id, pulsestorm_commerbug_json['other-files'], 'Other Files');   
    };

    var setupEventsTab = function(id)
    {
        return setupSingleRowDataTableTabWithDataWrapPre(
            id, pulsestorm_commerbug_json['dispatched_events'], 'Events');   
    };

    var setupObserversTab = function(id)
    {
        var dataTable = templateDataTable(id,['Name','Class']);          
        var rows      = '';
        jQueryCb.each(pulsestorm_commerbug_json['invoked_observers'], function(className, item){
            tmp     =  '<tr><td><pre><$name$></pre></td>';
            tmp     += '<td><pre class="pulsestorm_commercebug_phpclass"><$className$></pre></td>';
            tmp     += '</tr>';            
            rows    += tmp.replace('<$name$>',item.name).
                replace('<$className$>',item.instance);            
        });              
        // var rows      = '<tr><td valign="top">This is a test</td></tr>';
        dataTable     = replaceDataTableStringBodyWithRows(dataTable, rows);
        dataTable     = dataTable.replace('<tbody></tbody>',
            '<tbody>'+rows+'</tbody>');
    
        var selector = id + ' div';
        jQueryCb(selector).append(dataTable); 
    
    };

    var setupClassLookupTab = function(id)
    {
        var selector = id + ' div';
        var target   = '_blank';
        if(window.location.href.indexOf('/commercebug/lookup') !== -1)
        {
            target = '_top';
        }
        var string = '<h2>Class Lookup</h2><p>Enter a PHP class name and get back object system information.</p><form action="/commercebug/lookup" method="post" target="'+target+'"><input type="text" name="lookup" value="" /><button>Submit</button></form>';        
        string += '<br>';
        string += '<h2>Knockout.js Lookup</h2>' +
            '<p>Enter a Knockout.js template identifier (<code>ui/collection</code>) and get back "raw" template syntax</p>' +
            '<form id="ps-commercebug-class-lookup">' + 
            '<input type="text" name="lookup" value="" />' +
            '<button>Submit</button>' +
            '</form><br><textarea id="ps-commercebug-class-lookup-results" rows="10" cols="72" style="display:none"></textarea><br>';         
        jQueryCb(selector).append(string);            
    };

        
    all_tabs = [
            { id: 'tab-commercebug-request', caption: 'Request' },
            { id: 'tab-commercebug-crud-models', caption: 'Crud Models', closable: false },
            { id: 'tab-commercebug-collections', caption: 'Collections', closable: false },
            { id: 'tab-commercebug-blocks', caption: 'Blocks', closable: false },
            { id: 'tab-commercebug-layout', caption: 'Layout', closable: false },
//             { id: 'tab-commercebug-other-files', caption: 'Other Files', closable: false },
            { id: 'tab-commercebug-ui', caption: 'KO Scopes', closable: false },
            { id: 'tab-commercebug-events', caption: 'Events', closable: false },
            { id: 'tab-commercebug-observers', caption: 'Observers', closable: false },
            { id: 'tab-commercebug-class-lookup', caption: 'Class Lookup', closable: false },            
            // { id: 'tab-commercebug-tasks', caption: 'Tasks', closable: false }                        
    ];

    setupSkeleton(all_tabs);    
    setupJqueryHook();
    
    setupRequestTab('#commercebug-request');
    setupCrudModelTab('#commercebug-crud-models');
    setupCollectionsTab('#commercebug-collections');
    setupBlocksTab('#commercebug-blocks');
    setupLayoutTab('#commercebug-layout');
    //setupOtherFilesTab('#commercebug-other-files');
    setupUiComponentTab('#commercebug-ui');
    setupEventsTab('#commercebug-events');
    setupObserversTab('#commercebug-observers');
    setupClassLookupTab('#commercebug-class-lookup');
    
    var tab_id = localStorage.pulsestorm_commercebug_lasttab;
    if(!tab_id)
    {
        tab_id = 'tab-commercebug-request';
    }
    switchToTab(tab_id);    
    setupKeyboardShortcuts(all_tabs);
});